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

// Начинаем с начала прошлого месяца или даты присоединения — что позже
$firstDayLastMonth = (new DateTime("first day of last month"))->format('Y-m-d');
$minDate = max($firstDayLastMonth, $joinDate);

// Получаем матчи команды начиная с этой даты
$stmt = $db->prepare("
    SELECT * FROM result 
    WHERE teams_id = ? 
      AND date >= ? 
    ORDER BY date DESC
");
$stmt->bind_param("is", $teamId, $minDate);
$stmt->execute();
$matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Проверка: уже оценивал ли этот матч
function hasAlreadyRated($db, $matchId, $playerId) {
    $stmt = $db->prepare("SELECT 1 FROM player_ratings WHERE match_id = ? AND rated_by_player_id = ? LIMIT 1");
    $stmt->bind_param("ii", $matchId, $playerId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

$data = [];

foreach ($matches as $match) {
    $matchId = (int)$match['id'];
    $date = $match['date'];

    // Получаем индивидуальную статистику игрока
    $stmt2 = $db->prepare("SELECT * FROM match_players WHERE match_id = ? AND player_id = ?");
    $stmt2->bind_param("ii", $matchId, $playerId);
    $stmt2->execute();
    $stat = $stmt2->get_result()->fetch_assoc();

    $played = $stat ? (bool)$stat['played'] : false;

    // Получаем среднюю оценку
    $stmt3 = $db->prepare("SELECT AVG(rating) AS avg_rating FROM player_ratings WHERE match_id = ? AND target_player_id = ?");
    $stmt3->bind_param("ii", $matchId, $playerId);
    $stmt3->execute();
    $avg = $stmt3->get_result()->fetch_assoc();
    $averageRating = $avg['avg_rating'] !== null ? round((float)$avg['avg_rating'], 1) : null;

    // Разрешено ли оценивание: игрок играл, сегодня или на следующий день, и ещё не голосовал
    $matchDate = new DateTime($date);
    $now = new DateTime();
    $interval = $now->diff($matchDate)->days;
    $canRate = $played && $now >= $matchDate && $interval <= 1 && !hasAlreadyRated($db, $matchId, $playerId);

    $data[] = [
        'id' => $matchId,
        'date' => $date,
        'played' => $played,
        'goals' => $stat['goals'] ?? 0,
        'assists' => $stat['assists'] ?? 0,
        'goals_conceded' => $stat['goals_conceded'] ?? 0,
        'average_rating' => $averageRating,
        'can_rate' => $canRate
    ];
}

echo json_encode($data);
