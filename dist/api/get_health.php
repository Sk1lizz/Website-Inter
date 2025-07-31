<?php
require_once '../db.php';
header('Content-Type: application/json');

$playerId = (int)($_GET['player_id'] ?? 0);
if (!$playerId) exit(json_encode(['error' => 'no id']));

// если нет записи — создаём с дефолтом
$db->query("INSERT IGNORE INTO player_health (player_id) VALUES ($playerId)");

$res = $db->query("SELECT last_ekg_date, has_heart_condition FROM player_health WHERE player_id = $playerId");
echo json_encode($res->fetch_assoc());
