<?php
require_once '../db.php';

header('Content-Type: application/json; charset=utf-8');

$query = "
  SELECT p.id, p.name, 
         COALESCE(s2025.zanetti_priz, 0) + COALESCE(sall.zanetti_priz, 0) AS trainings
  FROM players p
  LEFT JOIN player_statistics_2025 s2025 ON s2025.player_id = p.id
  LEFT JOIN player_statistics_all sall ON sall.player_id = p.id
  WHERE p.team_id = 2
  HAVING trainings <= 20
  ORDER BY trainings ASC, p.name ASC
";

$result = $db->query($query);
$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
