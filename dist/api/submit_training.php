<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';

// Получение данных
$teamId       = $_POST['team_id'] ?? null;
$trainingDate = $_POST['training_date'] ?? null;
$playerIds    = $_POST['players'] ?? [];
$statuses     = $_POST['status'] ?? [];
$ratings      = $_POST['rating'] ?? [];   // NEW: оценки с ползунка 3..10 (шаг 0.5)

if (!$teamId || !$trainingDate || empty($playerIds)) {
    die("Ошибка: не все поля заполнены.");
}

// === 1. Проверка на существующую тренировку ===
$check = $db->prepare("SELECT id FROM training_sessions WHERE team_id = ? AND training_date = ?");
$check->bind_param("is", $teamId, $trainingDate);
$check->execute();
$check->bind_result($existingId);
$check->fetch();
$check->close();

$isDuplicate = false;

if ($existingId) {
    $trainingId = $existingId;
    $isDuplicate = true;

    // Удалим старые записи о посещении (рейтинг тоже удалится)
    $del = $db->prepare("DELETE FROM training_attendance WHERE training_id = ?");
    $del->bind_param("i", $trainingId);
    $del->execute();
} else {
    // === 2. Сохраняем новую тренировку ===
    $stmt = $db->prepare("INSERT INTO training_sessions (team_id, training_date) VALUES (?, ?)");
    if (!$stmt) {
        die("Ошибка подготовки запроса (training_sessions): " . $db->error);
    }
    $stmt->bind_param("is", $teamId, $trainingDate);
    if (!$stmt->execute()) {
        die("Ошибка при выполнении запроса (training_sessions): " . $stmt->error);
    }
    $trainingId = $stmt->insert_id;
}

// === 3. Обрабатываем игроков ===
// Подготовим INSERT с rating
$att = $db->prepare("INSERT INTO training_attendance (training_id, player_id, status, rating) VALUES (?, ?, ?, ?)");
if (!$att) {
    die("Ошибка подготовки запроса (training_attendance): " . $db->error);
}

foreach ($playerIds as $playerId) {
    $playerId = (int)$playerId;

    // Преобразуем дату в формат YYYYMM
    $monthKey = (int)date("Ym", strtotime($trainingDate));

    // Если статус явно передан — используем его
    if (isset($statuses[$playerId])) {
        // ваши JS-хуки переводят спец-значения в 1/0 перед отправкой формы
        $status = (int)$statuses[$playerId];
    } else {
        // Если статус не передан — проверим, был ли отпуск
        $holidayCheck = $db->prepare("SELECT 1 FROM player_holidays WHERE player_id = ? AND month = ?");
        $holidayCheck->bind_param("ii", $playerId, $monthKey);
        $holidayCheck->execute();
        $holidayCheck->store_result();
        $status = $holidayCheck->num_rows > 0 ? 2 : 0; // 2 — отпуск, иначе 0
        $holidayCheck->close();
    }

    // === NEW: рейтинг — только при присутствии (status=1)
    $rating = null;
    if ($status === 1 && isset($ratings[$playerId]) && $ratings[$playerId] !== '') {
        $val = (float)$ratings[$playerId];
        // Валидация диапазона 3..10 и «квантование» до 0.5
        if ($val >= 3.0 && $val <= 10.0) {
            $rating = round($val * 2) / 2.0; // 7.25 -> 7.5, 7.74 -> 7.5, и т.п.
        }
    }

    // Вставка attendance с рейтингом (NULL, если не присутствовал/нет валидной оценки)
    // Важно: при $rating === null mysqli нормально пошлёт NULL при bind_param('d', $rating)
    $att->bind_param("iiid", $trainingId, $playerId, $status, $rating);
    if (!$att->execute()) {
        die("Ошибка при выполнении запроса (training_attendance): " . $att->error);
    }

    // ✅ Увеличиваем zanetti_priz только если новая тренировка и статус "присутствовал"
    if (!$isDuplicate && $status === 1) {
        $check = $db->prepare("SELECT zanetti_priz FROM player_statistics_2025 WHERE player_id = ?");
        $check->bind_param("i", $playerId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $insertStat = $db->prepare("INSERT INTO player_statistics_2025 (player_id, zanetti_priz) VALUES (?, 1)");
            $insertStat->bind_param("i", $playerId);
            $insertStat->execute();
        } else {
            $update = $db->prepare("UPDATE player_statistics_2025 SET zanetti_priz = zanetti_priz + 1 WHERE player_id = ?");
            $update->bind_param("i", $playerId);
            $update->execute();
        }
    }
}

echo "✅ Тренировка успешно сохранена!";