<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => 'Нет доступа']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$playerId = (int)$_SESSION['player_id'];
$key = trim($data['background_key'] ?? '');

// Проверяем разрешение на смену фона
$stmt = $db->prepare("SELECT can_change_background FROM player_backgrounds WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row || (int)$row['can_change_background'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Смена фона запрещена']);
    exit;
}

// Если ключ пустой — это сброс фона
if ($key === '') {
    $upd = $db->prepare("
        UPDATE player_backgrounds 
        SET background_key = '', background_name = '— Без фона —', assigned_at = NOW()
        WHERE player_id = ?
    ");
    $upd->bind_param("i", $playerId);
    $upd->execute();

    echo json_encode(['success' => true, 'message' => 'Фон сброшен']);
    exit;
}

// Проверяем, есть ли у игрока этот фон
$check = $db->prepare("
    SELECT 1
    FROM backgrounds b
    LEFT JOIN player_unlocked_backgrounds ub 
        ON ub.background_key = b.key_name AND ub.player_id = ?
    WHERE b.key_name = ? AND (b.is_free = 1 OR ub.player_id IS NOT NULL)
    LIMIT 1
");
$check->bind_param("is", $playerId, $key);
$check->execute();
$allowed = $check->get_result()->num_rows > 0;

if (!$allowed) {
    echo json_encode(['success' => false, 'message' => 'Этот фон вам недоступен']);
    exit;
}

// Получаем название фона для отображения
$getName = $db->prepare("SELECT title FROM backgrounds WHERE key_name = ? LIMIT 1");
$getName->bind_param("s", $key);
$getName->execute();
$bg = $getName->get_result()->fetch_assoc();
$bgName = $bg ? $bg['title'] : 'Неизвестный фон';

// Обновляем фон
$upd = $db->prepare("
    UPDATE player_backgrounds 
    SET background_key = ?, background_name = ?, assigned_at = NOW()
    WHERE player_id = ?
");
$upd->bind_param("ssi", $key, $bgName, $playerId);
$upd->execute();

echo json_encode(['success' => true, 'message' => 'Фон успешно изменён']);
