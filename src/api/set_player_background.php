<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$playerId = $data['player_id'] ?? null;
$backgroundKey = trim($data['background_key'] ?? "");
$backgroundName = trim($data['background_name'] ?? "");
$canChange = isset($data['can_change_background']) ? (int)$data['can_change_background'] : 0;

if (!$playerId) {
    echo json_encode(['success' => false, 'message' => 'player_id обязателен']);
    exit;
}

try {
    // Проверка наличия записи
    $check = $db->prepare("SELECT COUNT(*) FROM player_backgrounds WHERE player_id = ?");
    $check->bind_param("i", $playerId);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        // Обновление
        $update = $db->prepare("UPDATE player_backgrounds SET background_key = ?, background_name = ?, can_change_background = ? WHERE player_id = ?");
        $update->bind_param("ssii", $backgroundKey, $backgroundName, $canChange, $playerId);
        $update->execute();
        $update->close();
    } else {
        // Вставка
        $insert = $db->prepare("INSERT INTO player_backgrounds (player_id, background_key, background_name, can_change_background) VALUES (?, ?, ?, ?)");
        $insert->bind_param("issi", $playerId, $backgroundKey, $backgroundName, $canChange);
        $insert->execute();
        $insert->close();
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
