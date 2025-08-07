<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../db.php';
header('Content-Type: application/json');

function getStatistics($excludeMiniFootball = false) {
    global $db;

    $whereClause = "1"; // нет поля deleted — убираем

    if ($excludeMiniFootball) {
        $whereClause .= " AND teams_id != 9";
    }

    $sql = "
        SELECT 
            COUNT(*) AS total_matches,
            SUM(match_result = 'W') AS wins,
            SUM(match_result = 'X') AS draws,
            SUM(match_result = 'L') AS losses,
            SUM(our_goals) AS goals_scored,
            SUM(opponent_goals) AS goals_conceded
        FROM result
        WHERE $whereClause
    ";

    $res = $db->query($sql);
    if (!$res) {
        http_response_code(500);
        echo json_encode([
            "error" => "Ошибка SQL-запроса",
            "message" => $db->error,
            "sql" => $sql
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $row = $res->fetch_assoc();
    $row['goal_difference'] = (int)$row['goals_scored'] - (int)$row['goals_conceded'];
    return $row;
}

$response = [
    "all" => getStatistics(false),
    "without_mini" => getStatistics(true)
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
