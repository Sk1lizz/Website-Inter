<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

if (!isset($_SESSION['player_id'])) {
    http_response_code(403);
    exit('Нет доступа');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Метод не поддерживается');
}

$playerId = $_SESSION['player_id'];

$errors = [];

if (isset($_POST['weight'])) {
    $weight = (float)$_POST['weight'];
    if ($weight < 40 || $weight > 200) {
        $errors[] = 'Недопустимый вес';
    } else {
        $stmt = $db->prepare("UPDATE players SET weight_kg = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare error (weight): " . $db->error);
            http_response_code(500);
            exit('Ошибка запроса по весу');
        }
        $stmt->bind_param("di", $weight, $playerId);
        $stmt->execute();
    }
}

if (isset($_POST['height'])) {
    $height = (int)$_POST['height'];
    if ($height < 100 || $height > 250) {
        $errors[] = 'Недопустимый рост';
    } else {
        $stmt = $db->prepare("UPDATE players SET height_cm = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare error (height): " . $db->error);
            http_response_code(500);
            exit('Ошибка запроса по росту');
        }
        $stmt->bind_param("ii", $height, $playerId);
        $stmt->execute();
    }
}

if (!empty($errors)) {
    http_response_code(422);
    exit(implode(', ', $errors));
}

header("Location: ../user.php");
exit;