<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

$teamId = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
if ($teamId <= 0) {
  echo json_encode(['success'=>false,'message'=>'team_id required']);
  exit;
}

$now = new DateTime();
$year = (int)$now->format('Y');
$month = (int)$now->format('m');

// Текущий месяц
$startCur = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$endCur   = (new DateTime($startCur))->modify('last day of this month')->format('Y-m-d');

// Прошлый месяц
$prev = (new DateTime($startCur))->modify('-1 month');
$startPrev = $prev->format('Y-m-01');
$endPrev   = $prev->format('Y-m-t');

// Текущий год
$startYear = "$year-01-01";
$endYear   = "$year-12-31";

function getPercent($db, $teamId, $start, $end) {
  $sql = "
    SELECT 
      SUM(CASE WHEN ta.status = 1 THEN 1 ELSE 0 END) AS attended,
      SUM(CASE WHEN ta.status IN (0,1) THEN 1 ELSE 0 END) AS total
    FROM training_sessions ts
    JOIN training_attendance ta ON ta.training_id = ts.id
    WHERE ts.team_id = ? AND ts.training_date BETWEEN ? AND ?
  ";
  $stmt = $db->prepare($sql);
  $stmt->bind_param('iss', $teamId, $start, $end);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  if (!$res || $res['total'] == 0) return 0;
  return round(($res['attended'] / $res['total']) * 100);
}

function getPlayers75($db, $teamId, $start, $end) {
  $sql = "
    SELECT p.id, p.name,
      SUM(CASE WHEN ta.status = 1 THEN 1 ELSE 0 END) AS attended,
      SUM(CASE WHEN ta.status IN (0,1) THEN 1 ELSE 0 END) AS total
    FROM training_sessions ts
    JOIN training_attendance ta ON ta.training_id = ts.id
    JOIN players p ON p.id = ta.player_id
    WHERE ts.team_id = ? AND ts.training_date BETWEEN ? AND ?
    GROUP BY p.id, p.name
    HAVING total > 0 AND (attended/total) >= 0.75
    ORDER BY p.name
  ";
  $stmt = $db->prepare($sql);
  $stmt->bind_param('iss', $teamId, $start, $end);
  $stmt->execute();
  $res = $stmt->get_result();
  $players = [];
  while ($row = $res->fetch_assoc()) {
    $players[] = [
      'id' => (int)$row['id'],
      'name' => $row['name'],
      'percent' => round(($row['attended']/$row['total'])*100)
    ];
  }
  return $players;
}

echo json_encode([
  'success' => true,
  'current_month' => getPercent($db, $teamId, $startCur, $endCur),
  'previous_month' => getPercent($db, $teamId, $startPrev, $endPrev),
  'year' => getPercent($db, $teamId, $startYear, $endYear),
  'players' => [
    'current_month' => getPlayers75($db, $teamId, $startCur, $endCur),
    'previous_month' => getPlayers75($db, $teamId, $startPrev, $endPrev),
    'year' => getPlayers75($db, $teamId, $startYear, $endYear)
  ]
], JSON_UNESCAPED_UNICODE);
