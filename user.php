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

function getAllFines($db, $playerId) {
    $stmt = $db->prepare("
        SELECT reason, amount, date 
        FROM fines 
        WHERE player_id = ?
        ORDER BY date DESC
    ");
    $stmt->bind_param("i", $playerId);
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

    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">  <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/img/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/img/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="apple-touch-icon" href="/img/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" sizes="192x192" href="/img/android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="/img/android-chrome-512x512.png"> <title>Вход</title>
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
$fines = getAllFines($db, $_SESSION['player_id']);
$fineTotal = getTotalFineAmount($fines);
$includeFines = $fineTotal >= 299;
$totalToPay = $amount + ($includeFines ? $fineTotal : 0);
$deadlineStr = formatRussianDay($deadline);

// === Данные физической формы игрока ===
$stmt = $db->prepare("SELECT height_cm, weight_kg FROM players WHERE id = ?");
$stmt->bind_param("i", $_SESSION['player_id']);
$stmt->execute();
$phys = $stmt->get_result()->fetch_assoc();

$height_cm = (float)($phys['height_cm'] ?? 0);
$weight_kg = (float)($phys['weight_kg'] ?? 0);

$height_m = $height_cm / 100;
$bmi = $height_m > 0 ? round($weight_kg / ($height_m * $height_m), 1) : 0;
$ideal_weight = $height_m > 0 ? round(22 * $height_m * $height_m, 1) : 0;

$bmi_feedback = '';
if ($bmi < 18.5) $bmi_feedback = 'Недостаток веса';
elseif ($bmi < 25) $bmi_feedback = 'Норма';
elseif ($bmi < 30) $bmi_feedback = 'Избыточный вес';
else $bmi_feedback = 'Ожирение';

// Границы шкалы
$min_weight = round($ideal_weight * 0.8, 1);
$max_weight = round($ideal_weight * 1.2, 1);

// Динамические границы зоны нормы ±7%
$range_from = round($ideal_weight * 0.93, 1);
$range_to   = round($ideal_weight * 1.07, 1);

// Позиции в процентах
$range_from_percent = 100 * ($range_from - $min_weight) / ($max_weight - $min_weight);
$range_to_percent   = 100 * ($range_to - $min_weight) / ($max_weight - $min_weight);
$weight_percent     = 100 * ($weight_kg - $min_weight) / ($max_weight - $min_weight);

$stmt = $db->prepare("SELECT background_key, can_change_background FROM player_backgrounds WHERE player_id = ?");
$stmt->bind_param("i", $_SESSION['player_id']);
$stmt->execute();
$bg = $stmt->get_result()->fetch_assoc() ?? ['background_key' => '', 'can_change_background' => 0];
$currentBgKey = $bg['background_key'];
$canChangeBackground = (int)$bg['can_change_background'];

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

  <title>Кабинет игрока</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/main.css">
</head>

 <?php include 'headerlk.html'; ?>

<body>
<div class="user_page">
  

  <h1 style="text-align:center">Добро пожаловать, <?= htmlspecialchars($_SESSION['player_name']) ?>!</h1>

  <div class="dashboard-grid">
    <!-- Слева -->
    <div class="left-column">

    <div class="card" id="advStatsCard">
  <h2>Продвинутая статистика</h2>
  <div id="advStatsBody">Загрузка…</div>
</div>

      <div class="card">
        <h2>Месячный взнос</h2>
        <p><strong>Взнос за месяц:</strong> <?= number_format($amount, 2, '.', ' ') ?> ₽</p>
        <p><strong>Штрафы за месяц:</strong> <?= $fineTotal ?> ₽</p>
        <p><strong>Итого к оплате:</strong> <?= number_format($totalToPay, 2, '.', ' ') ?> ₽</p>
        <p><strong>Дедлайн:</strong> <?= $deadlineStr ?></p>
         <p><strong>Реквизиты Pro: 4276 4000 6388 7252</strong></p>
          <p><strong>Реквизиты 8х8: 5536 9137 8962 1493</strong></p>
      </div>
      <div class="card">
        <h2>Все штрафы</h2>
        <?php if (count($fines) === 0): ?><p>Так держать — штрафов нет!</p>
        <?php else: ?>
          <table class="attendance-table"><thead><tr><th>Дата</th><th>Причина</th><th>Сумма</th></tr></thead><tbody>
          <?php foreach ($fines as $fine): $highlight = ((int)$fine['amount'] >= 299) ? 'highlight-fine' : ''; ?>
            <tr class="<?= $highlight ?>">
              <td><?= date('d.m.Y', strtotime($fine['date'])) ?></td>
              <td><?= htmlspecialchars($fine['reason']) ?></td>
              <td><?= $fine['amount'] ?> ₽</td>
            </tr>
          <?php endforeach; ?>
          </tbody></table>
        <?php endif; ?>
      </div>

      <div class="card">
  <h2>Моя форма</h2>
  <p><strong>Индекс массы тела (BMI):</strong> <?= $bmi ?> (<?= $bmi_feedback ?>)</p>
  <p><strong>Мой вес:</strong> <?= $weight_kg ?> кг</p>
  <p><strong>Мой рост:</strong> <?= $height_cm ?> см</p>
  <p><strong>Мой идеальный вес:</strong> <?= $ideal_weight ?> кг</p>

  <?php
    $weight_percent = $ideal_weight > 0 ? min(100, max(0, round(($weight_kg - $min_weight) / ($max_weight - $min_weight) * 100))) : 50;
  ?>
 <div id="bmi-bar">
  <div class="bmi-fill"></div>
  <!-- Синяя зона идеального веса -->
  <div class="bmi-range" style="left: <?= (float)$range_from_percent ?>%; width: <?= (float)($range_to_percent - $range_from_percent) ?>%;"></div>
  <!-- Маркер текущего веса -->
  <div class="bmi-marker" style="left: <?= (float)$weight_percent ?>%;"></div>
  <!-- Подписи -->
  <div class="bmi-label left"><?= $min_weight ?> кг</div>
  <div class="bmi-label right"><?= $max_weight ?> кг</div>
  <div class="bmi-label mid1" style="left: <?= (float)$range_from_percent ?>%;"><?= (float)$range_from ?> кг</div>
  <div class="bmi-label mid2" style="left: <?= (float)$range_to_percent ?>%;"><?= (float)$range_to ?> кг</div>
</div>
  <button id="changeWeightButton" onclick="document.getElementById('modal_weight').style.display='flex'">Изменить вес</button>
<button id="changeHeightButton" onclick="document.getElementById('modal_height').style.display='flex'">Изменить рост</button>

</div>

<div class="card">
  <h2>Моё здоровье</h2>
  <p><strong>Дата последнего ЭКГ:</strong> <span id="lastEkgDate">Данные не указаны</span></p>
  <p><strong>Времени с последнего ЭКГ:</strong> <span id="ekgElapsed">—</span></p>
  <p><strong>Рекомендация:</strong> <span id="ekgRecommendation">—</span></p>

  <button id="editHealthButton" onclick="document.getElementById('editHealthModal').style.display='flex'">Редактировать</button>
</div>

    </div>

    <!-- Справа -->
    <div class="right-column">
      <div class="card">
        <h2>Моя посещаемость</h2>
        <script>
  const PLAYER_ID = <?= (int)$_SESSION['player_id'] ?>;
  const TEAM_ID = <?= (int)$_SESSION['team_id'] ?>;

  document.addEventListener("DOMContentLoaded", () => {
    loadHealth();           // ЗАГРУЗИТЬ ЗДОРОВЬЕ
    loadVacationStatus();   // (если используется)
  });
</script>
        <select id="monthSelect"></select>
        <table class="attendance-table" id="attendanceTable">
  <thead>
    <tr><th>Дата</th><th>Статус</th><th>Рейтинг</th></tr>
  </thead>
  <tbody></tbody>
</table>
        <p><strong>Процент посещаемости:</strong> <span id="percent">0%</span></p>
        <p id="feedback" style="font-weight:bold;"></p>
        <p><strong>Средний тренировочный рейтинг за месяц:</strong> <span id="monthlyTrainAvg">—</span></p>
        <div id="rateTrainingWrap" style="margin-top:10px;">
  <button id="rateTrainingButton" onclick="openRateTrainingModal()">Оценить предыдущую тренировку</button>
  <p id="rateTrainingHint" style="margin-top:6px; font-size:12px; color:#666;"></p>
</div>
      </div>

      <div class="card">
  <h2>Матчи за месяц</h2>
  <table class="attendance-table" id="matchStatsTable">
    <thead>
      <tr><th>Дата</th><th>Играл</th><th>Г</th><th>А</th><th>ПМ</th><th>Рейтинг</th><th>Оценка</th></tr>
    </thead>
    <tbody></tbody>
  </table>
  <p><strong>Процент участия:</strong> <span id="matchParticipation">0%</span></p>
</div>

      <div class="card">
  <h2>Мой отпуск</h2>
  <p id="vacationInfo">Загрузка информации об отпуске...</p>
  <button id="openVacationModal">Запланировать отпуск</button>
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
  const monthlyAvgEl = document.getElementById('monthlyTrainAvg');

  tbody.innerHTML = '';

  const filtered = data.filter(d => d.training_date.startsWith(selectedMonth));

  // присутствие/процент
  const countable = filtered.filter(d => d.status === 0 || d.status === 1);
  const present = countable.filter(d => d.status === 1).length;
  const total = countable.length;

  // рендер строк
  for (const row of filtered) {
    const date = new Date(row.training_date).toLocaleDateString('ru-RU');
    const status = STATUS_MAP[row.status] || '—';
    const className = `status-${row.status}`;

    const ratingCell = (row.status === 1 && row.rating != null)
      ? Number(row.rating).toFixed(1)
      : '—';

    tbody.innerHTML += `<tr>
      <td>${date}</td>
      <td class="${className}">${status}</td>
      <td style="text-align:center;">${ratingCell}</td>
    </tr>`;
  }

  // процент
  const percent = total ? Math.round((present / total) * 100) : 0;
  percentEl.textContent = percent + '%';
  feedbackEl.textContent = percent < 50 ? 'Надо поднажать' : (percent < 75 ? 'Неплохо!' : 'Превосходно!');

  // СРЕДНИЙ тренировочный рейтинг за месяц: только присутствия с НЕ NULL rating
  const monthRatings = filtered
    .filter(r => r.status === 1 && r.rating != null)
    .map(r => Number(r.rating));

  const monthAvg = monthRatings.length
    ? (monthRatings.reduce((a,b)=>a+b, 0) / monthRatings.length)
    : null;

  if (monthlyAvgEl) {
    monthlyAvgEl.textContent = monthAvg !== null ? monthAvg.toFixed(1) : '—';
  }
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

<?php if ($canChangeBackground === 1): ?>
<div id="user_bg-modal_background" class="user_bg-modal_background">
  <div class="modal-content">
    <h3>Выберите фон</h3>
    <div class="background-options">
      <div class="bg-option" onclick="setBackground('')">
        <div class="no-image"></div>
        <small>Без фона</small>
      </div>
      <?php
      $backgrounds = [
          '1' => 'Полосы рваные',
          '2' => 'Стена',
          '3' => 'Соты',
          '4' => 'Золото',
          '5' => 'Дракон',
          '6' => 'Кремль',
          '7' => 'Инь и Янь',
          '8' => 'Самурай',
          '9' => 'Город, дождь',
          '10' => 'Волна',
          '11' => 'Джунгли',
          '12' => 'Переулок',
      ];
      foreach ($backgrounds as $key => $label): ?>
        <div class="bg-option" onclick="setBackground('<?= $key ?>')">
          <img src="/img/background_player/mini<?= $key ?>.PNG" alt="фон <?= $key ?>">
          <small><?= $label ?></small>
        </div>
      <?php endforeach; ?>
    </div>
    <button onclick="document.getElementById('user_bg-modal_background').style.display='none'">Отмена</button>
  </div>
</div>
<?php endif; ?>


<script>
function setBackground(key) {
  fetch('/api/player_set_background.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ background_key: key })
  }).then(res => res.json()).then(data => {
    if (data.success) {
      alert("Фон обновлён");
      location.reload();
    } else {
      alert("Ошибка: " + (data.message || "неизвестно"));
    }
  });
}
</script>

<script>
function closeVacationModal() {
  document.getElementById('vacationModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
  const btnOpenVac = document.getElementById('openVacationModal');
  if (btnOpenVac) {
    btnOpenVac.addEventListener('click', () => {
      const m = document.getElementById('vacationModal');
      if (m) m.style.display = 'flex';
    });
  }
});

async function loadVacationStatus() {
  const res = await fetch(`/api/player_vacation_status.php?player_id=${PLAYER_ID}`);
  const data = await res.json();

  const info = document.getElementById('vacationInfo');
  const openBtn = document.getElementById('openVacationModal');

  if (data.already_on_vacation) {
    info.textContent = "Вы уже брали отпуск в этом году.";
    openBtn.disabled = true;
    openBtn.style.opacity = 0.5;
    return;
  }

  info.textContent = "Отпуск доступен для планирования.";
  openBtn.disabled = false;

  const monthSelect = document.getElementById('vacationMonth');
  const slotsInfo = document.getElementById('vacationSlotsInfo');
  monthSelect.innerHTML = '';
  
const today = new Date();
today.setHours(0, 0, 0, 0); // убираем время, чтобы сравнение шло только по датам

const now = new Date();
let monthsAdded = 0;
let i = 0;

while (monthsAdded < 2 && i < 6) {
  const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
  const daysBefore = (d - now) / (1000 * 60 * 60 * 24);

  if (daysBefore >= 10) {
    const yyyyMM = `${d.getFullYear()}${String(d.getMonth() + 1).padStart(2, '0')}`;
    const label = d.toLocaleString('ru-RU', { month: 'long', year: 'numeric' });

    const option = document.createElement('option');
    option.value = yyyyMM;
    option.textContent = label;
    monthSelect.appendChild(option);
    monthsAdded++;
  }

  i++;
}

  if (monthSelect.options.length === 0) {
    monthSelect.innerHTML = '<option>Нет доступных месяцев</option>';
    document.getElementById('confirmVacationBtn').disabled = true;
  } else {
    updateSlots();
  }

  monthSelect.addEventListener('change', updateSlots);

  async function updateSlots() {
    const month = monthSelect.value;
    const res = await fetch(`/api/get_holiday_slots.php?team_id=${TEAM_ID}&month=${month}`);
    const data = await res.json();
    const used = data.count ?? 0;

    slotsInfo.textContent = `Свободных слотов: ${Math.max(0, 3 - used)} из 3`;

    const btn = document.getElementById('confirmVacationBtn');
    btn.disabled = used >= 3;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const confirmBtn = document.getElementById('confirmVacationBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      const month = document.getElementById('vacationMonth').value;
      if (!month) return;

      const res = await fetch('/api/set_holiday.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ player_id: PLAYER_ID, month })
      });

      const result = await res.json();
      if (result.success) {
        alert("Отпуск успешно запланирован!");
        location.reload();
      } else {
        alert("Ошибка: " + (result.message || 'неизвестно'));
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  window.TEAM_ID = <?= (int)$_SESSION['team_id'] ?>;
  loadVacationStatus();
});
</script>

<script>
  async function fetchMatchStats() {
    const res = await fetch(`/api/get_match_stats.php?player_id=${PLAYER_ID}&team_id=${TEAM_ID}`);
    return await res.json();
}

function renderMatchStats(data) {
  const tbody = document.querySelector('#matchStatsTable tbody');
  tbody.innerHTML = '';

  const now = new Date();
  const thisMonth = now.getMonth(); // август = 7
  const thisYear = now.getFullYear(); // 2025

  // Матчи текущего месяца
  const currentMonthMatches = data.filter(match => {
    const date = new Date(match.date);
    return date.getFullYear() === thisYear && date.getMonth() === thisMonth;
  });

  // Последний день предыдущего месяца
const lastDayPrevMonth = new Date(thisYear, thisMonth, 0);
const lastSaturday = new Date(lastDayPrevMonth.getTime());
while (lastSaturday.getDay() !== 6) {
  lastSaturday.setDate(lastSaturday.getDate() - 1);
}
let lastSaturdayStr = lastSaturday.toISOString().slice(0, 10);

console.log('lastSaturday:', lastSaturday.toDateString());      // должно быть: Sat Jul 26 2025
console.log('lastSaturdayStr:', lastSaturdayStr);               // должно быть: 2025-07-26

  // Находим матч, состоявшийся в эту дату или позже (в прошлом месяце)
  let lastPrevMonthMatch = null;
  for (let i = data.length - 1; i >= 0; i--) {
    const match = data[i];
    const dateStr = match.date.slice(0, 10); // в формате YYYY-MM-DD
    const matchDate = new Date(dateStr);

    console.log('Проверка матча:', match.date.slice(0, 10), '>=', lastSaturdayStr, '?');
    if (match.date.slice(0, 10) >= lastSaturdayStr && match.date.slice(0, 7) < now.toISOString().slice(0, 7)) {
  lastPrevMonthMatch = match;
  break;
}
  }

  const finalMatches = lastPrevMonthMatch
    ? [lastPrevMonthMatch, ...currentMonthMatches]
    : currentMonthMatches;

  let playedCount = 0;
  for (const match of finalMatches) {
    if (match.played) playedCount++;

   tbody.innerHTML += `
  <tr>
    <td>${new Date(match.date).toLocaleDateString('ru-RU')}</td>
    <td>${match.played ? 'Да' : 'Нет'}</td>
    <td class="match-icon">${match.goals > 0 ? `<img src="/img/icon/goal.svg" title="Гол">×${match.goals}` : ''}</td>
    <td class="match-icon">${match.assists > 0 ? `<img src="/img/icon/assist.svg" title="Ассист">×${match.assists}` : ''}</td>
    <td class="match-icon">${match.goals_conceded > 0 ? `<img src="/img/icon/form.svg" title="Пропущено">×${match.goals_conceded}` : ''}</td>
    <td>${match.average_rating !== null ? match.average_rating.toFixed(1) : '-'}</td>
    <td>
  ${match.played && match.can_rate 
    ? `<button class="match-rate-btn" data-match-id="${match.id}" onclick="openRatingModal(${match.id})">Оценка</button>` 
    : ''}
</td>
  </tr>`;
  }
  

  const percent = finalMatches.length
    ? Math.round((playedCount / finalMatches.length) * 100)
    : 0;

  document.getElementById('matchParticipation').textContent = `${percent}%`;
}

document.addEventListener("DOMContentLoaded", async () => {
    const matchStats = await fetchMatchStats();
    renderMatchStats(matchStats);
});

</script>

<script>
  async function loadHealth() {
  const res = await fetch(`/api/get_health.php?player_id=${PLAYER_ID}`);
  const data = await res.json();

  const lastEkg = new Date(data.last_ekg_date);
  const hasCondition = data.has_heart_condition == 1; // строго сравнение с числом
  const today = new Date();

  const diffMonths = (today.getFullYear() - lastEkg.getFullYear()) * 12 + (today.getMonth() - lastEkg.getMonth());

  document.getElementById('lastEkgDate').textContent = lastEkg.toLocaleDateString('ru-RU');
  document.getElementById('ekgElapsed').textContent = `${diffMonths} мес.`;

  const spanRec = document.getElementById('ekgRecommendation');
  spanRec.className = 'health-recommendation'; // сброс классов

  const maxMonths = hasCondition ? 6 : 12;

  if (diffMonths >= maxMonths) {
    spanRec.textContent = 'Вам нужно провериться — обследование просрочено';
    spanRec.classList.add('danger');
  } else if (diffMonths >= maxMonths - 2) {
    spanRec.textContent = 'Пора записаться на плановую проверку';
    spanRec.classList.add('warning');
  } else {
    spanRec.textContent = 'Всё в порядке';
    spanRec.classList.add('ok');
  }
}

</script>

<script>
  async function openRatingModal(matchId) {
  const res = await fetch(`/api/get_match_players.php?match_id=${matchId}`);
  const players = await res.json();

  const list = document.getElementById('playerRatingList');
  list.innerHTML = '';

  players.forEach(player => {
  if (player.id === PLAYER_ID) return;
  if (player.position === 'Тренер') return;

  const wrapper = document.createElement('div');
  wrapper.classList.add('player-rating-item');
  wrapper.innerHTML = `
    <label>${player.name}:</label>
    <input type="range" min="3.0" max="10.0" step="0.1" value="7.0" 
           name="rating_${player.id}" oninput="this.nextElementSibling.textContent = this.value">
    <span class="rating-value">7.0</span>
  `;
  list.appendChild(wrapper);
});

  document.getElementById('rateMatchModal').style.display = 'flex';

  // Подготовка формы
  const form = document.getElementById('ratingForm');
  form.onsubmit = async (e) => {
    e.preventDefault();

    const data = {
      match_id: matchId,
      ratings: []
    };

    players.forEach(player => {
      const input = form.querySelector(`[name="rating_${player.id}"]`);
      if (input && player.id !== PLAYER_ID && player.position !== 'Тренер') {
        data.ratings.push({
          target_player_id: player.id,
          rating: parseFloat(input.value)
        });
      }
    });

    const saveRes = await fetch('/api/save_ratings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const result = await saveRes.json();

if (result.success) {
  alert('Оценки сохранены!');
  document.getElementById('rateMatchModal').style.display = 'none';

  // Делаем кнопку неактивной после голосования
  const rateBtn = document.querySelector(`.match-rate-btn[data-match-id="${matchId}"]`);
  if (rateBtn) {
    rateBtn.disabled = true;
    rateBtn.textContent = 'Оценено';
    rateBtn.classList.add('disabled-rating-btn');
  }
} else {
  alert('Ошибка сохранения');
}
  };
}

</script>

<div id="changePasswordModal" class="user_password-modal">
  <div class="modal-content">
    <h3>Смена пароля</h3>
    <form method="POST" action="change_password.php">
      <label>Старый пароль:</label>
      <input type="password" name="old_password" required>

      <label>Новый пароль:</label>
      <input type="password" name="new_password" required>

      <div class="modal-buttons">
        <button type="submit">Сменить</button>
        <button type="button" onclick="document.getElementById('changePasswordModal').style.display='none'">Отмена</button>
      </div>
    </form>
  </div>
</div>

<div id="vacationModal" class="user_password-modal">
  <div class="modal-content">
    <h3>Запланировать отпуск</h3>
    <p style="margin-bottom: 10px;">Вы можете уйти в отпуск 1 раз в год на срок не более 1 месяца, при свободных слотах под отпуск.</p>
    <label for="vacationMonth">Месяц отпуска:</label>
    <select id="vacationMonth"></select>
    <p id="vacationSlotsInfo" style="margin: 10px 0;"></p>
    <div class="modal-buttons">
      <button id="confirmVacationBtn">Подтвердить</button>
      <button onclick="closeVacationModal()">Отмена</button>
    </div>
  </div>
</div>

<div id="modal_weight" class="user_password-modal">
  <div class="modal-content">
    <h3>Изменить вес</h3>
    <form method="POST" action="/api/update_weight.php">
      <label>Новый вес (кг):</label>
      <input type="number" name="weight" min="40" max="200" step="0.1" required>
      <div class="modal-buttons">
        <button type="submit">Сохранить</button>
        <button type="button" onclick="document.getElementById('modal_weight').style.display='none'">Отмена</button>
      </div>
    </form>
  </div>
</div>

<div id="modal_height" class="user_password-modal">
  <div class="modal-content">
    <h3>Изменить рост</h3>
    <form method="POST" action="/api/update_weight.php">
      <label>Новый рост (см):</label>
      <input type="number" name="height" min="100" max="250" step="1" required>
      <div class="modal-buttons">
        <button type="submit">Сохранить</button>
        <button type="button" onclick="document.getElementById('modal_height').style.display='none'">Отмена</button>
      </div>
    </form>
  </div>
</div>

<div id="editHealthModal" class="user_password-modal">
  <div class="modal-content">
    <h3>Обновить данные ЭКГ</h3>
    <form id="healthForm">
      <label>Дата последнего ЭКГ:</label>
      <input type="date" name="last_ekg_date" required>

     <div class="checkbox-wrapper">
  <label>
    <input type="checkbox" name="has_heart_condition">
    У меня есть сердечно-сосудистые заболевания
  </label>
</div>

      <div class="modal-buttons">
        <button type="submit">Сохранить</button>
        <button type="button" onclick="document.getElementById('editHealthModal').style.display='none'">Отмена</button>
      </div>
    </form>
  </div>
</div>

<div id="rateMatchModal" class="user_password-modal">
  <div class="modal-content" id="rateMatchModalContent">
    <h3>Оцените игроков</h3>
    <form id="ratingForm">
      <div id="playerRatingList"></div>
      <div class="modal-buttons">
        <button type="submit" style="font-size: 14px; padding: 8px;">Сохранить</button>
        <button type="button" onclick="document.getElementById('rateMatchModal').style.display='none'" style="font-size: 14px; padding: 8px;">Отмена</button>
      </div>
    </form>
  </div>
</div>

<div id="rateTrainingModal" class="user_password-modal">
  <div class="modal-content">
    <h3>Оценить тренировку <span id="rateTrainDate"></span></h3>

    <div class="player-rating-item">
      <label>Интенсивность (насколько было тяжело)</label>
      <input type="range" min="1" max="5" step="1" value="3" id="rt_intensity" oninput="document.getElementById('rt_intensity_v').textContent=this.value">
      <span class="rating-value" id="rt_intensity_v">3</span>
    </div>

    <div class="player-rating-item">
      <label>Усталость после тренировки</label>
      <input type="range" min="1" max="5" step="1" value="3" id="rt_fatigue" oninput="document.getElementById('rt_fatigue_v').textContent=this.value">
      <span class="rating-value" id="rt_fatigue_v">3</span>
    </div>

    <div class="player-rating-item">
      <label>Настроение во время тренировки</label>
      <input type="range" min="1" max="5" step="1" value="3" id="rt_mood" oninput="document.getElementById('rt_mood_v').textContent=this.value">
      <span class="rating-value" id="rt_mood_v">3</span>
    </div>

    <div class="player-rating-item">
      <label>Удовольствие от процесса</label>
      <input type="range" min="1" max="5" step="1" value="3" id="rt_enjoyment" oninput="document.getElementById('rt_enjoyment_v').textContent=this.value">
      <span class="rating-value" id="rt_enjoyment_v">3</span>
    </div>

    <div class="modal-buttons">
      <button id="rt_submitBtn">Сохранить</button>
      <button type="button" onclick="document.getElementById('rateTrainingModal').style.display='none'">Отмена</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('healthForm');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = {
        player_id: PLAYER_ID,
        last_ekg_date: form.last_ekg_date.value,
        has_heart_condition: form.has_heart_condition.checked ? 1 : 0
      };
      const res = await fetch('/api/set_health.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(data)
      });
      const out = await res.json();
      if (out.success) {
        alert('Информация обновлена');
        document.getElementById('editHealthModal').style.display='none';
        loadHealth();
      } else {
        alert('Ошибка: ' + (out.message || 'неизвестно'));
      }
    });
  }

  const btnVacation = document.getElementById('openVacationModal');
  if (btnVacation) {
    btnVacation.addEventListener('click', () => {
      document.getElementById('vacationModal').style.display='flex';
    });
  }
});
</script>


<script>
async function loadAdvancedStats() {
  const box = document.getElementById('advStatsBody');
  if (!box) return;

  try {
    const res = await fetch('/api/get_advanced_stats.php', { credentials: 'same-origin' });
    const txt = await res.text();

    let json = null;
    try { json = JSON.parse(txt); } catch (_) {}

    if (!res.ok || !json || json.success === false) {
      console.error('API error:', res.status, txt);
      box.textContent = 'Не удалось загрузить статистику';
      return;
    }

    const d = json.data, t = d.totals, r = d.ranks, isGK = !!d.is_gk;

    const rows = [
      `<tr><td>Матчи</td><td>${t.matches}</td><td>${t.avg_goals_per_match !== null ? t.avg_goals_per_match : '—'}</td><td>${r.team.matches}</td><td>${r.all_time.matches}</td></tr>`,
      `<tr><td>Голы</td><td>${t.goals}</td><td>${t.avg_goals_per_match}</td><td>${r.team.goals}</td><td>${r.all_time.goals}</td></tr>`,
      `<tr><td>Ассисты</td><td>${t.assists}</td><td>${t.avg_assists_per_match}</td><td>${r.team.assists}</td><td>${r.all_time.assists}</td></tr>`,
      `<tr><td>Матчи на ноль</td><td>${t.zeromatch}</td><td>${t.avg_zeromatch_per_match}</td><td>${isGK && r.team.zeromatch !== '-' ? r.team.zeromatch : '—'}</td><td>${isGK && r.all_time.zeromatch !== '-' ? r.all_time.zeromatch : '—'}</td></tr>`
    ];
    if (isGK) {
      rows.push(
        `<tr><td>Голов пропущено</td><td>${t.lostgoals}</td><td>${t.avg_conceded_per_match ?? '—'}</td><td>${r.team.lostgoals !== '-' ? r.team.lostgoals : '—'}</td><td>${r.all_time.lostgoals !== '-' ? r.all_time.lostgoals : '—'}</td></tr>`
      );
    }

    box.innerHTML = `
      <table class="attendance-table">
        <thead><tr>
          <th>Показатель</th><th>Количество</th><th>В среднем за матч</th><th>Место в команде</th><th>Место за всё время</th>
        </tr></thead>
        <tbody>${rows.join('')}</tbody>
      </table>
      ${isGK && (t.avg_conceded_per_match === null || t.matches < 15)
        ? '<p style="margin-top:8px;font-size:12px;color:#666;">* «Средне пропущено/матч» и ранги показываются для вратарей с ≥ 15 матчей.</p>'
        : ''}
      <div id="adv-extra-ratings" style="margin-top:10px;">
        <p><strong>Мой тренировочный рейтинг (средний):</strong> <span id="advTrainAvg">—</span></p>
        <p><strong>Мой игровой рейтинг:</strong> <span id="advMatchAvg">—</span></p>
      </div>
    `;

    // Догружаем два средних параллельно
    const [trainRes, matchRes] = await Promise.all([
      fetch(`/api/get_training_rating_avg.php?player_id=${PLAYER_ID}`, { credentials: 'same-origin' }),
      fetch(`/api/get_match_rating_avg.php?player_id=${PLAYER_ID}`, { credentials: 'same-origin' })
    ]);

    let trainJson = null, matchJson = null;
    try { trainJson = await trainRes.json(); } catch { trainJson = { success: false }; }
    try { matchJson = await matchRes.json(); } catch { matchJson = { success: false }; }

    const trainAvgEl = document.getElementById('advTrainAvg');
    const matchAvgEl = document.getElementById('advMatchAvg');

    if (trainAvgEl) {
  // сначала пробуем avg_all_time (как отдаёт ваш API без month),
  // если его нет — используем avg
  const v =
    (trainJson && trainJson.success && trainJson.avg_all_time != null)
      ? Number(trainJson.avg_all_time)
      : (trainJson && trainJson.success && trainJson.avg != null)
       ? Number(trainJson.avg)
        : null;
   trainAvgEl.textContent = (v != null && !isNaN(v)) ? v.toFixed(2) : '—';
 }

    if (matchAvgEl) {
  const v = (matchJson && matchJson.success && matchJson.avg != null)
     ? Number(matchJson.avg)   // сервер уже присылает округлённое значение
     : null;
   matchAvgEl.textContent = (v != null && !isNaN(v)) ? v.toFixed(2) : '—';
}

  } catch (e) {
    console.error(e);
    const box = document.getElementById('advStatsBody');
    if (box) box.textContent = 'Не удалось загрузить статистику';
  }
}
document.addEventListener('DOMContentLoaded', loadAdvancedStats);
</script>

<script>
let RT_TRAINING_ID = null;

async function fetchPreviousTrainForRating() {
  try {
    const res = await fetch('/api/get_previous_training.php', { credentials:'same-origin' });
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch(e) {
      console.error('API non-JSON:', text.slice(0,500));
      return { success:false };
    }
  } catch(e) {
    console.error(e);
    return { success:false };
  }
}

function setupRateTraining() {
  const btn  = document.getElementById('rateTrainingButton');
  const hint = document.getElementById('rateTrainingHint');
  if (!btn) return;
  btn.disabled = true;

  fetchPreviousTrainForRating().then(data => {
    if (!data || data.success !== true) {
      hint.textContent = 'Не удалось проверить возможность оценки.';
      btn.style.opacity = 0.6;
      return;
    }
    if (!data.can_rate) {
      hint.textContent = 'Нет тренировок для оценки.';
      btn.disabled = true; btn.style.opacity = 0.6;
      return;
    }
    RT_TRAINING_ID = data.training.id;
    const d = new Date(data.training.date + 'T00:00:00');
    hint.textContent = `Доступна тренировка от ${d.toLocaleDateString('ru-RU')}.`;
    btn.disabled = false; btn.style.opacity = 1;

    btn.onclick = () => {
      document.getElementById('rateTrainDate').textContent = d.toLocaleDateString('ru-RU');
      ['intensity','fatigue','mood','enjoyment'].forEach(k=>{
        const input = document.getElementById('rt_'+k);
        input.value = 3;
        document.getElementById('rt_'+k+'_v').textContent = '3';
      });
      document.getElementById('rateTrainingModal').style.display='flex';
    };
  });

  const submit = document.getElementById('rt_submitBtn');
  if (submit) {
    submit.onclick = async () => {
      if (!RT_TRAINING_ID) return;
      submit.disabled = true;
      const payload = {
        training_id: RT_TRAINING_ID,
        intensity:  parseInt(document.getElementById('rt_intensity').value,10),
        fatigue:    parseInt(document.getElementById('rt_fatigue').value,10),
        mood:       parseInt(document.getElementById('rt_mood').value,10),
        enjoyment:  parseInt(document.getElementById('rt_enjoyment').value,10)
      };
      try {
        const res = await fetch('/api/save_training_rating.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          credentials:'same-origin',
          body: JSON.stringify(payload)
        });
        const text = await res.text();
        let out; try { out = JSON.parse(text); } catch{ out = {success:false, message:'bad_json'}; }
        if (out.success) {
          alert('Спасибо! Оценка сохранена.');
          document.getElementById('rateTrainingModal').style.display='none';
          btn.disabled = true; btn.textContent='Оценка сохранена'; btn.style.opacity=0.6;
          hint.textContent = 'Эта тренировка уже оценена.';
        } else {
          alert('Ошибка: ' + (out.message || 'не удалось сохранить'));
        }
      } catch(e) {
        console.error(e);
        alert('Сеть/сервер: не удалось сохранить');
      } finally {
        submit.disabled = false;
      }
    };
  }
}

document.addEventListener('DOMContentLoaded', setupRateTraining);
</script>


<div id="some-missing-id" style="display:none"></div>
<script src="./js/index.bundle.js"></script>

</body>
</html>
