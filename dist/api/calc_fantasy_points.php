<?php
// /api/calc_fantasy_points.php
// PHP 7.1

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php'; // mysqli $db

// Временный файл для отладки
$debugFile = __DIR__ . '/debug_calc_fantasy_points.log';
function debugLog($message) {
    global $debugFile;
    file_put_contents($debugFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

// ---------- utils ----------
function getLastWeekendWindow(): array {
    $now = new DateTime('now', new DateTimeZone('Europe/Moscow'));
    $dow = (int)$now->format('N'); // 1..7 Mon..Sun
    if ($dow >= 6) { // Sat/Sun -> previous weekend
        $sat = new DateTime('last week saturday');
        $sun = new DateTime('last week sunday 23:59:59');
    } else {
        $sat = new DateTime('last saturday');
        $sun = new DateTime('last sunday 23:59:59');
    }
    if ($sat > $sun) { $t = $sat; $sat = $sun; $sun = $t; }
    $nextMonday = new DateTime($sun->format('Y-m-d') . ' +1 day');

    $window = [
        'start' => $sat->format('Y-m-d 00:00:00'),
        'end' => $sun->format('Y-m-d 23:59:59'),
        'start_date' => $sat->format('Y-m-d'),
        'end_date' => $sun->format('Y-m-d'),
        'next_monday' => $nextMonday->format('Y-m-d 00:00:00'),
    ];
    debugLog("Weekend window: " . json_encode($window));
    return $window;
}

function normalizePosition($pos) {
    $rawPos = (string)$pos;
    $p = mb_strtolower(trim($rawPos), 'UTF-8');
   $map = [
  'gk'=>'GK', 'вратарь'=>'GK', 'голкипер'=>'GK',
  'df'=>'DF', 'защитник'=>'DF',
  'mf'=>'MF', 'полузащитник'=>'MF', 'хавбек'=>'MF',
  'fw'=>'FW', 'нападающий'=>'FW', 'форвард'=>'FW',
];
    $normPos = $map[$p] ?? strtoupper($rawPos);
    debugLog("Normalizing position raw='$rawPos', lower='$p', result='$normPos'");
    return $normPos;
}

function goalPoints($teamId, $position) {
    $position = normalizePosition($position);
    $is11x11 = ((int)$teamId === 2);
    $points = $is11x11 ? 
        ($position === 'GK' || $position === 'DF' ? 6 : ($position === 'MF' ? 5 : 4)) :
        ($position === 'GK' || $position === 'DF' ? 4 : ($position === 'MF' ? 3 : 2));
    debugLog("Goal points for teamId=$teamId, position=$position: $points");
    return $points;
}

function i($v) { return (int)$v; }

// ---------- 1) окно прошедших выходных ----------
$window = getLastWeekendWindow();
$start = $db->real_escape_string($window['start']);
$end = $db->real_escape_string($window['end']);

// ---------- 2) матчи этих выходных (только наши команды 1,2) ----------
$sqlMatches = "
    SELECT id, teams_id, date
    FROM result
    WHERE teams_id IN (1,2)
      AND date >= '{$start}' AND date <= '{$end}'
";
$matchesRes = $db->query($sqlMatches);
if (!$matchesRes) {
    debugLog("ERROR: Matches query failed: " . $db->error);
    echo json_encode(['success' => false, 'message' => 'Ошибка выборки матчей: ' . $db->error]);
    exit;
}

$matchIds = [];
$matchTeamById = [];
while ($m = $matchesRes->fetch_assoc()) {
    $mid = i($m['id']);
    $matchIds[] = $mid;
    $matchTeamById[$mid] = i($m['teams_id']);
}
debugLog("Matches found: " . json_encode($matchIds));
debugLog("Match teams: " . json_encode($matchTeamById));

if (empty($matchIds)) {
    echo json_encode([
        'success' => true,
        'updated' => 0,
        'message' => 'Матчей на прошедших выходных нет',
        'window' => $window
    ]);
    exit;
}
$idList = implode(',', array_map('intval', $matchIds));

// ---------- 3) события игроков и очки ----------
$sqlMp = "
    SELECT
        mp.match_id, mp.player_id, mp.played, mp.goals, mp.assists, mp.clean_sheet,
        mp.goals_conceded, mp.yellow_cards, mp.red_cards, mp.missed_penalties,
        p.team_id AS player_team_id, p.position
    FROM match_players mp
    INNER JOIN players p ON p.id = mp.player_id
    WHERE mp.match_id IN ($idList)
";
$mpRes = $db->query($sqlMp);
if (!$mpRes) {
    debugLog("ERROR: Match players query failed: " . $db->error);
    echo json_encode(['success' => false, 'message' => 'Ошибка выборки match_players: ' . $db->error]);
    exit;
}

$weekPointsByPlayer = [];
$playedThisWeekend = [];
$playerBasePos = [];

while ($r = $mpRes->fetch_assoc()) {
    $playerId = i($r['player_id']);
    $matchId = i($r['match_id']);
    $played = i($r['played']) > 0 ? 1 : 0;
    $goals = i($r['goals']);
    $assists = i($r['assists']);
    $clean = i($r['clean_sheet']);
    $gc = i($r['goals_conceded']);
    $yc = i($r['yellow_cards']);
    $rc = i($r['red_cards']);
    $missed = i($r['missed_penalties']);
    $teamsIdForMatch = isset($matchTeamById[$matchId]) ? $matchTeamById[$matchId] : i($r['player_team_id']);
    $pos = $r['position'];
    $normPos = normalizePosition($pos);

    $playerBasePos[$playerId] = $normPos;

    $points = 0;
    $pointDetails = ['played' => 0, 'goals' => 0, 'assists' => 0, 'clean_sheet' => 0, 'yellow_cards' => 0, 'red_cards' => 0, 'missed_penalties' => 0, 'goals_conceded' => 0];
    if ($played) {
        $points += 1;
        $pointDetails['played'] = 1;
        $playedThisWeekend[$playerId] = true;
    }
    if ($goals > 0) {
        $goalPts = $goals * goalPoints($teamsIdForMatch, $pos);
        $points += $goalPts;
        $pointDetails['goals'] = $goalPts;
    }
    if ($assists > 0) {
        $assistPts = $assists * 3;
        $points += $assistPts;
        $pointDetails['assists'] = $assistPts;
    }
    if ($clean > 0 && ($normPos === 'GK' || $normPos === 'DF')) {
        $cleanPts = 4;
        $points += $cleanPts;
        $pointDetails['clean_sheet'] = $cleanPts;
    }
    if ($yc > 0) {
        $ycPts = -1 * $yc;
        $points += $ycPts;
        $pointDetails['yellow_cards'] = $ycPts;
    }
    if ($rc > 0) {
        $rcPts = -3 * $rc;
        $points += $rcPts;
        $pointDetails['red_cards'] = $rcPts;
    }
    if ($normPos === 'GK' && $gc > 5) {
        $gcPts = -3;
        $points += $gcPts;
        $pointDetails['goals_conceded'] = $gcPts;
    }
    if ($missed > 0) {
        $missedPts = -2 * $missed;
        $points += $missedPts;
        $pointDetails['missed_penalties'] = $missedPts;
    }

    if (!isset($weekPointsByPlayer[$playerId])) $weekPointsByPlayer[$playerId] = 0;
    $weekPointsByPlayer[$playerId] += $points;

    debugLog("Player $playerId in match $matchId: pos=$normPos, played=$played, goals=$goals, assists=$assists, clean=$clean, gc=$gc, yc=$yc, rc=$rc, missed=$missed, team_id=$teamsIdForMatch, points=$points, details=" . json_encode($pointDetails));
}

debugLog("Week points by player: " . json_encode($weekPointsByPlayer));
debugLog("Played this weekend: " . json_encode($playedThisWeekend));
debugLog("Player positions: " . json_encode($playerBasePos));

// ---------- 3a) заранее подтянем позиции для всех игроков из составов ----------
$squadsResTmp = $db->query("SELECT gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id FROM fantasy_squads");
$idsToFetch = [];
if ($squadsResTmp) {
    while ($row = $squadsResTmp->fetch_assoc()) {
        foreach (['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'] as $f) {
            if (!empty($row[$f])) $idsToFetch[i($row[$f])] = true;
        }
    }
    $squadsResTmp->data_seek(0);
}
debugLog("Players to fetch positions: " . json_encode(array_keys($idsToFetch)));

if (!empty($idsToFetch)) {
    $idListAll = implode(',', array_keys($idsToFetch));
    $pr = $db->query("SELECT id, position FROM players WHERE id IN ($idListAll)");
    if ($pr) {
        while ($p = $pr->fetch_assoc()) {
            $playerBasePos[i($p['id'])] = normalizePosition($p['position']);
        }
    } else {
        debugLog("ERROR: Players position query failed: " . $db->error);
    }
}
debugLog("Updated player positions: " . json_encode($playerBasePos));

// ---------- 4) обновляем fantasy_squads (с учётом скамейки) ----------
$squadsRes = $db->query("SELECT * FROM fantasy_squads");
if (!$squadsRes) {
    debugLog("ERROR: Fantasy squads query failed: " . $db->error);
    echo json_encode(['success' => false, 'message' => 'Ошибка выборки fantasy_squads: ' . $db->error]);
    exit;
}

$updated = 0;
$details = [];
$weekStart = new DateTime($window['start'], new DateTimeZone('Europe/Moscow'));
$weekEnd = new DateTime($window['end'], new DateTimeZone('Europe/Moscow'));
$nextMonday = new DateTime($window['next_monday'], new DateTimeZone('Europe/Moscow'));

while ($sq = $squadsRes->fetch_assoc()) {
    $userId = i($sq['user_id']);
    $createdAt = !empty($sq['created_at']) ? new DateTime($sq['created_at'], new DateTimeZone('Europe/Moscow')) : null;

    // Пропуск новых составов
    if ($createdAt && $createdAt >= $weekStart && $createdAt <= $weekEnd) {
        $db->query("UPDATE fantasy_squads SET last_week_points = 0, updated_at = NOW() WHERE user_id = {$userId} LIMIT 1");
        $details[] = ['user_id' => $userId, 'skipped' => 'created_on_weekend'];
        debugLog("Squad for user $userId skipped (created on weekend)");
        continue;
    }
    if ($createdAt && $createdAt >= $nextMonday) {
        $db->query("UPDATE fantasy_squads SET last_week_points = 0, updated_at = NOW() WHERE user_id = {$userId} LIMIT 1");
        $details[] = ['user_id' => $userId, 'skipped' => 'created_next_week'];
        debugLog("Squad for user $userId skipped (created next week)");
        continue;
    }

    // Состав
    $gk = i($sq['gk_id']);
    $df1 = i($sq['df1_id']);
    $df2 = i($sq['df2_id']);
    $mf1 = i($sq['mf1_id']);
    $mf2 = i($sq['mf2_id']);
    $fw = i($sq['fw_id']);
    $benchId = !empty($sq['bench_id']) ? i($sq['bench_id']) : 0;
    $captainId = !empty($sq['captain_player_id']) ? i($sq['captain_player_id']) : 0;

    $playersInSquad = array_values(array_filter([$gk, $df1, $df2, $mf1, $mf2, $fw]));
    debugLog("Squad for user $userId: players=" . json_encode($playersInSquad) . ", bench=$benchId, captain=$captainId");

    $sum = 0;
    $playerPointsDetails = [];
    foreach ($playersInSquad as $pid) {
        $playerPoints = isset($weekPointsByPlayer[$pid]) ? $weekPointsByPlayer[$pid] : 0;
        $sum += $playerPoints;
        $playerPointsDetails[$pid] = $playerPoints;
        debugLog("Player $pid points for user $userId: $playerPoints");
    }

    // Скамейка
    $benchPts = 0;
if ($benchId && isset($playerBasePos[$benchId])) {
    $benchPos = $playerBasePos[$benchId];
    $roleMap = ['GK' => [$gk], 'DF' => [$df1, $df2], 'MF' => [$mf1, $mf2], 'FW' => [$fw]];
    if (isset($roleMap[$benchPos])) {
        $notPlayed = 0;
        $notPlayedStarters = [];
        foreach ($roleMap[$benchPos] as $starterId) {
            $playedFlag = !empty($playedThisWeekend[$starterId]);
            if (!$playedFlag) {
                $notPlayed++;
                $notPlayedStarters[] = $starterId;
            }
        }
        if ($notPlayed >= 1) {
            $benchPts = isset($weekPointsByPlayer[$benchId]) ? $weekPointsByPlayer[$benchId] : 0;
            // Не добавляем $sum += $benchPts, так как стартеры уже учтены с 0 очков
            $sum += $benchPts; // Учитываем только запасного
            debugLog("Bench player $benchId (pos=$benchPos) replaced starter for user $userId: $benchPts points, replaced: " . json_encode($notPlayedStarters));
        } else {
            debugLog("No non-playing starters for bench position $benchPos for user $userId");
        }
    }
}

    // Капитан
    $capPts = 0;
    if ($captainId && in_array($captainId, $playersInSquad, true)) {
        $capPts = isset($weekPointsByPlayer[$captainId]) ? $weekPointsByPlayer[$captainId] : 0;
        $sum += $capPts;
        debugLog("Captain $captainId for user $userId: $capPts points (doubled)");
    } else if ($captainId) {
        debugLog("WARNING: Captain $captainId for user $userId not in starting squad");
    }

    $sumInt = (int)$sum;
    $totalPrev = isset($sq['total_points']) ? (float)$sq['total_points'] : 0.0;
    $totalNew = $totalPrev + $sumInt;

    debugLog("Squad for user $userId: last_week_points=$sumInt, total_points_new=$totalNew, player_points=" . json_encode($playerPointsDetails) . ", bench_points=$benchPts, captain_points=$capPts");

    $upd = $db->prepare("
        UPDATE fantasy_squads
        SET last_week_points = ?, total_points = ?, updated_at = NOW()
        WHERE user_id = ? LIMIT 1
    ");
    if ($upd) {
        $upd->bind_param('dii', $sumInt, $totalNew, $userId);
        $upd->execute();
        if ($upd->affected_rows > 0) {
            $updated++;

              // --- начисляем очки в fantasy_users.point ---
        $upPoints = $db->prepare("
            UPDATE fantasy_users 
               SET point = point + ? 
             WHERE id = ? 
             LIMIT 1
        ");
        if ($upPoints) {
            $upPoints->bind_param('ii', $sumInt, $userId);
            $upPoints->execute();
            $upPoints->close();
            debugLog("User $userId: +$sumInt points added to fantasy_users.point");
        } else {
            debugLog("ERROR: cannot prepare update fantasy_users.point for user $userId: " . $db->error);
        }
        
            $details[] = [
                'user_id' => $userId,
                'last_week_points' => $sumInt,
                'total_points_new' => $totalNew,
                'bench_points' => $benchPts,
                'captain_points' => $capPts,
                'player_points' => $playerPointsDetails
            ];
            debugLog("Updated squad for user $userId: affected_rows=" . $upd->affected_rows);
        } else {
            debugLog("WARNING: No rows affected for user $userId");
        }
        $upd->close();
    } else {
        debugLog("ERROR: Prepare statement failed for user $userId: " . $db->error);
    }
}

// ---------- 5) ответ ----------
echo json_encode([
    'success' => true,
    'updated' => $updated,
    'window' => $window,
    'matches_count' => count($matchIds),
    'note' => 'Составы, созданные в эти выходные или на следующей неделе, пропущены; скамейка учитывается по позиции один раз.',
    'details' => $details
]);
?>