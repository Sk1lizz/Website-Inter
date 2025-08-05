<?php
require_once '../db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Match ID is required']);
    exit;
}

$matchId = (int)$input['id'];
$championship_name = $input['championship_name'] ?? '';
$opponent = $input['opponent'] ?? '';
$our_goals = (int)($input['our_goals'] ?? 0);
$opponent_goals = (int)($input['opponent_goals'] ?? 0);
$date = $input['date'] ?? null;
if (empty($date)) {
    $date = null;
}
$result = $input['result'] ?? null;
$players = $input['players'] ?? [];

// Обновление матча
$stmt = $db->prepare("UPDATE result SET championship_name=?, opponent=?, our_goals=?, opponent_goals=?, date=?, match_result=? WHERE id=?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $db->error]);
    exit;
}
$stmt->bind_param("ssiiisi", $championship_name, $opponent, $our_goals, $opponent_goals, $date, $result, $matchId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed: ' . $stmt->error]);
    exit;
}

// Обновляем/вставляем игроков
$insertStmt = $db->prepare("
    INSERT INTO match_players (match_id, player_id, played, goals, assists, goals_conceded)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      played = VALUES(played),
      goals = VALUES(goals),
      assists = VALUES(assists),
      goals_conceded = VALUES(goals_conceded)
");

if (!$insertStmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed for player insert: ' . $db->error]);
    exit;
}

foreach ($players as $p) {
    $pid = (int)$p['id'];
    $played = 1;
    $goals = (int)$p['goals'];
    $assists = (int)$p['assists'];
    $conceded = (int)($p['goals_conceded'] ?? 0);

    $insertStmt->bind_param("iiiiii", $matchId, $pid, $played, $goals, $assists, $conceded);

    if (!$insertStmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => "Insert/Update failed for player $pid: " . $insertStmt->error]);
        exit;
    }
}

http_response_code(200);
echo json_encode(['success' => true]);
