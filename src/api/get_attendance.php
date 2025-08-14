<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// player_id обязателен
$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
if ($playerId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'player_id is required']);
    exit;
}

/*
  training_attendance: status, rating
  training_sessions: training_date
*/
$sql = "
    SELECT
        ts.training_date,
        ta.status,
        ta.rating
    FROM training_attendance ta
    INNER JOIN training_sessions ts ON ts.id = ta.training_id
    WHERE ta.player_id = ?
    ORDER BY ts.training_date ASC
";
$stmt = $db->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'db_prepare_error', 'errno' => $db->errno, 'message' => $db->error]);
    exit;
}

$stmt->bind_param('i', $playerId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'db_exec_error', 'errno' => $stmt->errno, 'message' => $stmt->error]);
    exit;
}

$res = $stmt->get_result();
$out = [];

while ($row = $res->fetch_assoc()) {
    $out[] = [
        'training_date' => $row['training_date'],
        'status'        => (int)$row['status'],
        // Если присутствовал и рейтинг есть — число с одним знаком после запятой, иначе null
        'rating'        => ($row['rating'] !== null ? (float)$row['rating'] : null),
    ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);