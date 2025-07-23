<?php
require_once 'db.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => 'Нет ID']);
    exit;
}

// Получаем матч
$matchQuery = $db->prepare("SELECT * FROM result WHERE id = ?");
$matchQuery->bind_param("i", $id);
$matchQuery->execute();
$matchResult = $matchQuery->get_result()->fetch_assoc();
$matchQuery->close();

if (!$matchResult) {
    echo json_encode(['error' => 'Матч не найден']);
    exit;
}

// Получаем игроков
$playersQuery = $db->prepare("
    SELECT p.name, mp.goals, mp.assists, mp.goals_conceded, mp.clean_sheet
    FROM match_players mp
    JOIN players p ON p.id = mp.player_id
    WHERE mp.match_id = ?
");
$playersQuery->bind_param("i", $id);
$playersQuery->execute();
$players = $playersQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$playersQuery->close();

echo json_encode([
    'match' => $matchResult,
    'players' => $players
]);
