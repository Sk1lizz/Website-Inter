<?php
// Показываем все ошибки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';
header('Content-Type: application/json');

// Получаем входные данные как ассоциативный массив
$input = json_decode(file_get_contents("php://input"), true);

// Проверка обязательных полей
$required = ['teams_id', 'year', 'date', 'championship_name', 'opponent', 'our_goals', 'opponent_goals', 'match_result'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        http_response_code(400);
        echo json_encode([
            'error' => "Поле '$field' обязательно",
            'received_data' => $input
        ]);
        exit;
    }
}

// Необязательные поля
$tour = $input['tour'] ?? '';
$goals = $input['goals'] ?? '';
$assists = $input['assists'] ?? '';
$our_team = $input['our_team'] ?? '';

// Подготавливаем SQL-запрос
$stmt = $conn->prepare("
    INSERT INTO result (
        teams_id, our_team, year, date, championship_name, tour,
        opponent, our_goals, opponent_goals, goals, assists, match_result
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка подготовки запроса: ' . $conn->error,
        'received_data' => $input
    ]);
    exit;
}

// Привязываем параметры с правильными типами
$stmt->bind_param(
    "isisssssisss",
    $input['teams_id'],        // i
    $our_team,                 // s
    $input['year'],            // i
    $input['date'],            // s
    $input['championship_name'], // s
    $tour,                     // s
    $input['opponent'],        // s
    $input['our_goals'],       // i
    $input['opponent_goals'],  // s 
    $goals,                    // s
    $assists,                  // s
    $input['match_result']     // s
);

// Выполняем и возвращаем результат
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'match_id' => $stmt->insert_id,
        'received_data' => $input
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка БД: ' . $stmt->error,
        'received_data' => $input
    ]);
}

$stmt->close();
$conn->close();
