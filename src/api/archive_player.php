<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID не передан']);
    exit;
}

// Архивная команда — id = 4
$stmt = $db->prepare("UPDATE players SET team_id = 3 WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка архивации']);
}

