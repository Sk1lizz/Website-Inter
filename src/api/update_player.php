<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

if (!$id || !$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверные данные']);
    exit;
}

$stmt = $db->prepare("UPDATE players SET 
    name = ?, 
    patronymic = ?, 
    birth_date = ?, 
    number = ?, 
    position = ?, 
    height_cm = ?, 
    weight_kg = ?, 
    team_id = ?
WHERE id = ?");

$stmt->bind_param(
    "sssissiii",
    $data['name'],
    $data['patronymic'],
    $data['birth_date'],
    $data['number'],
    $data['position'],
    $data['height_cm'],
    $data['weight_kg'],
    $data['team_id'],
    $id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка обновления игрока']);
}