<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Подключение к БД
require_once __DIR__ . '/../db.php'; // путь правильный, если db.php в корне сайта

$sql = "
    SELECT
        name,
        DATE_FORMAT(birth_date, '%d.%m.%Y') AS birthday,
        DATEDIFF(
            IF(
                DATE_FORMAT(birth_date, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d'),
                STR_TO_DATE(CONCAT(YEAR(NOW()), '-', DATE_FORMAT(birth_date, '%m-%d')), '%Y-%m-%d'),
                STR_TO_DATE(CONCAT(YEAR(NOW()) + 1, '-', DATE_FORMAT(birth_date, '%m-%d')), '%Y-%m-%d')
            ),
            CURDATE()
        ) AS days_left
    FROM players
    WHERE team_id IS NOT NULL AND team_id != 3 AND birth_date IS NOT NULL
    ORDER BY days_left ASC
    LIMIT 3
";

$result = $db->query($sql); // ← используем только $db
if (!$result) {
    echo json_encode(['error' => 'SQL error', 'details' => $db->error]);
    exit;
}

$players = [];

while ($row = $result->fetch_assoc()) {
    $row['first_name'] = explode(' ', $row['name'])[0];
    $row['last_name'] = explode(' ', $row['name'])[1] ?? '';
    $players[] = $row;
}

echo json_encode($players);
$db->close();
