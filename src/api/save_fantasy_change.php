<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Нет доступа']);
    exit;
}

require_once __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$text = trim($data['text'] ?? '');

if ($text === '') {
    echo json_encode(['success' => false, 'message' => 'Пустой текст']);
    exit;
}

$stmt = $db->prepare("INSERT INTO fantasy_changes (text) VALUES (?)");
$stmt->bind_param("s", $text);
$ok = $stmt->execute();

echo json_encode(['success' => $ok]);
