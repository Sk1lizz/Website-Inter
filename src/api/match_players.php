<?php
require_once '../db.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['match_id'], $input['year'], $input['players']) || !is_array($input['players'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректный формат запроса']);
    exit;
}

$match_id = (int)$input['match_id'];
$year     = (int)$input['year'];

foreach ($input['players'] as $player_id => $info) {
    $player_id       = (int)$player_id;

    // базовые поля
    $played          = isset($info['played']) ? 1 : 0;
    $goals           = (int)($info['goals'] ?? 0);
    $assists         = (int)($info['assists'] ?? 0);
    $goals_conceded  = (int)($info['goals_conceded'] ?? 0);
    $clean_sheet     = !empty($info['clean_sheet']) ? 1 : 0;

    // новые поля
    $yellow_cards      = (int)($info['yellow_cards'] ?? 0);
    $red_cards         = (int)($info['red_cards'] ?? 0);
    $missed_penalties  = (int)($info['missed_penalties'] ?? 0);

    // Вставка в match_players (добавили 3 новых поля)
    $stmt = $db->prepare("
        INSERT INTO match_players
            (match_id, player_id, played, goals, assists, clean_sheet, goals_conceded, yellow_cards, red_cards, missed_penalties)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt) {
        $stmt->bind_param(
            "iiiiiiiiii",
            $match_id,
            $player_id,
            $played,
            $goals,
            $assists,
            $clean_sheet,
            $goals_conceded,
            $yellow_cards,
            $red_cards,
            $missed_penalties
        );
        $stmt->execute();
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка подготовки запроса к match_players']);
        exit;
    }

    // Обновление годовой статистики — БЕЗ новых полей (как договорились)
    $update = $db->prepare("
        INSERT INTO player_statistics_2025 (player_id, matches, goals, assists, zeromatch, lostgoals)
        VALUES (?, 1, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            matches   = matches + 1,
            goals     = goals + VALUES(goals),
            assists   = assists + VALUES(assists),
            zeromatch = zeromatch + VALUES(zeromatch),
            lostgoals = lostgoals + VALUES(lostgoals)
    ");
    if ($update) {
        $update->bind_param("iiiii", $player_id, $goals, $assists, $clean_sheet, $goals_conceded);
        $update->execute();
        $update->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка подготовки апдейта статистики']);
        exit;
    }
}

echo json_encode(['success' => true, 'match_id' => $match_id]);
