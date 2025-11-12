<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
if ($playerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректный ID']);
    exit;
}

$stmt = $db->prepare("SELECT point FROM fantasy_users WHERE player_id = ? LIMIT 1");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$points = isset($res['point']) ? (int)$res['point'] : 0;

echo json_encode(['success' => true, 'points' => $points]);
?>
