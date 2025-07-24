<?php
require '../db.php';
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// Исключённые ID
$excludedIds = [3, 4];
$excludedIdsStr = implode(',', $excludedIds);

// 📌 Получаем список всех активных игроков, у которых есть хотя бы 1 ачивка
$playersRes = $db->query("
    SELECT DISTINCT ps.player_id 
    FROM player_success ps
    INNER JOIN players p ON p.id = ps.player_id
    WHERE ps.player_id NOT IN ($excludedIdsStr)
");

$playerIds = [];
while ($row = $playersRes->fetch_assoc()) {
    $playerIds[] = (int)$row['player_id'];
}

$totalPlayers = count($playerIds);

// 📌 Считаем количество игроков по каждой ачивке
$statRes = $db->query("
    SELECT ps.success_id, COUNT(*) as count 
    FROM player_success ps
    WHERE ps.player_id NOT IN ($excludedIdsStr)
    GROUP BY ps.success_id
");

$stats = [];
while ($row = $statRes->fetch_assoc()) {
    $stats[(int)$row['success_id']] = (int)$row['count'];
}

echo json_encode([
    "total_players" => $totalPlayers,
    "counts" => $stats
]);
