<?php
require_once '../db.php';
header('Content-Type: application/json');

$teamId = (int) ($_GET['team_id'] ?? 0);
$month = $_GET['month'] ?? date('Y-m');
$response = ['total' => 0];

if ($teamId > 0 && preg_match('/^\\d{4}-\\d{2}$/', $month)) {
    $year = (int) substr($month, 0, 4);
    $mon = (int) substr($month, 5, 2);

    $stmt = $db->prepare("
        SELECT total_amount AS total 
        FROM payments_all_monthly 
        WHERE team_id = ? AND year = ? AND month = ?
    ");
    $stmt->bind_param('iii', $teamId, $year, $mon);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    $response['total'] = (float) ($res['total'] ?? 0);
}

echo json_encode($response);
