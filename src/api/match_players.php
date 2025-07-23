<?php
require_once '../db.php'; // подключение к БД через $db или $conn

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['match_id'], $input['year'], $input['players']) || !is_array($input['players'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректный формат запроса']);
    exit;
}

$match_id = intval($input['match_id']);
$year = intval($input['year']);

foreach ($input['players'] as $player_id => $info) {
    $player_id = intval($player_id);
    $played = isset($info['played']) ? 1 : 0;
    $goals = intval($info['goals'] ?? 0);
    $assists = intval($info['assists'] ?? 0);
    $goals_conceded = intval($info['goals_conceded'] ?? 0);
    $clean_sheet = isset($info['clean_sheet']) ? 1 : 0;

    // Вставка в match_players
    $stmt = $db->prepare("INSERT INTO match_players (match_id, player_id, played, goals, assists, clean_sheet, goals_conceded) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iiiiiii", $match_id, $player_id, $played, $goals, $assists, $clean_sheet, $goals_conceded);
        $stmt->execute();
        $stmt->close();
    }

    // Обновление статистики игрока за год
    $update = $db->prepare("
        INSERT INTO player_statistics_2025 (player_id, matches, goals, assists, zeromatch, lostgoals)
    VALUES (?, 1, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        matches = matches + 1,
        goals = goals + VALUES(goals),
        assists = assists + VALUES(assists),
        zeromatch = zeromatch + VALUES(zeromatch),
        lostgoals = lostgoals + VALUES(lostgoals)
    ");
    if ($update) {
        $update->bind_param("iiiii", $player_id, $goals, $assists, $clean_sheet, $goals_conceded);
        $update->execute();
        $update->close();
    }
}

echo json_encode(['success' => true, 'match_id' => $match_id]);
