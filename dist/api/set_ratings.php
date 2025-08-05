<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['player_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$matchId = (int)($data['match_id'] ?? 0);
$ratedBy = (int)$_SESSION['player_id'];
$ratings = $data['ratings'] ?? [];

if (!$matchId || empty($ratings)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

foreach ($ratings as $r) {
    $target = (int)$r['target_id'];
    $val = (float)$r['rating'];

    if ($target === $ratedBy) continue; // 🛑 Пропустить себя
    if ($val < 3 || $val > 10) continue;

    $stmt = $db->prepare("INSERT INTO player_ratings (match_id, rated_by_player_id, target_player_id, rating)
                          VALUES (?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
    $stmt->bind_param("iiid", $matchId, $ratedBy, $target, $val);
    $stmt->execute();
}

echo json_encode(['success' => true]);
