<?php
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$playerId = intval($data['player_id'] ?? 0);
$amount = intval($data['amount'] ?? 0);
$reason = trim($data['reason'] ?? '');
$date = trim($data['date'] ?? '');

if ($playerId <= 0 || $amount <= 0 || $reason === '' || !$date) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

$stmt = $db->prepare("INSERT INTO fines (player_id, amount, reason, date) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $playerId, $amount, $reason, $date);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении']);
}
