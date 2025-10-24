<?php
require_once __DIR__ . '/../db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

// === Логируем все вызовы ===
file_put_contents(__DIR__ . '/yookassa_debug.txt',
    date('c') . " — callback called\n", FILE_APPEND);

// === Читаем JSON от ЮKassa ===
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || empty($input['object'])) {
    http_response_code(200);
    echo "ok (empty)";
    exit;
}

$object = $input['object'];
file_put_contents(__DIR__ . '/yookassa_debug.txt',
    date('c') . " — received: " . print_r($object, true) . "\n", FILE_APPEND);

// === Проверяем успешную оплату ===
if ($object['status'] === 'succeeded' && isset($object['metadata']['player_id'])) {
    $player_id = (int)$object['metadata']['player_id'];

    // === Обнуляем сумму взноса ===
    $stmt = $db->prepare("
        UPDATE payments 
        SET amount = 0, paid_at = NOW()
        WHERE player_id = ?
    ");
    $stmt->bind_param("i", $player_id);
    $stmt->execute();

    // === Удаляем все штрафы, если они были 299 ₽ и выше ===
    $stmt2 = $db->prepare("
        DELETE FROM fines 
        WHERE player_id = ? 
          AND amount >= 299
    ");
    $stmt2->bind_param("i", $player_id);
    $stmt2->execute();

    // === Логируем успешный платёж ===
    file_put_contents(__DIR__ . '/yookassa_log.txt',
        date('c') . " player_id={$player_id} → payment succeeded, fines deleted\n",
        FILE_APPEND);
}

// === Ответ ЮKassa ===
http_response_code(200);
echo json_encode(['ok' => true]);
