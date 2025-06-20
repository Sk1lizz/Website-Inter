<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';

// Получение данных
$teamId = $_POST['team_id'] ?? null;
$trainingDate = $_POST['training_date'] ?? null;
$playerIds = $_POST['players'] ?? [];
$statuses = $_POST['status'] ?? [];

if (!$teamId || !$trainingDate || empty($playerIds)) {
    die("Ошибка: не все поля заполнены.");
}

// === 1. Сохраняем тренировку ===
$stmt = $db->prepare("INSERT INTO training_sessions (team_id, training_date) VALUES (?, ?)");
if (!$stmt) {
    die("Ошибка подготовки запроса (training_sessions): " . $db->error);
}
$stmt->bind_param("is", $teamId, $trainingDate);
if (!$stmt->execute()) {
    die("Ошибка при выполнении запроса (training_sessions): " . $stmt->error);
}
$trainingId = $stmt->insert_id;

// === 2. Обрабатываем игроков ===
foreach ($playerIds as $playerId) {
    $playerId = (int)$playerId;
    $status = isset($statuses[$playerId]) ? (int)$statuses[$playerId] : 0;

    // Добавляем запись о посещении
    $att = $db->prepare("INSERT INTO training_attendance (training_id, player_id, status) VALUES (?, ?, ?)");
    if (!$att) {
        die("Ошибка подготовки запроса (training_attendance): " . $db->error);
    }
    $att->bind_param("iii", $trainingId, $playerId, $status);
    if (!$att->execute()) {
        die("Ошибка при выполнении запроса (training_attendance): " . $att->error);
    }

    // Увеличиваем zanetti_priz только если присутствовал
   
    if ((int)$status === 1) {
        // Увеличить zanetti_priz — но сначала проверим, есть ли запись
        $check = $db->prepare("SELECT zanetti_priz FROM player_statistics_2025 WHERE player_id = ?");
        $check->bind_param("i", $playerId);
        $check->execute();
        $check->store_result();
    
        if ($check->num_rows === 0) {
            // Вставляем новую запись
            $insertStat = $db->prepare("INSERT INTO player_statistics_2025 (player_id, zanetti_priz) VALUES (?, 1)");
            $insertStat->bind_param("i", $playerId);
            $insertStat->execute();
        } else {
            // Увеличиваем
            $update = $db->prepare("UPDATE player_statistics_2025 SET zanetti_priz = zanetti_priz + 1 WHERE player_id = ?");
            $update->bind_param("i", $playerId);
            $update->execute();
        }
    }
}

echo "✅ Тренировка успешно сохранена!";
