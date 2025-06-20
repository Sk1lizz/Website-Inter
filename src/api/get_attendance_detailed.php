<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Получаем параметры
$teamId = $_GET['team_id'] ?? null;
$month = $_GET['month'] ?? null;
$year = date('Y');

if (!$teamId || !$month) {
    http_response_code(400);
    echo json_encode(['error' => 'team_id and month are required']);
    exit;
}

// 1. Получаем тренировки команды в выбранном месяце
$trainings = [];
$trainingMap = [];

$stmt = $db->prepare("
    SELECT id, training_date
    FROM training_sessions
    WHERE team_id = ? AND MONTH(training_date) = ? AND YEAR(training_date) = ?
    ORDER BY training_date ASC
");

$stmt->bind_param("iii", $teamId, $month, $year);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $trainings[] = $row['training_date'];
    $trainingMap[$row['id']] = $row['training_date'];
}

if (empty($trainings)) {
    echo json_encode(['dates' => [], 'players' => []]);
    exit;
}

// 2. Получаем игроков команды
$players = [];
$playerIds = [];

$stmt = $db->prepare("SELECT id, name FROM players WHERE team_id = ?");
$stmt->bind_param("i", $teamId);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $players[$row['id']] = [
        'name' => $row['name'],
        'statuses' => array_fill_keys($trainings, null)
    ];
    $playerIds[] = $row['id'];
}

if (empty($playerIds)) {
    echo json_encode(['dates' => $trainings, 'players' => []]);
    exit;
}

// 3. Загружаем посещаемость
$trainingIds = array_keys($trainingMap);
if (empty($trainingIds)) {
    echo json_encode(['dates' => $trainings, 'players' => []]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($trainingIds), '?'));
$types = str_repeat('i', count($trainingIds));
$query = "SELECT player_id, training_id, status FROM training_attendance WHERE training_id IN ($placeholders)";

$stmt = $db->prepare($query);
$stmt->bind_param($types, ...$trainingIds);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $pid = $row['player_id'];
    $tid = $row['training_id'];
    $status = (int)$row['status'];

    // ✅ защита от отсутствующего training_id
    if (!isset($trainingMap[$tid])) {
        continue;
    }

    $date = $trainingMap[$tid];

    if (isset($players[$pid])) {
        $players[$pid]['statuses'][$date] = $status;
    }
}

// 4. Финальный вывод
echo json_encode([
    'dates' => $trainings,
    'players' => array_values($players)
]);
