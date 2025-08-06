<?php
require_once '../db.php';
header('Content-Type: application/json');

$matchId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
if (!$matchId) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("
    SELECT 
        p.id,
        p.name,
        mp.goals,
        mp.assists,
        mp.goals_conceded,
        (
            SELECT ROUND(AVG(r.rating), 2)
            FROM player_ratings r
            WHERE r.match_id = mp.match_id AND r.target_player_id = p.id
        ) AS rating
    FROM match_players mp
    JOIN players p ON mp.player_id = p.id
    WHERE mp.match_id = ? AND mp.played = 1 AND p.position != 'Тренер'
");

$stmt->bind_param("i", $matchId);
$stmt->execute();
$result = $stmt->get_result();

$players = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($players);
exit;
