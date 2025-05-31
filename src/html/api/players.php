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

$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;

$sql = "SELECT id, name, number, position FROM players WHERE team_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();

$players = [];
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

echo json_encode($players);
$conn->close();