<?php
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$fineId = intval($data['fine_id'] ?? 0);

if ($fineId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID']);
    exit;
}

$stmt = $db->prepare("DELETE FROM fines WHERE id = ?");
$stmt->bind_param("i", $fineId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка удаления']);
}