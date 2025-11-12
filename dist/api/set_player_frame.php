<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['player_id'])) { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'auth']); exit; }

$playerId = (int)$_SESSION['player_id'];
$frameKey = isset($_POST['frame_key']) ? trim($_POST['frame_key']) : '';

if ($frameKey === '') { echo json_encode(['ok'=>false,'msg'=>'empty']); exit; }

// проверяем, что рамка куплена
$chk = $db->prepare("SELECT 1 FROM player_unlocked_frames WHERE player_id = ? AND frame_key = ?");
$chk->bind_param("is", $playerId, $frameKey);
$chk->execute();
if (!$chk->get_result()->fetch_row()) {
  echo json_encode(['ok'=>false,'msg'=>'not_owned']); exit;
}

// назначаем
$up = $db->prepare("
  INSERT INTO player_frames (player_id, frame_key)
  VALUES (?, ?)
  ON DUPLICATE KEY UPDATE frame_key = VALUES(frame_key), updated_at = CURRENT_TIMESTAMP
");
$up->bind_param("is", $playerId, $frameKey);
$up->execute();

echo json_encode(['ok'=>true]);
