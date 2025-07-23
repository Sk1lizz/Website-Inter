<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// === ФУНКЦИИ ===
function getPaymentAmount($db, $playerId) {
    $stmt = $db->prepare("SELECT amount FROM payments WHERE player_id = ?");
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['amount'] ?? 0;
}

function getPaymentDeadline($teamId) {
    $now = new DateTime();
    $year = (int)$now->format('Y');
    $month = (int)$now->format('n');

    if ($teamId == 1) {
        return new DateTime("$year-$month-10");
    } else {
        $lastDay = new DateTime("last day of $year-$month");
        while ($lastDay->format('N') != 6) {
            $lastDay->modify('-1 day');
        }
        return $lastDay;
    }
}

function formatRussianDay($date) {
    $days = [
        'Monday'    => 'Понедельник',
        'Tuesday'   => 'Вторник',
        'Wednesday' => 'Среда',
        'Thursday'  => 'Четверг',
        'Friday'    => 'Пятница',
        'Saturday'  => 'Суббота',
        'Sunday'    => 'Воскресенье'
    ];
    $dayEn = $date->format('l');
    return $date->format('d.m.Y') . ' (' . ($days[$dayEn] ?? $dayEn) . ')';
}

function getMonthlyFines($db, $playerId) {
    $year = date('Y');
    $month = date('m');

    $stmt = $db->prepare("
        SELECT reason, amount, date 
        FROM fines 
        WHERE player_id = ? 
        AND YEAR(date) = ? 
        AND MONTH(date) = ?
        ORDER BY date DESC
    ");
    $stmt->bind_param("iss", $playerId, $year, $month);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalFineAmount($fines) {
    $sum = 0;
    foreach ($fines as $fine) {
        $sum += (int)$fine['amount'];
    }
    return $sum;
}

// === АВТОРИЗАЦИЯ ===
if (!isset($_SESSION['player_id'])) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['pass'])) {
        $login = $_POST['login'];
        $pass = $_POST['pass'];

        $stmt = $db->prepare("SELECT id, name, team_id, password FROM players WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && $pass === $res['password']) {
            if (in_array($res['team_id'], [1, 2])) {
                $_SESSION['player_id'] = $res['id'];
                $_SESSION['player_name'] = $res['name'];
                $_SESSION['team_id'] = $res['team_id'];
                header("Location: user.php");
                exit;
            } else {
                $error = 'Профиль отключён';
            }
        } else {
            $error = 'Неверный логин или пароль';
        }
    }

    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Вход</title>
    <style>
    body { background: #f3f6fb; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
    .login-box {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        max-width: 320px;
        width: 90%;
        text-align: center;
    }
    .login-box h2 { margin-bottom: 20px; color: #1c3d7d; }
    .login-box input {
        width: 100%;
        padding: 10px;
        margin-bottom: 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .login-box button {
        width: 100%;
        background: #083c7e;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
    }
    .login-box .error { color: red; margin-bottom: 10px; }
    </style>
     <meta name="viewport" content="width=device-width, initial-scale=1">
    </head><body>
    <div class="login-box">
        <h2>Личный кабинет</h2>';
    if (!empty($error)) echo '<div class="error">' . $error . '</div>';
    echo '<form method="post">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="pass" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form></div></body></html>';
    exit;
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: user.php");
    exit;
}

// === Данные ===
$amount = getPaymentAmount($db, $_SESSION['player_id']);
$deadline = getPaymentDeadline($_SESSION['team_id']);
$fines = getMonthlyFines($db, $_SESSION['player_id']);
$fineTotal = getTotalFineAmount($fines);
$includeFines = $fineTotal >= 299;
$totalToPay = $amount + ($includeFines ? $fineTotal : 0);
$deadlineStr = formatRussianDay($deadline);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
     <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/img/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/img/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="apple-touch-icon" href="/img/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" sizes="192x192" href="/img/android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="/img/android-chrome-512x512.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Кабинет игрока</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="user_page">
  <div class="top-wrapper">
    <div class="header-buttons">
      <button type="button" onclick="document.getElementById('changePasswordModal').style.display='block'">
        Сменить пароль
      </button>
      <form method="POST" style="margin: 0;">
        <button type="submit" name="logout">Выйти</button>
      </form>
    </div>
  </div>

  <div id="changePasswordModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
      background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.3); z-index:1000;">
    <h3>Смена пароля</h3>
    <form method="POST" action="change_password.php">
      <label>Старый пароль:<br><input type="password" name="old_password" required></label><br><br>
      <label>Новый пароль:<br><input type="password" name="new_password" required></label><br><br>
      <button type="submit">Сменить</button>
      <button type="button" onclick="document.getElementById('changePasswordModal').style.display='none'">Отмена</button>
    </form>
  </div>

  <h1 style="text-align:center">Добро пожаловать, <?= htmlspecialchars($_SESSION['player_name']) ?>!</h1>

  <div class="dashboard-grid">
    <div class="left-column">
      <div class="card">
        <h2>Месячный взнос</h2>
        <p><strong>Взнос за месяц:</strong> <?= number_format($amount, 2, '.', ' ') ?> ₽</p>
        <p><strong>Штрафы за месяц:</strong> <?= $fineTotal ?> ₽</p>
        <p><strong>Итого к оплате:</strong> <?= number_format($totalToPay, 2, '.', ' ') ?> ₽</p>
        <p><strong>Дедлайн:</strong> <?= $deadlineStr ?></p>
      </div>

      <div class="card">
        <h2>Штрафы в этом месяце</h2>
        <?php if (count($fines) === 0): ?>
          <p>Так держать — штрафов нет!</p>
        <?php else: ?>
          <table class="attendance-table">
            <thead><tr><th>Дата</th><th>Причина</th><th>Сумма</th></tr></thead>
            <tbody>
              <?php foreach ($fines as $fine): 
                $highlight = ((int)$fine['amount'] >= 299) ? 'highlight-fine' : '';
              ?>
                <tr class="<?= $highlight ?>">
                  <td><?= date('d.m.Y', strtotime($fine['date'])) ?></td>
                  <td><?= htmlspecialchars($fine['reason']) ?></td>
                  <td><?= $fine['amount'] ?> ₽</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
        <p style="margin-top:10px; font-size: 13px; color: var(--gray-light);">
          Штрафы суммой менее 299 ₽ сгорают в конце месяца.
        </p>
      </div>
    </div>

    <div class="right-column">
      <div class="card">
        <h2>Моя посещаемость</h2>
        <script>const PLAYER_ID = <?= (int)$_SESSION['player_id'] ?>;</script>

        <select id="monthSelect"></select>
        <table class="attendance-table" id="attendanceTable">
            <thead><tr><th>Дата</th><th>Статус</th></tr></thead>
            <tbody></tbody>
        </table>
        <p><strong>Процент посещаемости:</strong> <span id="percent">0%</span></p>
<p id="feedback" style="font-weight:bold; color:#FFFFFF;"></p>
        
      </div>
    </div>
  </div>
</div>

<script>
const STATUS_MAP = {
    0: '– Не был',
    1: '+ Присутствовал',
    2: 'О Отпуск',
    3: 'Т Травма',
    4: 'Б Болел'
};

async function fetchAttendance() {
    const res = await fetch(`/api/get_attendance.php?player_id=${PLAYER_ID}`);
    return await res.json();
}

function fillMonthSelector(data) {
    const select = document.getElementById('monthSelect');
    const uniqueMonths = [...new Set(data.map(d => d.training_date.slice(0, 7)))];
    uniqueMonths.sort().reverse();
    select.innerHTML = uniqueMonths.map(month => {
        const label = new Date(month + "-01").toLocaleDateString("ru-RU", { month: 'long', year: 'numeric' });
        return `<option value="${month}">${label}</option>`;
    }).join('');
}

function renderAttendance(data, selectedMonth) {
    const tbody = document.querySelector('#attendanceTable tbody');
    const percentEl = document.getElementById('percent');
    const feedbackEl = document.getElementById('feedback');
    tbody.innerHTML = '';

    const filtered = data.filter(d => d.training_date.startsWith(selectedMonth));
    
    // Подсчёт только по статусам 0 и 1
    const countable = filtered.filter(d => d.status === 0 || d.status === 1);
    const present = countable.filter(d => d.status === 1).length;
    const total = countable.length;

    for (const row of filtered) {
        const date = new Date(row.training_date).toLocaleDateString('ru-RU');
        const status = STATUS_MAP[row.status] || '—';
        const className = `status-${row.status}`;
        tbody.innerHTML += `<tr><td>${date}</td><td class="${className}">${status}</td></tr>`;
    }

    const percent = total ? Math.round((present / total) * 100) : 0;
    percentEl.textContent = percent + '%';

    // Добавляем фразу
    let message = '';
    if (percent < 50) message = 'Надо поднажать';
    else if (percent < 75) message = 'Неплохо!';
    else message = 'Превосходно!';
    feedbackEl.textContent = message;
}

document.addEventListener("DOMContentLoaded", async () => {
    const data = await fetchAttendance();
    if (!data.length) return;

    fillMonthSelector(data);
    const currentMonth = new Date().toISOString().slice(0, 7);
    document.getElementById('monthSelect').value = currentMonth;
    renderAttendance(data, currentMonth);

    document.getElementById('monthSelect').addEventListener('change', e => {
        renderAttendance(data, e.target.value);
    });
});
</script>

</body>
</html>
