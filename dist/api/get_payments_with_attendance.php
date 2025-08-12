<?php
require_once '../db.php';
header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$teamIdFilter = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
if ($teamIdFilter <= 0) {
    echo json_encode([]);
    exit;
}

function applyReturnBonus($baseAmount, $returnsCount) {
    if ($returnsCount <= 0) return $baseAmount;
    $bonusPercent = 0.2 * $returnsCount;
    $newAmount = $baseAmount * (1 + $bonusPercent);
    $minBonus = 200 * $returnsCount;
    $diff = $newAmount - $baseAmount;
    if ($diff < $minBonus) {
        $newAmount = $baseAmount + $minBonus;
    }
    return ceil($newAmount / 50) * 50;
}

// >>>>>>>>> НОВОЕ: возвращаем id И date, т.к. дальше фильтруем по датам
function getMatchesForPeriod($db, $teamId, $startDate, $endDate) {
    $stmt = $db->prepare("SELECT id, date FROM result WHERE teams_id = ? AND date BETWEEN ? AND ? ORDER BY date");
    $stmt->bind_param("iss", $teamId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// НОВОЕ: выбор матчей по точным датам
function getMatchesForExactDates($db, $teamId, array $dates) {
    if (empty($dates)) return [];
    $placeholders = implode(',', array_fill(0, count($dates), '?'));
    $types = 'i' . str_repeat('s', count($dates));
    $stmt = $db->prepare("SELECT id, date FROM result WHERE teams_id = ? AND date IN ($placeholders) ORDER BY date");
    $params = array_merge([$types, $teamId], $dates);
    $stmt->bind_param(...refValues($params));
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// bind_param helper для переменного количества аргументов
function refValues($arr){
    $refs = [];
    foreach($arr as $key => $value){
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}

// НОВОЕ: получить даты последних выходных месяца (суббота и/или воскресенье,
// которые попадают в указанный месяц)
function getLastWeekendDatesOfMonth(DateTime $anyDayInMonth) {
    $lastDay = (clone $anyDayInMonth)->modify('last day of this month');
    // Найдём последнюю субботу и воскресенье, которые лежат ВНУТРИ этого месяца
    $lastSaturday = (clone $lastDay)->modify('last saturday');
    $dates = [];
    if ((int)$lastSaturday->format('m') === (int)$anyDayInMonth->format('m')) {
        $dates[] = $lastSaturday->format('Y-m-d');
    }
    $lastSunday = (clone $lastDay)->modify('last sunday');
    if ((int)$lastSunday->format('m') === (int)$anyDayInMonth->format('m')) {
        $dates[] = $lastSunday->format('Y-m-d');
    }
    // На случай, если месяц заканчивается в воскресенье — оба будут в месяце.
    // Если заканчивается в субботу — воскресенье выпадет в следующий месяц и не добавится.
    return array_values(array_unique($dates));
}

function getPlayerPlayedMatches($db, $matchIds, $playerId) {
    if (empty($matchIds)) return 0;
    $ids = implode(',', array_map('intval', $matchIds));
    $sql = "SELECT COUNT(*) AS cnt FROM match_players WHERE match_id IN ($ids) AND player_id = $playerId AND played = 1";
    $res = $db->query($sql)->fetch_assoc();
    return (int)($res['cnt'] ?? 0);
}

$today = new DateTime();
$currentMonthStart = $today->format("Y-m-01");
$currentMonthEnd = $today->format("Y-m-t");

$prevMonth = (clone $today)->modify('first day of last month');
$prevMonthStart = $prevMonth->format("Y-m-01");
$prevMonthEnd = $prevMonth->format("Y-m-t");

$data = [];

$playersRes = $db->prepare("SELECT id, name, position, team_id FROM players WHERE team_id = ?");
$playersRes->bind_param("i", $teamIdFilter);
$playersRes->execute();
$players = $playersRes->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($players as $player) {
    $teamId = (int)$player['team_id'];

    if ($teamId === 2) {
        // НОВОЕ: строим список дат для включения/исключения
        $currMonthObj = new DateTime($currentMonthStart);
        $prevMonthObj = new DateTime($prevMonthStart);

        $excludeCurrent = getLastWeekendDatesOfMonth($currMonthObj); // исключить из текущего месяца
        $includePrev   = getLastWeekendDatesOfMonth($prevMonthObj);  // добавить из прошлого месяца

        // 1) Базовый набор: все матчи текущего месяца
        $matchesCurr = getMatchesForPeriod($db, $teamId, $currentMonthStart, $currentMonthEnd);

        // 2) Исключаем последние выходные текущего месяца
        $filteredCurr = array_values(array_filter($matchesCurr, function($m) use ($excludeCurrent) {
            return !in_array($m['date'], $excludeCurrent, true);
        }));

        // 3) Добавляем последние выходные ПРЕДыдущего месяца (могут быть 0–2 даты)
        $matchesPrevWeekend = getMatchesForExactDates($db, $teamId, $includePrev);

        // 4) Итоговый набор матчей для расчёта посещаемости
        $allMatches = array_merge($filteredCurr, $matchesPrevWeekend);

        // Уникальные id (на всякий случай)
        $matchIds = array_values(array_unique(array_column($allMatches, 'id')));

        $totalMatches = count($matchIds);
    } elseif ($teamId === 1) {
        // Было как раньше: прошлый месяц целиком
        $matches = getMatchesForPeriod($db, $teamId, $prevMonthStart, $prevMonthEnd);
        $matchIds = array_column($matches, 'id');
        $totalMatches = count($matchIds);
    } else {
        continue;
    }

    $playedCount = getPlayerPlayedMatches($db, $matchIds, $player['id']);
    $attendancePercent = $totalMatches > 0 ? round(($playedCount / $totalMatches) * 100) : 0;

    // Базовая сумма
    $baseAmount = 0;
    if ($teamId === 2) {
        if (mb_strtolower($player['position']) === 'вратарь') {
            $baseAmount = ($attendancePercent >= 75) ? 3250 : 3575;
        } else {
            $baseAmount = ($attendancePercent >= 75) ? 3950 : 4345;
        }
    } elseif ($teamId === 1) {
        if ($attendancePercent >= 50) {
            $baseAmount = 400;
        } else {
            $baseAmount = 500;
        }
    }

    // Возвращения
    $returnsCount = 0;
    $stmt = $db->prepare("SELECT returns_count FROM returnsplayer WHERE player_id = ?");
    $stmt->bind_param("i", $player['id']);
    $stmt->execute();
    $retRow = $stmt->get_result()->fetch_assoc();
    if ($retRow) {
        $returnsCount = (int)$retRow['returns_count'];
    }

    $finalAmount = applyReturnBonus($baseAmount, $returnsCount);

    $data[] = [
        'player_id' => $player['id'],
        'name' => $player['name'],
        'team_id' => $teamId,
        'position' => $player['position'],
        'attendance_percent' => $attendancePercent,
        'base_amount' => $baseAmount,
        'final_amount' => $finalAmount,
        'returns_count' => $returnsCount
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
