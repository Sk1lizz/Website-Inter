<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$teamId = $_GET['team_id'] ?? null;

if (!$teamId) {
    http_response_code(400);
    echo json_encode(['error' => 'team_id is required']);
    exit;
}

// Получаем игроков этой команды
$stmt = $db->prepare("SELECT id, name, patronymic, number FROM players WHERE team_id = ?");
$stmt->bind_param("i", $teamId);
$stmt->execute();
$res = $stmt->get_result();
$players = $res->fetch_all(MYSQLI_ASSOC);

// Получаем статистику игроков из player_statistics_all
$playerIds = array_column($players, 'id');
$statsMap = [];

if (!empty($playerIds)) {
    $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
    $types = str_repeat('i', count($playerIds));
    $query = "SELECT * FROM player_statistics_all WHERE player_id IN ($placeholders)";
    $statStmt = $db->prepare($query);
    $statStmt->bind_param($types, ...$playerIds);
    $statStmt->execute();
    $statRes = $statStmt->get_result();

    while ($row = $statRes->fetch_assoc()) {
        $statsMap[$row['player_id']] = $row;
    }
}

// Объединяем
$output = [];
foreach ($players as $player) {
    $output[] = [
        'id' => $player['id'],
        'name' => $player['name'] . ' ' . ($player['patronymic'] ?? ''),
        'number' => $player['number'],
        'stats' => $statsMap[$player['id']] ?? [
            'matches' => 0,
            'goals' => 0,
            'assists' => 0,
            'zeromatch' => 0,
            'lostgoals' => 0,
            'zanetti_priz' => 0
        ]
    ];
}

echo json_encode($output);
