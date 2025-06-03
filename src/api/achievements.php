<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Подключение к БД
require_once __DIR__ . '/db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $player_id = isset($_GET['player_id']) ? intval($_GET['player_id']) : 0;

        $sql = "
            SELECT a.id, a.award_title, a.award_year, a.team_name
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
    }

    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['player_id']) ||
            empty($data['award_year']) ||
            empty($data['award_title']) ||
            empty($data['team_name'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Все поля обязательны']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO achievements (player_id, award_year, award_title, team_name)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $data['player_id'], $data['award_year'], $data['award_title'], $data['team_name']);
        $stmt->execute();

        echo json_encode(['status' => 'ok']);
    }

    elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID обязателен для удаления']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM achievements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(['status' => 'deleted']);
    }

    else {
        http_response_code(405);
        echo json_encode(['error' => 'Метод не поддерживается']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка на сервере',
        'details' => $e->getMessage()
    ]);
}

$conn->close();