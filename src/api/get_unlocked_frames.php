<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
if ($playerId <= 0) { echo json_encode([]); exit; }

$res = $db->prepare("SELECT frame_key FROM player_unlocked_frames WHERE player_id = ?");
$res->bind_param("i", $playerId);
$res->execute();
$rows = $res->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(array_map(fn($r) => $r['frame_key'], $rows));
