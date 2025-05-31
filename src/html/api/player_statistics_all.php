<?php
header('Content-Type: application/json; charset=utf-8');
// подключение...

$player_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM player_statistics_all WHERE player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Нет общей статистики']);
}

$conn->close();