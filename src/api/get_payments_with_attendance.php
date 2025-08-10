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

function getMatchesForPeriod($db, $teamId, $startDate, $endDate) {
    $stmt = $db->prepare("SELECT id FROM result WHERE teams_id = ? AND date BETWEEN ? AND ?");
    $stmt->bind_param("iss", $teamId, $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        $startDate = $currentMonthStart;
        $endDate = $currentMonthEnd;
    } elseif ($teamId === 1) {
        $startDate = $prevMonthStart;
        $endDate = $prevMonthEnd;
    } else {
        continue;
    }

    $matches = getMatchesForPeriod($db, $teamId, $startDate, $endDate);
    $totalMatches = count($matches);
    $matchIds = array_column($matches, 'id');

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
