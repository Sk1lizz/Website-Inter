<?php
require '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$player_id = intval($data['player_id'] ?? 0);
$success_ids = $data['success_ids'] ?? [];

if (!$player_id || !is_array($success_ids)) {
    http_response_code(400);
    echo json_encode(["error" => "Некорректные данные"]);
    exit;
}

$stmt = $db->prepare("INSERT IGNORE INTO player_success (player_id, success_id) VALUES (?, ?)");

foreach ($success_ids as $sid) {
    $sid = intval($sid);
    $stmt->bind_param("ii", $player_id, $sid);
    $stmt->execute();
}

echo json_encode(["status" => "ok"]);
