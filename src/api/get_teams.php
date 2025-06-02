<?php
// Включаем вывод ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем БД (убедись, что путь правильный!)
require_once '../db.php'; // если db.php в корне

// Устанавливаем заголовок для JSON
header('Content-Type: application/json');

// Выполняем запрос
$result = $db->query("SELECT id, name FROM teams ORDER BY name");

// Проверка результата
if (!$result) {
    echo json_encode(['error' => 'Ошибка запроса к базе данных']);
    exit;
}

// Отправляем JSON
$teams = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($teams);