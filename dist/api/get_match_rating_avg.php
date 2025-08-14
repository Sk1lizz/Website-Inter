<?php
// /api/get_match_rating_avg.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// --- read params
$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
$month    = isset($_GET['month']) ? trim($_GET['month']) : '';        // YYYY-MM
$start    = isset($_GET['start']) ? trim($_GET['start']) : '';        // YYYY-MM-DD
$end      = isset($_GET['end'])   ? trim($_GET['end'])   : '';        // YYYY-MM-DD

if ($playerId <= 0) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'player_id is required']); exit;
}

// --- build WHERE
$where  = "r.target_player_id = ? AND r.rating IS NOT NULL";
$params = [$playerId];
$types  = "i";

// month filter (higher precedence than start/end if both are sent)
if ($month !== '') {
  // нормализуем: YYYY-MM
  if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'month must be YYYY-MM']); exit;
  }
  // для совместимости с MySQL DATE/DATETIME: LEFT(created_at,7) = 'YYYY-MM'
  $where .= " AND LEFT(r.created_at, 7) = ?";
  $params[] = $month;
  $types   .= "s";
} else {
  // range by created_at
  if ($start !== '') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
      http_response_code(400);
      echo json_encode(['success'=>false,'message'=>'start must be YYYY-MM-DD']); exit;
    }
    $where   .= " AND r.created_at >= ?";
    $params[] = $start . " 00:00:00";
    $types   .= "s";
  }
  if ($end !== '') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
      http_response_code(400);
      echo json_encode(['success'=>false,'message'=>'end must be YYYY-MM-DD']); exit;
    }
    $where   .= " AND r.created_at <= ?";
    $params[] = $end . " 23:59:59";
    $types   .= "s";
  }
}

$sql = "
  SELECT
    AVG(r.rating) AS avg_rating,
    COUNT(*)      AS ratings_count
  FROM player_ratings r
  WHERE $where
";

$stmt = $db->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode([
    'success'=>false,
    'message'=>'db_prepare_error',
    'errno'=>$db->errno,
    'error'=>$db->error
  ]);
  exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

$avg   = ($row && $row['avg_rating'] !== null) ? (float)$row['avg_rating'] : null;
$count = ($row) ? (int)$row['ratings_count'] : 0;

// округлим до 1 знака в выводе, но сохраним и «сырое» значение на всякий
echo json_encode([
  'success' => true,
  'player_id' => $playerId,
  'filters' => [
    'month' => $month !== '' ? $month : null,
    'start' => $start !== '' ? $start : null,
    'end'   => $end   !== '' ? $end   : null
  ],
 'avg'          => $avg !== null ? round($avg, 1) : null,
  'avg_all_time' => $avg,                 // ← добавили для фронта
 'count'        => $count,
 'avg_raw'      => $avg
], JSON_UNESCAPED_UNICODE);
