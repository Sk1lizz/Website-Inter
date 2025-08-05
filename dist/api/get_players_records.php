<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

// Параметры запроса
$type = $_GET['type'] ?? '';
$min = isset($_GET['min']) ? (int)$_GET['min'] : 0;

// Валидные поля для выборки
$validFields = ['matches', 'goals', 'assists'];
if (!in_array($type, $validFields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

// Получаем статистику из обеих таблиц
$query = "
    SELECT p.id, p.name, 
           COALESCE(s_all.matches, 0) + COALESCE(s_2025.matches, 0) AS matches,
           COALESCE(s_all.goals, 0) + COALESCE(s_2025.goals, 0) AS goals,
           COALESCE(s_all.assists, 0) + COALESCE(s_2025.assists, 0) AS assists
    FROM players p
    LEFT JOIN player_statistics_all s_all ON p.id = s_all.player_id
    LEFT JOIN player_statistics_2025 s_2025 ON p.id = s_2025.player_id
";

// Выполняем запрос
$result = $db->query($query);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $db->error]);
    exit;
}

$players = [];
while ($row = $result->fetch_assoc()) {
    // Пропускаем, если меньше минимума
    if ((int)$row[$type] < $min) continue;

    $players[] = [
        'id' => $row['id'],
        'name' => trim($row['name']), // Убрали отчество
        'matches' => (int)$row['matches'],
        'goals' => (int)$row['goals'],
        'assists' => (int)$row['assists']
    ];
}

// Сортировка по нужному полю
usort($players, function ($a, $b) use ($type) {
    return $b[$type] <=> $a[$type];
});

echo json_encode($players);
