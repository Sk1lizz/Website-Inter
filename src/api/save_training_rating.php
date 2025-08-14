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

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$trainingId = (int)($input['training_id'] ?? 0);
$intensity  = (int)($input['intensity']  ?? 0);
$fatigue    = (int)($input['fatigue']    ?? 0);
$mood       = (int)($input['mood']       ?? 0);
$enjoyment  = (int)($input['enjoyment']  ?? 0);

if ($trainingId <= 0) out(['success'=>false,'message'=>'invalid_training_id'], 422);
foreach ([$intensity,$fatigue,$mood,$enjoyment] as $v) {
  if ($v < 1 || $v > 5) out(['success'=>false,'message'=>'invalid_scales'], 422);
}

/* 1) проверяем, что тренировка этой команды, игрок присутствовал, и дата в прошлом */
$sqlCheck = "
  SELECT ts.training_date
  FROM training_attendance ta
  JOIN training_sessions ts
    ON ts.id = ta.training_id
   AND ts.team_id = ?
  WHERE ta.player_id = ?
    AND ta.training_id = ?
    AND ta.status = 1
  LIMIT 1
";
$stmt = $db->prepare($sqlCheck);
if (!$stmt) out(['success'=>false,'message'=>'db_prepare_error_check','errno'=>$db->errno,'error'=>$db->error], 500);
$stmt->bind_param('iii', $teamId, $playerId, $trainingId);
if (!$stmt->execute()) out(['success'=>false,'message'=>'db_exec_error_check','errno'=>$db->errno,'error'=>$db->error], 500);

$res = $stmt->get_result();
$att = $res ? $res->fetch_assoc() : null;
if (!$att) out(['success'=>false,'message'=>'not_present_or_wrong_team'], 403);
if (strtotime($att['training_date']) >= strtotime(date('Y-m-d'))) {
  out(['success'=>false,'message'=>'not_past_training'], 403);
}

/* 2) дубль */
$stmt = $db->prepare("SELECT 1 FROM training_ratings WHERE player_id=? AND training_id=? LIMIT 1");
if (!$stmt) out(['success'=>false,'message'=>'db_prepare_error_dup','errno'=>$db->errno,'error'=>$db->error], 500);
$stmt->bind_param('ii', $playerId, $trainingId);
if (!$stmt->execute()) out(['success'=>false,'message'=>'db_exec_error_dup','errno'=>$db->errno,'error'=>$db->error], 500);
$dup = $stmt->get_result();
if ($dup && $dup->fetch_row()) out(['success'=>false,'message'=>'already_rated']);

/* 3) вставка */
$sqlIns = "
  INSERT INTO training_ratings (player_id, team_id, training_id, intensity, fatigue, mood, enjoyment)
  VALUES (?,?,?,?,?,?,?)
";
$stmt = $db->prepare($sqlIns);
if (!$stmt) out(['success'=>false,'message'=>'db_prepare_error_insert','errno'=>$db->errno,'error'=>$db->error], 500);
$stmt->bind_param('iiiiiii', $playerId, $teamId, $trainingId, $intensity, $fatigue, $mood, $enjoyment);
if (!$stmt->execute()) out(['success'=>false,'message'=>'db_insert_error','errno'=>$db->errno,'error'=>$db->error], 500);

out(['success'=>true]);
