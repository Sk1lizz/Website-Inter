<?php
require_once '../db.php';
header('Content-Type: application/json');

$matchId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
if (!$matchId) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("
    SELECT 
        p.id, 
        p.name, 
        mp.goals, 
        mp.assists, 
        mp.goals_conceded
    FROM match_players mp
    JOIN players p ON mp.player_id = p.id
    WHERE mp.match_id = ? AND mp.played = 1 AND p.position != 'Тренер'
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка подготовки запроса']);
    exit;
}

$stmt->bind_param("i", $matchId);
$stmt->execute();
$result = $stmt->get_result();

$players = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($players);
exit;
