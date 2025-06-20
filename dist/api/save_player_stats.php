<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php'; // путь к твоему db.php

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['player_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$player_id = intval($data['player_id']);
$matches = intval($data['matches']);
$goals = intval($data['goals']);
$assists = intval($data['assists']);
$lostgoals = intval($data['lostgoals']);
$zeromatch = intval($data['zeromatch']);
$zanetti_priz = intval($data['zanetti_priz']);

// Проверяем, есть ли запись для player_id в player_statistics_all
$checkStmt = $db->prepare("SELECT player_id FROM player_statistics_all WHERE player_id = ?");
$checkStmt->bind_param('i', $player_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // Запись есть — делаем UPDATE
    $stmt = $db->prepare("UPDATE player_statistics_all SET matches=?, goals=?, assists=?, lostgoals=?, zeromatch=?, zanetti_priz=? WHERE player_id=?");
    $stmt->bind_param(
        'iiiiiii',
        $matches,
        $goals,
        $assists,
        $lostgoals,
        $zeromatch,
        $zanetti_priz,
        $player_id
    );
} else {
    // Записи нет — делаем INSERT
    $stmt = $db->prepare("INSERT INTO player_statistics_all (player_id, matches, goals, assists, lostgoals, zeromatch, zanetti_priz) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'iiiiiii',
        $player_id,
        $matches,
        $goals,
        $assists,
        $lostgoals,
        $zeromatch,
        $zanetti_priz
    );
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
