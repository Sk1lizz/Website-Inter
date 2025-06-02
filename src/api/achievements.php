<?php
// Показываем ошибки (удалить в продакшене)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Подключение к БД
require_once __DIR__ . '/db_connection.php';

$player_id = isset($_GET['player_id']) ? intval($_GET['player_id']) : 0;

try {
    $sql = "
        SELECT a.award_title, a.award_year, a.team_name
        FROM achievements a
        WHERE a.player_id = ?
        ORDER BY a.award_year DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Ошибка подготовки запроса: " . $conn->error);
    }

    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $achievements = [];
    while ($row = $result->fetch_assoc()) {
        $achievements[] = $row;
    }

    echo json_encode($achievements);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Ошибка при получении достижений',
        'details' => $e->getMessage()
    ]);
}

$conn->close();