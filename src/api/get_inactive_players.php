<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

// Порог — показываем игроков, не игравших более 30 дней
$days_limit = 30;

// Берём последнюю дату матча каждого игрока
$query = "
SELECT 
  p.id,
  p.name,
  MAX(r.date) AS last_match_date,
  DATEDIFF(CURDATE(), MAX(r.date)) AS days_since
FROM players p
LEFT JOIN match_players mp ON mp.player_id = p.id AND mp.played = 1
LEFT JOIN result r ON r.id = mp.match_id
WHERE p.team_id IN (1, 2) -- можно поменять на нужные team_id
GROUP BY p.id
HAVING last_match_date IS NOT NULL AND days_since > ?
ORDER BY days_since DESC
";

$stmt = $db->prepare($query);
$stmt->bind_param('i', $days_limit);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);