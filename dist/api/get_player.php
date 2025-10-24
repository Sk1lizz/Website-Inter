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

// получаем игрока и фон
$stmt = $db->prepare("
   SELECT p.*, b.key_name AS background_key, b.full_image_path
FROM players p
LEFT JOIN player_backgrounds pb ON pb.player_id = p.id
LEFT JOIN backgrounds b ON b.key_name = pb.background_key
WHERE p.id = ?
");
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
