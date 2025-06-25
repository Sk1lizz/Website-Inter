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

if (!$playerId) {
    echo json_encode(['success' => false, 'message' => 'player_id обязателен']);
    exit;
}

try {
    // Удалить старую запись
    $delete = $db->prepare("DELETE FROM player_backgrounds WHERE player_id = ?");
    $delete->bind_param("i", $playerId);
    $delete->execute();
    $delete->close();

    // Если задан фон — вставить новую
    if ($backgroundKey !== "") {
        $insert = $db->prepare("INSERT INTO player_backgrounds (player_id, background_key, background_name) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $playerId, $backgroundKey, $backgroundName);
        $insert->execute();
        $insert->close();
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}