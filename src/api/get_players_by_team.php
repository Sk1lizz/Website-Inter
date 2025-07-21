<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db.php';
$conn = $db;

$teamId = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;
$players = [];

if ($teamId > 0) {
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.patronymic, pay.amount AS payment
        FROM players p
        LEFT JOIN payments pay ON pay.player_id = p.id
        WHERE p.team_id = ?
        ORDER BY p.name
    ");

    if (!$stmt) {
        die('Ошибка в SQL-запросе: ' . $conn->error);
    }

    $stmt->bind_param('i', $teamId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($players);
