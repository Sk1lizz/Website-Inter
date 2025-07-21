<?php
require_once '../db.php';
$conn = $db;

$data = json_decode(file_get_contents('php://input'), true);
$updates = $data['payments'] ?? [];

$response = ['success' => false];

if (!empty($updates)) {
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            INSERT INTO payments (player_id, amount)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE amount = VALUES(amount)
        ");

        foreach ($updates as $row) {
            $playerId = (int) $row['player_id'];
            $amount = (float) $row['amount'];
            $stmt->bind_param('id', $playerId, $amount);
            $stmt->execute();
        }

        $conn->commit();
        $response['success'] = true;
    } catch (Exception $e) {
        $conn->rollback();
        $response['error'] = $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
