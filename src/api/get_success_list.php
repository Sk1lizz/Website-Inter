<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../db.php';
header('Content-Type: application/json');

$sql = "SELECT id, title, description, points FROM Success ORDER BY id";
$res = $db->query($sql);

if (!$res) {
    http_response_code(500);
    echo json_encode(["error" => $db->error]); // исправлено: $db вместо $conn
    exit;
}

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = [
        "id" => (int)$row['id'],
        "title" => $row['title'],
        "description" => $row['description'],
        "points" => (int)$row['points']
    ];
}

echo json_encode($items);