<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Нет доступа']);
  exit;
}

header('Content-Type: application/json');
require_once '../db.php';

$payload = json_decode(file_get_contents('php://input'), true);
$items = $payload['items'] ?? [];

if (empty($items)) {
  echo json_encode(['success' => false, 'message' => 'Нет данных для сохранения']);
  exit;
}

$saved = 0;
$stmt = $db->prepare("
  INSERT INTO fantasy_players (player_id, cost, points)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE cost = VALUES(cost), points = VALUES(points)
");

foreach ($items as $it) {
  $pid = (int)($it['player_id'] ?? 0);
  $cost = (float)($it['cost'] ?? 0);
  $points = (int)($it['points'] ?? 0);

  // Проверка корректности данных
  if ($pid > 0 && is_numeric($cost) && is_numeric($points)) {
    $stmt->bind_param('idi', $pid, $cost, $points);
    if ($stmt->execute()) {
      $saved++;
    }
  }
}

echo json_encode(['success' => true, 'saved' => $saved]);