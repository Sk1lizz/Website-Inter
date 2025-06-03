<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../db.php';
header('Content-Type: application/json');

$player_id = intval($_GET['player_id'] ?? 0);
if (!$player_id) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT success_id FROM player_success WHERE player_id = $player_id";
$res = $db->query($sql); // ← исправлено с $conn на $db

if (!$res) {
    http_response_code(500);
    echo json_encode(["error" => $db->error]); // ← тоже исправлено
    exit;
}

$ids = [];
while ($row = $res->fetch_assoc()) {
    $ids[] = (int)$row['success_id'];
}

echo json_encode($ids);