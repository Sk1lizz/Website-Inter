<?php
require_once '../db.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);
$playerId = (int)($data['player_id'] ?? 0);
$returnsCount = (int)($data['returns_count'] ?? 0);

if ($playerId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Некорректный ID игрока']);
    exit;
}

// Проверяем, есть ли запись
$stmt = $db->prepare("SELECT player_id FROM returnsplayer WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $stmt = $db->prepare("UPDATE returnsplayer SET returns_count = ? WHERE player_id = ?");
    $stmt->bind_param("ii", $returnsCount, $playerId);
    $stmt->execute();
} else {
    $stmt = $db->prepare("INSERT INTO returnsplayer (player_id, returns_count) VALUES (?, ?)");
    $stmt->bind_param("ii", $playerId, $returnsCount);
    $stmt->execute();
}

echo json_encode(['success' => true]);
