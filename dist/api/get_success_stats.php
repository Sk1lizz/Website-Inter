<?php
require '../db.php';
header('Content-Type: application/json');

$totalRes = $db->query("SELECT COUNT(*) as total FROM players");
$totalRow = $totalRes->fetch_assoc();
$totalPlayers = (int)$totalRow['total'];

$statRes = $db->query("SELECT success_id, COUNT(*) as count FROM player_success GROUP BY success_id");

$stats = [];
while ($row = $statRes->fetch_assoc()) {
    $stats[(int)$row['success_id']] = (int)$row['count'];
}

echo json_encode([
    "total_players" => $totalPlayers,
    "counts" => $stats
]);