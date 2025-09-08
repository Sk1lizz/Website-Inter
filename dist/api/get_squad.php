<?php
session_start();
require_once '../db.php';

$SEASON = 2025;


function _normPos($pos) {
    $p = mb_strtolower(trim((string)$pos), 'UTF-8');
    if (preg_match('/вратар/u', $p) || $p === 'gk') return 'GK';
    if (preg_match('/полузащит/u', $p) || $p === 'mf') return 'MF';
    if (preg_match('/защит/u', $p) || $p === 'df') return 'DF';
    if (preg_match('/напад/u', $p) || $p === 'fw') return 'FW';
    return strtoupper($pos);
}

header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    echo json_encode(['error' => 'Неверный ID пользователя']);
    exit;
}

// Проверка возможности просмотра (суббота, воскресенье, понедельник)
date_default_timezone_set('Europe/Moscow');
$currentDay = (new DateTime())->format('N');
if (!($currentDay >= 6 || $currentDay == 1)) {
    echo json_encode(['error' => 'Просмотр составов доступен только в субботу, воскресенье и понедельник']);
    exit;
}

$teamName = 'Без названия';
$stmt = $db->prepare("SELECT team_name FROM fantasy_users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $teamName = $row['team_name'] ?: $teamName;
}
$stmt->close();

$squad = [];
$stmt = $db->prepare("
    SELECT gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id, captain_player_id
    FROM fantasy_squads
    WHERE user_id = ? AND season = ?
");
$stmt->bind_param('ii', $userId, $SEASON);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $playerIds = [
        'gk' => $row['gk_id'],
        'df1' => $row['df1_id'],
        'df2' => $row['df2_id'],
        'mf1' => $row['mf1_id'],
        'mf2' => $row['mf2_id'],
        'fw' => $row['fw_id'],
        'bench' => $row['bench_id'],
    ];
    $captainId = $row['captain_player_id'];

    $ids = array_filter($playerIds);
    if ($ids) {
        $idsList = implode(',', array_map('intval', $ids));
        $players = [];
        $query = $db->query("
            SELECT id, name, position, photo, team_id
            FROM players
            WHERE id IN ($idsList)
        ");
        while ($p = $query->fetch_assoc()) {
            $players[$p['id']] = [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'position' => _normPos($p['position']),
                'photo' => $p['photo'] ?: '/img/player/player_0.png',
                'team_id' => (int)$p['team_id']
            ];
        }

        $squad['players'] = [];
        foreach ($playerIds as $slot => $id) {
            $squad['players'][$slot] = $id && isset($players[$id]) ? array_merge($players[$id], ['is_captain' => $id == $captainId]) : null;
        }
    }
}
$stmt->close();

echo json_encode([
    'team_name' => $teamName,
    'players' => $squad['players'] ?? []
]);