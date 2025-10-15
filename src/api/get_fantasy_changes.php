<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

$res = $db->query("SELECT text, updated_at FROM fantasy_changes ORDER BY id DESC LIMIT 1");

if ($res && $row = $res->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'text' => $row['text'],
        'updated_at' => $row['updated_at']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Изменений пока нет'
    ]);
}
