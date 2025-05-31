<?php
header('Content-Type: application/json; charset=utf-8');
// подключение...

$player_id = isset($_GET['player_id']) ? intval($_GET['player_id']) : 0;

$sql = "
    SELECT a.*, p.name
    FROM achievements a
    JOIN players p ON a.player_id = p.id
    WHERE a.player_id = ?
    ORDER BY a.award_year DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

$achievements = [];
while ($row = $result->fetch_assoc()) {
    $achievements[] = $row;
}

echo json_encode($achievements);
$conn->close();