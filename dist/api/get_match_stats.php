<?php
require_once '../db.php';
header('Content-Type: application/json');

$playerId = (int)($_GET['player_id'] ?? 0);
$teamId   = (int)($_GET['team_id'] ?? 0);
if (!$playerId || !$teamId) exit(json_encode([]));

// Получаем дату присоединения игрока
$stmt = $db->prepare("SELECT join_date FROM players WHERE id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$player = $stmt->get_result()->fetch_assoc();

if (!$player || !$player['join_date']) exit(json_encode([]));

$joinDate = $player['join_date'];
$now = new DateTime();
$year = $now->format('Y');
$month = $now->format('m');

// Берём только матчи в этом месяце после join_date
$stmt = $db->prepare("
    SELECT * FROM result 
    WHERE teams_id = ? 
      AND YEAR(date) = ? AND MONTH(date) = ? 
      AND date >= ?
    ORDER BY date DESC
");
$stmt->bind_param("isss", $teamId, $year, $month, $joinDate);
$stmt->execute();
$matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$data = [];

foreach ($matches as $match) {
    $matchId = $match['id'];
    $date = $match['date'];

    // Статистика игрока
    $stmt2 = $db->prepare("SELECT * FROM match_players WHERE match_id = ? AND player_id = ?");
    $stmt2->bind_param("ii", $matchId, $playerId);
    $stmt2->execute();
    $stat = $stmt2->get_result()->fetch_assoc();

    $data[] = [
        'date' => $date,
        'played' => $stat ? (bool)$stat['played'] : false,
        'goals' => $stat['goals'] ?? 0,
        'assists' => $stat['assists'] ?? 0,
        'goals_conceded' => $stat['goals_conceded'] ?? 0,
    ];
}

echo json_encode($data);
