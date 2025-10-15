<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';

// Получаем список игроков, у которых sick = 1
$sql = "SELECT player_id FROM fantasy_players WHERE sick = 1";
$res = $db->query($sql);

$out = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $out[] = (int)$r['player_id'];
    }
}

echo json_encode($out);