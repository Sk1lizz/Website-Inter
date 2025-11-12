<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
if ($playerId <= 0) {
    echo json_encode(['frame_key' => '', 'title' => '']);
    exit;
}

// Берём текущую рамку игрока
$stmt = $db->prepare("SELECT frame_key FROM player_frames WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// Карта человекочитаемых названий
$titleMap = [
     'gold'        => 'Золотая рамка профиля',
  'green'       => 'Зелёная рамка профиля',
  'blue'        => 'Синяя рамка профиля',
  'purple'      => 'Фиолетовая рамка профиля',
  'gold_glow'   => 'Сияющая золотая рамка профиля',
  'green_glow'  => 'Сияющая зелёная рамка профиля',
  'blue_glow'   => 'Сияющая синяя рамка профиля',
  'purple_glow' => 'Сияющая фиолетовая рамка профиля',
];

$frameKey = $row['frame_key'] ?? '';
$title = isset($titleMap[$frameKey]) ? $titleMap[$frameKey] : '';

echo json_encode([
    'frame_key' => $frameKey,
    'title'     => $title
], JSON_UNESCAPED_UNICODE);
