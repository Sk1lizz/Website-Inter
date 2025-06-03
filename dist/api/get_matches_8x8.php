<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';
header('Content-Type: application/json');

$year = date("Y");

$sql = "
    SELECT 
        date, 
        championship_name, 
        tour, 
        opponent, 
        our_goals, 
        opponent_goals, 
        goals, 
        assists,
        match_result
    FROM result
    WHERE year = ? AND teams_id = 1
    ORDER BY STR_TO_DATE(date, '%d.%m.%Y') ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $year);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
$conn->close();
?>