<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

// Транслитерация
function transliterate($text) {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ь'=>'','ы'=>'y','ъ'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
    ];
    return preg_replace('/[^a-z0-9_]/', '', strtr(mb_strtolower($text), $map));
}

// Генерация логина
$nameParts = explode(' ', mb_strtolower($data['name']));
$baseLogin = transliterate($nameParts[1] ?? '') . '_' . transliterate($nameParts[0] ?? '');
$login = $baseLogin;
$counter = 1;

$checkStmt = $db->prepare("SELECT COUNT(*) FROM players WHERE login = ?");
while (true) {
    $checkStmt->bind_param('s', $login);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    if ($count == 0) break;
    $login = $baseLogin . (++$counter);
}
$checkStmt->close();

// Генерация пароля
$password = strval(rand(10000, 99999));

// Вставка игрока
$stmt = $db->prepare("INSERT INTO players 
    (team_id, name, patronymic, number, position, birth_date, join_date, height_cm, weight_kg, login, password) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    'ississsisss',
    $data['team_id'],
    $data['name'],
    $data['patronymic'],
    $data['number'],
    $data['position'],
    $data['birth_date'],
    $data['join_date'],
    $data['height_cm'],
    $data['weight_kg'],
    $login,
    $password
);

if ($stmt->execute()) {
    $playerId = $stmt->insert_id;

    // Вставка в player_statistics_2025
    $insert2025 = $db->prepare("INSERT INTO player_statistics_2025 (player_id) VALUES (?)");
    $insert2025->bind_param("i", $playerId);
    $insert2025->execute();

    // Вставка в player_statistics_all
    $insertAll = $db->prepare("INSERT INTO player_statistics_all 
        (player_id, matches, goals, assists, zeromatch, lostgoals, zanetti_priz) 
        VALUES (?, 0, 0, 0, 0, 0, 0)");
    $insertAll->bind_param("i", $playerId);
    $insertAll->execute();

    echo json_encode([
        'success' => true,
        'id' => $playerId,
        'login' => $login,
        'password' => $password
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка добавления игрока']);
}
?>
