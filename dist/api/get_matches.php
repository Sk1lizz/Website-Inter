<?php
session_start();
require_once __DIR__ . '/../db.php'; // Должен создать $db = new mysqli(...)
header('Content-Type: application/json; charset=utf-8');

$teamId = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
$year   = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($teamId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'team_id is required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT id, teams_id, year, date, championship_name, tour, our_team,
           opponent, our_goals, opponent_goals, match_result, goals, assists
    FROM result
    WHERE teams_id = ? AND year = ?
    ORDER BY date DESC, id DESC
";

$stmt = $db->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed'], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param('ii', $teamId, $year);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'id'             => (int)$r['id'],
        'date'           => $r['date'],
        'championship_name' => $r['championship_name'],
        'tour'           => $r['tour'],
        'our_team'       => $r['our_team'],
        'opponent'       => $r['opponent'],
        'our_goals'      => is_numeric($r['our_goals']) ? (int)$r['our_goals'] : null,
        'opponent_goals' => is_numeric($r['opponent_goals']) ? (int)$r['opponent_goals'] : null,
        'match_result'   => $r['match_result'],
        'goals'          => $r['goals'],
        'assists'        => $r['assists'],
    ];
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
