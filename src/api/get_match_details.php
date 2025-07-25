<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
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
    http_response_code(404);
    echo json_encode(['error' => 'Матч не найден']);
    exit;
}

// Получаем игроков
$playersQuery = $db->prepare("
       SELECT p.name, p.number, p.position, mp.goals, mp.assists, mp.goals_conceded
    FROM match_players mp
    JOIN players p ON p.id = mp.player_id
    WHERE mp.match_id = ? AND mp.played = 1
    ORDER BY 
      FIELD(p.position, 'Вратарь', 'Защитник', 'Полузащитник', 'Нападающий', 'Тренер'),
      p.number ASC
");
$playersQuery->bind_param("i", $id);
$playersQuery->execute();
$players = $playersQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$playersQuery->close();

// Отправляем JSON
echo json_encode([
    'match' => $matchResult,
    'players' => $players
], JSON_UNESCAPED_UNICODE);
