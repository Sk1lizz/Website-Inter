<?php
session_start();

$valid_user = 'coach';
$valid_pass = '!coach_Inter';

if (!isset($_SESSION['auth'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['pass'])) {
        if ($_POST['login'] === $valid_user && $_POST['pass'] === $valid_pass) {
            $_SESSION['auth'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
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
    </head><body>
    <div class="login-box">
        <h2>Авторизация</h2>';
    if (!empty($error)) echo '<div class="error">' . $error . '</div>';
    echo '<form method="post">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="pass" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form></div></body></html>';
    exit;
}
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
  
  <title>Учёт посещаемости тренировок</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f3f6fb;
      padding: 30px;
      margin: 0;
    }
    h1 {
      text-align: center;
      color: #083c7e;
    }
    form {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      max-width: 700px;
      margin: 20px auto;
    }
    label {
      display: block;
      margin-bottom: 10px;
    }
    select, input[type="date"] {
      padding: 8px;
      width: 100%;
      max-width: 300px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    #players-list > div {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }
    #players-list label {
      flex: 1;
    }
    #players-list select {
      flex: 1;
      max-width: none;
    }
    #players-list input[type="checkbox"] {
      width: 20px;
      height: 20px;
    }
    button {
      padding: 10px 20px;
      background: #083c7e;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      font-size: 14px;
    }
    th {
      background: #e0e7f1;
      font-weight: bold;
    }
    @media (max-width: 768px) {
      body { padding: 10px; }
      form, table { width: 100%; }
      select, input[type="date"] { width: 100%; max-width: none; }
      #players-list > div { flex-direction: column; align-items: flex-start; }
    }

   
  .btn-details {
    padding: 8px 12px;
    background: #083c7e;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s ease;
  }
  .btn-details:hover {
    background: #0a4da5;
  }

  </style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
      const list = document.getElementById('players-list');
      const countDisplay = document.createElement('div');
      countDisplay.id = 'present-count';
      list.insertAdjacentElement('afterend', countDisplay);

      const updateCount = () => {
        const checkboxes = document.querySelectorAll('#players-list input[type="checkbox"]:checked');
        countDisplay.textContent = `Отмечено присутствующих: ${checkboxes.length}`;
      };

      new MutationObserver(updateCount).observe(list, { childList: true, subtree: true });
      list.addEventListener('change', updateCount);
    });
  </script>

</head>
<body>
<h1>Учёт посещаемости тренировок</h1>

<form method="POST" action="/api/submit_training.php" id="training-form">
  <label>Команда:
    <select name="team_id" id="teamSelect" required>
      <option value="">-- Выберите команду --</option>
      <option value="2">Pro (11x11)</option>
      <option value="1">8x8</option>
    </select>
  </label>

  <label>Дата:
    <input type="date" name="training_date" required>
  </label>

  <div id="players-list" style="margin-top: 15px;"><p>Выберите команду, чтобы увидеть игроков.</p></div>
  <button type="submit" style="margin-top: 10px;">Сохранить тренировку</button>
</form>

<hr>

<label>Выберите месяц:
  <select id="monthSelect">
    <option value="">-- Месяц --</option>
    <option value="6">Июнь</option><option value="7">Июль</option><option value="8">Август</option>
    <option value="9">Сентябрь</option><option value="10">Октябрь</option><option value="11">Ноябрь</option>
    <option value="12">Декабрь</option><option value="1">Январь</option><option value="2">Февраль</option>
    <option value="3">Март</option><option value="4">Апрель</option><option value="5">Май</option>
  </select>
</label>

<!-- Аналитика оценок -->
<div id="ratings-analytics" style="margin-top:20px;">
  <h2 style="color:#083c7e;">Оценки тренировок — аналитика</h2>

  <div id="ratings-summary" style="background:#fff; border-radius:8px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <p>Выберите команду и месяц — сводка появится здесь.</p>
  </div>

  <div id="ratings-trainings" style="margin-top:16px;"></div>
</div>

<!-- Модалка с деталями оценок -->
<div id="ratings-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:2000; align-items:center; justify-content:center;">
  <div style="background:#fff; width:95%; max-width:700px; border-radius:10px; padding:18px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
    <h3 style="margin-top:0; color:#083c7e;">Детали оценок</h3>
    <div id="ratings-modal-body">Загрузка…</div>
    <div style="text-align:right; margin-top:12px;">
      <button id="close-ratings-modal" style="padding:8px 14px; background:#083c7e; color:#fff; border:none; border-radius:6px; cursor:pointer;">Закрыть</button>
    </div>
  </div>
</div>

<div id="attendance-table-wrapper"></div>

<script>
const statusSymbols = { 1: '+', 0: '–', 2: 'О', 3: 'Т', 4: 'Б' };
const statusColors = { 1: '#d4f4d2', 0: '#f9d6d5', 2: '#fdf3c0', 3: '#e0d4f5', 4: '#cfe7f7' };

function createPlayerRow(player, isOnHoliday = false) {
  const wrapper = document.createElement('div');
  wrapper.style.marginBottom = '5px';
  wrapper.style.display = 'grid';
  wrapper.style.gridTemplateColumns = 'auto 1fr auto';
  wrapper.style.alignItems = 'center';
  wrapper.style.gap = '10px';

  const checkbox = document.createElement('input');
  checkbox.type = 'checkbox';
  checkbox.name = 'present[]';
  checkbox.value = player.id;
  checkbox.style.marginRight = '6px';

  const label = document.createElement('label');
  label.textContent = player.name;
  label.style.marginRight = '10px';

  const hiddenPlayerId = document.createElement('input');
  hiddenPlayerId.type = 'hidden';
  hiddenPlayerId.name = 'players[]';
  hiddenPlayerId.value = player.id;

  const select = document.createElement('select');
  select.name = `status[${player.id}]`;

  // Блок с ползунком рейтинга (3..10 шаг 0.5), по умолчанию скрыт
  const ratingWrap = document.createElement('div');
  ratingWrap.style.display = 'none'; // появится только при присутствии
  ratingWrap.style.gridColumn = '1 / -1'; // во всю ширину строки

  ratingWrap.innerHTML = `
    <div style="display:flex; align-items:center; gap:10px;">
      <span style="min-width:160px; font-size:13px; color:#333;">Оценка игрока (3.0 – 10.0):</span>
      <input type="range" min="3" max="10" step="0.5"
             name="rating[${player.id}]"
             value="7.0"
             style="width:220px;"
      >
      <span class="rating-value" style="width:40px; text-align:center; font-weight:600;">7.0</span>
    </div>
  `;

  const ratingInput = ratingWrap.querySelector('input[type="range"]');
  const ratingValue = ratingWrap.querySelector('.rating-value');
  ratingInput.addEventListener('input', () => {
    ratingValue.textContent = ratingInput.value;
  });

  // Инициализация вариантов селекта
  if (isOnHoliday) {
    select.innerHTML = `<option value="2" selected>Отпуск</option>`;
    checkbox.disabled = true;
    checkbox.checked = false;
    select.disabled = true;
  } else {
    select.innerHTML = `
      <option value="0" selected>Не был</option>
      <option value="2">Отпуск</option>
      <option value="3">Травма</option>
      <option value="4">Болел</option>
      <option value="late_notice">Не был, предупреждение < 3 ч.</option>
      <option value="absent">Неявка</option>
    `;
  }

  // Функция переключения UI под присутствие
  const setPresenceUI = (isPresent) => {
    if (isPresent) {
      select.innerHTML = `
        <option value="1" selected>Присутствовал</option>
        <option value="опоздание">Опоздание</option>
      `;
      ratingWrap.style.display = '';
      // если ранее сбросили — вернём дефолт
      if (!ratingInput.value) ratingInput.value = '7.0';
      ratingValue.textContent = ratingInput.value;
    } else {
      select.innerHTML = `
        <option value="0" selected>Не был</option>
        <option value="2">Отпуск</option>
        <option value="3">Травма</option>
        <option value="4">Болел</option>
        <option value="late_notice">Не был, предупреждение < 3 ч.</option>
        <option value="absent">Неявка</option>
      `;
      ratingWrap.style.display = 'none';
      ratingInput.value = ''; // очистим, чтобы на сервер не ушёл мусор
      ratingValue.textContent = '';
    }
  };

  // Смена чекбокса — управляет "присутствовал/не был"
  checkbox.addEventListener('change', () => setPresenceUI(checkbox.checked));

  // Если пользователь вручную поменяет селект, тоже ловим
  select.addEventListener('change', () => {
    const val = select.value;
    if (val === '1' || val === 'опоздание') {
      // при «опоздании» мы всё равно сохраняем как присутствовал (вы уже мапите это в JS)
      setPresenceUI(true);
    } else {
      setPresenceUI(false);
      // для штрафных вариантов ваш код ниже всё равно заменит значение на 0/1 и добавит штраф
    }
  });

  wrapper.appendChild(checkbox);
  wrapper.appendChild(label);
  wrapper.appendChild(select);
  wrapper.appendChild(hiddenPlayerId);
  wrapper.appendChild(ratingWrap);
  return wrapper;
}

const dateInput = document.querySelector('input[name="training_date"]');

dateInput.addEventListener('change', () => {
  const teamId = teamSelect.value;
  const date = dateInput.value;
  if (teamId && date) loadPlayers(teamId);
});

async function loadPlayers(teamId) {
  const listDiv = document.getElementById('players-list');
  listDiv.innerHTML = 'Загрузка...';

  const dateInput = document.querySelector('input[name="training_date"]');
  const date = dateInput?.value;
  if (!date) {
    listDiv.innerHTML = '<p>Пожалуйста, выберите дату.</p>';
    return;
  }

  try {
    const [playersRes, holidayRes] = await Promise.all([
  fetch('/api/get_players.php?team_id=' + teamId),
  fetch(`/api/get_holiday_for_training.php?team_id=${teamId}&date=${date}`)
]);

const players = await playersRes.json();
const holidayIds = await holidayRes.json(); // ← теперь данные поступают

players.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));
listDiv.innerHTML = '';

players.forEach(player => {
  const isOnHoliday = holidayIds.includes(player.id);
  const row = createPlayerRow(player, isOnHoliday);
  listDiv.appendChild(row);
});

  } catch (e) {
    console.error('Ошибка загрузки игроков:', e);
    listDiv.innerHTML = '<p>Ошибка загрузки игроков</p>';
  }
}

async function loadAttendanceTable(teamId, month) {
  const wrapper = document.getElementById('attendance-table-wrapper');
  const ratingsSummaryBox = document.getElementById('ratings-summary');
  const ratingsTrainingsBox = document.getElementById('ratings-trainings');

  if (!teamId) {
    wrapper.innerHTML = '<p>Сначала выберите команду.</p>';
    ratingsSummaryBox.innerHTML = '<p>Выберите команду и месяц.</p>';
    ratingsTrainingsBox.innerHTML = '';
    return;
  }
  if (!month) {
    wrapper.innerHTML = '<p>Сначала выберите месяц.</p>';
    return;
  }

  // год + нормализуем месяц к двузначному (на случай, если API этого хочет)
  const year = new Date().getFullYear();
  const mm = String(month).padStart(2, '0');

  wrapper.innerHTML = 'Загрузка...';
  console.log('[attendance] fetch', { teamId, month: mm, year });

  try {
    const res = await fetch(`/api/get_attendance_detailed.php?team_id=${teamId}&month=${mm}&year=${year}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const payload = await res.json();
    const { dates = [], players = [] } = payload || {};

    if (!dates.length || !players.length) {
      wrapper.innerHTML = '<p>Нет данных за выбранный месяц.</p>';
      return;
    }

    // сортировка по имени
    players.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));

    let html = '<table><thead><tr><th>Игрок</th>';
    dates.forEach(dateStr => {
      const d = new Date(dateStr);
      const formatted = d.toLocaleDateString('ru-RU', { day: '2-digit', month: 'short' });
      html += `<th>${formatted}</th>`;
    });
    html += '<th>Итого</th><th>%</th><th>Редактировать</th></tr></thead><tbody>';

    const totalPerDate = {};
    dates.forEach(date => totalPerDate[date] = 0);

    players.forEach(p => {
      let attended = 0;
      let valid = 0;

      html += `<tr><td style="text-align:left">${p.name}</td>`;

      dates.forEach(date => {
        const status = p.statuses[date];
        const symbol = statusSymbols[status] || '';
        const bg = statusColors[status] || '';

        if (status === 1) {
          attended++;
          totalPerDate[date]++;
        }
        if (status === 1 || status === 0) valid++;

        html += `<td style="background:${bg}">${symbol}</td>`;
      });

      const percent = valid ? Math.round((attended / valid) * 100) : 0;
      const pcColor = percent >= 80 ? '#c8e6c9' : percent >= 50 ? '#fff9c4' : '#ffcdd2';

      html += `<td>${attended}</td><td style="background:${pcColor}">${percent}%</td>`;
      html += `<td><button class="edit-btn" data-player='${JSON.stringify(p)}'>Редактировать</button></td>`;
    });

    html += `<tr style="font-weight:bold; background:#f0f0f0"><td>Присутствовали</td>`;
    dates.forEach(date => { html += `<td>${totalPerDate[date]}</td>`; });
    html += `<td colspan="2"></td></tr>`;
    html += '</tbody></table>';

    wrapper.innerHTML = html;

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const player = JSON.parse(btn.dataset.player);
        const editFields = document.getElementById('edit-fields');
        const modal = document.getElementById('edit-modal');
        editFields.innerHTML = '';
        document.getElementById('edit-player-id').value = player.id;
        document.getElementById('edit-team-id').value = teamSelect.value;
        document.getElementById('edit-month').value = monthSelect.value;

        dates.forEach(date => {
          const value = player.statuses[date] ?? 0;
          const field = document.createElement('div');
          field.innerHTML = `
            <label>${date}: 
              <select name="status[${date}]">
                <option value="0" ${value===0?'selected':''}>– Не был</option>
                <option value="1" ${value===1?'selected':''}>+ Присутствовал</option>
                <option value="2" ${value===2?'selected':''}>О Отпуск</option>
                <option value="3" ${value===3?'selected':''}>Т Травма</option>
                <option value="4" ${value===4?'selected':''}>Б Болел</option>
              </select>
            </label>`;
          editFields.appendChild(field);
        });

        modal.style.display = 'block';
      });
    });

  } catch (e) {
    console.error('Ошибка загрузки посещаемости:', e);
    wrapper.innerHTML = '<p>Ошибка загрузки таблицы</p>';
  }
}

// обработчики
const teamSelect = document.getElementById('teamSelect');
const monthSelect = document.getElementById('monthSelect');

// единая функция перерисовки
function refreshTablesAndAnalytics() {
  const teamId = teamSelect.value;
  const month  = monthSelect.value;
  // посещаемость
  loadAttendanceTable(teamId, month);
  // аналитика оценок
  loadRatingsAnalytics();
}

// если меняется команда: подгружаем игроков на выбранную дату + перерисовываем
teamSelect.addEventListener('change', () => {
  const teamId = teamSelect.value;
  const date = document.querySelector('input[name="training_date"]')?.value;
  if (teamId && date) loadPlayers(teamId);
  refreshTablesAndAnalytics();
});

// если меняется месяц: просто перерисовываем
monthSelect.addEventListener('change', refreshTablesAndAnalytics);

// на всякий: если что-то уже выбрано (например, после автофилла), перерисуем при старте
document.addEventListener('DOMContentLoaded', () => {
  if (teamSelect.value && monthSelect.value) {
    refreshTablesAndAnalytics();
  }
});
</script>

<div id="edit-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
  background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.3); z-index:1000;">
  <h3>Редактировать посещаемость</h3>
  <form id="edit-form">
    <div id="edit-fields"></div>
    <input type="hidden" name="player_id" id="edit-player-id">
    <input type="hidden" name="team_id" id="edit-team-id">
    <input type="hidden" name="month" id="edit-month">
    <button type="submit">Сохранить</button>
    <button type="button" onclick="document.getElementById('edit-modal').style.display='none'">Отмена</button>
  </form>
</div>

<script>
document.getElementById('edit-form').addEventListener('submit', async e => {
  e.preventDefault();
  const form = new FormData(e.target);
  const res = await fetch('/api/update_attendance.php', {
    method: 'POST',
    body: form
  });
  const text = await res.text();
  alert(text);
  document.getElementById('edit-modal').style.display = 'none';

  const teamId = document.getElementById('teamSelect').value;
  const month = document.getElementById('monthSelect').value;
  if (teamId && month) loadAttendanceTable(teamId, month);
  console.log('Submitting:', Object.fromEntries(form.entries()));
});
</script>

<script>
document.getElementById('training-form').addEventListener('submit', async function (e) {
  e.preventDefault();

  const form = new FormData(this);
  const playerIds = form.getAll('players[]');

  for (const id of playerIds) {
    const selectVal = form.get(`status[${id}]`);
   if (selectVal === 'опоздание') {
  await fetch('/api/add_fine.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      player_id: parseInt(id),
      amount: 250,
      reason: 'Опоздание на тренировку',
      date: document.querySelector('input[name="training_date"]').value
    })
  });
  form.set(`status[${id}]`, '1'); // присутстововал
}

if (selectVal === 'late_notice') {
  await fetch('/api/add_fine.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      player_id: parseInt(id),
      amount: 500,
      reason: 'Предупреждение о неявке на тренировку менее чем за 3 часа',
      date: document.querySelector('input[name="training_date"]').value
    })
  });
  form.set(`status[${id}]`, '0'); // не был
}

if (selectVal === 'absent') {
  await fetch('/api/add_fine.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      player_id: parseInt(id),
      amount: 750,
      reason: 'Неявка на тренировку',
      date: document.querySelector('input[name="training_date"]').value
    })
  });
  form.set(`status[${id}]`, '0'); // не был
}

  }

  // Отправляем форму на сервер
  fetch('/api/submit_training.php', {
    method: 'POST',
    body: form
  })
  .then(res => res.text())
  .then(resp => {
    alert('Тренировка сохранена');
    location.reload();
  });
});
</script>

<script>
async function loadRatingsAnalytics() {
  const teamId = teamSelect.value;
  const month  = monthSelect.value;
  if (!teamId || !month) {
    document.getElementById('ratings-summary').innerHTML = '<p>Выберите команду и месяц.</p>';
    document.getElementById('ratings-trainings').innerHTML = '';
    return;
  }
  // Текущий год (можете заменить/добавить селект года при желании)
  const year = new Date().getFullYear();

  try {
    const res = await fetch(`/api/ratings_summary.php?team_id=${teamId}&month=${month}&year=${year}`);
    const data = await res.json();
    if (!data.success) throw new Error('API error');

    // Сводка
    const s = data.summary;
    const summaryHtml = `
      <div style="display:flex; flex-wrap:wrap; gap:12px;">
        <div style="flex:1; min-width:200px;">
          <strong>Количество оценённых тренировок:</strong> ${data.summary.trainings_rated}<br>
          <strong>Всего оценок (голосов):</strong> ${data.summary.ratings_count}
        </div>
        <div style="flex:2; min-width:280px;">
          <strong>Средние:</strong>
          <ul style="margin:6px 0 0 16px; padding:0;">
            <li>Интенсивность: <b>${s.avg_intensity ?? '—'}</b></li>
            <li>Усталость: <b>${s.avg_fatigue ?? '—'}</b></li>
            <li>Настроение: <b>${s.avg_mood ?? '—'}</b></li>
            <li>Удовольствие: <b>${s.avg_enjoyment ?? '—'}</b></li>
            <li>Итого (среднее из 4): <b>${s.avg_overall ?? '—'}</b></li>
          </ul>
        </div>
      </div>
    `;
    document.getElementById('ratings-summary').innerHTML = summaryHtml;

    // Таблица по тренировкам
    const rows = data.trainings.map(t => {
      const overall = (t.avg_intensity!=null && t.avg_fatigue!=null && t.avg_mood!=null && t.avg_enjoyment!=null)
        ? (((t.avg_intensity + t.avg_fatigue + t.avg_mood + t.avg_enjoyment)/4).toFixed(2))
        : '—';
      return `
        <tr>
          <td>${new Date(t.training_date).toLocaleDateString('ru-RU')}</td>
          <td>${t.raters}</td>
          <td>${t.avg_intensity ?? '—'}</td>
          <td>${t.avg_fatigue ?? '—'}</td>
          <td>${t.avg_mood ?? '—'}</td>
          <td>${t.avg_enjoyment ?? '—'}</td>
          <td><b>${overall}</b></td>
          <td><button class="btn-details" data-training="${t.training_id}">Подробнее</button></td>
        </tr>`;
    }).join('');

    const tableHtml = `
      <table>
        <thead>
          <tr>
            <th>Дата</th>
            <th>Оценивших</th>
            <th>Интенсивность</th>
            <th>Усталость</th>
            <th>Настроение</th>
            <th>Удовольствие</th>
            <th>Средняя</th>
            <th></th>
          </tr>
        </thead>
        <tbody>${rows || '<tr><td colspan="8" style="text-align:center;">Нет тренировок</td></tr>'}</tbody>
      </table>`;
    document.getElementById('ratings-trainings').innerHTML = tableHtml;

    // Хэндлер «Подробнее»
    document.querySelectorAll('.btn-details').forEach(btn => {
      btn.addEventListener('click', async () => {
        const trainingId = btn.dataset.training;
        openRatingsModal(trainingId);
      });
    });

  } catch (e) {
    console.error(e);
    document.getElementById('ratings-summary').innerHTML = '<p>Ошибка загрузки сводки</p>';
    document.getElementById('ratings-trainings').innerHTML = '';
  }
}

async function openRatingsModal(trainingId) {
  const modal = document.getElementById('ratings-modal');
  const body  = document.getElementById('ratings-modal-body');
  body.innerHTML = 'Загрузка...';
  modal.style.display = 'flex';

  try {
    const res = await fetch(`/api/ratings_details.php?training_id=${trainingId}`);
    const data = await res.json();
    if (!data.success) throw new Error('API error');

    if (!data.ratings.length) {
      body.innerHTML = '<p>Оценок нет.</p>';
      return;
    }

    const rows = data.ratings.map(r => `
      <tr>
        <td style="text-align:left;">${r.player_name}</td>
        <td>${r.intensity}</td>
        <td>${r.fatigue}</td>
        <td>${r.mood}</td>
        <td>${r.enjoyment}</td>
        <td>${new Date(r.created_at).toLocaleString('ru-RU')}</td>
      </tr>
    `).join('');

    body.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>Игрок</th>
            <th>Интенс.</th>
            <th>Устал.</th>
            <th>Настроение</th>
            <th>Удовольствие</th>
            <th>Когда</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    `;
  } catch (e) {
    console.error(e);
    body.innerHTML = '<p>Ошибка загрузки.</p>';
  }
}

document.getElementById('close-ratings-modal').addEventListener('click', () => {
  document.getElementById('ratings-modal').style.display = 'none';
});

// Подвяжем к существующим селекторам
teamSelect.addEventListener('change', loadRatingsAnalytics);
monthSelect.addEventListener('change', loadRatingsAnalytics);
</script>

</body>
</html>
