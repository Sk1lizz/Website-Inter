<?php
session_start();
if (empty($_SESSION['fantasy_user_id'])) {
    header('Location: /fantasy_login.php');
    exit;
}
$userId   = (int)$_SESSION['fantasy_user_id'];
$teamName = isset($_SESSION['fantasy_team']) ? $_SESSION['fantasy_team'] : 'Моя команда';

require_once dirname(__FILE__) . '/db.php';

$BUDGET = 50.0;
$SEASON = 2025;

// Проверка возможности редактирования
date_default_timezone_set('Europe/Moscow');
$currentDateTime = new DateTime();
$currentDay = $currentDateTime->format('N'); // 1 (понедельник) - 7 (воскресенье)
$currentHour = $currentDateTime->format('H');
$currentMinute = $currentDateTime->format('i');
$isEditingAllowed = true;
$editingMessage = '';

// Проверяем, существует ли состав
$squadExists = false;
if ($stmt = $db->prepare("SELECT 1 FROM fantasy_squads WHERE user_id=? LIMIT 1")) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $squadExists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Если состав существует, проверяем день и время
if ($squadExists) {
    // Разрешено редактирование со вторника (2) по пятницу (5)
    // Запрещено с субботы 00:01
    if ($currentDay >= 6 || ($currentDay == 6 && $currentHour >= 0 && $currentMinute >= 1)) {
        $isEditingAllowed = false;
        $editingMessage = 'Редактирование состава возможно только со вторника по пятницу.';
    }
}

/*
CREATE TABLE IF NOT EXISTS fantasy_squads (
  user_id INT NOT NULL PRIMARY KEY,
  season SMALLINT NOT NULL DEFAULT 2025,
  gk_id INT DEFAULT NULL,
  df1_id INT DEFAULT NULL,
  df2_id INT DEFAULT NULL,
  mf1_id INT DEFAULT NULL,
  mf2_id INT DEFAULT NULL,
  fw_id  INT DEFAULT NULL,
  bench_id INT DEFAULT NULL,
  captain_player_id INT DEFAULT NULL,
  budget_left DECIMAL(10,2) NOT NULL DEFAULT 50.00,
  total_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  last_week_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/

$squad = array(
    'gk_id' => null,
    'df1_id' => null,
    'df2_id' => null,
    'mf1_id' => null,
    'mf2_id' => null,
    'fw_id' => null,
    'bench_id' => null,
    'captain_player_id' => null,
    'budget_left' => $BUDGET
);


if ($stmt = $db->prepare("SELECT gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id, captain_player_id, budget_left, total_points, last_week_points FROM fantasy_squads WHERE user_id=? LIMIT 1")) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $filtered_row = array();
        foreach ($row as $key => $value) {
            if ($value !== null && $value !== 0) {
                $filtered_row[$key] = $value;
            }
        }
        $squad = array_merge($squad, $filtered_row);
        error_log("Squad data for user $userId: " . print_r($squad, true));
    } else {
        error_log("No squad found for user $userId");
    }
    $stmt->close();
}

$saveSuccess = false;
$saveError   = '';

if (isset($_POST['save_squad'])) {
    $gk_id    = isset($_POST['gk_id']) ? (int)$_POST['gk_id'] : 0;
    $df1_id   = isset($_POST['df1_id']) ? (int)$_POST['df1_id'] : 0;
    $df2_id   = isset($_POST['df2_id']) ? (int)$_POST['df2_id'] : 0;
    $mf1_id   = isset($_POST['mf1_id']) ? (int)$_POST['mf1_id'] : 0;
    $mf2_id   = isset($_POST['mf2_id']) ? (int)$_POST['mf2_id'] : 0;
    $fw_id    = isset($_POST['fw_id']) ? (int)$_POST['fw_id'] : 0;
    $bench_id = isset($_POST['bench_id']) ? (int)$_POST['bench_id'] : 0;
    $captain  = isset($_POST['captain_player_id']) ? (int)$_POST['captain_player_id'] : 0;

    $ids = array_filter([$gk_id, $df1_id, $df2_id, $mf1_id, $mf2_id, $fw_id, $bench_id]);
    if (count($ids) !== 7 || count(array_unique($ids)) !== 7) {
        $saveError = 'Все слоты должны быть заполнены уникальными игроками.';
    } else {
        // Запасной не может быть нападающим
        $benchPos = '';
        if ($st = $db->prepare("SELECT position FROM players WHERE id=?")) {
            $st->bind_param('i', $bench_id);
            $st->execute();
            $res = $st->get_result();
            $benchPos = $res->num_rows ? $res->fetch_assoc()['position'] : '';
            $st->close();
        }
        $p = mb_strtolower($benchPos, 'UTF-8');
        if (strpos($p, 'напад') !== false) {
            $saveError = 'Запасной не может быть нападающим.';
        }

        // Не более 4 игроков из одной команды
        if (!$saveError) {
            $in = implode(',', $ids);
            $teamCounts = [];
            $q = $db->query("SELECT team_id FROM players WHERE id IN ($in)");
            while ($r = $q->fetch_assoc()) {
                $teamId = (int)$r['team_id'];
                $teamCounts[$teamId] = ($teamCounts[$teamId] ?? 0) + 1;
            }
            foreach ($teamCounts as $count) {
                if ($count > 4) {
                    $saveError = 'Нельзя выбрать более 4 игроков из одной команды.';
                    break;
                }
            }
        }

        // --- Лимит трансферов: максимум 2/неделю (исключая замены ушедших игроков) ---
        $oldRow = null;
        if (!$saveError && ($stmt = $db->prepare("SELECT gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id, transfers_made_week, transfers_week_start FROM fantasy_squads WHERE user_id=? LIMIT 1"))) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $oldRow = $res->fetch_assoc();
            $stmt->close();
        }

        $deltaTransfers = 0;
        $alreadyUsed = 0;
        $currentWeekStartTs  = strtotime('monday this week');
        $currentWeekStartStr = date('Y-m-d', $currentWeekStartTs);

        if (!$saveError && $oldRow) {
            $storedStart = !empty($oldRow['transfers_week_start']) ? date('Y-m-d', strtotime($oldRow['transfers_week_start'])) : null;
            $alreadyUsed = ($storedStart === $currentWeekStartStr) ? (int)$oldRow['transfers_made_week'] : 0;

            $oldIds = [
                'gk_id'    => (int)$oldRow['gk_id'],
                'df1_id'   => (int)$oldRow['df1_id'],
                'df2_id'   => (int)$oldRow['df2_id'],
                'mf1_id'   => (int)$oldRow['mf1_id'],
                'mf2_id'   => (int)$oldRow['mf2_id'],
                'fw_id'    => (int)$oldRow['fw_id'],
                'bench_id' => (int)$oldRow['bench_id'],
            ];
            $newIds = [
                'gk_id'    => $gk_id,
                'df1_id'   => $df1_id,
                'df2_id'   => $df2_id,
                'mf1_id'   => $mf1_id,
                'mf2_id'   => $mf2_id,
                'fw_id'    => $fw_id,
                'bench_id' => $bench_id,
            ];

            foreach ($oldIds as $slot => $oldId) {
                $newId = $newIds[$slot];
                if ($oldId && $newId && $oldId !== $newId) {
                    // считаем как трансфер только если старый игрок ещё в одной из наших команд (1,2)
                    $teamId = 0;
                    $qr = $db->query("SELECT team_id FROM players WHERE id=".(int)$oldId." LIMIT 1");
                    if ($qr && $rr = $qr->fetch_assoc()) $teamId = (int)$rr['team_id'];
                    if (in_array($teamId, [1, 2], true)) {
                        $deltaTransfers++;
                    }
                }
            }

            if ($alreadyUsed + $deltaTransfers > 2) {
                $saveError = 'Лимит 2 трансферов в неделю исчерпан. Попробуйте на следующей неделе.';
            }
        }

        // Бюджет
        if (!$saveError) {
            $in = implode(',', $ids);
            $sum = 0.0;
            $q = $db->query("SELECT COALESCE(fp.cost,0) price FROM fantasy_players fp WHERE fp.player_id IN ($in)");
            while ($r = $q->fetch_assoc()) {
                $sum += (float)$r['price'];
            }
            if ($sum - $BUDGET > 0.000001) {
                $saveError = 'Превышен бюджет.';
            } else {
                $left = max(0, $BUDGET - $sum);

               if ($st = $db->prepare("
    INSERT INTO fantasy_squads
    (user_id, season, gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id,
     captain_player_id, budget_left, total_points, last_week_points,
     transfers_made_week, transfers_week_start)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
      season               = VALUES(season),
      gk_id                = VALUES(gk_id),
      df1_id               = VALUES(df1_id),
      df2_id               = VALUES(df2_id),
      mf1_id               = VALUES(mf1_id),
      mf2_id               = VALUES(mf2_id),
      fw_id                = VALUES(fw_id),
      bench_id             = VALUES(bench_id),
      captain_player_id    = VALUES(captain_player_id),
      budget_left          = VALUES(budget_left),
      -- ВАЖНО: НЕ трогаем total_points и last_week_points при апдейте
      transfers_made_week  = VALUES(transfers_made_week),
      transfers_week_start = VALUES(transfers_week_start)
")) {
    $totalPoints = 0.0;      // новому пользователю выставятся 0
    $lastWeekPoints = 0.0;   // новому пользователю выставятся 0
    $newTransfersUsed = $oldRow ? $alreadyUsed + $deltaTransfers : 0;

    $st->bind_param(
        'iiiiiiiiiidddis',
        $userId, $SEASON, $gk_id, $df1_id, $df2_id, $mf1_id, $mf2_id, $fw_id, $bench_id, $captain,
        $left, $totalPoints, $lastWeekPoints,
        $newTransfersUsed, $currentWeekStartStr
    );

                    $saveSuccess = $st->execute();
                    if (!$saveSuccess) {
                        $saveError = 'Ошибка сохранения: ' . $st->error;
                        error_log('Fantasy save error: ' . $st->error);
                    }
                    $st->close();

                    if ($saveSuccess) {
                        $squad = [
                            'gk_id' => $gk_id, 'df1_id' => $df1_id, 'df2_id' => $df2_id,
                            'mf1_id' => $mf1_id, 'mf2_id' => $mf2_id, 'fw_id' => $fw_id,
                            'bench_id' => $bench_id, 'captain_player_id' => $captain,
                            'budget_left' => $left
                        ];
                        $url = strtok($_SERVER['REQUEST_URI'], '?');
                        if (headers_sent($f, $l)) {
                            error_log("Headers already sent at $f:$l");
                        } else {
                            header('Location: ' . $url . '?saved=1', true, 303);
                        }
                        exit;
                    }
                } else {
                    $saveError = 'Ошибка подготовки запроса: ' . $db->error;
                    error_log('Fantasy prepare error: ' . $db->error);
                }
            }
        }
    }
}

// Списки игроков
$playersByPos = array('GK' => array(), 'DF' => array(), 'MF' => array(), 'FW' => array());
$q = $db->query("
    SELECT
        p.id, p.name, p.position, p.team_id,
        CASE
            WHEN TRIM(p.photo) = '' OR TRIM(p.photo) = '/' OR TRIM(p.photo) = '0' OR UPPER(TRIM(p.photo)) = 'NULL'
                THEN '/img/player/player_0.png'
            ELSE p.photo
        END AS photo,
        COALESCE(fp.cost, 0) AS price
    FROM players p
    LEFT JOIN fantasy_players fp ON fp.player_id = p.id
    WHERE p.team_id IN (1, 2) AND COALESCE(fp.cost, 0) > 0
    ORDER BY 
        CASE 
            WHEN p.position REGEXP 'Вратар'    THEN 1
            WHEN p.position REGEXP 'Полузащит' THEN 2
            WHEN p.position REGEXP 'Защит'     THEN 3
            WHEN p.position REGEXP 'Напад'     THEN 4
            ELSE 5
        END,
        fp.cost DESC, p.name ASC
");
while ($r = $q->fetch_assoc()) {
    $pos = mb_strtolower($r['position'], 'UTF-8');
    if (preg_match('/вратар/u', $pos)) {
        $key = 'GK';
    } elseif (preg_match('/полузащит/u', $pos)) {
        $key = 'MF';
    } elseif (preg_match('/защит/u', $pos)) {
        $key = 'DF';
    } elseif (preg_match('/напад/u', $pos)) {
        $key = 'FW';
    } else {
        continue;
    }
    if ($r['photo'] === null) $r['photo'] = '';
    $r['photo'] = trim((string)$r['photo']);
    if ($r['photo'] === '' || $r['photo'] === '/' || $r['photo'] === '0' || strcasecmp($r['photo'], 'NULL') === 0) {
        $r['photo'] = '/img/player/player_0.png';
    } elseif ($r['photo'][0] !== '/') {
        $r['photo'] = '/' . $r['photo'];
    }
    $r['team_name'] = $r['team_id'] == 1 ? 'FC Inter Moscow 8x8' : 'FC Inter Moscow 11x11';
    $playersByPos[$key][] = $r;
}
?>

<!doctype html>
<html lang="ru">
<head>
     <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/img/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/img/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="apple-touch-icon" href="/img/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" sizes="192x192" href="/img/android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="/img/android-chrome-512x512.png">
    <meta charset="utf-8">
    <title>Fantasy — кабинет</title>
    <link rel="stylesheet" href="/css/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
<style>
  .Fantasy {
    padding-top: 20svh;
    display: flex;
    justify-content: center;
    background: #fff;
    font-family: PLAY-REGULAR, Arial;
    position: relative;
    z-index: 0;
    min-height: 100vh; /* Убедимся, что контейнер занимает всю высоту */
    overflow-x: hidden; /* Предотвращаем горизонтальную прокрутку */
}
.fantasy-card {
    width: 100%;
    max-width: 1100px;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 5px rgba(0,0,0,.08);
    position: relative;
    z-index: 1;
    overflow-y: auto; /* Разрешаем только вертикальную прокрутку, если нужно */
    max-height: 90vh; /* Ограничиваем высоту */
}

  h1 { font-family: PLAY-BOLD, Arial; color: #00296B; font-size: 28px; margin-bottom: 8px; }
  .muted { color: #666; padding-bottom: 10px; }
  .btn { background: #00296B; color: #FDC500; border: 2px solid #FDC500; border-radius: 10px; padding: 10px 16px; font-size: 16px; cursor: pointer; }
  .btn:hover { background: #000; color: #fff; border-color: #fff; }

  /* Сетка */
  .grid { display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start; }
  .field, .picker { position: relative; z-index: 1; min-width: 0; }

  /* Поле: пропорциональное + координаты в процентах */
  .field {
    position: relative;
    width: 100%;
    max-width: 420px;
    aspect-ratio: 420 / 650;
    height: auto;
    background: url('/img/field.jpg') center/cover;
    border-radius: 12px;
    /* overflow: hidden;  <-- Временно закомментируйте или удалите */
    margin: 0;
    z-index: 1; /* Убедимся, что поле не перекрывает подсказку */
}

  @supports not (aspect-ratio: 1) {
    .field { height: 0; padding-bottom: 154.762%; }
  }

  /* ширина слота: 90px от 420px -> 21.4286% */
  .slot { position: absolute; width: 21.4286%; text-align: center; }
  /* bench был 110px -> 26.190% */
  .s-bench { width: 26.190%; }

  .slot .avatar { width: clamp(54px, 17vw, 74px); height: clamp(54px, 17vw, 74px); border-radius: 50%; margin: 0 auto 6px; border: 3px solid #fff; background: #e9eef5; box-shadow: 0 2px 4px rgba(0,0,0,.2); }
  .slot img { width: 100%; height: 100%; object-fit: cover; }
  .slot .name, .slot .pos { color: #fff; text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000; }
  .slot .name { font-size: clamp(10px, 2.8vw, 12px); font-weight: 700; }
  .slot .pos  { font-size: clamp(10px, 2.6vw, 12px); }
  .slot .pos.black-text { color: #000; text-shadow: none; }
  .slot .capt { display: none !important; font-size: clamp(10px, 2.6vw, 11px); color: #FDC500; text-shadow: none; }
  .slot.captain .avatar { border-color: #FDC500; box-shadow: 0 0 0 3px rgba(253,197,0,0.55), 0 0 12px rgba(253,197,0,0.75); }
  .slot.captain .capt { display: block !important; }

  /* Координаты слотов: px -> % (top: px/650*100, left: px/420*100) */
  .s-fw   { top: 12.3077%; left: 39.2857%; }
  .s-mf1  { top: 36.9231%; left: 14.2857%; }
  .s-mf2  { top: 36.9231%; left: 64.2857%; }
  .s-df1  { top: 62.3077%; left: 14.2857%; }
  .s-df2  { top: 62.3077%; left: 64.2857%; }
  .s-bench{ top: 81.5385%; left: 2.3810%; }
  .s-gk   { top: 81.5385%; left: 39.2857%; }

  /* Подбор */
  .picker { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; }
  .budget { margin-bottom: 10px; }
  .budget-instruction { font-size: 14px; color: #666; margin-top: 5px; }

  /* Табы позиций */
  .tabs { display: flex; gap: 8px; margin-bottom: 10px; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
  .tab { padding: 8px 12px; border: 1px solid #ddd; border-radius: 10px; background: #fff; cursor: pointer; color: #000; white-space: nowrap; }
  .tab.active { background: #00296B; color: #FDC500; border-color: #00296B; }
  .tab.disabled { opacity: 0.45; pointer-events: none; cursor: not-allowed; }

  /* Списки игроков */
  .list { display: none; max-height: 470px; overflow: auto; border: 1px solid #eee; border-radius: 10px; }
  .list.active { display: block; }

  /* Карточка игрока — GRID: [аватар][текст][цена][кнопка] */
  .card {
    display: grid;
    grid-template-columns: 46px 1fr auto auto;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
  }
  .card:last-child { border-bottom: none; }
  .card .ph { grid-column: 1; width: 46px; height: 46px; border-radius: 50%; background: #eef2ff; border: 2px solid #fff; box-shadow: 0 1px 2px rgba(0,0,0,.12); }
  .card .ph img { width: 100%; height: 100%; object-fit: cover; }
  .card .nm, .card .meta { grid-column: 2; }
  .card .nm { font-weight: 600; line-height: 1.15; }
  .card .meta { font-size: 12px; color: #64748b; }
  .card .price { grid-column: 3; margin: 0; white-space: nowrap; font-weight: 700; }
  .card .choose, .card .remove, .card .btn {
    grid-column: 4;
    justify-self: end;
    align-self: center;
    width: auto !important;
    max-width: none !important;
    white-space: nowrap;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 8px;
  }
  .disabled { opacity: .45; pointer-events: none; }

  .row { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
  select { padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }

  .warn { background: #fdeceb; color: #7d1c1a; border: 1px solid #f5c6cb; padding: 10px; border-radius: 8px; margin: 10px 0; }
  .ok   { background: #e7f6e7; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 8px; margin: 10px 0; }

  .team-counts { margin-top: 10px; display: flex; gap: 12px; margin-bottom: 10px; flex-wrap: wrap; }
  .team-count  { padding: 6px 12px; border-radius: 8px; color: #fff; font-size: 14px; }
  .team-count-1 { background: #000; }
  .team-count-2 { background: #00509D; }
  .team-label { padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 12px; margin-left: 4px; display: inline-block; }
  .team-label-1 { background: #000; }
  .team-label-2 { background: #00509D; }

  .muted strong { color: #00296B; font-weight: 700; padding-bottom: 10px; }

  .rules-section { margin-top: 20px; }
  .rules-section h3 { font-size: 18px; color: #00296B; margin-bottom: 10px; }
  .rules-section ul { list-style-type: disc; padding-left: 20px; margin-bottom: 15px; }
  .rules-section li { margin-bottom: 5px; }

  .ranking-section { margin-top: 20px; }
  .ranking-section h3 { font-size: 18px; color: #00296B; margin-bottom: 10px; }

  .ranking-table-container { padding: 16px; overflow-x: auto; }
  .ranking-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  .ranking-table th, .ranking-table td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
  .ranking-table th { background-color: #f1f5f9; font-weight: 600; }
  .ranking-table td { color: #64748b; }
  .ranking-table tr:last-child td { border-bottom: none; }

  /* Модалка */
  .modal {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.5); display: flex; justify-content: center !important; align-items: center !important; z-index: 1000;
  }
  .modal-content { background: #fff; padding: 20px; border-radius: 12px; text-align: center; width: 90%; max-width: 400px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
  .modal-content h3 { color: #00296B; margin-bottom: 10px; }
  .modal-buttons { margin-top: 15px; }
  .modal-buttons .btn { margin: 0 5px; }

  /* ===== Адаптивные медиазапросы ===== */

  /* Планшеты и узкие ноуты */
  @media (max-width: 1024px) and (min-width: 769px) {
    .Fantasy { padding-top: 26svh; }
    .grid { grid-template-columns: 1fr; gap: 18px; }
    .field { margin: 0 auto; }
    .list { max-height: min(58vh, 520px); }
    .picker { padding: 14px; }
  }

  /* Смартфоны */
  @media (max-width: 768px) {
    .Fantasy {
    padding-top: 10svh;
    display: flex;
    justify-content: center;
    background: #fff;
    font-family: PLAY-REGULAR, Arial;
    position: relative; /* Убедимся, что это контекст для z-index */
    z-index: 0; /* Базовый уровень */
}
    .grid { grid-template-columns: 1fr; gap: 14px; }
    .field { margin: 0 auto; }
    .btn { width: 100%; text-align: center; font-size: 15px; padding: 10px 14px; }

    .list { max-height: min(54vh, 480px); }
    .slot .avatar { width: 60px; height: 60px; }

    .card { grid-template-columns: 40px 1fr auto auto; gap: 8px; padding: 8px; }
    .card .ph { width: 40px; height: 40px; }
    .card .nm { font-size: 14px; }
    .card .meta { font-size: 11px; }
    .card .price { font-size: 13px; }
    .card { grid-auto-rows: minmax(0, auto); }
    .card .choose, .card .remove { font-size: 13px; padding: 6px 10px; border-radius: 6px; justify-self: end; }
  }

  /* Таблица/модалка на очень узких */
  @media (max-width: 480px) {
    .ranking-table th, .ranking-table td { padding: 6px 8px; font-size: 13px; }
    .modal-content { width: 92%; padding: 16px; }
  }

  /* Адаптив табов позиций: перенос */
  @media (max-width: 618px) {
    .tabs { overflow: visible; flex-wrap: wrap; gap: 8px; }
    .tab { flex: 1 1 calc(50% - 8px); text-align: center; padding: 8px 10px; font-size: 14px; }
  }
  @media (max-width: 340px) {
    .tab { flex: 1 1 100%; font-size: 13px; padding: 7px 10px; }
  }

/* аватарки становятся позиционированными контейнерами */
.card .ph,
.slot .avatar { position: relative; }

/* сам бейдж; иконка из корня /img/holiday.svg */
.holiday-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 22px;
    height: 22px;
    background: url('/img/icon/holiday.svg') center/contain no-repeat;
    border: none;
    z-index: 3;
    cursor: pointer;
    outline: none;
}

.holiday-badge .tip {
    display: none;
    white-space: nowrap;
     background: #111 !important;
    color: #fff !important;
    font-size: 12px;
    line-height: 1.2;
    padding: 6px 8px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
    position: absolute;
    z-index: 1002;
     pointer-events: none; /* важно: чтобы подсказка не ловила курсор */
    transition: opacity 0.2s ease;
    opacity: 0;
}

.holiday-badge.active .tip,
.holiday-badge:focus .tip,
.holiday-badge:hover .tip {
    display: block;
    opacity: 1;
}

.holiday-wrap {
    position: relative; /* Убедимся, что это контейнер для абсолютного позиционирования */
    z-index: 1; /* Базовый z-index для аватарок */
}

.img-wrap {
    width: 100%;
    height: 100%;
    overflow: hidden;  /* Обрезаем фото */
    border-radius: 50%;  /* Круглая форма */
}

/* мягкая подсветка карточки после перехода */
.card._jump-highlight {
  outline: 2px solid #FDC500;
  background: #fffbe6;
  transition: outline .2s, background .2s;
}
</style>



    <script>function imgFallback(i){i.onerror=null;i.src='/img/player/player_0.png';}</script>
</head>
<body>
<?php
    $header_files = array(dirname(__FILE__) . '/blocks/header.php', dirname(__FILE__) . '/blocks/header.html');
    foreach ($header_files as $file) {
        if (file_exists($file)) {
            include $file;
            break;
        }
    }
?>

<div class="Fantasy">
    <div class="fantasy-card">
        <h1>Fantasy team (beta)</h1>
       <p class="muted">Команда: <strong><?php echo htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p class="muted">Очки команды: <strong><?php echo (int)round((float)($squad['total_points'] ?? 0)); ?></strong></p>
<p class="muted">Очки на прошлой неделе: <strong><?php echo (int)round((float)($squad['last_week_points'] ?? 0)); ?></strong></p>
  <p class="muted">Редактирование состава возможно со вторника по пятницу.<strong></strong></p>

        <?php if (isset($_GET['saved'])): ?>
            <div class="ok">Состав сохранён!</div>
        <?php endif; ?>

        <?php if ($saveError): ?>
            <div class="warn"><?php echo htmlspecialchars($saveError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="grid">
            <!-- Поле -->
            <div class="field" id="field">
                <div class="slot s-bench" data-slot="bench_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Запасной</div><div class="pos"></div><div class="capt">Капитан</div>
                </div>
                <div class="slot s-fw" data-slot="fw_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Нападающий</div><div class="pos">FW</div><div class="capt">Капитан</div>
                </div>
                <div class="slot s-mf1" data-slot="mf1_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Полузащитник</div><div class="pos">MF</div><div class="capt">Капитан</div>
                </div>
                <div class="slot s-mf2" data-slot="mf2_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Полузащитник</div><div class="pos">MF</div><div class="capt">Капитан</div>
                </div>
                <div class="slot s-df1" data-slot="df1_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Защитник</div><div class="pos">DF</div><div class="capt">Капитан</div>
                </div>
                <div class="slot s-df2" data-slot="df2_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Защитник</div><div class="pos">DF</div><div class="capt">Капитан</div>
                </div>
                <div class="slot s-gk" data-slot="gk_id">
                    <div class="avatar"><img src="/img/player/player_0.png" onerror="imgFallback(this)" alt=""></div>
                    <div class="name">Вратарь</div><div class="pos">GK</div><div class="capt">Капитан</div>
                </div>
            </div>

         <!-- Подбор -->
<div class="picker" id="picker">
    <?php if (!$isEditingAllowed): ?>
        <div class="warn"><?php echo htmlspecialchars($editingMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="budget">
        Бюджет: <strong><span id="budgetLeft"><?php echo number_format((float)$squad['budget_left'], 2, '.', ''); ?></span></strong> из <?php echo number_format($BUDGET, 2, '.', ''); ?>
    </div>
    <div class="budget-instruction">
        Собери команду на предоставленный бюджет. Вы можете взять не более четырёх игроков из одной команды.
    </div>
    <div class="team-counts">
        <div class="team-count team-count-1" id="teamCount1">FC Inter Moscow 8x8: <span>0</span></div>
        <div class="team-count team-count-2" id="teamCount2">FC Inter Moscow 11x11: <span>0</span></div>
    </div>
    <div class="tabs" id="tabs">
        <button class="tab active" data-tab="FW">Нападающие</button>
        <button class="tab" data-tab="MF">Полузащитники</button>
        <button class="tab" data-tab="DF">Защитники</button>
        <button class="tab" data-tab="GK">Вратари</button>
        <button class="tab" data-tab="BENCH">Запасной</button>
    </div>

    <?php
    function fmt_price_visible($raw) {
        $f = (float) str_replace(',', '.', (string)$raw);
        return rtrim(rtrim(number_format($f, 2, '.', ''), '0'), '.');
    }
    function fmt_price_attr($raw) {
        $f = (float) str_replace(',', '.', (string)$raw);
        return number_format($f, 2, '.', '');
    }
    if (!function_exists('price_text_1')) {
        function price_text_1($raw) {
            $f = (float)str_replace(',', '.', (string)$raw);
            return number_format($f, 1, '.', '');
        }
    }
    if (!function_exists('price_attr_1')) {
        function price_attr_1($raw) {
            $f = (float)str_replace(',', '.', (string)$raw);
            return number_format($f, 1, '.', '');
        }
    }

    function printList($key, $players, $label) {
        $active = $key === 'FW' ? 'active' : '';
        echo '<div class="list ' . $active . '" id="list_' . $key . '">';
        foreach ($players as $pl) {
            $name = htmlspecialchars($pl['name'], ENT_QUOTES, 'UTF-8');
            $photo = htmlspecialchars($pl['photo'], ENT_QUOTES, 'UTF-8');
            $priceText = price_text_1($pl['price']);
            $priceAttr = price_attr_1($pl['price']);
            $teamName = htmlspecialchars($pl['team_name'], ENT_QUOTES, 'UTF-8');
            echo '<div class="card" data-id="' . $pl['id'] . '" data-pos="' . $key . '" data-price="' . $priceAttr . '" data-team-id="' . $pl['team_id'] . '">
                    <div class="ph"><img src="' . $photo . '" onerror="imgFallback(this)" alt=""></div>
                    <div>
                        <div class="nm">' . $name . '</div>
                        <div class="meta">' . $key . ', <span class="team-label team-label-' . $pl['team_id'] . '">' . $teamName . '</span></div>
                    </div>
                    <div class="price">' . $priceText . '</div>
                    <button class="choose btn" type="button">Выбрать</button>
                  </div>';
        }
        echo '</div>';
    }
    printList('FW', $playersByPos['FW'], 'FW');
    printList('MF', $playersByPos['MF'], 'MF');
    printList('DF', $playersByPos['DF'], 'DF');
    printList('GK', $playersByPos['GK'], 'GK');

    $bench = array_merge($playersByPos['MF'], $playersByPos['DF'], $playersByPos['GK']);
    usort($bench, function($a, $b) {
        if ($a['price'] == $b['price']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['price'] > $a['price'] ? 1 : -1;
    });
    printList('BENCH', $bench, 'Запасной');
    ?>

    <form method="post" id="saveForm" class="row">
        <input type="hidden" name="gk_id"   id="inp_gk_id"   value="<?php echo (int)$squad['gk_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <input type="hidden" name="df1_id"  id="inp_df1_id"  value="<?php echo (int)$squad['df1_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <input type="hidden" name="df2_id"  id="inp_df2_id"  value="<?php echo (int)$squad['df2_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <input type="hidden" name="mf1_id"  id="inp_mf1_id"  value="<?php echo (int)$squad['mf1_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <input type="hidden" name="mf2_id"  id="inp_mf2_id"  value="<?php echo (int)$squad['mf2_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <input type="hidden" name="fw_id"   id="inp_fw_id"   value="<?php echo (int)$squad['fw_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <input type="hidden" name="bench_id"id="inp_bench_id" value="<?php echo (int)$squad['bench_id']; ?>" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>
        <div class="row" id="captainRow" style="display:<?php echo $isEditingAllowed && $squad['gk_id'] ? '' : 'none'; ?>;">
            <label for="captain_player_id">Капитан:</label>
            <select name="captain_player_id" id="captainSelect" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>></select>
        </div>
        <button class="btn" type="submit" name="save_squad" id="saveBtn" <?php echo $isEditingAllowed ? '' : 'disabled'; ?>>Сохранить состав</button>
        <span class="muted" id="hint"></span>
    </form>
</div>
            
    </div> <!-- Закрытие .grid -->
        
        <div class="rules-section">
            <h3 class="muted">Правила игры</h3>
            <p class="muted">Фэнтези-футбол — игра, в которой участники формируют виртуальную команду футболистов, чьи прототипы принимают участие в реальных соревнованиях и, в зависимости от актуальной статистики своих выступлений, набирают зачетные баллы. Сформируйте свою команду и набирайте очки. Суммарная стоимость всех игроков не должна превышать 50 баллов. После формирования команды вы сможете делать не более 2-х трансферов в неделю. Так что выбирайте игроков с умом! Вы можете взять не более четырёх игроков из одной команды. Запасной игрок учитывается, только если кто-то из вашего состава не сыграл в матче и его позиция совпадает с этим игроком. Мы будем учитывать в статистике только матчи, которые прошли на выходных, включая товарищеские матчи. Сезон длится весь календарный год. Очки можно получить следующим образом:</p>
            <ul class="muted">
                <li>Игрок сыграл в матче - 1 очко;</li>
                <li>Гол, забитый вратарем или защитником команды 11х11 = 6 очков;</li>
                <li>Гол, забитый вратарем или защитником команды 8х8 = 4 очка;</li>
                <li>Гол, забитый полузащитником команды 11х11 = 5 очков;</li>
                <li>Гол, забитый полузащитником команды 8х8 = 3 очка;</li>
                <li>Гол, забитый нападающим команды 11х11 = 4 очка;</li>
                <li>Гол, забитый нападающим команды 8х8 = 2 очка;</li>
                <li>Голевая передача = 3 очка;</li>
                <li>Сухой матч (для вратаря или защитника) = 4 очка;</li>
                <li>Желтая карточка = -1 очко;</li>
                <li>Красная карточка = -3 очка;</li>
                <li>Нереализованный пенальти = -2 очка;</li>
                <li>Вратарь пропустил более 5 мячей в матче = -3 очка;</li>
                <li>У игрока, выбранного капитаном, очки удваиваются.</li>
            </ul>
        </div>

        <?php
// ===== Расчёт очков выбранных игроков за прошедшие выходные для текущей команды =====

// Получим текущий состав пользователя
$my = [
  'gk' => (int)($squad['gk_id'] ?? 0),
  'df1'=> (int)($squad['df1_id'] ?? 0),
  'df2'=> (int)($squad['df2_id'] ?? 0),
  'mf1'=> (int)($squad['mf1_id'] ?? 0),
  'mf2'=> (int)($squad['mf2_id'] ?? 0),
  'fw' => (int)($squad['fw_id'] ?? 0),
  'bench' => (int)($squad['bench_id'] ?? 0),
  'captain' => (int)($squad['captain_player_id'] ?? 0),
];

// если состав не полный, таблицу не показываем
$hasFullSquad = $my['gk'] && $my['df1'] && $my['df2'] && $my['mf1'] && $my['mf2'] && $my['fw'] && $my['bench'];

$weekRows = [];       // строки: ['player_id','name','pos','points']
$weekTeamTotal = 0;   // сумма для отображения
if ($hasFullSquad) {

    // --- функции такие же, как в API ---
    function _normPos($pos) {
        $p = mb_strtolower(trim((string)$pos),'UTF-8');
        if (preg_match('/вратар/u',$p) || $p==='gk') return 'GK';
        if (preg_match('/защит/u',$p) || $p==='df') return 'DF';
        if (preg_match('/полузащит/u',$p) || $p==='mf') return 'MF';
        if (preg_match('/напад/u',$p) || $p==='fw') return 'FW';
        return strtoupper($pos);
    }
    function _goalPts($teamId,$pos) {
        $pos = _normPos($pos);
        $is11 = ((int)$teamId===2);
        if ($is11) {
            if ($pos==='GK'||$pos==='DF') return 6;
            if ($pos==='MF') return 5;
            return 4;
        } else {
            if ($pos==='GK'||$pos==='DF') return 4;
            if ($pos==='MF') return 3;
            return 2;
        }
    }
    // --- окно прошедших выходных + граница понедельника (нужно, чтобы не считать если состав создан позже) ---
    $now = new DateTime('now', new DateTimeZone('Europe/Moscow'));
    $dow = (int)$now->format('N');
    if ($dow >= 6) {
        $sat = new DateTime('last week saturday');
        $sun = new DateTime('last week sunday 23:59:59');
    } else {
        $sat = new DateTime('last saturday');
        $sun = new DateTime('last sunday 23:59:59');
    }
    if ($sat > $sun) { $t=$sat; $sat=$sun; $sun=$t; }
    $startStr = $sat->format('Y-m-d 00:00:00');
    $endStr   = $sun->format('Y-m-d 23:59:59');
    $nextMonday = new DateTime($sun->format('Y-m-d').' +1 day');

    // если состав создан в эти выходные или уже на следующей неделе — не показываем таблицу
    if (!empty($squad['created_at'])) {
        $createdAt = new DateTime($squad['created_at']);
        if (($createdAt >= $sat && $createdAt <= $sun) || $createdAt >= $nextMonday) {
            $hasFullSquad = false; // таблица не нужна
        }
    }

    if ($hasFullSquad) {
        // матчи только этих выходных для наших команд
        $rs = $db->query("
            SELECT id, teams_id FROM result
            WHERE teams_id IN (1,2) AND date >= '{$db->real_escape_string($startStr)}' AND date <= '{$db->real_escape_string($endStr)}'
        ");
        $matchIds = []; $teamByMatch = [];
        while ($r = $rs->fetch_assoc()) { $matchIds[]=(int)$r['id']; $teamByMatch[(int)$r['id']] = (int)$r['teams_id']; }

        if ($matchIds) {
            $idsList = implode(',', $matchIds);

            // подтянем имена/позиции всех задействованных игроков
            $allIds = array_values(array_unique([$my['gk'],$my['df1'],$my['df2'],$my['mf1'],$my['mf2'],$my['fw'],$my['bench']]));
            $mapName = []; $mapPos = [];
            $rs = $db->query("SELECT id,name,position FROM players WHERE id IN (".implode(',',$allIds).")");
            while ($r=$rs->fetch_assoc()) { $mapName[(int)$r['id']]=$r['name']; $mapPos[(int)$r['id']]=$r['position']; }

            // события за уик-энд
            $mp = $db->query("
                SELECT mp.*, p.position, p.team_id AS player_team_id
                FROM match_players mp
                INNER JOIN players p ON p.id=mp.player_id
                WHERE mp.match_id IN ($idsList) AND mp.player_id IN (".implode(',',$allIds).")
            ");

            $ptsByPlayer = [];   // raw очки (без капитана)
            $playedFlag  = [];   // играл хотя бы раз
            while ($row = $mp->fetch_assoc()) {
                $pid = (int)$row['player_id'];
                $mid = (int)$row['match_id'];
                $pos = $row['position'];
                $norm= _normPos($pos);
                $teamIdForMatch = isset($teamByMatch[$mid]) ? $teamByMatch[$mid] : (int)$row['player_team_id'];

                $pts = 0;
                if ((int)$row['played']>0) { $pts += 1; $playedFlag[$pid] = true; }
                $pts += ((int)$row['goals'])   * _goalPts($teamIdForMatch,$pos);
                $pts += ((int)$row['assists']) * 3;
                if ((int)$row['clean_sheet']>0 && ($norm==='GK'||$norm==='DF')) $pts += 4;
                $pts -= ((int)$row['yellow_cards']) * 1;
                $pts -= ((int)$row['red_cards']) * 3;
                if ($norm==='GK' && (int)$row['goals_conceded'] > 5) $pts -= 3;
                $pts -= ((int)$row['missed_penalties']) * 2;

                if (!isset($ptsByPlayer[$pid])) $ptsByPlayer[$pid]=0;
                $ptsByPlayer[$pid] += $pts;
            }

            // замена: если есть несыгравший в роли запасного — добавим очки бенча
            $benchCounted = false;
            $benchPos = _normPos($mapPos[$my['bench']] ?? '');
            $roles = [
                'GK' => [$my['gk']],
                'DF' => [$my['df1'],$my['df2']],
                'MF' => [$my['mf1'],$my['mf2']],
                'FW' => [$my['fw']],
            ];
            if (isset($roles[$benchPos])) {
                $someoneDidntPlay = false;
                foreach ($roles[$benchPos] as $starter) {
                    if ($starter && empty($playedFlag[$starter])) { $someoneDidntPlay = true; break; }
                }
                if ($someoneDidntPlay) $benchCounted = true;
            }

            // сформируем строки для таблицы
            $order = [
                $my['gk'],$my['df1'],$my['df2'],$my['mf1'],$my['mf2'],$my['fw']
            ];
            foreach ($order as $pid) {
                $weekRows[] = [
                    'player_id' => $pid,
                    'name' => $mapName[$pid] ?? ('#'.$pid),
                    'pos'  => _normPos($mapPos[$pid] ?? ''),
                    'points' => (int)round($ptsByPlayer[$pid] ?? 0),
                    'captain' => ($pid === $my['captain']),
                    'bench'   => false
                ];
            }
            if ($benchCounted) {
                $weekRows[] = [
                    'player_id' => $my['bench'],
                    'name' => ($mapName[$my['bench']] ?? ('#'.$my['bench'])) . ' (замена)',
                    'pos'  => $benchPos,
                    'points' => (int)round($ptsByPlayer[$my['bench']] ?? 0),
                    'captain' => false,
                    'bench'   => true
                ];
            }

            // итог с учётом капитана (удвоение добавляем отдельно; на бенча не переносится)
            $weekTeamTotal = 0;
            foreach ($weekRows as $r) { $weekTeamTotal += (int)$r['points']; }
            // кап. бонус
            $capPts = (int)round($ptsByPlayer[$my['captain']] ?? 0);
            if ($capPts !== 0) $weekTeamTotal += $capPts;
        }
    }
}
?>

<?php if ($hasFullSquad && !empty($weekRows)): ?>
<div class="rules-section">
    <h3 class="muted">Очки на прошлой неделе</h3>
    <div class="picker ranking-table-container">
        <table class="ranking-table">
            <thead>
                <tr>
                    <th>Игрок</th>
                    <th>Позиция</th>
                    <th>Очки</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weekRows as $r): ?>
                    <tr>
                        <td>
                            <?php
                              $nm = htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8');
                              if ($r['captain']) $nm .= ' <span class="team-label team-label-2">капитан</span>';
                            echo $nm;
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['pos'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo (int)$r['points']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td><strong>Итого (с учётом капитана)</strong></td>
                    <td></td>
                    <td><strong><?php echo (int)$weekTeamTotal; ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


        <div class="ranking-section">
            <h3 class="muted">Рейтинг команд</h3>
            <div class="picker ranking-table-container">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Место</th>
                            <th>Название команды</th>
                            <th>Очки за неделю</th>
                            <th>Очки общие</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $ranking = [];

                    $rankQ = $db->query("
                        SELECT 
                            fs.user_id,
                            fs.total_points,
                            fs.last_week_points,
                            COALESCE(NULLIF(fu.team_name, ''), CONCAT('Команда #', fs.user_id)) AS team_name
                        FROM fantasy_squads fs
                        LEFT JOIN fantasy_users fu ON fu.id = fs.user_id
                    ");

                    if ($rankQ) {
                        while ($row = $rankQ->fetch_assoc()) {
                            $name = ($row['user_id'] == $userId && !empty($_SESSION['fantasy_team']))
                                ? $_SESSION['fantasy_team']
                                : $row['team_name'];

                            $ranking[] = [
                                'user_id'          => (int)$row['user_id'],
                                'team_name'        => $name,
                                'last_week_points' => (float)$row['last_week_points'],
                                'total_points'     => (float)$row['total_points'],
                            ];
                        }

                        usort($ranking, function ($a, $b) {
                            return $b['total_points'] <=> $a['total_points'];
                        });

                        $place = 1;
                        foreach ($ranking as $rank) {
                            echo '<tr>';
                            echo '<td>' . $place++ . '</td>';
                            echo '<td>' . htmlspecialchars($rank['team_name'], ENT_QUOTES, 'UTF-8') . '</td>';
                          echo '<td>' . (int)round($rank['last_week_points']) . '</td>';
echo '<td>' . (int)round($rank['total_points']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        error_log('Ranking query failed: ' . $db->error);
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="confirmModal" class="modal" style="display:none;">
            <div class="modal-content">
                <h3>Подтверждение</h3>
                <p>Вас устраивает этот состав?</p>
                <div class="modal-buttons">
                    <button class="btn" onclick="confirmSave(true)">Да</button>
                    <button class="btn" onclick="confirmSave(false)">Нет</button>
                </div>
            </div>
        </div>

    </div> <!-- Закрытие .fantasy-card -->
</div> <!-- Закрытие .Fantasy -->

<?php
$footer_files = array(dirname(__FILE__) . '/blocks/footer.php', dirname(__FILE__) . '/blocks/footer.html');
foreach ($footer_files as $file) {
    if (file_exists($file)) {
        include $file;
        break;
    }
}
?>

<script>
// Глобальные определения
var $ = function(sel) { return document.querySelector(sel); };
var $$ = function(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); };

/* ===== HOLIDAYS ===== */
var HOLIDAYS = new Set();
var HOLIDAYS_URL = 'api/get_holidays.php'; // поправь путь если файл лежит не в корне

function _holidayMonthYYYYMM() {
  var d = new Date();
  var y = d.getFullYear().toString();
  var m = (d.getMonth()+1).toString().padStart(2,'0');
  return y+m;
}

function _holidayBadgeHTML() {
    return '<span class="holiday-badge" tabindex="0" role="button" aria-label="Отпуск">' +
           '<span class="tip">Этот футболист находится в отпуске в этом месяце</span>' +
           '</span>';
}

function markHolidaysInLists() {
    $$('.card').forEach(function(card) {
        var id = parseInt(card.getAttribute('data-id'));
        if (!id) return;
        var ph = card.querySelector('.ph');
        if (!ph) return;
        ph.classList.add('holiday-wrap');
        var old = ph.querySelector('.holiday-badge');
        if (old) old.remove();

        // Создаем обертку для img, если её нет
        var img = ph.querySelector('img');
        if (img && !img.parentElement.classList.contains('img-wrap')) {
            var wrap = document.createElement('div');
            wrap.className = 'img-wrap';
            ph.insertBefore(wrap, img);
            wrap.appendChild(img);
        }

        if (HOLIDAYS.has(id)) {
            ph.insertAdjacentHTML('beforeend', _holidayBadgeHTML());
        }
    });
}

function markHolidaysOnField() {
    ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'].forEach(function(k) {
        var id = parseInt((document.getElementById('inp_' + k)?.value) || '0');
        var avatar = document.querySelector('.slot[data-slot="' + k + '"] .avatar');
        if (!avatar) return;
        avatar.classList.add('holiday-wrap');
        var old = avatar.querySelector('.holiday-badge');
        if (old) old.remove();

        // Создаем обертку для img, если её нет
        var img = avatar.querySelector('img');
        if (img && !img.parentElement.classList.contains('img-wrap')) {
            var wrap = document.createElement('div');
            wrap.className = 'img-wrap';
            avatar.insertBefore(wrap, img);
            wrap.appendChild(img);
        }

        if (id && HOLIDAYS.has(id)) {
            avatar.insertAdjacentHTML('beforeend', _holidayBadgeHTML());
        }
    });
}

// Тап по иконке и позиционирование подсказки
document.addEventListener('click', function(e) {
    var badge = e.target.closest('.holiday-badge');
    if (badge) {
        e.preventDefault();
        badge.classList.toggle('active');
        var tip = badge.querySelector('.tip');
        if (tip) {
            if (badge.classList.contains('active')) {
                showTip(tip, badge);
            } else {
                hideTip(tip);
            }
        }
    } else {
        $$('.holiday-badge.active').forEach(b => {
            b.classList.remove('active');
            var tip = b.querySelector('.tip');
            if (tip) hideTip(tip);
        });
    }
});

// Поддержка hover для десктопов
document.addEventListener('mouseover', function(e) {
    var badge = e.target.closest('.holiday-badge');
    if (badge && !badge.classList.contains('active')) {
        var tip = badge.querySelector('.tip');
        if (tip) {
            showTip(tip, badge);
        }
    }
});

document.addEventListener('mouseout', function(e) {
    var badge = e.target.closest('.holiday-badge');
    var tip = e.target.closest('.tip');
    if (badge && !badge.classList.contains('active') && !tip) {
        var relatedTip = badge.querySelector('.tip');
        if (relatedTip) hideTip(relatedTip);
    }
});

// Скрытие при клике вне
document.addEventListener('click', function(e) {
    var badge = e.target.closest('.holiday-badge');
    var tip = e.target.closest('.tip');
    if (!badge && !tip) {
        $$('.holiday-badge.active').forEach(b => {
            b.classList.remove('active');
            var relatedTip = b.querySelector('.tip');
            if (relatedTip) hideTip(relatedTip);
        });
    }
});

// Функции для показа/скрытия подсказки
function showTip(tip, badge) {
  tip.style.display = 'block';
  tip.style.opacity = '1';
}

function hideTip(tip) {
  tip.style.display = 'none';
  tip.style.opacity = '0';
}

// загрузка отпусков
(function(){
  var month=_holidayMonthYYYYMM();
  fetch(HOLIDAYS_URL+'?month='+encodeURIComponent(month))
    .then(r=>r.json())
    .then(ids=>{
      HOLIDAYS=new Set((ids||[]).map(x=>parseInt(x)));
      markHolidaysInLists();
      markHolidaysOnField();
    })
    .catch(err=>console.error('Holidays fetch error',err));
})();

(function(){
    var BUDGET = <?php echo json_encode($BUDGET); ?>;
    var SERVER_CAPTAIN = <?php echo (int)($squad['captain_player_id'] ?? 0); ?>;
    var IS_EDITING_ALLOWED = <?php echo json_encode($isEditingAllowed); ?>;
    var PRICES = {};
    var TEAM_IDS = {};
    $$('.card').forEach(function(c) {
        var id = parseInt(c.getAttribute('data-id'));
        var price = parseFloat(c.getAttribute('data-price') || '0');
        var teamId = parseInt(c.getAttribute('data-team-id') || '0');
        if (!isNaN(id)) {
            PRICES[id] = price;
            TEAM_IDS[id] = teamId;
        }
    });

    // Отключаем вкладки, если редактирование запрещено
    $$('#tabs .tab').forEach(function(tab) {
        tab.classList.toggle('disabled', !IS_EDITING_ALLOWED);
    });

    var budgetEl = $('#budgetLeft');
    var hintEl = $('#hint');
    var saveBtn = $('#saveBtn');
    var captainRow = $('#captainRow');
    var captainSelect = $('#captainSelect');

    function notify(msg) { hintEl.textContent = msg; }

    function restrictTeamLimit(id) {
        var picked = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id']
            .map(function(k) { return parseInt(($('#inp_' + k).value) || '0'); })
            .filter(function(x) { return x > 0; });
        var teamCounts = {};
        picked.forEach(function(pid) {
            var teamId = TEAM_IDS[pid] || 0;
            if (teamId) teamCounts[teamId] = (teamCounts[teamId] || 0) + 1;
        });
        var newTeamId = TEAM_IDS[id] || 0;
        return !newTeamId || (teamCounts[newTeamId] || 0) < 4;
    }

    $('#tabs').addEventListener('click', function(e) {
        var btn = e.target.closest('.tab');
        if (!btn || btn.classList.contains('disabled')) return;
        $$('#tabs .tab').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var key = btn.getAttribute('data-tab');
        $$('.list').forEach(function(l) { l.classList.remove('active'); });
        $('#list_' + key).classList.add('active');
    });

    document.getElementById('picker').addEventListener('click', function(e) {
        var btn = e.target.closest('.btn');
        if (!btn || !IS_EDITING_ALLOWED) return;
        var isPickBtn = btn.classList.contains('choose') || btn.classList.contains('remove');
        if (!isPickBtn) return;
        e.preventDefault();

        var card = btn.closest('.card');
        var id = parseInt(card.getAttribute('data-id'));
        var pos = card.getAttribute('data-pos');
        var price = parseFloat(card.getAttribute('data-price'));
        var name = card.querySelector('.nm').textContent.trim();
        var photo = card.querySelector('img').getAttribute('src');

        if (btn.classList.contains('choose')) {
            var left = getLeft();
            var slotKey = null;
            if (pos === 'GK') slotKey = 'gk_id';
            else if (pos === 'FW') slotKey = 'fw_id';
            else if (pos === 'BENCH') slotKey = 'bench_id';
            else if (pos === 'DF') {
                if (parseInt($('#inp_df1_id').value) === 0) slotKey = 'df1_id';
                else if (parseInt($('#inp_df2_id').value) === 0) slotKey = 'df2_id';
                else return notify('Оба слота защитников заняты');
            } else if (pos === 'MF') {
                if (parseInt($('#inp_mf1_id').value) === 0) slotKey = 'mf1_id';
                else if (parseInt($('#inp_mf2_id').value) === 0) slotKey = 'mf2_id';
                else return notify('Оба слота полузащитников заняты');
            }

            if (!slotKey) return;
            if (['gk_id', 'fw_id', 'bench_id'].indexOf(slotKey) !== -1 && parseInt($('#inp_' + slotKey).value) > 0)
                return notify('Слот уже занят');
            if (slotKey === 'bench_id' && pos === 'FW')
                return notify('Запасной не может быть нападающим');
            if (isAlreadyPicked(id))
                return notify('Этот игрок уже выбран');
            if (price > left + 0.000000001)
                return notify('Недостаточно бюджета');
            if (!restrictTeamLimit(id))
                return notify('Нельзя выбрать более 4 игроков из одной команды');

            fillSlot(slotKey, { id: id, name: name, photo: photo });
            recalcBudget();
            updateUIState();
        } else {
            var slotKey = getSlotKeyForId(id);
            if (slotKey) {
                handleRemove(id, slotKey);
                updateUIState();
            }
        }
    });

    bootFill();

    function bootFill() {
        var slots = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'];
        slots.forEach(function(k) {
            var val = parseInt(($('#inp_' + k).value) || '0');
            if (!val) return;
            var card = document.querySelector('.card[data-id="' + val + '"]');
            var name = card ? card.querySelector('.nm').textContent.trim() : 'Игрок';
            var photo = card ? card.querySelector('img').getAttribute('src') : '/img/player/player_0.png';
            fillSlot(k, { id: val, name: name, photo: photo }, true);
        });
        recalcBudget(true);
        updateUIState();
        updateTeamCounts();
    }

    function safeSrc(src) {
        if (!src || src === '/') return '/img/player/player_0.png';
        return src;
    }

    function setSlotImage(slotEl, src) {
        var img = slotEl.querySelector('.avatar img');
        img.onerror = function() { this.onerror = null; this.src = '/img/player/player_0.png'; };
        img.src = safeSrc(src);
    }

    function fillSlot(slotKey, data, silent) {
        document.getElementById('inp_' + slotKey).value = data.id;
        $('#inp_' + slotKey).value = data.id;
        var slot = document.querySelector('.slot[data-slot="' + slotKey + '"]');
        setSlotImage(slot, data.photo);
        slot.querySelector('.name').textContent = data.name;
        slot.classList.remove('captain');
        if (!silent) {
            slot.style.transition = 'box-shadow .2s';
            slot.style.boxShadow = '0 0 0 4px rgba(253,197,0,.6)';
            setTimeout(function() { slot.style.boxShadow = ''; }, 220);
        }

        var card = document.querySelector('.card[data-id="' + data.id + '"]');
        if (card) {
            var btn = card.querySelector('.choose');
            if (btn) {
                btn.textContent = 'Убрать';
                btn.classList.remove('choose');
                btn.classList.add('remove');
            }
        }
        markHolidaysOnField();
    }

    function handleRemove(id, slotKey) {
        $('#inp_' + slotKey).value = '0';
        var slot = document.querySelector('.slot[data-slot="' + slotKey + '"]');
        setSlotImage(slot, '/img/player/player_0.png');
        slot.querySelector('.name').textContent = {
            'gk_id': 'Вратарь',
            'fw_id': 'Нападающий',
            'mf1_id': 'Полузащитник',
            'mf2_id': 'Полузащитник',
            'df1_id': 'Защитник',
            'df2_id': 'Защитник',
            'bench_id': 'Запасной'
        }[slotKey];
        slot.classList.remove('captain');

        var card = document.querySelector('.card[data-id="' + id + '"]');
        if (card) {
            var btn = card.querySelector('.remove');
            if (btn) {
                btn.textContent = 'Выбрать';
                btn.classList.remove('remove');
                btn.classList.add('choose');
            }
        }

        recalcBudget();
        updateUIState();
        markHolidaysOnField();
    }

    function isAlreadyPicked(id) {
        var picked = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id']
            .map(function(k) { return parseInt(($('#inp_' + k).value) || '0'); })
            .filter(function(x) { return x; });
        console.log('Picked IDs:', picked, 'Checking ID:', id);
        return picked.indexOf(id) !== -1;
    }

    function getLeft() {
        var left = parseFloat(budgetEl.textContent);
        if (isNaN(left)) left = (typeof BUDGET === 'number' ? BUDGET : 50);
        return left;
    }

    function recalcBudget(initial) {
        var slotKeys = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'];
        var sum = 0;
        slotKeys.forEach(function(k) {
            var id = parseInt(($('#inp_' + k).value) || '0');
            if (id && (id in PRICES)) sum += PRICES[id];
        });

        var leftRaw = (typeof BUDGET === 'number' ? BUDGET : parseFloat(BUDGET)) - sum;
        var left = isNaN(leftRaw) ? (typeof BUDGET === 'number' ? BUDGET : 50) : Math.max(0, leftRaw);
        budgetEl.textContent = left.toFixed(2);
        if (!initial) hintEl.textContent = '';
    }

    function updateTeamCounts() {
        var picked = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id']
            .map(function(k) { return parseInt(($('#inp_' + k).value) || '0'); })
            .filter(function(x) { return x > 0; });
        var teamCounts = { 1: 0, 2: 0 };
        picked.forEach(function(pid) {
            var teamId = TEAM_IDS[pid] || 0;
            if (teamId) teamCounts[teamId]++;
        });
        $('#teamCount1').querySelector('span').textContent = teamCounts[1];
        $('#teamCount2').querySelector('span').textContent = teamCounts[2];
    }

    function updateUIState() {
        updateTeamCounts();
        var left = getLeft();
        console.log('Budget left:', left);
        markHolidaysInLists();

        var singleTaken = {
            GK: parseInt($('#inp_gk_id').value) > 0,
            FW: parseInt($('#inp_fw_id').value) > 0,
            BENCH: parseInt($('#inp_bench_id').value) > 0
        };
        var dfTaken = (parseInt($('#inp_df1_id').value) > 0) && (parseInt($('#inp_df2_id').value) > 0);
        var mfTaken = (parseInt($('#inp_mf1_id').value) > 0) && (parseInt($('#inp_mf2_id').value) > 0);
        console.log('Input values:', {
            gk_id: $('#inp_gk_id').value,
            df1_id: $('#inp_df1_id').value,
            df2_id: $('#inp_df2_id').value,
            mf1_id: $('#inp_mf1_id').value,
            mf2_id: $('#inp_mf2_id').value,
            fw_id: $('#inp_fw_id').value,
            bench_id: $('#inp_bench_id').value
        });
        console.log('Slots taken:', singleTaken, 'DF:', dfTaken, 'MF:', mfTaken);

        $$('.card').forEach(function(card) {
            var id = parseInt(card.getAttribute('data-id'));
            var pos = card.getAttribute('data-pos');
            var price = parseFloat(card.getAttribute('data-price') || '0');
            var btn = card.querySelector('.choose, .remove');

            var disabled = !IS_EDITING_ALLOWED; // Отключаем, если редактирование запрещено

            if (!disabled) {
                if (isAlreadyPicked(id)) {
                    if (btn && btn.classList.contains('choose')) {
                        btn.textContent = 'Убрать';
                        btn.classList.remove('choose');
                        btn.classList.add('remove');
                    }
                    disabled = false;
                } else {
                    if (btn && btn.classList.contains('remove')) {
                        btn.textContent = 'Выбрать';
                        btn.classList.remove('remove');
                        btn.classList.add('choose');
                    }
                    if (pos === 'GK' && singleTaken.GK) disabled = true;
                    if (pos === 'FW' && singleTaken.FW) disabled = true;
                    if (pos === 'BENCH' && singleTaken.BENCH) disabled = true;
                    if (pos === 'DF' && dfTaken) disabled = true;
                    if (pos === 'MF' && mfTaken) disabled = true;
                    if (price > left + 0.000000001) disabled = true;
                }
            }

            card.classList.toggle('disabled', disabled);
            if (btn) btn.disabled = disabled;
        });

        var slotsFilled = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id']
            .every(function(k) { return parseInt($('#inp_' + k).value) > 0; });

        $('#saveBtn').disabled = !IS_EDITING_ALLOWED || !slotsFilled || left < 0;
        $('#hint').textContent = !IS_EDITING_ALLOWED ? 'Редактирование состава возможно только со вторника по пятницу.' : (!slotsFilled ? 'Заполните все слоты' : (left < 0 ? 'Превышен бюджет' : ''));

        if (slotsFilled && IS_EDITING_ALLOWED) {
            var opts = [];
            ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'].forEach(function(k) {
                var id = parseInt($('#inp_' + k).value);
                if (!id) return;
                var slot = document.querySelector('.slot[data-slot="' + k + '"]');
                var name = slot.querySelector('.name').textContent;
                opts.push({ id: id, name: name, slotKey: k });
            });

            var prev = captainSelect.value || (SERVER_CAPTAIN ? String(SERVER_CAPTAIN) : '');
            captainSelect.innerHTML = opts.map(function(o) {
                return '<option value="' + o.id + '">' + o.name + '</option>';
            }).join('');

            var hasPrev = opts.some(function(o) { return String(o.id) === prev; });
            var setTo = hasPrev ? prev : (opts[0] ? String(opts[0].id) : '');
            if (setTo) captainSelect.value = setTo;

            captainRow.style.display = '';
            saveBtn.disabled = saveBtn.disabled || false;
        } else {
            captainRow.style.display = 'none';
            saveBtn.disabled = true;
        }

        captainSelect.disabled = !IS_EDITING_ALLOWED;
        captainSelect.removeEventListener('change', markCaptain);
        if (IS_EDITING_ALLOWED) {
            captainSelect.addEventListener('change', markCaptain);
        }
        markCaptain();
    }

    function getSlotKeyForId(id) {
        var slots = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'];
        for (var i = 0; i < slots.length; i++) {
            if (parseInt($('#inp_' + slots[i]).value) === id) return slots[i];
        }
        return null;
    }

    function markCaptain() {
        $$('.slot').forEach(function(s) { s.classList.remove('captain'); });
        var id = parseInt(captainSelect.value || '0');
        if (!id) return;
        ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id'].forEach(function(k) {
            var cur = parseInt(($('#inp_' + k).value) || '0');
            if (cur === id) document.querySelector('.slot[data-slot="' + k + '"]').classList.add('captain');
        });
    }
})();

function confirmSave(ok) {
    const form = document.querySelector('#saveForm');
    if (!ok) { document.querySelector('#confirmModal').style.display = 'none'; return; }
    if (form.requestSubmit) {
        form.requestSubmit(document.getElementById('saveBtn'));
    } else {
        let h = document.createElement('input');
        h.type = 'hidden'; h.name = 'save_squad'; h.value = '1';
        form.appendChild(h);
        form.submit();
    }
}

document.getElementById('saveBtn').addEventListener('click', function(e) {
    if (!this.disabled && IS_EDITING_ALLOWED) {
        e.preventDefault();
        var modal = document.querySelector('#confirmModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.display = 'flex';
        }, 0);
    }
});

function slotKeyToTabKey(slotKey) {
  if (slotKey === 'gk_id') return 'GK';
  if (slotKey === 'fw_id') return 'FW';
  if (slotKey === 'bench_id') return 'BENCH';
  if (slotKey === 'df1_id' || slotKey === 'df2_id') return 'DF';
  if (slotKey === 'mf1_id' || slotKey === 'mf2_id') return 'MF';
  return 'FW';
}

function switchToTab(key) {
  // активируем кнопку таба
  $$('#tabs .tab').forEach(function(b) {
    b.classList.toggle('active', b.getAttribute('data-tab') === key);
  });
  // активируем сам список
  $$('.list').forEach(function(l) { l.classList.remove('active'); });
  var targetList = $('#list_' + key);
  if (targetList) targetList.classList.add('active');
}

function scrollToCard(cardEl, listKey) {
  // убедимся, что открыт правильный список
  switchToTab(listKey);
  var list = $('#list_' + listKey);
  if (!cardEl || !list) return;

  // прокрутим к карточке
  cardEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

  // вспышка-подсветка
  cardEl.classList.add('_jump-highlight');
  setTimeout(function(){ cardEl.classList.remove('_jump-highlight'); }, 1200);
}

// ---- клик по аватарке на поле: открыть соответствующий список/игрока ----
$$('.field .slot .avatar').forEach(function(avatarEl) {
  avatarEl.style.cursor = 'pointer';
  avatarEl.addEventListener('click', function() {
    var slot = avatarEl.closest('.slot');
    if (!slot) return;
    var slotKey = slot.getAttribute('data-slot');            // например 'mf1_id'
    var pickedId = parseInt(($('#inp_' + slotKey).value) || '0', 10);

    if (pickedId > 0) {
      // игрок выбран — открываем его карточку
      var card = document.querySelector('.card[data-id="' + pickedId + '"]');
      var listKey = card ? card.getAttribute('data-pos') : slotKeyToTabKey(slotKey);
      scrollToCard(card, listKey);
    } else {
      // слот пуст — открываем список по позиции слота
      var listKey = slotKeyToTabKey(slotKey);
      switchToTab(listKey);
      // можно дать маленький хинт
      notify('Выберите игрока на эту позицию');
    }
  });
});

</script>

<script src="./js/index.bundle.js"></script>

</body>
</html>