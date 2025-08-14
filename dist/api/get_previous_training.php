<?php
session_start();
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

function out($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

if (!isset($_SESSION['player_id'], $_SESSION['team_id'])) {
  out(['success'=>false,'message'=>'unauthorized'], 401);
}

$playerId = (int)$_SESSION['player_id'];
$teamId   = (int)$_SESSION['team_id'];

/*
Схема:
  training_sessions(id, team_id, training_date)
  training_attendance(id, training_id, player_id, status)   -- 1 = присутствовал
  training_ratings(id, player_id, team_id, training_id, ...)
*/
$sql = "
  SELECT ts.id AS training_id, ts.training_date
  FROM training_attendance ta
  JOIN training_sessions ts
    ON ts.id = ta.training_id
   AND ts.team_id = ?
  LEFT JOIN training_ratings tr
    ON tr.player_id = ta.player_id
   AND tr.training_id = ta.training_id
  WHERE ta.player_id = ?
    AND ta.status = 1
    AND ts.training_date < CURDATE()
    AND tr.id IS NULL
  ORDER BY ts.training_date DESC
  LIMIT 1
";

$stmt = $db->prepare($sql);
if (!$stmt) out(['success'=>false,'message'=>'db_prepare_error','errno'=>$db->errno,'error'=>$db->error], 500);
$stmt->bind_param('ii', $teamId, $playerId);
if (!$stmt->execute()) out(['success'=>false,'message'=>'db_exec_error','errno'=>$db->errno,'error'=>$db->error], 500);

$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

if (!$row) {
  out(['success'=>true,'can_rate'=>false]);
}

out([
  'success'=>true,
  'can_rate'=>true,
  'training'=>[
    'id'   => (int)$row['training_id'],
    'date' => $row['training_date'],
  ]
]);
