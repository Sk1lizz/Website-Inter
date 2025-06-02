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

$number = isset($_GET['number']) ? intval($_GET['number']) : 0;

$sql = "SELECT * FROM players WHERE number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $number);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Игрок не найден']);
}

$conn->close();