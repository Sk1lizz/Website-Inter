<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$teamId = $_GET['team_id'] ?? null;

if (!$teamId) {
    http_response_code(400);
    echo json_encode(['error' => 'team_id не указан']);
    exit;
}

$stmt = $db->prepare("
    SELECT p.id, p.name, p.join_date
    FROM players p
    WHERE p.team_id = ?
");
$stmt->bind_param("i", $teamId);
$stmt->execute();
$res = $stmt->get_result();
$players = $res->fetch_all(MYSQLI_ASSOC);

if (!$players) {
    http_response_code(404);
    echo json_encode(['error' => 'Игроки не найдены']);
    exit;
}

$today = new DateTime('today');
$results = [];

foreach ($players as $player) {
    $joinDate = new DateTime($player['join_date']);
    $diff = $today->diff($joinDate);
    $yearsInTeam = (int)$diff->y;

    // ближайший юбилей среди 1,2,5,10,15
    $milestones = [1, 2, 5, 10, 15];
    $nextJubilee = null;
    $nextJubileeYear = null;
    $daysUntilNext = PHP_INT_MAX;

    foreach ($milestones as $year) {
        $jubileeDate = (clone $joinDate)->modify("+{$year} years");
        $daysDiff = (int)$today->diff($jubileeDate)->format('%r%a'); // отрицательные — уже прошли
        if ($daysDiff >= 0 && $daysDiff < $daysUntilNext) {
            $nextJubilee = $jubileeDate->format('Y-m-d');
            $nextJubileeYear = $year;
            $daysUntilNext = $daysDiff;
        }
    }

    // дата годовщины в этом году и признак "в этом месяце"
    $anniversaryThisYear = DateTime::createFromFormat('Y-m-d', $today->format('Y') . '-' . $joinDate->format('m-d'));
    // если годовщина в этом году уже прошла, всё равно оставим её как "в этом году" для месяца
    $isAnniversaryMonth = ($anniversaryThisYear->format('m') === $today->format('m'));

    $results[] = [
        'id' => (int)$player['id'],
        'name' => $player['name'],
        'join_date' => $player['join_date'],
        'years_in_team' => $yearsInTeam,
        'has_5_plus' => $yearsInTeam >= 5,
        'next_jubilee' => $nextJubilee,            // Y-m-d или null
        'next_jubilee_year' => $nextJubileeYear,   // 1|2|5|10|15|null
        'days_until_next_jubilee' => $daysUntilNext === PHP_INT_MAX ? null : $daysUntilNext,
        'anniversary_date_this_year' => $anniversaryThisYear->format('Y-m-d'),
        'is_anniversary_month' => $isAnniversaryMonth
    ];
}

echo json_encode($results);
