<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

$stmt = $db->prepare("INSERT INTO players 
    (team_id, name, patronymic, number, position, birth_date, join_date, height_cm, weight_kg) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    'ississsii',
    $data['team_id'],
    $data['name'],
    $data['patronymic'],
    $data['number'],
    $data['position'],
    $data['birth_date'],
    $data['join_date'],
    $data['height_cm'],
    $data['weight_kg']
);

if ($stmt->execute()) {
    $playerId = $stmt->insert_id;

    // Вставка пустой строки в таблицу общей статистики
$insertAll = $db->prepare("
INSERT INTO player_statistics_all (
    player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz
) VALUES (?, 0, 0, 0, 0, 0, 0)
");
$insertAll->bind_param("i", $playerId);
$insertAll->execute();

    // Создать запись для текущего сезона
    $db->query("INSERT INTO player_statistics_2025 (player_id) VALUES ($playerId)");

    // Создать пустую общую статистику
    $db->query("INSERT INTO player_statistics_all (player_id, matches, goals, assists, zeromatch, lostgoals) 
                VALUES ($playerId, 0, 0, 0, 0, 0)");

    echo json_encode(['success' => true, 'id' => $playerId]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка добавления игрока']);
}

