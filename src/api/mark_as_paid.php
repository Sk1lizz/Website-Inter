<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db.php';
$conn = $db;

$data = json_decode(file_get_contents("php://input"), true);
$playerId = (int) ($data['player_id'] ?? 0);
$amount = (float) ($data['amount'] ?? 0);
$teamId = (int) ($data['team_id'] ?? 0);

$response = ['success' => false];

if ($playerId > 0 && $amount > 0 && $teamId > 0) {
    $year = date('Y');
    $month = date('n'); // без ведущего нуля

    // 1. Добавить/обновить сумму в месячной таблице
    $stmt1 = $conn->prepare("
        INSERT INTO payments_all_monthly (team_id, year, month, total_amount)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE total_amount = total_amount + VALUES(total_amount)
    ");
    if (!$stmt1) {
        $response['error'] = 'Ошибка prepare: ' . $conn->error;
    } else {
        $stmt1->bind_param('iiid', $teamId, $year, $month, $amount);
        $ok1 = $stmt1->execute();

        // 2. Обнулить в таблице payments
        $stmt2 = $conn->prepare("UPDATE payments SET amount = 0 WHERE player_id = ?");
        $stmt2->bind_param('i', $playerId);
        $ok2 = $stmt2->execute();

        $response['success'] = $ok1 && $ok2;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
