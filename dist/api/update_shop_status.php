<?php
// Включаем подробные ошибки — чтобы проще было отлавливать проблемы
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

// Получаем JSON из запроса
$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');

// Разрешённые статусы
$allowed = ['ожидает', 'в обработке', 'выдано', 'отменено'];

// Проверка данных
if (!$id || !in_array($status, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

// Подготавливаем запрос к БД
$stmt = $db->prepare("UPDATE shop_purchases SET status = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка prepare: ' . $db->error]);
    exit;
}

$stmt->bind_param("si", $status, $id);
$ok = $stmt->execute();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка execute: ' . $stmt->error]);
    exit;
}

echo json_encode(['success' => true]);
