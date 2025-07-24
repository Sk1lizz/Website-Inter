<?php
require_once '../db.php';

$teamId = (int)($_GET['team_id'] ?? 0);
$month = $_GET['month'] ?? '';

$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM player_holidays h
    JOIN players p ON h.player_id = p.id
    WHERE h.month = ? AND p.team_id = ?
");
$stmt->bind_param("si", $month, $teamId);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();

echo json_encode(['count' => $count]);
