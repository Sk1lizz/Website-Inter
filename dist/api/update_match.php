<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

// Читаем JSON-тело
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Обязательный параметр
if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Match ID is required']);
    exit;
}

$matchId          = (int)$input['id'];
$championshipName = $input['championship_name'] ?? '';
$opponent         = $input['opponent'] ?? '';
$ourGoals         = (int)($input['our_goals'] ?? 0);
$opponentGoals    = (int)($input['opponent_goals'] ?? 0);

// Дата может приходить как 'date' или 'match_date' — поддержим оба
$dateRaw = $input['match_date'] ?? $input['date'] ?? null;
$date    = $dateRaw ?: null; // null запишется как NULL

// Обозначение результата матча
$matchResult = $input['result'] ?? null;

// Текстовые поля из таблицы result
// (названия ключей оставим как goals/assists, чтобы совпадали с get_matches_admin.php)
$goalsText   = array_key_exists('goals', $input)   ? (string)$input['goals']   : null;
$assistsText = array_key_exists('assists', $input) ? (string)$input['assists'] : null;

// Игроки
$players = is_array($input['players'] ?? null) ? $input['players'] : [];

// --- Транзакция ---
if (!$db->begin_transaction()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to start transaction: ' . $db->error]);
    exit;
}

try {
    // 1) Обновляем сам матч в таблице result
    $stmt = $db->prepare("
        UPDATE result
        SET championship_name = ?,
            opponent          = ?,
            our_goals         = ?,
            opponent_goals    = ?,
            date              = ?,
            match_result      = ?,
            goals             = ?,
            assists           = ?
        WHERE id = ?
    ");
    if (!$stmt) {
        throw new Exception('Prepare failed (update result): ' . $db->error);
    }

    // Типы: s s i i s s s s i
    if (!$stmt->bind_param(
        "ssiissssi",
        $championshipName,
        $opponent,
        $ourGoals,
        $opponentGoals,
        $date,
        $matchResult,
        $goalsText,
        $assistsText,
        $matchId
    )) {
        throw new Exception('Bind failed (update result): ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Update failed (result): ' . $stmt->error);
    }
    $stmt->close();

    // 2) Заменяем состав и статистику игроков матча
    // Сначала удалим старые строки, чтобы не было дублей и “висяков”
    $del = $db->prepare("DELETE FROM match_players WHERE match_id = ?");
    if (!$del) {
        throw new Exception('Prepare failed (delete match_players): ' . $db->error);
    }
    if (!$del->bind_param("i", $matchId)) {
        throw new Exception('Bind failed (delete match_players): ' . $del->error);
    }
    if (!$del->execute()) {
        throw new Exception('Delete failed (match_players): ' . $del->error);
    }
    $del->close();

    // Теперь вставим текущий список игроков (played = 1 для отмеченных)
    if (!empty($players)) {
        $ins = $db->prepare("
            INSERT INTO match_players (match_id, player_id, played, goals, assists, goals_conceded)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if (!$ins) {
            throw new Exception('Prepare failed (insert match_players): ' . $db->error);
        }

        foreach ($players as $p) {
            $pid      = (int)($p['id'] ?? 0);
            if ($pid <= 0) continue;

            $played   = 1; // По модалке — чекбокс означает, что игрок сыграл
            $goals    = (int)($p['goals'] ?? 0);
            $assists  = (int)($p['assists'] ?? 0);
            $conceded = (int)($p['goals_conceded'] ?? 0);

            if (!$ins->bind_param("iiiiii", $matchId, $pid, $played, $goals, $assists, $conceded)) {
                throw new Exception("Bind failed (insert player_id=$pid): " . $ins->error);
            }
            if (!$ins->execute()) {
                throw new Exception("Insert failed (player_id=$pid): " . $ins->error);
            }
        }
        $ins->close();
    }

    // Всё успешно — коммитим
    if (!$db->commit()) {
        throw new Exception('Commit failed: ' . $db->error);
    }

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Откатываем любые изменения при ошибке
    $db->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
