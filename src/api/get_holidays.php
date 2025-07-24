<?php
require_once '../db.php';

$month = $db->real_escape_string($_GET['month']);
$result = $db->query("SELECT player_id FROM player_holidays WHERE month = '$month'");

$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = (int)$row['player_id'];
}
echo json_encode($ids);
