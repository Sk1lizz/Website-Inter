<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
$month    = isset($_GET['month']) ? trim($_GET['month']) : ''; // YYYY-MM

if ($playerId <= 0) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'player_id is required']); exit;
}

$params = [$playerId];
$types  = "i";
$where  = "ta.player_id = ? AND ta.status = 1 AND ta.rating IS NOT NULL";

if ($month !== '') {
  if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'month must be YYYY-MM']); exit;
  }
  $where .= " AND LEFT(ts.training_date,7) = ?";
  $params[] = $month;
  $types   .= "s";
}

$sql = "
  SELECT AVG(ta.rating) AS avg_rating, COUNT(*) AS ratings_count
  FROM training_attendance ta
  JOIN training_sessions ts ON ts.id = ta.training_id
  WHERE $where
";
$stmt = $db->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'db_prepare_error','errno'=>$db->errno,'error'=>$db->error]); exit;
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

$avg   = ($row && $row['avg_rating'] !== null) ? (float)$row['avg_rating'] : null;
$count = ($row) ? (int)$row['ratings_count'] : 0;

echo json_encode([
  'success' => true,
  'player_id' => $playerId,
  'month' => $month !== '' ? $month : null,
  'avg_all_time' => $month === '' ? ($avg !== null ? round($avg, 2) : null) : null,
  'avg' => $avg !== null ? round($avg, 2) : null,
  'count' => $count
], JSON_UNESCAPED_UNICODE);