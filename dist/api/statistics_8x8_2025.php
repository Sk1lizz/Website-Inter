<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';
header('Content-Type: application/json');

$teamId = 1;
$year = 2025;

$sql = "
    SELECT date, our_goals, opponent_goals, match_result, opponent
    FROM result
    WHERE teams_id = ? AND year = ?
    ORDER BY STR_TO_DATE(date, '%d.%m.%Y') ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teamId, $year);
$stmt->execute();
$result = $stmt->get_result();

$matches = [];
$total = $wins = $draws = $losses = 0;
$goalsFor = $goalsAgainst = 0;
$form = [];
$bestWin = null;
$bestLoss = null;
$currentWinStreak = 0;
$maxWinStreak = 0;
$currentUnbeatenStreak = 0;
$maxUnbeatenStreak = 0;

while ($row = $result->fetch_assoc()) {
    $total++;
    $goalsFor += (int)$row['our_goals'];
    $goalsAgainst += (int)$row['opponent_goals'];
    $form[] = $row['match_result'];

    // Победы, ничьи, поражения
    if ($row['match_result'] === 'W') {
        $wins++;
        $currentWinStreak++;
        $maxWinStreak = max($maxWinStreak, $currentWinStreak);
        $currentUnbeatenStreak++;
        $maxUnbeatenStreak = max($maxUnbeatenStreak, $currentUnbeatenStreak);

        // Проверка на крупную победу
        $diff = $row['our_goals'] - $row['opponent_goals'];
        if (!$bestWin || $diff > ($bestWin['our_goals'] - $bestWin['opponent_goals'])) {
            $bestWin = $row;
        }

    } elseif ($row['match_result'] === 'X') {
        $draws++;
        $currentWinStreak = 0;
        $currentUnbeatenStreak++;
        $maxUnbeatenStreak = max($maxUnbeatenStreak, $currentUnbeatenStreak);

    } elseif ($row['match_result'] === 'L') {
        $losses++;
        $currentWinStreak = 0;
        $currentUnbeatenStreak = 0;

        // Проверка на крупное поражение
        $diff = $row['opponent_goals'] - $row['our_goals'];
        if (!$bestLoss || $diff > ($bestLoss['opponent_goals'] - $bestLoss['our_goals'])) {
            $bestLoss = $row;
        }
    }
}

$form5 = array_slice(array_reverse($form), 0, 5);

$response = [
    'matches' => $total,
    'wins' => $wins,
    'draws' => $draws,
    'losses' => $losses,
    'goals_for' => $goalsFor,
    'goals_against' => $goalsAgainst,
    'difference' => $goalsFor - $goalsAgainst,
    'form' => implode('', $form5),
    'win_streak' => $maxWinStreak,
    'unbeaten_streak' => $maxUnbeatenStreak,
    'best_win' => $bestWin ? $bestWin['opponent'] . " ({$bestWin['our_goals']}:{$bestWin['opponent_goals']})" : null,
    'worst_loss' => $bestLoss ? $bestLoss['opponent'] . " ({$bestLoss['our_goals']}:{$bestLoss['opponent_goals']})" : null
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);