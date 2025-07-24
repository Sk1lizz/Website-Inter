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
    b.background_key,
    b.can_change_background
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
    'background_key' => $row['background_key'],
    'can_change_background' => $row['can_change_background']
];
}

echo json_encode($players);