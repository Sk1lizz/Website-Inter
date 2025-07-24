<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

require_once '../db.php';

if (!isset($db) || !$db instanceof mysqli || $db->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ошибка подключения к БД: ' . ($db->connect_error ?? 'неизвестная')]);
    exit;
}

$teamId = $_GET['team_id'] ?? null; // не используется, но пусть остаётся для совместимости
$date = $_GET['date'] ?? null;

if (!$date) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$month = date('Ym', strtotime($date)); // формат YYYYMM

$stmt = $db->prepare("SELECT player_id FROM player_holidays WHERE month = ?");
if (!$stmt) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ошибка запроса: ' . $db->error]);
    exit;
}

$stmt->bind_param("s", $month);
$stmt->execute();

$res = $stmt->get_result();
$ids = [];
while ($row = $res->fetch_assoc()) {
    $ids[] = (int)$row['player_id'];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($ids);
