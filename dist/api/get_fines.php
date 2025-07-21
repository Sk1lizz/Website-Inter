<?php
require_once '../db.php';
header('Content-Type: application/json');

$playerId = intval($_GET['player_id'] ?? 0);
if ($playerId <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("SELECT id, amount, reason, DATE_FORMAT(date, '%d.%m.%Y') as date FROM fines WHERE player_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$result = $stmt->get_result();

$fines = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($fines);
