<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');

$allowed = ['ожидает', 'принят', 'выполнен'];
if (!$id || !in_array($status, $allowed, true)) {
  echo json_encode(['success'=>false, 'message'=>'Некорректные данные']);
  exit;
}

$stmt = $db->prepare("UPDATE shop_purchases SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$ok = $stmt->execute();

echo json_encode(['success'=>$ok]);
