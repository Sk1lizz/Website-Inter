<?php
require_once '../db.php';
header('Content-Type: application/json');

$teamId = (int)($_GET['team_id'] ?? 0);
$year = (int)($_GET['year'] ?? date('Y'));

if (!$teamId || !$year) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Получаем матчи команды за указанный год
$stmt = $db->prepare("
    SELECT * FROM result
    WHERE teams_id = ? AND YEAR(date) = ?
    ORDER BY date DESC
");
$stmt->bind_param("ii", $teamId, $year);
$stmt->execute();
$matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$data = [];
$monthlyRatings = []; // ['2025-08' => [player_id => ['total' => x, 'count' => y]]]
$playerNames = []; // Кэш имён игроков

foreach ($matches as $match) {
    $matchId = (int)$match['id'];
    $date = $match['date'];
    $monthKey = substr($date, 0, 7); // YYYY-MM

    // Получаем лучшего игрока матча
    $stmtTop = $db->prepare("
        SELECT p.name, AVG(pr.rating) AS avg_rating
        FROM player_ratings pr
        JOIN players p ON p.id = pr.target_player_id
        WHERE pr.match_id = ?
        GROUP BY pr.target_player_id
        ORDER BY avg_rating DESC
        LIMIT 1
    ");
    $stmtTop->bind_param("i", $matchId);
    $stmtTop->execute();
    $topResult = $stmtTop->get_result()->fetch_assoc();

    $topPlayer = $topResult['name'] ?? null;
    $topRating = isset($topResult['avg_rating']) ? round($topResult['avg_rating'], 1) : null;

    // Добавляем рейтинги в месячную выборку
    $stmtAll = $db->prepare("
        SELECT pr.target_player_id, pr.rating
        FROM player_ratings pr
        JOIN players p ON p.id = pr.target_player_id
        WHERE pr.match_id = ? AND p.team_id = ?
    ");
    $stmtAll->bind_param("ii", $matchId, $teamId);
    $stmtAll->execute();
    $allRatings = $stmtAll->get_result();

    while ($row = $allRatings->fetch_assoc()) {
        $pid = $row['target_player_id'];
        $rating = (float)$row['rating'];
        if (!isset($monthlyRatings[$monthKey][$pid])) {
            $monthlyRatings[$monthKey][$pid] = ['total' => 0, 'count' => 0];
        }
        $monthlyRatings[$monthKey][$pid]['total'] += $rating;
        $monthlyRatings[$monthKey][$pid]['count'] += 1;
    }

    $data[] = [
        'id' => $matchId,
        'date' => $date,
        'championship_name' => $match['championship_name'],
        'tour' => $match['tour'],
        'opponent' => $match['opponent'],
        'our_goals' => $match['our_goals'],
        'opponent_goals' => $match['opponent_goals'],
        'match_result' => $match['match_result'],
        'goals' => $match['goals'],
        'assists' => $match['assists'],
        'top_player' => $topPlayer,
        'top_rating' => $topRating,
        'month_key' => $monthKey
    ];
}

// Лучший игрок месяца
$monthlyBest = [];      // ['2025-08' => ['name' => ..., 'avg_rating' => ...]]
$monthlyTop3 = [];      // ['2025-08' => [ {name, avg_rating}, {name, avg_rating}, {name, avg_rating} ]]

foreach ($monthlyRatings as $month => $players) {
    $averages = [];

    foreach ($players as $pid => $stats) {
        if ($stats['count'] < 2) continue; // минимальное количество оценок
        $avg = $stats['total'] / $stats['count'];
        $averages[$pid] = $avg;
    }

    arsort($averages); // сортировка по убыванию рейтинга

    $i = 0;
    foreach ($averages as $pid => $avg) {
        if (!isset($playerNames[$pid])) {
            $stmtName = $db->prepare("SELECT name FROM players WHERE id = ?");
            $stmtName->bind_param("i", $pid);
            $stmtName->execute();
            $res = $stmtName->get_result()->fetch_assoc();
            $playerNames[$pid] = $res['name'] ?? 'Неизвестно';
        }

        $playerInfo = [
            'name' => $playerNames[$pid],
            'avg_rating' => round($avg, 2)
        ];

        if ($i === 0) {
            $monthlyBest[$month] = $playerInfo;
        }

        if (!isset($monthlyTop3[$month])) $monthlyTop3[$month] = [];
        if (count($monthlyTop3[$month]) < 3) {
            $monthlyTop3[$month][] = $playerInfo;
        }

        $i++;
        if ($i >= 3) break;
    }
}

// Возвращаем JSON
echo json_encode([
    'matches' => $data,
    'monthly_best' => $monthlyBest,
    'top3' => $monthlyTop3
]);

// Временно, для отладки
// header('Content-Type: application/json');
file_put_contents('debug_matches.json', json_encode([
    'matches' => $data,
    'monthly_best' => $monthlyBest,
    'top3' => $monthlyTop3
], JSON_PRETTY_PRINT));
