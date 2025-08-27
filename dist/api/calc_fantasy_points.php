<?php
// /api/calc_fantasy_points.php
// PHP 7.1

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php'; // mysqli $db

// ---------- utils ----------
function getLastWeekendWindow(): array {
    $now = new DateTime('now');
    $dow = (int)$now->format('N'); // 1..7 Mon..Sun
    if ($dow >= 6) { // Sat/Sun -> previous weekend
        $sat = new DateTime('last week saturday');
        $sun = new DateTime('last week sunday 23:59:59');
    } else {
        $sat = new DateTime('last saturday');
        $sun = new DateTime('last sunday 23:59:59');
    }
    if ($sat > $sun) { $t=$sat; $sat=$sun; $sun=$t; }
    $nextMonday = new DateTime($sun->format('Y-m-d') . ' +1 day'); // Monday 00:00:00

    return [
        'start' => $sat->format('Y-m-d 00:00:00'),
        'end'   => $sun->format('Y-m-d 23:59:59'),
        'start_date' => $sat->format('Y-m-d'),
        'end_date'   => $sun->format('Y-m-d'),
        'next_monday'=> $nextMonday->format('Y-m-d 00:00:00'),
    ];
}
function normalizePosition($pos) {
    $p = mb_strtolower(trim((string)$pos), 'UTF-8');
    $map = [
        'gk'=>'GK','Вратарь'=>'GK','голкипер'=>'GK',
        'df'=>'DF','Защитник'=>'DF',
        'mf'=>'MF','Полузащитник'=>'MF','хавбек'=>'MF',
        'fw'=>'FW','Нападающий'=>'FW','форвард'=>'FW',
    ];
    return $map[$p] ?? strtoupper($pos);
}
function goalPoints($teamId, $position) {
    $position = normalizePosition($position);
    $is11x11 = ((int)$teamId === 2);
    if ($is11x11) {
        if ($position === 'GK' || $position === 'DF') return 6;
        if ($position === 'MF') return 5;
        return 4; // FW
    } else {
        if ($position === 'GK' || $position === 'DF') return 4;
        if ($position === 'MF') return 3;
        return 2; // FW
    }
}
function i($v){ return (int)$v; }

// ---------- 1) окно прошедших выходных ----------
$window = getLastWeekendWindow();
$start  = $db->real_escape_string($window['start']);
$end    = $db->real_escape_string($window['end']);

// ---------- 2) матчи этих выходных (только наши команды 1,2) ----------
$sqlMatches = "
    SELECT id, teams_id, date
    FROM result
    WHERE teams_id IN (1,2)
      AND date >= '{$start}' AND date <= '{$end}'
";
$matchesRes = $db->query($sqlMatches);
if (!$matchesRes) { echo json_encode(['success'=>false,'message'=>'Ошибка выборки матчей: '.$db->error]); exit; }

$matchIds = []; $matchTeamById = [];
while ($m = $matchesRes->fetch_assoc()) {
    $mid = i($m['id']);
    $matchIds[] = $mid;
    $matchTeamById[$mid] = i($m['teams_id']);
}
if (empty($matchIds)) {
    echo json_encode(['success'=>true,'updated'=>0,'message'=>'Матчей на прошедших выходных нет','window'=>$window]);
    exit;
}
$idList = implode(',', array_map('intval',$matchIds));

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
if (!$mpRes) { echo json_encode(['success'=>false,'message'=>'Ошибка выборки match_players: '.$db->error]); exit; }

$weekPointsByPlayer = [];   // player_id => points for weekend
$playedThisWeekend  = [];   // player_id => bool (играл хотя бы в одном матче уик-энда)
$playerBasePos      = [];   // player_id => 'GK'/'DF'/'MF'/'FW' (соберём тут для тех, кто играл)

while ($r = $mpRes->fetch_assoc()) {
    $playerId = i($r['player_id']);
    $matchId  = i($r['match_id']);

    $played   = i($r['played']) > 0 ? 1 : 0;
    $goals    = i($r['goals']);
    $assists  = i($r['assists']);
    $clean    = i($r['clean_sheet']);
    $gc       = i($r['goals_conceded']);
    $yc       = i($r['yellow_cards']);
    $rc       = i($r['red_cards']);
    $missed   = i($r['missed_penalties']);

    $teamsIdForMatch = isset($matchTeamById[$matchId]) ? $matchTeamById[$matchId] : i($r['player_team_id']);
    $pos = $r['position'];
    $normPos = normalizePosition($pos);

    $playerBasePos[$playerId] = $normPos;

    $points = 0;
    if ($played) {
        $points += 1;
        $playedThisWeekend[$playerId] = true; // отметка: игрок выходил хотя бы раз
    }
    if ($goals > 0) $points += $goals * goalPoints($teamsIdForMatch, $pos);
    if ($assists > 0) $points += $assists * 3;
    if ($clean > 0 && ($normPos==='GK' || $normPos==='DF')) $points += 4;
    if ($yc > 0) $points += -1 * $yc;
    if ($rc > 0) $points += -3 * $rc;
    if ($normPos==='GK' && $gc > 5) $points += -3;
    if ($missed > 0) $points += -2 * $missed;

    if (!isset($weekPointsByPlayer[$playerId])) $weekPointsByPlayer[$playerId] = 0;
    $weekPointsByPlayer[$playerId] += $points;
}

// ---------- 3a) заранее подтянем позиции для всех игроков из составов (включая тех, кто НЕ играл) ----------
$squadsResTmp = $db->query("SELECT gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id FROM fantasy_squads");
$idsToFetch = [];
if ($squadsResTmp) {
    while ($row = $squadsResTmp->fetch_assoc()) {
        foreach (['gk_id','df1_id','df2_id','mf1_id','mf2_id','fw_id','bench_id'] as $f) {
            if (!empty($row[$f])) $idsToFetch[i($row[$f])] = true;
        }
    }
    $squadsResTmp->data_seek(0);
}
if (!empty($idsToFetch)) {
    $idListAll = implode(',', array_keys($idsToFetch));
    $pr = $db->query("SELECT id, position FROM players WHERE id IN ($idListAll)");
    if ($pr) {
        while ($p = $pr->fetch_assoc()) {
            $playerBasePos[i($p['id'])] = normalizePosition($p['position']);
        }
    }
}

// ---------- 4) обновляем fantasy_squads (с учётом скамейки) ----------
$squadsRes = $db->query("SELECT * FROM fantasy_squads");
if (!$squadsRes) { echo json_encode(['success'=>false,'message'=>'Ошибка выборки fantasy_squads: '.$db->error]); exit; }

$updated = 0; $details = [];
$weekStart = new DateTime($window['start']);
$weekEnd   = new DateTime($window['end']);
$nextMonday= new DateTime($window['next_monday']);

while ($sq = $squadsRes->fetch_assoc()) {
    $userId = i($sq['user_id']);
    $createdAt = !empty($sq['created_at']) ? new DateTime($sq['created_at']) : null;

    // 1) создан в эти выходные -> пропуск
    if ($createdAt && $createdAt >= $weekStart && $createdAt <= $weekEnd) {
        $db->query("UPDATE fantasy_squads SET last_week_points = 0, updated_at = NOW() WHERE user_id = {$userId} LIMIT 1");
        $details[] = ['user_id'=>$userId,'skipped'=>'created_on_weekend'];
        continue;
    }
    // 2) создан на следующей неделе -> пропуск
    if ($createdAt && $createdAt >= $nextMonday) {
        $db->query("UPDATE fantasy_squads SET last_week_points = 0, updated_at = NOW() WHERE user_id = {$userId} LIMIT 1");
        $details[] = ['user_id'=>$userId,'skipped'=>'created_next_week'];
        continue;
    }

    // стартовые позиции
    $gk  = i($sq['gk_id']);
    $df1 = i($sq['df1_id']); $df2 = i($sq['df2_id']);
    $mf1 = i($sq['mf1_id']); $mf2 = i($sq['mf2_id']);
    $fw  = i($sq['fw_id']);
    $benchId = !empty($sq['bench_id']) ? i($sq['bench_id']) : 0;

    $playersInSquad = array_values(array_filter([$gk,$df1,$df2,$mf1,$mf2,$fw]));
    $sum = 0;
    foreach ($playersInSquad as $pid) {
        $sum += isset($weekPointsByPlayer[$pid]) ? $weekPointsByPlayer[$pid] : 0;
    }

    // ---------- СКАМЕЙКА ПО ПОЗИЦИИ ----------
    if ($benchId) {
        $benchPos = isset($playerBasePos[$benchId]) ? $playerBasePos[$benchId] : null;

        // сгруппируем стартеров по "ролям" состава (роль известна из слота, не из players)
        $roleMap = [
            'GK' => array_filter([$gk]),
            'DF' => array_filter([$df1,$df2]),
            'MF' => array_filter([$mf1,$mf2]),
            'FW' => array_filter([$fw]),
        ];

        if ($benchPos && isset($roleMap[$benchPos])) {
            // посчитаем, сколько стартеров этой роли НЕ играли ни разу за уик-энд
            $notPlayed = 0;
            foreach ($roleMap[$benchPos] as $starterId) {
                $playedFlag = !empty($playedThisWeekend[$starterId]); // true если сыграл в любом матче уик-энда
                if (!$playedFlag) $notPlayed++;
            }
            // если хотя бы один не играл — добавляем ОДИН раз очки бенча
            if ($notPlayed >= 1) {
                $benchPts = isset($weekPointsByPlayer[$benchId]) ? $weekPointsByPlayer[$benchId] : 0;
                $sum += $benchPts;
            }
        }
    }
    // ---------- конец скамейки ----------

    // капитан — удвоение, даже если минус; если он не играл и/или был заменён — удвоение не переносится само собой
    $captainId = !empty($sq['captain_player_id']) ? i($sq['captain_player_id']) : 0;
    if ($captainId && in_array($captainId, $playersInSquad, true)) {
        $capPts = isset($weekPointsByPlayer[$captainId]) ? $weekPointsByPlayer[$captainId] : 0;
        $sum += $capPts; // прибавляем ещё раз
    }

    $sumInt = (int)$sum;
    $totalPrev = isset($sq['total_points']) ? (float)$sq['total_points'] : 0.0;
    $totalNew  = $totalPrev + $sumInt;

    $upd = $db->prepare("
        UPDATE fantasy_squads
        SET last_week_points = ?, total_points = ?, updated_at = NOW()
        WHERE user_id = ? LIMIT 1
    ");
    if ($upd) {
        $upd->bind_param('dii', $sumInt, $totalNew, $userId);
        $upd->execute();
        $upd->close();
        $updated++;
        $details[] = ['user_id'=>$userId,'last_week_points'=>$sumInt,'total_points_new'=>$totalNew];
    }
}

// ---------- 5) ответ ----------
echo json_encode([
    'success'=>true,
    'updated'=>$updated,
    'window'=>$window,
    'matches_count'=>count($matchIds),
    'note'=>'Составы, созданные в эти выходные или на следующей неделе, пропущены; скамейка учитывается по позиции один раз.',
    'details'=>$details
]);
