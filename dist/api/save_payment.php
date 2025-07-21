<?php
require_once '../db.php';
$conn = $db;

$data = json_decode(file_get_contents('php://input'), true);
$playerId = (int) ($data['player_id'] ?? 0);
$amount = (float) ($data['amount'] ?? 0);

$response = ['success' => false];

if ($playerId > 0) {
    $stmt = $conn->prepare("
        INSERT INTO payments (player_id, amount)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE amount = VALUES(amount)
    ");

    if ($stmt) {
        $stmt->bind_param('id', $playerId, $amount);
        $response['success'] = $stmt->execute();
    } else {
        $response['error'] = $conn->error;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
