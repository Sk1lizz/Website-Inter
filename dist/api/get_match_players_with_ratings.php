<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($code, $msg) {
    http_response_code($code);
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$matchId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
if ($matchId <= 0) fail(400, 'match_id is required');

$sql = "
    SELECT 
        p.id,
        p.name,
        mp.goals,
        mp.assists,
        mp.goals_conceded,
        mp.yellow_cards      AS yellow_card,
        mp.red_cards         AS red_card,
        mp.missed_penalties  AS missed_penalty,
        (
            SELECT ROUND(AVG(r.rating), 2)
            FROM player_ratings r
            WHERE r.match_id = mp.match_id
              AND r.target_player_id = p.id
        ) AS rating
    FROM match_players mp
    JOIN players p ON p.id = mp.player_id
    WHERE mp.match_id = ?
      AND mp.played = 1
      AND (p.position IS NULL OR p.position != 'Тренер')
    ORDER BY p.name
";

$stmt = $db->prepare($sql);
if (!$stmt) fail(500, 'prepare failed: '.$db->error);
if (!$stmt->bind_param("i", $matchId)) fail(500, 'bind failed: '.$stmt->error);
if (!$stmt->execute()) fail(500, 'execute failed: '.$stmt->error);

$res = $stmt->get_result();
if (!$res) fail(500, 'get_result failed: '.$stmt->error);

$players = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode($players, JSON_UNESCAPED_UNICODE);
