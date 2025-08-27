<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Нет ID']);
    exit;
}

/** ---- Матч ---- */
$matchStmt = $db->prepare("SELECT * FROM result WHERE id = ?");
$matchStmt->bind_param("i", $id);
$matchStmt->execute();
$match = $matchStmt->get_result()->fetch_assoc();
$matchStmt->close();

if (!$match) {
    http_response_code(404);
    echo json_encode(['error' => 'Матч не найден']);
    exit;
}

/** ---- Игроки ----
 * ВАЖНО: добираем карточки и нереализованные пенальти
 * В БД колонки называются во множественном числе:
 *   yellow_cards, red_cards, missed_penalties
 * Для совместимости отдаем ещё алиасы в единственном числе.
 */
$playersSql = "
    SELECT
        p.name,
        p.number,
        p.position,

        mp.goals,
        mp.assists,
        mp.goals_conceded,

        mp.yellow_cards,
        mp.red_cards,
        mp.missed_penalties,

        /* алиасы для фронтов, ожидающих singular-имена */
        mp.yellow_cards       AS yellow_card,
        mp.red_cards          AS red_card,
        mp.missed_penalties   AS missed_penalty

    FROM match_players mp
    JOIN players p ON p.id = mp.player_id
    WHERE mp.match_id = ? AND mp.played = 1
    ORDER BY
      FIELD(p.position, 'Вратарь','Защитник','Полузащитник','Нападающий','Тренер'),
      p.number ASC
";

$playersStmt = $db->prepare($playersSql);
$playersStmt->bind_param("i", $id);
$playersStmt->execute();
$players = $playersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$playersStmt->close();

/** ---- Ответ ---- */
echo json_encode([
    'match'   => $match,
    'players' => $players,
], JSON_UNESCAPED_UNICODE);
