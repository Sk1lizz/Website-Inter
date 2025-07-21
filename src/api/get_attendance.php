<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Получаем player_id из GET-параметра
$playerId = $_GET['player_id'] ?? null;
if (!$playerId) {
    http_response_code(400);
    echo json_encode(['error' => 'player_id is required']);
    exit;
}

// Запрос: дата тренировки и статус игрока
$stmt = $db->prepare("
    SELECT ts.training_date, ta.status
    FROM training_attendance ta
    JOIN training_sessions ts ON ts.id = ta.training_id
    WHERE ta.player_id = ?
    ORDER BY ts.training_date ASC
");

$stmt->bind_param("i", $playerId);
$stmt->execute();

$res = $stmt->get_result();
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = [
        'training_date' => $row['training_date'],
        'status' => (int)$row['status']
    ];
}

echo json_encode($data);
