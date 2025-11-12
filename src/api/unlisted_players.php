<?php
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$matchId = intval($data['match_id'] ?? 0);
$players = $data['players'] ?? [];

if ($matchId <= 0 || !is_array($players)) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

$stmt = $db->prepare("INSERT INTO unlisted_players (match_id, player_id) VALUES (?, ?)");

foreach ($players as $row) {
    $pid = intval($row['player_id'] ?? 0);
    if ($pid <= 0) continue;
    $stmt->bind_param("ii", $matchId, $pid);
    $stmt->execute();
}

echo json_encode(['success' => true]);
