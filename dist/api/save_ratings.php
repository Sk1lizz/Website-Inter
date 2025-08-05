<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$matchId = (int)$data['match_id'];
$ratedBy = (int)$_SESSION['player_id'];
$ratings = $data['ratings'] ?? [];

foreach ($ratings as $r) {
    $target = (int)$r['target_player_id']; // ✅ а не player_id
    $val = (float)$r['rating'];
    if ($val < 3 || $val > 10 || $target === $ratedBy) continue;

    $stmt = $db->prepare("
        INSERT INTO player_ratings (match_id, rated_by_player_id, target_player_id, rating)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating)
    ");
    $stmt->bind_param("iiid", $matchId, $ratedBy, $target, $val);
    $stmt->execute();
}

echo json_encode(['success' => true]);

