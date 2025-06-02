<?php
header('Content-Type: application/json');

$host = 'VH303.spaceweb.ru';
$user = 'skvantergm_fcint';
$pass = '5688132zZ-';
$dbname = 'skvantergm_fcint';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection error']);
    exit;
}

$conn->set_charset("utf8");

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
    ORDER BY days_left ASC
    LIMIT 3
";

$result = $conn->query($sql);
$players = [];

while ($row = $result->fetch_assoc()) {
    $row['first_name'] = explode(' ', $row['name'])[0];
    $row['last_name'] = explode(' ', $row['name'])[1] ?? '';
    $players[] = $row;
}

echo json_encode($players);
$conn->close();
?>