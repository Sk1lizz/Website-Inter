<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$playerId = $_POST['player_id'] ?? null;
$teamId = $_POST['team_id'] ?? null;
$month = $_POST['month'] ?? null;
$statuses = $_POST['status'] ?? [];

if (!$playerId || !$teamId || !$month || empty($statuses)) {
    die("Не все данные переданы");
}

// Получаем все ID тренировок за месяц
$year = date('Y');
$stmt = $db->prepare("
    SELECT id, training_date
    FROM training_sessions
    WHERE team_id = ? AND MONTH(training_date) = ? AND YEAR(training_date) = ?
");
$stmt->bind_param("iii", $teamId, $month, $year);
$stmt->execute();
$res = $stmt->get_result();

$trainingMap = [];
while ($row = $res->fetch_assoc()) {
    $trainingMap[$row['training_date']] = $row['id'];
}

if (empty($trainingMap)) {
    die("Нет тренировок за месяц");
}

// Получаем текущие статусы игрока
$trainingIds = array_values($trainingMap);
$placeholders = implode(',', array_fill(0, count($trainingIds), '?'));
$types = str_repeat('i', count($trainingIds));
$query = "SELECT training_id, status FROM training_attendance WHERE player_id = ? AND training_id IN ($placeholders)";
$stmt = $db->prepare($query);
$stmt->bind_param('i' . $types, $playerId, ...$trainingIds);
$stmt->execute();
$res = $stmt->get_result();

$oldStatuses = [];
while ($row = $res->fetch_assoc()) {
    $tid = $row['training_id'];
    $oldStatuses[$tid] = (int)$row['status'];
}

file_put_contents('log_update.txt', json_encode([
  'playerId' => $playerId,
  'teamId' => $teamId,
  'month' => $month,
  'statuses' => $statuses,
  'trainingMap' => $trainingMap,
  'oldStatuses' => $oldStatuses
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), FILE_APPEND);

// Обновляем
foreach ($statuses as $date => $status) {
    $tid = $trainingMap[$date] ?? null;
    if (!$tid) continue;

    $newStatus = (int)$status;
    $oldStatus = $oldStatuses[$tid] ?? null;

    if ($oldStatus === null) {
        // вставка новой записи
        $insert = $db->prepare("INSERT INTO training_attendance (training_id, player_id, status) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $tid, $playerId, $newStatus);
        $insert->execute();
        if ($newStatus === 1) {
            $db->query("INSERT INTO player_statistics_2025 (player_id, zanetti_priz) VALUES ($playerId, 1)
                        ON DUPLICATE KEY UPDATE zanetti_priz = zanetti_priz + 1");
        }
    } else {
        // обновление
        $update = $db->prepare("UPDATE training_attendance SET status = ? WHERE player_id = ? AND training_id = ?");
        $update->bind_param("iii", $newStatus, $playerId, $tid);
        $update->execute();

        if ($oldStatus === 1 && $newStatus !== 1) {
            $db->query("UPDATE player_statistics_2025 SET zanetti_priz = GREATEST(0, zanetti_priz - 1) WHERE player_id = $playerId");
        } elseif ($oldStatus !== 1 && $newStatus === 1) {
            $db->query("INSERT INTO player_statistics_2025 (player_id, zanetti_priz) VALUES ($playerId, 1)
                        ON DUPLICATE KEY UPDATE zanetti_priz = zanetti_priz + 1");
        }
    }
}

echo "✅ Обновлено";
?>
