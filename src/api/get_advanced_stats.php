<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

file_put_contents(__DIR__.'/debug.log', "=== START " . date('c') . " ===\n", FILE_APPEND);
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) file_put_contents(__DIR__.'/debug.log', "SHUTDOWN: " . print_r($e, true) . "\n", FILE_APPEND);
});

session_start();
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

function jerr($msg, $code = 500, $debug = null) {
    http_response_code($code);
    $out = ['success' => false, 'message' => $msg];
    if (isset($_GET['debug']) && $debug) $out['debug'] = $debug;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

function str_lower_utf8($v) {
    return is_string($v) ? (function_exists('mb_strtolower') ? mb_strtolower($v, 'UTF-8') : strtolower($v)) : '';
}

function colExists(mysqli $db, $table, $col) {
    static $cache = [];
    $key = $table . '|' . $col;
    if (array_key_exists($key, $cache)) return $cache[$key];
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$col'";
    $result = $db->query($sql);
    if (!$result) return $cache[$key] = false;
    $ok = $result->num_rows > 0;
    $result->free();
    return $cache[$key] = $ok;
}

try {
    if (empty($_SESSION['player_id'])) jerr('Неавторизован', 401);
    $playerId = (int)$_SESSION['player_id'];

    $tbl25  = 'player_statistics_2025';
    $tblAll = 'player_statistics_all';

    $colMatches = 'matches';
    $colGoals   = 'goals';
    $colAssists = 'assists';
    $colZero    = 'zeromatch';
    $colLost    = 'lostgoals';

    $stmt = $db->prepare("SELECT position, team_id FROM players WHERE id = ?");
    if (!$stmt) jerr('Ошибка БД (подготовка players)', 500, $db->error);
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $meInfo = $stmt->get_result()->fetch_assoc();
    if (!$meInfo) jerr('Игрок не найден', 404);

    $teamId = (int)$meInfo['team_id'];
    $isGK   = (str_lower_utf8(trim($meInfo['position'] ?? '')) === 'вратарь');

    $t25HasZero  = colExists($db, $tbl25, $colZero);
    $t25HasLost  = colExists($db, $tbl25, $colLost);
    $allHasZero  = colExists($db, $tblAll, $colZero);
    $allHasLost  = colExists($db, $tblAll, $colLost);

    $p25 = [
        "COALESCE($colMatches,0) AS matches",
        "COALESCE($colGoals,0)   AS goals",
        "COALESCE($colAssists,0) AS assists",
        ($t25HasZero ? "COALESCE($colZero,0)" : "0")." AS zeromatch",
        ($t25HasLost ? "COALESCE($colLost,0)" : "0")." AS lostgoals"
    ];
    $pAll = [
        "COALESCE($colMatches,0) AS matches",
        "COALESCE($colGoals,0)   AS goals",
        "COALESCE($colAssists,0) AS assists",
        ($allHasZero ? "COALESCE($colZero,0)" : "0")." AS zeromatch",
        ($allHasLost ? "COALESCE($colLost,0)" : "0")." AS lostgoals"
    ];

    $sumSql = "
      SELECT
        SUM(matches)   AS matches,
        SUM(goals)     AS goals,
        SUM(assists)   AS assists,
        SUM(zeromatch) AS zeromatch,
        SUM(lostgoals) AS lostgoals
      FROM (
        SELECT " . implode(',', $p25) . " FROM {$tbl25}  WHERE player_id = ?
        UNION ALL
        SELECT " . implode(',', $pAll) . " FROM {$tblAll} WHERE player_id = ?
      ) t
    ";
    $stmt = $db->prepare($sumSql);
    if (!$stmt) jerr('Ошибка БД (подготовка sum)', 500, $db->error);
    $stmt->bind_param("ii", $playerId, $playerId);
    $stmt->execute();
    $meSum = $stmt->get_result()->fetch_assoc() ?: [];
    file_put_contents(__DIR__.'/debug.log', "Player $playerId Sum: " . print_r($meSum, true) . "\n", FILE_APPEND);

    $meMatches = (int)($meSum['matches'] ?? 0);
    $meGoals   = (int)($meSum['goals'] ?? 0);
    $meAssists = (int)($meSum['assists'] ?? 0);
    $meZero    = (int)($meSum['zeromatch'] ?? 0);
    $meLost    = (int)($meSum['lostgoals'] ?? 0);

    $avgGoalsPerMatch     = $meMatches > 0 ? round($meGoals / $meMatches, 2) : 0.00;
    $avgAssistsPerMatch   = $meMatches > 0 ? round($meAssists / $meMatches, 2) : 0.00;
    $avgZeroPerMatch      = $meMatches > 0 ? round($meZero / $meMatches, 2) : 0.00;
    $avgConcededPerMatch  = ($isGK && $meMatches >= 15) ? round($meLost / $meMatches, 2) : null;

    // Места в команде
    $teamAggSql = "
      SELECT p.id, p.position,
             COALESCE(ps25.$colMatches,0) + COALESCE(psall.$colMatches,0) AS matches,
             COALESCE(ps25.$colGoals,0)   + COALESCE(psall.$colGoals,0)   AS goals,
             COALESCE(ps25.$colAssists,0) + COALESCE(psall.$colAssists,0) AS assists,
             COALESCE(ps25.$colZero,0)    + COALESCE(psall.$colZero,0)    AS zeromatch,
             COALESCE(ps25.$colLost,0)    + COALESCE(psall.$colLost,0)    AS lostgoals
      FROM players p
      LEFT JOIN (
        SELECT player_id, SUM($colMatches) AS $colMatches, SUM($colGoals) AS $colGoals, SUM($colAssists) AS $colAssists,
               " . ($t25HasZero ? "SUM($colZero) AS $colZero" : "0 AS $colZero") . ",
               " . ($t25HasLost ? "SUM($colLost) AS $colLost" : "0 AS $colLost") . "
        FROM $tbl25 GROUP BY player_id
      ) ps25  ON ps25.player_id = p.id
      LEFT JOIN (
        SELECT player_id, SUM($colMatches) AS $colMatches, SUM($colGoals) AS $colGoals, SUM($colAssists) AS $colAssists,
               " . ($allHasZero ? "SUM($colZero) AS $colZero" : "0 AS $colZero") . ",
               " . ($allHasLost ? "SUM($colLost) AS $colLost" : "0 AS $colLost") . "
        FROM $tblAll GROUP BY player_id
      ) psall ON psall.player_id = p.id
      WHERE p.team_id = ?
    ";
    $stmt = $db->prepare($teamAggSql);
    if (!$stmt) jerr('Ошибка БД (подготовка team agg)', 500, $db->error);
    $stmt->bind_param("i", $teamId);
    $stmt->execute();
    $teamRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    file_put_contents(__DIR__.'/debug.log', "Team Rows: " . print_r($teamRows, true) . "\n", FILE_APPEND);

    $vals = function($k, $rows) {
        return array_map('intval', array_column($rows, $k));
    };
    $rankDesc = function($values, $target) {
        rsort($values, SORT_NUMERIC);
        $rank = 1; $prev = null; $i = 0;
        foreach ($values as $v) {
            $i++;
            if ($prev !== null && $v < $prev) $rank = $i;
            if ($v === $target) return $rank;
            $prev = $v;
        }
        return max(1, count($values));
    };
    $rankAsc = function($values, $target) { // Для lostgoals (меньше - лучше)
        sort($values, SORT_NUMERIC);
        $rank = 1; $prev = null; $i = 0;
        foreach ($values as $v) {
            $i++;
            if ($prev !== null && $v > $prev) $rank = $i;
            if ($v === $target) return $rank;
            $prev = $v;
        }
        return max(1, count($values));
    };

    // Фильтрация для вратарских показателей (только для isGK и >=15 матчей)
    $teamRowsFiltered = array_filter($teamRows, function($row) {
        return str_lower_utf8(trim($row['position'] ?? '')) === 'вратарь' && (int)$row['matches'] >= 15;
    });
    $teamPlaceMatches = $rankDesc($vals('matches', $teamRows), $meMatches);
    $teamPlaceGoals   = $rankDesc($vals('goals', $teamRows), $meGoals);
    $teamPlaceAssists = $rankDesc($vals('assists', $teamRows), $meAssists);
    $teamPlaceZero    = $t25HasZero && $allHasZero && $isGK && $meMatches >= 15 ? $rankDesc($vals('zeromatch', $teamRowsFiltered), $meZero) : '-';
    $teamPlaceLost    = $t25HasLost && $allHasLost && $isGK && $meMatches >= 15 ? $rankAsc($vals('lostgoals', $teamRowsFiltered), $meLost) : '-';

    // Места за всё время
    $allAggSql = "
      SELECT p.id, p.position,
             COALESCE(m25.$colMatches,0) + COALESCE(mall.$colMatches,0) AS matches,
             COALESCE(m25.$colGoals,0)   + COALESCE(mall.$colGoals,0)   AS goals,
             COALESCE(m25.$colAssists,0) + COALESCE(mall.$colAssists,0) AS assists,
             COALESCE(m25.$colZero,0)    + COALESCE(mall.$colZero,0)    AS zeromatch,
             COALESCE(m25.$colLost,0)    + COALESCE(mall.$colLost,0)    AS lostgoals
      FROM players p
      LEFT JOIN (
        SELECT player_id, SUM($colMatches) AS $colMatches, SUM($colGoals) AS $colGoals, SUM($colAssists) AS $colAssists,
               " . ($t25HasZero ? "SUM($colZero) AS $colZero" : "0 AS $colZero") . ",
               " . ($t25HasLost ? "SUM($colLost) AS $colLost" : "0 AS $colLost") . "
        FROM $tbl25 GROUP BY player_id
      ) m25  ON m25.player_id = p.id
      LEFT JOIN (
        SELECT player_id, SUM($colMatches) AS $colMatches, SUM($colGoals) AS $colGoals, SUM($colAssists) AS $colAssists,
               " . ($allHasZero ? "SUM($colZero) AS $colZero" : "0 AS $colZero") . ",
               " . ($allHasLost ? "SUM($colLost) AS $colLost" : "0 AS $colLost") . "
        FROM $tblAll GROUP BY player_id
      ) mall ON mall.player_id = p.id
    ";
    $allRows = $db->query($allAggSql)->fetch_all(MYSQLI_ASSOC);
    file_put_contents(__DIR__.'/debug.log', "All Rows: " . print_r($allRows, true) . "\n", FILE_APPEND);

    $allRowsFiltered = array_filter($allRows, function($row) {
        return str_lower_utf8(trim($row['position'] ?? '')) === 'вратарь' && (int)$row['matches'] >= 15;
    });
    $allPlaceMatches = $rankDesc($vals('matches', $allRows), $meMatches);
    $allPlaceGoals   = $rankDesc($vals('goals', $allRows), $meGoals);
    $allPlaceAssists = $rankDesc($vals('assists', $allRows), $meAssists);
    $allPlaceZero    = $t25HasZero && $allHasZero && $isGK && $meMatches >= 15 ? $rankDesc($vals('zeromatch', $allRowsFiltered), $meZero) : '-';
    $allPlaceLost    = $t25HasLost && $allHasLost && $isGK && $meMatches >= 15 ? $rankAsc($vals('lostgoals', $allRowsFiltered), $meLost) : '-';

    // Рейтинг вратарей по среднему пропущенных
    $gkAggSql = "
      SELECT p.id, p.position,
             COALESCE(m25.$colMatches,0) + COALESCE(mall.$colMatches,0) AS matches,
             COALESCE(l25.$colLost,0)    + COALESCE(lall.$colLost,0)    AS lostgoals
      FROM players p
      LEFT JOIN (
        SELECT player_id, SUM($colMatches) AS $colMatches
        FROM $tbl25 GROUP BY player_id
      ) m25 ON m25.player_id = p.id
      LEFT JOIN (
        SELECT player_id, SUM($colMatches) AS $colMatches
        FROM $tblAll GROUP BY player_id
      ) mall ON mall.player_id = p.id
      LEFT JOIN (
        SELECT player_id, " . ($t25HasLost ? "SUM($colLost) AS $colLost" : "0 AS $colLost") . "
        FROM $tbl25 GROUP BY player_id
      ) l25 ON l25.player_id = p.id
      LEFT JOIN (
        SELECT player_id, " . ($allHasLost ? "SUM($colLost) AS $colLost" : "0 AS $colLost") . "
        FROM $tblAll GROUP BY player_id
      ) lall ON lall.player_id = p.id
      WHERE LOWER(TRIM(p.position)) = 'вратарь'
    ";
    $gkRows = $db->query($gkAggSql)->fetch_all(MYSQLI_ASSOC);
    file_put_contents(__DIR__.'/debug.log', "GK Rows: " . print_r($gkRows, true) . "\n", FILE_APPEND);

    $gkAverages = [];
    foreach ($gkRows as $r) {
        $m = (int)$r['matches'];
        if ($m < 15) continue;
        $l = (int)$r['lostgoals'];
        $gkAverages[(int)$r['id']] = $m > 0 ? round($l / $m, 2) : INF;
    }
    asort($gkAverages, SORT_NUMERIC);

    $gkRank = null;
    if ($isGK && $meMatches >= 15 && isset($gkAverages[$playerId])) {
        $pos = 0; $rank = 0; $prev = null;
        foreach ($gkAverages as $pid => $avg) {
            $pos++;
            if ($prev === null || $avg > $prev) $rank = $pos;
            if ($pid === $playerId) { $gkRank = $rank; break; }
            $prev = $avg;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'is_gk' => $isGK,
            'totals' => [
                'matches'                  => $meMatches,
                'goals'                    => $meGoals,
                'assists'                  => $meAssists,
                'zeromatch'                => $meZero,
                'lostgoals'                => $meLost,
                'avg_goals_per_match'      => number_format($avgGoalsPerMatch, 2, '.', ''),
                'avg_assists_per_match'    => number_format($avgAssistsPerMatch, 2, '.', ''),
                'avg_zeromatch_per_match'  => number_format($avgZeroPerMatch, 2, '.', ''),
                'avg_conceded_per_match'   => ($avgConcededPerMatch !== null ? number_format($avgConcededPerMatch, 2, '.', '') : null)
            ],
            'ranks' => [
                'team'     => [
                    'matches' => $teamPlaceMatches,
                    'goals'   => $teamPlaceGoals,
                    'assists' => $teamPlaceAssists,
                    'zeromatch' => $teamPlaceZero,
                    'lostgoals' => $teamPlaceLost
                ],
                'all_time' => [
                    'matches' => $allPlaceMatches,
                    'goals'   => $allPlaceGoals,
                    'assists' => $allPlaceAssists,
                    'zeromatch' => $allPlaceZero,
                    'lostgoals' => $allPlaceLost
                ],
                'gk'       => ['avg_conceded_rank' => $gkRank]
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    file_put_contents(__DIR__.'/debug.log', "CATCH: " . $e->getMessage() . "\n", FILE_APPEND);
    jerr('Ошибка сервера', 500, $e->getMessage());
}