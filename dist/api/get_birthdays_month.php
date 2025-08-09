<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

// ВКЛЮЧИТЬ все ошибки для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Чтобы mysqli выбрасывал исключения вместо молчаливых false
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $teamIdsParam = $_GET['team_ids'] ?? '1,2';
    $teamIds = array_values(array_filter(
    array_map('intval', explode(',', $teamIdsParam)),
    function($v) { return $v > 0; }
));

    if (!$teamIds) {
        http_response_code(400);
        echo json_encode(['error'=>'team_ids не указаны']);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
    $types = str_repeat('i', count($teamIds));

    $sql = "SELECT p.id, p.name, p.team_id, p.birth_date 
            FROM players p 
            WHERE p.team_id IN ($placeholders)";

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$teamIds);
    $stmt->execute();
    $res = $stmt->get_result();
    $players = $res->fetch_all(MYSQLI_ASSOC);

    $year = (int)date('Y');
    $isLeap = (bool)date('L', strtotime("$year-01-01"));
    $out = [];

    foreach ($players as $pl) {
        if (empty($pl['birth_date'])) continue;
        [$y, $m, $d] = explode('-', $pl['birth_date']);
        if ((int)$m !== $month) continue;

        $bdDay = (int)$d;
        $bdMonth = (int)$m;
        if ($bdMonth === 2 && $bdDay === 29 && !$isLeap) {
            $bdDay = 28;
        }

        $thisYearDate = sprintf('%04d-%02d-%02d', $year, $bdMonth, $bdDay);
        $ageTurning = $year - (int)$y;

        $out[] = [
            'id' => (int)$pl['id'],
            'name' => $pl['name'],
            'team_id' => (int)$pl['team_id'],
            'birth_date' => $pl['birth_date'],
            'this_year_birthday' => $thisYearDate,
            'day' => $bdDay,
            'age_turning' => $ageTurning
        ];
    }

    usort($out, function($a,$b){
        return $a['day'] <=> $b['day'] ?: strcmp($a['name'], $b['name']);
    });

    echo json_encode($out);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Exception',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
