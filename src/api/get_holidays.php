<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';

$month = isset($_GET['month']) ? preg_replace('/[^0-9]/', '', $_GET['month']) : '';
if (strlen($month) !== 6) {
    echo json_encode([]); exit;
}

$stmt = $db->prepare("SELECT player_id FROM player_holidays WHERE month = ?");
$stmt->bind_param('s', $month);
$stmt->execute();
$res = $stmt->get_result();

$ids = [];
while ($row = $res->fetch_assoc()) {
    $ids[] = (int)$row['player_id'];
}
$stmt->close();

echo json_encode($ids, JSON_UNESCAPED_UNICODE);