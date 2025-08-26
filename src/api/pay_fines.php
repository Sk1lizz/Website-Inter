<?php
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$playerId = (int)($data['player_id'] ?? 0);

if ($playerId <= 0) {
  echo json_encode(['success' => false, 'error' => 'Bad params']);
  exit;
}

$db->begin_transaction();
try {
  // Сумма штрафов игрока
  $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM fines WHERE player_id=?");
  if (!$stmt) throw new Exception("SUM fines prepare: ".$db->error);
  $stmt->bind_param("i", $playerId);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  if ($total <= 0) {
    $db->rollback();
    echo json_encode(['success' => false, 'error' => 'Нет штрафов к оплате']);
    exit;
  }

  // Удаляем штрафы игрока
  $stmt = $db->prepare("DELETE FROM fines WHERE player_id=?");
  if (!$stmt) throw new Exception("DELETE fines prepare: ".$db->error);
  $stmt->bind_param("i", $playerId);
  if (!$stmt->execute()) throw new Exception("DELETE fines exec: ".$stmt->error);
  $stmt->close();

  $db->commit();
  echo json_encode(['success' => true, 'paid_amount' => $total]);
} catch (Throwable $e) {
  $db->rollback();
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
