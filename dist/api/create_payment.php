<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../db.php';

use YooKassa\Client;

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['player_id'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'unauthorized']));
}

$player_id = (int)$_SESSION['player_id'];

// === Получаем данные игрока ===
$stmt = $db->prepare("SELECT name, email FROM players WHERE id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$playerRow = $stmt->get_result()->fetch_assoc();

$playerName  = $playerRow['name'] ?? 'Игрок FC Inter Moscow';
$playerEmail = trim($playerRow['email'] ?? '');

// если нет email → вернуть ответ клиенту, чтобы запросил e-mail
if (empty($playerEmail)) {
    echo json_encode(['need_email' => true]);
    exit;
}

// === Ежемесячный взнос ===
$stmt = $db->prepare("SELECT amount FROM payments WHERE player_id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$baseAmount = (float)($row['amount'] ?? 0);

// === Штрафы ===
$stmt = $db->prepare("SELECT SUM(amount) AS total_fines FROM fines WHERE player_id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$fineRow = $stmt->get_result()->fetch_assoc();
$totalFines = (float)($fineRow['total_fines'] ?? 0);

// === Общая сумма к оплате ===
$totalToPay = $baseAmount + ($totalFines >= 299 ? $totalFines : 0);
if ($totalToPay <= 0) {
    exit(json_encode(['error' => 'no_amount']));
}

// === Настройки ЮKassa ===
$config = require __DIR__ . '/../../config/yookassa.php';
$client = new Client();
$client->setAuth($config['shop_id'], $config['secret_key']);

try {
    $payment = $client->createPayment([
        'amount' => [
            'value' => number_format($totalToPay, 2, '.', ''),
            'currency' => 'RUB'
        ],
        'confirmation' => [
            'type' => 'redirect',
            'return_url' => 'https://fcintermoscow.com/user.php'
        ],
        'capture' => true,
        'description' => "Оплата взноса игрока #$player_id ($playerName)",
        'receipt' => [
            'customer' => [
                'full_name' => $playerName,
                'email' => $playerEmail
            ],
            'items' => [[
                'description' => 'Тренировки по футболу (взнос и штрафы)',
                'quantity' => 1.0,
                'amount' => [
                    'value' => number_format($totalToPay, 2, '.', ''),
                    'currency' => 'RUB'
                ],
                'vat_code' => 1,
                'payment_mode' => 'full_payment',
                'payment_subject' => 'service'
            ]]
        ],
        'metadata' => [
            'player_id' => $player_id,
            'fines' => $totalFines,
            'base_amount' => $baseAmount
        ]
    ], uniqid('', true));

    echo json_encode([
        'success' => true,
        'url' => $payment->getConfirmation()->getConfirmationUrl()
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}