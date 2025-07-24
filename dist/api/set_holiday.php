<?php
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$playerId = intval($data['player_id']);
$month = $db->real_escape_string($data['month']);

$stmt = $db->prepare("INSERT INTO player_holidays (player_id, month) VALUES (?, ?) ON DUPLICATE KEY UPDATE month = VALUES(month)");
$stmt->bind_param("is", $playerId, $month);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
