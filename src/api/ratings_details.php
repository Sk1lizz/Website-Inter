<?php
session_start();
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * GET:
 *  - training_id (int) — обязательно
 */
$trainingId = isset($_GET['training_id']) ? (int)$_GET['training_id'] : 0;
if ($trainingId <= 0) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'training_id required']); exit;
}

$sql = "
  SELECT
    tr.player_id,
    p.name AS player_name,
    tr.intensity, tr.fatigue, tr.mood, tr.enjoyment,
    tr.created_at
  FROM training_ratings tr
  LEFT JOIN players p ON p.id = tr.player_id
  WHERE tr.training_id = ?
  ORDER BY tr.created_at DESC
";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $trainingId);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  $rows[] = [
    'player_id'  => (int)$r['player_id'],
    'player_name'=> $r['player_name'] ?: 'Игрок #'.$r['player_id'],
    'intensity'  => (int)$r['intensity'],
    'fatigue'    => (int)$r['fatigue'],
    'mood'       => (int)$r['mood'],
    'enjoyment'  => (int)$r['enjoyment'],
    'created_at' => $r['created_at'],
  ];
}

echo json_encode(['success'=>true,'ratings'=>$rows], JSON_UNESCAPED_UNICODE);