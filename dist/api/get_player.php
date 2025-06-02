<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID не указан']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM players WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$player = $res->fetch_assoc();

if (!$player) {
    http_response_code(404);
    echo json_encode(['error' => 'Игрок не найден']);
    exit;
}

echo json_encode($player);
