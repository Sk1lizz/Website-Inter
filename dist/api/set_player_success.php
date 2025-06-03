<?php
require '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$player_id = intval($data['player_id'] ?? 0);
$success_ids = $data['success_ids'] ?? [];

$db->query("DELETE FROM player_success WHERE player_id = $player_id");
$stmt = $db->prepare("INSERT IGNORE INTO player_success (player_id, success_id) VALUES (?, ?)");

foreach ($success_ids as $sid) {
    $stmt->bind_param("ii", $player_id, $sid);
    $stmt->execute();
}

echo json_encode(["status" => "ok"]);