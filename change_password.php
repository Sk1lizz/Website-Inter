<?php
session_start();
require_once 'db.php';

$player_id = $_SESSION['player_id'] ?? 0;
if (!$player_id) exit("Нет доступа");

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';

$stmt = $mysqli->prepare("SELECT password_hash FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if ($res && password_verify($old, $res['password_hash'])) {
    $new_hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE players SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $new_hash, $player_id);
    $stmt->execute();
    echo "Пароль изменён. <a href='user.php'>Назад</a>";
} else {
    echo "Старый пароль неверный. <a href='user.php'>Назад</a>";
}