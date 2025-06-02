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

$sql = "
    SELECT ps.*, p.name, p.position
    FROM player_statistics_2025 ps
    JOIN players p ON ps.player_id = p.id
    WHERE p.team_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}

echo json_encode($stats);
$conn->close();