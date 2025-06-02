<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connection.php';

$player_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Получаем текущий сезон
    $stmtSeason = $conn->prepare("SELECT * FROM player_statistics_2025 WHERE player_id = ?");
    if (!$stmtSeason) {
        throw new Exception("Ошибка запроса к сезонной статистике: " . $conn->error);
    }
    $stmtSeason->bind_param("i", $player_id);
    $stmtSeason->execute();
    $resSeason = $stmtSeason->get_result();
    $season = $resSeason->fetch_assoc() ?: [
        'matches' => 0,
        'goals' => 0,
        'assists' => 0,
        'zeromatch' => 0,
        'lostgoals' => 0,
        'zanetti_priz' => 0
    ];

    // Получаем накопленную общую статистику
    $stmtAll = $conn->prepare("SELECT * FROM player_statistics_all WHERE player_id = ?");
    if (!$stmtAll) {
        throw new Exception("Ошибка запроса к общей статистике: " . $conn->error);
    }
    $stmtAll->bind_param("i", $player_id);
    $stmtAll->execute();
    $resAll = $stmtAll->get_result();
    $all = $resAll->fetch_assoc();

    // Если нет общей — возвращаем только текущий сезон
    if (!$all) {
        echo json_encode($season);
        exit;
    }

    // Складываем сезонную + общую
    $combined = [
        'matches'       => $season['matches'] + $all['matches'],
        'goals'         => $season['goals'] + $all['goals'],
        'assists'       => $season['assists'] + $all['assists'],
        'zeromatch'     => $season['zeromatch'] + $all['zeromatch'],
        'lostgoals'     => $season['lostgoals'] + $all['lostgoals'],
        'zanetti_priz'  => $season['zanetti_priz'] + $all['zanetti_priz'],
    ];

    echo json_encode([
        'season' => $season,
        'all' => $all,
        'combined' => $combined
    ]);
    exit;
    
    echo json_encode($combined);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Ошибка на сервере',
        'details' => $e->getMessage()
    ]);
}

$conn->close();