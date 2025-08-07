<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../db.php';
header('Content-Type: application/json');

// Самая крупная победа
$sql_win = "
    SELECT our_team, opponent, our_goals, opponent_goals, date
    FROM result
    ORDER BY (our_goals - opponent_goals) DESC
    LIMIT 1
";

// Топ-3 самых крупных поражения
$sql_loss = "
    SELECT our_team, opponent, our_goals, opponent_goals, date
    FROM result
    WHERE our_goals < opponent_goals
    ORDER BY (our_goals - opponent_goals) ASC
    LIMIT 3
";

// Форматирует строку матча
function formatMatch($row, $isWin = false) {
    $score = "{$row['our_goals']}:{$row['opponent_goals']}";
    $year = date('Y', strtotime($row['date']));
    $our = htmlspecialchars($row['our_team']);
    $opp = htmlspecialchars($row['opponent']);

    return $isWin
        ? "$opp ($score) $our ($year)"
        : "$our ($score) $opp ($year)";
}

// Получаем массив полных лет по каждой команде
$fullYearsSql = "
    SELECT our_team, YEAR(date) as y
    FROM result
    GROUP BY our_team, YEAR(date)
    HAVING COUNT(DISTINCT MONTH(date)) = 12
";

$fullYears = [];
$resFullYears = $db->query($fullYearsSql);
while ($row = $resFullYears->fetch_assoc()) {
    $fullYears[] = [
        'our_team' => $row['our_team'],
        'year' => $row['y']
    ];
}

// Если нет полных годов — завершаем
if (empty($fullYears)) {
    echo json_encode(["error" => "Нет полных годов"]);
    exit;
}

// Преобразуем в строку условий для WHERE IN
$conditions = [];
foreach ($fullYears as $row) {
    $team = $db->real_escape_string($row['our_team']);
    $year = (int)$row['year'];
    $conditions[] = "(our_team = '$team' AND YEAR(date) = $year)";
}
$whereFullYears = implode(" OR ", $conditions);

// Получение рекордов по полным годам
$recordsSql = "
    SELECT 
        our_team,
        YEAR(date) as y,
        COUNT(*) as matches,
        SUM(match_result = 'W') as wins,
        SUM(match_result = 'L') as losses,
        SUM(our_goals) as goals_scored,
        SUM(opponent_goals) as goals_conceded
    FROM result
    WHERE $whereFullYears
    GROUP BY our_team, YEAR(date)
";

$resRecords = $db->query($recordsSql);

$records = [];
while ($row = $resRecords->fetch_assoc()) {
    $key = $row['our_team'] . '|' . $row['y'];
    $records[$key] = $row;
}

// Функция поиска по критерию
function findExtreme($records, $field, $type = 'max') {
    $best = null;
    foreach ($records as $row) {
        if (!isset($row[$field])) continue;
        if ($best === null || 
            ($type === 'max' && $row[$field] > $best[$field]) ||
            ($type === 'min' && $row[$field] < $best[$field])) {
            $best = $row;
        }
    }
    return $best;
}

// Собираем финальный JSON
$response = [
    "biggest_win" => null,
    "biggest_losses" => [],
    "year_records" => [
        "max_matches"     => findExtreme($records, 'matches', 'max'),
        "max_wins"        => findExtreme($records, 'wins', 'max'),
        "min_losses"      => findExtreme($records, 'losses', 'min'),
        "max_goals"       => findExtreme($records, 'goals_scored', 'max'),
        "min_conceded"    => findExtreme($records, 'goals_conceded', 'min'),
        "min_matches"     => findExtreme($records, 'matches', 'min'),
        "min_wins"        => findExtreme($records, 'wins', 'min'),
        "max_losses"      => findExtreme($records, 'losses', 'max'),
        "min_goals"       => findExtreme($records, 'goals_scored', 'min'),
        "max_conceded"    => findExtreme($records, 'goals_conceded', 'max'),
    ]
];

// Победа/поражения
$res_win = $db->query($sql_win);
$res_loss = $db->query($sql_loss);

if ($res_win) {
    $row = $res_win->fetch_assoc();
    $response['biggest_win'] = formatMatch($row, true);
}

if ($res_loss) {
    while ($row = $res_loss->fetch_assoc()) {
        $response['biggest_losses'][] = formatMatch($row);
    }
}

$sql_series = "
    SELECT our_team, match_result, DATE(date) as match_date, YEAR(date) as y
    FROM result
    ORDER BY our_team, date
";

$res_series = $db->query($sql_series);
if (!$res_series) {
    http_response_code(500);
    echo json_encode(["error" => $db->error]);
    exit;
}

// Вычисление серий
$series = [];
$current = [];

while ($row = $res_series->fetch_assoc()) {
    $team = $row['our_team'];
    $result = $row['match_result'];
    $year = $row['y'];

    if (!isset($current[$team])) {
        $current[$team] = ['W' => 0, 'L' => 0, 'last' => null];
    }

    if ($result === 'W') {
        $current[$team]['W']++;
        $current[$team]['L'] = 0;
    } elseif ($result === 'L') {
        $current[$team]['L']++;
        $current[$team]['W'] = 0;
    } else {
        $current[$team]['W'] = 0;
        $current[$team]['L'] = 0;
    }

    // Обновляем максимум
    if (!isset($series['W']) || $current[$team]['W'] > $series['W']['count']) {
        $series['W'] = [
            'count' => $current[$team]['W'],
            'team' => $team,
            'year' => $year
        ];
    }
    if (!isset($series['L']) || $current[$team]['L'] > $series['L']['count']) {
        $series['L'] = [
            'count' => $current[$team]['L'],
            'team' => $team,
            'year' => $year
        ];
    }
}

// Добавляем в JSON-ответ
$response['streaks'] = [
    'longest_win_streak' => $series['W'] ?? null,
    'longest_loss_streak' => $series['L'] ?? null
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);