<?php
require_once '../db.php';
header('Content-Type: application/json');


$data = json_decode(file_get_contents("php://input"), true);
$playerId = (int)($data['player_id'] ?? 0);
$date = $data['last_ekg_date'] ?? '';
$hasCondition = (int)($data['has_heart_condition'] ?? 0);

if (!$playerId || !$date) {
    exit(json_encode(['success' => false, 'message' => 'Недостаточно данных']));
}

// ✅ 1. Убедимся, что запись существует
$db->query("INSERT IGNORE INTO player_health (player_id, last_ekg_date, has_heart_condition) VALUES ($playerId, '2025-01-01', 0)");

// ✅ 2. Обновим данные
$stmt = $db->prepare("UPDATE player_health SET last_ekg_date = ?, has_heart_condition = ? WHERE player_id = ?");
$stmt->bind_param("sii", $date, $hasCondition, $playerId);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
