<?php
session_start();
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * GET params:
 *  - team_id (int) — обязательно
 *  - month   (int 1..12) — обязательно
 *  - year    (int) — опционально, по умолчанию текущий год
 */

$teamId = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
$month  = isset($_GET['month'])   ? (int)$_GET['month']   : 0;
$year   = isset($_GET['year'])    ? (int)$_GET['year']    : (int)date('Y');

if ($teamId <= 0 || $month <= 0 || $month > 12) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'team_id and month are required']); exit;
}

/* --- СВОДКА ЗА МЕСЯЦ --- */
$sqlSummary = "
  SELECT
    COUNT(DISTINCT tr.training_id) AS trainings_rated,
    COUNT(tr.id)                  AS ratings_count,
    AVG(tr.intensity)             AS avg_intensity,
    AVG(tr.fatigue)               AS avg_fatigue,
    AVG(tr.mood)                  AS avg_mood,
    AVG(tr.enjoyment)             AS avg_enjoyment
  FROM training_sessions ts
  JOIN training_ratings  tr ON tr.training_id = ts.id
  WHERE ts.team_id = ?
    AND MONTH(ts.training_date) = ?
    AND YEAR(ts.training_date)  = ?
";
$stmt = $db->prepare($sqlSummary);
$stmt->bind_param('iii', $teamId, $month, $year);
$stmt->execute();
$sum = $stmt->get_result()->fetch_assoc() ?: [
  'trainings_rated'=>0,'ratings_count'=>0,
  'avg_intensity'=>null,'avg_fatigue'=>null,'avg_mood'=>null,'avg_enjoyment'=>null
];

/* --- СПИСОК ТРЕНИРОВОК С СРЕДНИМИ --- */
$sqlList = "
  SELECT
    ts.id AS training_id,
    ts.training_date,
    COUNT(tr.id)                         AS raters,
    AVG(tr.intensity)                    AS avg_intensity,
    AVG(tr.fatigue)                      AS avg_fatigue,
    AVG(tr.mood)                         AS avg_mood,
    AVG(tr.enjoyment)                    AS avg_enjoyment
  FROM training_sessions ts
  LEFT JOIN training_ratings tr ON tr.training_id = ts.id
  WHERE ts.team_id = ?
    AND MONTH(ts.training_date) = ?
    AND YEAR(ts.training_date)  = ?
  GROUP BY ts.id, ts.training_date
  ORDER BY ts.training_date DESC
";
$stmt = $db->prepare($sqlList);
$stmt->bind_param('iii', $teamId, $month, $year);
$stmt->execute();
$listRes = $stmt->get_result();

$list = [];
while ($r = $listRes->fetch_assoc()) {
  $list[] = [
    'training_id'   => (int)$r['training_id'],
    'training_date' => $r['training_date'],
    'raters'        => (int)$r['raters'],
    'avg_intensity' => $r['avg_intensity'] !== null ? round((float)$r['avg_intensity'],2) : null,
    'avg_fatigue'   => $r['avg_fatigue']   !== null ? round((float)$r['avg_fatigue'],2)   : null,
    'avg_mood'      => $r['avg_mood']      !== null ? round((float)$r['avg_mood'],2)      : null,
    'avg_enjoyment' => $r['avg_enjoyment'] !== null ? round((float)$r['avg_enjoyment'],2) : null,
  ];
}

echo json_encode([
  'success'=>true,
  'summary'=>[
    'trainings_rated' => (int)$sum['trainings_rated'],
    'ratings_count'   => (int)$sum['ratings_count'],
    'avg_intensity'   => $sum['avg_intensity'] !== null ? round((float)$sum['avg_intensity'],2) : null,
    'avg_fatigue'     => $sum['avg_fatigue']   !== null ? round((float)$sum['avg_fatigue'],2)   : null,
    'avg_mood'        => $sum['avg_mood']      !== null ? round((float)$sum['avg_mood'],2)      : null,
    'avg_enjoyment'   => $sum['avg_enjoyment'] !== null ? round((float)$sum['avg_enjoyment'],2) : null,
    'avg_overall'     => ($sum['avg_intensity']!==null)
                          ? round(((float)$sum['avg_intensity'] + (float)$sum['avg_fatigue'] + (float)$sum['avg_mood'] + (float)$sum['avg_enjoyment']) / 4, 2)
                          : null
  ],
  'trainings'=>$list
], JSON_UNESCAPED_UNICODE);