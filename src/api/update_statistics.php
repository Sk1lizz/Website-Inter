<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$updates = $data['updates'] ?? [];

if (!is_array($updates)) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный формат']);
    exit;
}

$stmt = $db->prepare("
    UPDATE player_statistics_2025 
    SET matches = ?, goals = ?, assists = ?, zeromatch = ?, lostgoals = ?, zanetti_priz = ? 
    WHERE player_id = ?
");

foreach ($updates as $u) {
    $stmt->bind_param(
        'iiiiiii',
        $u['stats']['matches'],
        $u['stats']['goals'],
        $u['stats']['assists'],
        $u['stats']['zeromatch'],
        $u['stats']['lostgoals'],
        $u['stats']['zanetti_priz'],
        $u['playerId']
    );
    $stmt->execute();
}

echo json_encode(['success' => true]);