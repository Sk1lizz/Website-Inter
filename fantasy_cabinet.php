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

if ($stmt = $db->prepare("SELECT gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id, captain_player_id, budget_left FROM fantasy_squads WHERE user_id=? LIMIT 1")) {
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
    $gk_id   = isset($_POST['gk_id']) ? (int)$_POST['gk_id'] : 0;
    $df1_id  = isset($_POST['df1_id']) ? (int)$_POST['df1_id'] : 0;
    $df2_id  = isset($_POST['df2_id']) ? (int)$_POST['df2_id'] : 0;
    $mf1_id  = isset($_POST['mf1_id']) ? (int)$_POST['mf1_id'] : 0;
    $mf2_id  = isset($_POST['mf2_id']) ? (int)$_POST['mf2_id'] : 0;
    $fw_id   = isset($_POST['fw_id']) ? (int)$_POST['fw_id'] : 0;
    $bench_id= isset($_POST['bench_id']) ? (int)$_POST['bench_id'] : 0;
    $captain = isset($_POST['captain_player_id']) ? (int)$_POST['captain_player_id'] : 0;

    $ids = array_filter(array($gk_id, $df1_id, $df2_id, $mf1_id, $mf2_id, $fw_id, $bench_id));
    if (count($ids) !== 7 || count(array_unique($ids)) !== 7) {
        $saveError = 'Нужно выбрать 7 разных игроков.';
    } else {
        // Проверка, что запасной не нападающий
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

        // Проверка ограничения на 4 игроков из одной команды
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

        // Проверка бюджета
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
                    (user_id, season, gk_id, df1_id, df2_id, mf1_id, mf2_id, fw_id, bench_id, captain_player_id, budget_left)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                    season=VALUES(season),
                    gk_id=VALUES(gk_id),
                    df1_id=VALUES(df1_id),
                    df2_id=VALUES(df2_id),
                    mf1_id=VALUES(mf1_id),
                    mf2_id=VALUES(mf2_id),
                    fw_id=VALUES(fw_id),
                    bench_id=VALUES(bench_id),
                    captain_player_id=VALUES(captain_player_id),
                    budget_left=VALUES(budget_left)
                ")) {
                    $st->bind_param(
                        'iiiiiiiiiid',
                        $userId, $SEASON, $gk_id, $df1_id, $df2_id, $mf1_id, $mf2_id, $fw_id, $bench_id, $captain, $left
                    );
                    $saveSuccess = $st->execute();
                    if (!$saveSuccess) {
                        $saveError = 'Ошибка сохранения: ' . $st->error;
                        error_log('Fantasy save error: ' . $st->error);
                    }
                    $st->close();

                    if ($saveSuccess) {
                        $squad = array(
                            'gk_id' => $gk_id, 'df1_id' => $df1_id, 'df2_id' => $df2_id,
                            'mf1_id' => $mf1_id, 'mf2_id' => $mf2_id, 'fw_id' => $fw_id,
                            'bench_id' => $bench_id, 'captain_player_id' => $captain,
                            'budget_left' => $left
                        );
                        $url = strtok($_SERVER['REQUEST_URI'], '?');
                        header('Location: ' . $url . '?saved=1');
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
    WHERE p.team_id IN (1, 2)
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
    <meta charset="utf-8">
    <title>Fantasy — кабинет</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .Fantasy { padding-top: 20svh; display: flex; justify-content: center; background: #fff; font-family: PLAY-REGULAR, Arial; }
        .fantasy-card { width: 100%; max-width: 1100px; background: rgba(0,0,0,.03); border-radius: 12px; padding: 24px; box-shadow: 0 2px 5px rgba(0,0,0,.08); }
        h1 { font-family: PLAY-BOLD, Arial; color: #00296B; font-size: 28px; margin-bottom: 8px; }
        .muted { color: #666; }
        .btn { background: #00296B; color: #FDC500; border: 2px solid #FDC500; border-radius: 10px; padding: 10px 16px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #000; color: #fff; border-color: #fff; }
        .grid { display: grid; grid-template-columns: 420px 1fr; gap: 24px; }
        .field, .picker { position: relative; z-index: 1; }
        .field { position: relative; width: 420px; height: 650px; background: url('/img/field.jpg') center/cover; border-radius: 12px; overflow: hidden; }
        .slot { position: absolute; width: 90px; text-align: center; }
        .slot .avatar { width: 74px; height: 74px; border-radius: 50%; overflow: hidden; margin: 0 auto 6px; border: 3px solid #fff; background: #e9eef5; box-shadow: 0 2px 4px rgba(0,0,0,.2); }
        .slot img { width: 100%; height: 100%; object-fit: cover; }
        .slot .name, .slot .pos {
            color: #fff;
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
        }
        .slot .name { font-size: 12px; font-weight: 700; }
        .slot .pos {
            color: #fff;
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
        }
        .slot .pos.black-text {
            color: #000;
            text-shadow: none;
        }
        .slot .capt { display: none; font-size: 11px; color: #FDC500; text-shadow: none; }
        .slot.captain .capt { display: block; }
        .s-fw { top: 80px; left: 165px; }
        .s-mf1 { top: 240px; left: 60px; }
        .s-mf2 { top: 240px; left: 270px; }
        .s-df1 { top: 405px; left: 60px; }
        .s-df2 { top: 405px; left: 270px; }
        .s-bench { top: 530px; left: 10px; width: 110px; }
        .s-gk { top: 530px; left: 165px; }
        .picker { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; }
        .budget { margin-bottom: 10px; }
        .budget-instruction { font-size: 14px; color: #666; margin-top: 5px; }
        .tabs { display: flex; gap: 8px; margin-bottom: 10px; }
        .tab { padding: 8px 12px; border: 1px solid #ddd; border-radius: 10px; background: #fff; cursor: pointer; color: #000; }
        .tab.active { background: #00296B; color: #FDC500; border-color: #00296B; }
        .list { display: none; max-height: 470px; overflow: auto; border: 1px solid #eee; border-radius: 10px; }
        .list.active { display: block; }
        .card { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        .card:last-child { border-bottom: none; }
        .card .ph { width: 46px; height: 46px; border-radius: 50%; overflow: hidden; background: #eef2ff; border: 2px solid #fff; box-shadow: 0 1px 2px rgba(0,0,0,.12); }
        .card .ph img { width: 100%; height: 100%; object-fit: cover; }
        .card .nm { font-weight: 600; }
        .card .meta { font-size: 12px; color: #64748b; }
        .card .price { margin-left: auto; font-weight: 700; }
        .card .choose { margin-left: 8px; }
        .disabled { opacity: .45; pointer-events: none; }
        .row { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
        select { padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; }
        .warn { background: #fdeceb; color: #7d1c1a; border: 1px solid #f5c6cb; padding: 10px; border-radius: 8px; margin: 10px 0; }
        .ok { background: #e7f6e7; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 8px; margin: 10px 0; }
        .slot .capt { display: none !important; }
        .slot.captain .avatar {
            border-color: #FDC500;
            box-shadow: 0 0 0 3px rgba(253,197,0,0.55), 0 0 12px rgba(253,197,0,0.75);
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
        <h1>Личный кабинет</h1>
        <p class="muted">Команда: <strong><?php echo htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8'); ?></strong></p>

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
                <div class="budget">
                    Бюджет: <strong><span id="budgetLeft"><?php echo number_format((float)$squad['budget_left'], 2, '.', ''); ?></span></strong> из <?php echo number_format($BUDGET, 2, '.', ''); ?>
                </div>
                <div class="budget-instruction">
                    Собери команду на предоставленный бюджет. Вы можете взять не более четырёх игроков из одной команды.
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
                                    <div class="meta">' . $key . ', ' . $teamName . '</div>
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
                    <input type="hidden" name="gk_id"   id="inp_gk_id"   value="<?php echo (int)$squad['gk_id']; ?>">
                    <input type="hidden" name="df1_id"  id="inp_df1_id"  value="<?php echo (int)$squad['df1_id']; ?>">
                    <input type="hidden" name="df2_id"  id="inp_df2_id"  value="<?php echo (int)$squad['df2_id']; ?>">
                    <input type="hidden" name="mf1_id"  id="inp_mf1_id"  value="<?php echo (int)$squad['mf1_id']; ?>">
                    <input type="hidden" name="mf2_id"  id="inp_mf2_id"  value="<?php echo (int)$squad['mf2_id']; ?>">
                    <input type="hidden" name="fw_id"   id="inp_fw_id"   value="<?php echo (int)$squad['fw_id']; ?>">
                    <input type="hidden" name="bench_id"id="inp_bench_id" value="<?php echo (int)$squad['bench_id']; ?>">
                    <div class="row" id="captainRow" style="display:none;">
                        <label for="captain_player_id">Капитан:</label>
                        <select name="captain_player_id" id="captainSelect"></select>
                    </div>
                    <button class="btn" type="submit" name="save_squad" id="saveBtn" disabled>Сохранить состав</button>
                    <span class="muted" id="hint"></span>
                </form>
            </div>
        </div>
    </div>
</div>

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
(function(){
    var BUDGET = <?php echo json_encode($BUDGET); ?>;
    var SERVER_CAPTAIN = <?php echo (int)($squad['captain_player_id'] ?? 0); ?>;
    var $ = function(sel) { return document.querySelector(sel); };
    var $$ = function(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); };

    var PRICES = {};
    var TEAM_IDS = {}; // Храним team_id для каждого игрока
    $$('.card').forEach(function(c) {
        var id = parseInt(c.getAttribute('data-id'));
        var price = parseFloat(c.getAttribute('data-price') || '0');
        var teamId = parseInt(c.getAttribute('data-team-id') || '0');
        if (!isNaN(id)) {
            PRICES[id] = price;
            TEAM_IDS[id] = teamId;
        }
    });

    var budgetEl = $('#budgetLeft');
    var hintEl = $('#hint');
    var saveBtn = $('#saveBtn');
    var captainRow = $('#captainRow');
    var captainSelect = $('#captainSelect');

    function notify(msg) { hintEl.textContent = msg; }

    // Проверка ограничения по количеству игроков из одной команды
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
        if (!btn) return;
        $$('#tabs .tab').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var key = btn.getAttribute('data-tab');
        $$('.list').forEach(function(l) { l.classList.remove('active'); });
        $('#list_' + key).classList.add('active');
    });

    document.getElementById('picker').addEventListener('click', function(e) {
        var btn = e.target.closest('.btn');
        if (!btn) return;
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
            }

            function safeSrc(src){
  if (!src || src === '/' ) return '/img/player/player_0.png';
  return src;
}

function setSlotImage(slotEl, src){
  var img = slotEl.querySelector('.avatar img');
  img.onerror = function(){ this.onerror = null; this.src = '/img/player/player_0.png'; };
  img.src = safeSrc(src);
}

            function fillSlot(slotKey, data, silent) {
                 document.getElementById('inp_' + slotKey).value = data.id;
                $('#inp_' + slotKey).value = data.id;
                var slot = document.querySelector('.slot[data-slot="' + slotKey + '"]');
                slot.querySelector('.avatar img').src = data.photo;
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
            }

            function handleRemove(id, slotKey) {
                $('#inp_' + slotKey).value = '0';
                var slot = document.querySelector('.slot[data-slot="' + slotKey + '"]');
                slot.querySelector('.avatar img').src = '/img/player/player_0.png';
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

            function updateUIState() {
                var left = getLeft();
                console.log('Budget left:', left);

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

                    var disabled = false;

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

                    card.classList.toggle('disabled', disabled);
                    if (btn) btn.disabled = disabled;
                });

                var allPicked = ['gk_id', 'df1_id', 'df2_id', 'mf1_id', 'mf2_id', 'fw_id', 'bench_id']
                    .every(function(k) { return parseInt($('#inp_' + k).value) > 0; });

                if (allPicked) {
  var opts = [];
  ['gk_id','df1_id','df2_id','mf1_id','mf2_id','fw_id','bench_id'].forEach(function(k){
    var id = parseInt($('#inp_' + k).value);
    if (!id) return;
    var slot = document.querySelector('.slot[data-slot="' + k + '"]');
    var name = slot.querySelector('.name').textContent;
    opts.push({ id:id, name:name, slotKey:k });
  });

  // ❶ запоминаем предыдущий выбор (или серверный, если ещё не выбирали)
  var prev = captainSelect.value || (SERVER_CAPTAIN ? String(SERVER_CAPTAIN) : '');

  // ❷ пересобираем список
  captainSelect.innerHTML = opts.map(function(o){
    return '<option value="'+o.id+'">'+o.name+'</option>';
  }).join('');

  // ❸ восстанавливаем выбор, если он всё ещё валиден
  var hasPrev = opts.some(function(o){ return String(o.id) === prev; });
  var setTo = hasPrev ? prev : (opts[0] ? String(opts[0].id) : '');
  if (setTo) captainSelect.value = setTo;

  captainRow.style.display = '';
  saveBtn.disabled = false;
} else {
  captainRow.style.display = 'none';
  saveBtn.disabled = true;
}

captainSelect.removeEventListener('change', markCaptain);
captainSelect.addEventListener('change', markCaptain);
markCaptain();  // останется на выбранном
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


        </script>
    </div>
</div>

<?php
    $footer_files = array(dirname(__FILE__) . '/blocks/footer.php', dirname(__FILE__) . '/blocks/footer.html');
    foreach ($footer_files as $file) {
        if (file_exists($file)) {
            include $file;
            break;
        }
    }
?>

 <script src="./js/index.bundle.js"></script>

</body>
</html>
