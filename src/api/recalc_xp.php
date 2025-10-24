<?php
file_put_contents(__DIR__ . '/recalc_log.txt', date('Y-m-d H:i:s') . " called player_id=" . ($_GET['player_id'] ?? 'null') . "\n", FILE_APPEND);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

// 0) проверка параметра
if (!isset($_GET['player_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Не указан player_id'], JSON_UNESCAPED_UNICODE);
    exit;
}
$playerId = (int)$_GET['player_id'];

// 1) гарантируем наличие столбца xp_last_recalc
$col = $db->query("SHOW COLUMNS FROM players LIKE 'xp_last_recalc'");
if ($col && $col->num_rows === 0) {
    $db->query("ALTER TABLE players ADD COLUMN xp_last_recalc DATETIME NULL AFTER xp_total");
    file_put_contents(__DIR__ . '/recalc_log.txt', "Added column xp_last_recalc\n", FILE_APPEND);
}

// 2) утилита получения суммарной статистики
function getStats($db, $table, $playerId) {
    $sql = "
        SELECT 
            SUM(matches)   AS matches,
            SUM(goals)     AS goals,
            SUM(assists)   AS assists,
            SUM(zeromatch) AS zeromatch
        FROM `$table`
        WHERE player_id = ?
    ";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        file_put_contents(__DIR__ . '/recalc_log.txt', "Prepare failed ($table): ".$db->error."\n", FILE_APPEND);
        return ['matches'=>0,'goals'=>0,'assists'=>0,'zeromatch'=>0];
    }
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];
    return [
        'matches'   => (int)($row['matches'] ?? 0),
        'goals'     => (int)($row['goals'] ?? 0),
        'assists'   => (int)($row['assists'] ?? 0),
        'zeromatch' => (int)($row['zeromatch'] ?? 0),
    ];
}

// 3) берём «all» + текущий сезон (как в levelplayer)
$statsAll  = getStats($db, 'player_statistics_all',  $playerId);
$stats2025 = getStats($db, 'player_statistics_2025', $playerId);

// суммарно (если all уже без текущего сезона)
$totalMatches   = $statsAll['matches']   + $stats2025['matches'];
$totalGoals     = $statsAll['goals']     + $stats2025['goals'];
$totalAssists   = $statsAll['assists']   + $stats2025['assists'];
$totalZeromatch = $statsAll['zeromatch'] + $stats2025['zeromatch'];


// === 4. Дата вступления и месяцы в команде ===
$stmt = $db->prepare("SELECT join_date FROM players WHERE id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$joinDate = ($stmt->get_result()->fetch_assoc()['join_date'] ?? null);

$monthsInTeam = 0;
if ($joinDate) {
    try {
        $join = new DateTime($joinDate);
        $now  = new DateTime();

        $years  = $now->format('Y') - $join->format('Y');
        $months = $now->format('n') - $join->format('n');

        if ($months < 0) {
            $years--;
            $months += 12;
        }

        // 👇 теперь полностью как в JS:
        $monthsInTeam = $years * 12 + $months;

    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/recalc_log.txt', "Bad join_date for player $playerId\n", FILE_APPEND);
    }
}

// 5) очки за успехи (player_success) — как в levelplayer: сумма points

$successPoints = 0;
$sqlSuccess = "
    SELECT COALESCE(SUM(s.points), 0) AS pts
    FROM player_success ps
    JOIN Success s ON s.id = ps.success_id
    WHERE ps.player_id = ?
";
if ($stmt = $db->prepare($sqlSuccess)) {
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $successPoints = (int)($stmt->get_result()->fetch_assoc()['pts'] ?? 0);
} else {
    file_put_contents(__DIR__ . '/recalc_log.txt', "Prepare failed (Success join): " . $db->error . "\n", FILE_APPEND);
}

// 6) очки за награды (achievements) — как в levelplayer: count * 1000
$awardsCount = 0;
if ($stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM achievements WHERE player_id = ?")) {
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $awardsCount = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
} else {
    file_put_contents(__DIR__ . "/recalc_log.txt", "Prepare failed (achievements): ".$db->error."\n", FILE_APPEND);
}
$awardsPoints = $awardsCount * 1000;

// 7) формула XP — 1 в 1 как в levelplayer
$xpTotal  = 0;
$xpTotal += $monthsInTeam * 100;         // месяцы
$xpTotal += $totalMatches   * 50;        // матчи
$xpTotal += $totalGoals     * 100;       // голы (100!)
$xpTotal += $totalAssists   * 100;       // ассисты
$xpTotal += $totalZeromatch * 250;       // матчи на ноль
$xpTotal += $successPoints;              // сумма points из success_list
$xpTotal += $awardsPoints;               // награды * 1000

// 7.1) ➕ XP за посещаемость тренировок (zanetti_priz * 25)
$trainingXP = 0;

// player_statistics_2025
$stmt = $db->prepare("SELECT COALESCE(SUM(zanetti_priz), 0) AS total FROM player_statistics_2025 WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$res1 = $stmt->get_result()->fetch_assoc();
$trainingXP += ((int)($res1['total'] ?? 0)) * 25;

// player_statistics_all
$stmt = $db->prepare("SELECT COALESCE(SUM(zanetti_priz), 0) AS total FROM player_statistics_all WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$res2 = $stmt->get_result()->fetch_assoc();
$trainingXP += ((int)($res2['total'] ?? 0)) * 25;

$xpTotal += $trainingXP;

// 8) апдейт игрока
$stmt = $db->prepare("UPDATE players SET xp_total = ?, xp_last_recalc = NOW() WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>'SQL prepare error (update)'], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param("ii", $xpTotal, $playerId);
$ok = $stmt->execute();
if (!$ok) {
    file_put_contents(__DIR__ . '/recalc_log.txt', "Update error: ".$stmt->error."\n", FILE_APPEND);
}

// 9) ответ
echo json_encode([
    'success'   => $ok,
    'player_id' => $playerId,
    'xp_total'  => $xpTotal,
    'details'   => [
        'months_in_team' => $monthsInTeam,
        'matches'        => $totalMatches,
        'goals'          => $totalGoals,
        'assists'        => $totalAssists,
        'zeromatch'      => $totalZeromatch,
        'success_points' => $successPoints,
        'awards_count'   => $awardsCount,
        'awards_points'  => $awardsPoints,
        'training_xp'    => $trainingXP       // 👈 добавили
    ]
], JSON_UNESCAPED_UNICODE);
