<?php
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$playerId = intval($data['player_id']);
$month = $db->real_escape_string($data['month']);

$stmt = $db->prepare("DELETE FROM player_holidays WHERE player_id = ? AND month = ?");
$stmt->bind_param("is", $playerId, $month);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
