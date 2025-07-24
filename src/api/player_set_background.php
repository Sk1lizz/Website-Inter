<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'message' => 'Нет доступа']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$playerId = $_SESSION['player_id'];
$key = trim($data['background_key'] ?? '');

$stmt = $db->prepare("SELECT can_change_background FROM player_backgrounds WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res || (int)$res['can_change_background'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Смена фона запрещена']);
    exit;
}

$stmt = $db->prepare("UPDATE player_backgrounds SET background_key = ?, background_name = '' WHERE player_id = ?");
$stmt->bind_param("si", $key, $playerId);
$stmt->execute();

echo json_encode(['success' => true]);
