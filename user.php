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
<body>
<div class="user_page">
  <div class="top-wrapper">
    <div class="header-buttons">
      <a href="/player.html?id=<?= (int)$_SESSION['player_id'] ?>" id="viewPublicProfile" target="_blank">Я на сайте</a>
      <?php if ($canChangeBackground === 1): ?>
  <button type="button" onclick="document.getElementById('user_bg-modal_background').style.display='flex'">Сменить фон</button>
<?php endif; ?>
      <button type="button" onclick="document.getElementById('changePasswordModal').style.display='block'">Сменить пароль</button>
      <form method="POST" style="margin: 0;"><button type="submit" name="logout">Выйти</button></form>
    </div>
  </div>

  <h1 style="text-align:center">Добро пожаловать, <?= htmlspecialchars($_SESSION['player_name']) ?>!</h1>

  <div class="dashboard-grid">
    <!-- Слева -->
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

    </div>

    <!-- Справа -->
    <div class="right-column">
      <div class="card">
        <h2>Моя посещаемость</h2>
        <script>const PLAYER_ID = <?= (int)$_SESSION['player_id'] ?>;</script>
        <select id="monthSelect"></select>
        <table class="attendance-table" id="attendanceTable">
            <thead><tr><th>Дата</th><th>Статус</th></tr></thead><tbody></tbody>
        </table>
        <p><strong>Процент посещаемости:</strong> <span id="percent">0%</span></p>
        <p id="feedback" style="font-weight:bold;"></p>
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
          '8' => 'Самурай'
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

document.getElementById('openVacationModal').addEventListener('click', () => {
  document.getElementById('vacationModal').style.display = 'flex';
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

</body>
</html>
