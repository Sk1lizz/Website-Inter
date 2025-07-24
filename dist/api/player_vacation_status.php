<?php
require_once '../db.php';

$playerId = (int)($_GET['player_id'] ?? 0);
$year = date('Y');

$stmt = $db->prepare("SELECT COUNT(*) FROM player_holidays WHERE player_id = ? AND LEFT(month,4) = ?");
$stmt->bind_param("is", $playerId, $year);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();

echo json_encode(['already_on_vacation' => $count > 0]);
