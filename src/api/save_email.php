<?php
require_once __DIR__ . '/../db.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['player_id'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'unauthorized']));
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit(json_encode(['error' => 'invalid email']));
}

$player_id = (int)$_SESSION['player_id'];
$stmt = $db->prepare("UPDATE players SET email = ? WHERE id = ?");
$stmt->bind_param("si", $email, $player_id);
$stmt->execute();

echo json_encode(['success' => true]);