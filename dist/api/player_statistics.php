<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$user = 'skvantergm_fcint';
$pass = '5688132zZ-';
$dbname = 'skvantergm_fcint';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection error']);
    exit;
}
$conn->set_charset("utf8");

$player_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM player_statistics_2025 WHERE player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Нет статистики']);
}

$conn->close();