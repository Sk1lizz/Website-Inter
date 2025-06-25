<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

// Получаем всех игроков и их фоны
$query = "
    SELECT 
        p.id, 
        CONCAT(p.name, ' ', COALESCE(p.patronymic, '')) AS name, 
        p.number, 
        b.background_key 
    FROM players p
    LEFT JOIN player_backgrounds b ON p.id = b.player_id
";

$res = $db->query($query);
$players = [];

while ($row = $res->fetch_assoc()) {
    $players[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'number' => $row['number'],
        'background_key' => $row['background_key']
    ];
}

echo json_encode($players);