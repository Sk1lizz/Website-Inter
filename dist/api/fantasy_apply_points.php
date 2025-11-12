<?php
// api/fantasy_apply_points.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false, 'error'=>'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$userId  = (int)($data['user_id'] ?? 0);
$weekKey = trim((string)($data['week_key'] ?? ''));
$points  = (int)($data['points'] ?? 0);

// Безопасность: начислять может либо сам владелец fantasy (по своей сессии), либо админ (добавишь свою проверку)
if (empty($_SESSION['fantasy_user_id']) || $_SESSION['fantasy_user_id'] !== $userId) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'error'=>'Forbidden']);
    exit;
}

if ($userId <= 0 || $weekKey === '' || $points === 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'Bad params']);
    exit;
}

$db->begin_transaction();
try {
    // 1) проверяем, не начисляли ли уже
    $st = $db->prepare("SELECT 1 FROM fantasy_points_log WHERE user_id=? AND week_key=? LIMIT 1");
    $st->bind_param('is', $userId, $weekKey);
    $st->execute();
    $dup = $st->get_result()->num_rows > 0;
    $st->close();

    if ($dup) {
        // ничего не делаем, просто сообщаем
        $db->commit();
        echo json_encode(['ok'=>true, 'already_applied'=>true]);
        exit;
    }

    // 2) апдейт fantasy_users.point (+points)
    $st = $db->prepare("UPDATE fantasy_users SET point = point + ? WHERE id = ? LIMIT 1");
    $st->bind_param('ii', $points, $userId);
    if (!$st->execute()) throw new Exception('update fantasy_users failed: '.$st->error);
    $st->close();

    // 3) синхронно обновим сводную таблицу состава за неделю (если она есть)
    //    last_week_points = points, total_points += points
    $st = $db->prepare("
        UPDATE fantasy_squads
           SET last_week_points = ?,
               total_points     = total_points + ?
         WHERE user_id = ? LIMIT 1
    ");
    $st->bind_param('dii', $points, $points, $userId);
    if (!$st->execute()) throw new Exception('update fantasy_squads failed: '.$st->error);
    $st->close();

    // 4) лог
    $st = $db->prepare("INSERT INTO fantasy_points_log (user_id, week_key, points) VALUES (?,?,?)");
    $st->bind_param('isi', $userId, $weekKey, $points);
    if (!$st->execute()) throw new Exception('insert log failed: '.$st->error);
    $st->close();

    $db->commit();
    echo json_encode(['ok'=>true, 'applied'=>true]);
} catch (Throwable $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}
