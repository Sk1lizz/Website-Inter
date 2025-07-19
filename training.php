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

<form method="POST" action="/api/submit_training.php">
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

<div id="attendance-table-wrapper"></div>

<script>
const statusSymbols = { 1: '+', 0: '–', 2: 'О', 3: 'Т', 4: 'Б' };
const statusColors = { 1: '#d4f4d2', 0: '#f9d6d5', 2: '#fdf3c0', 3: '#e0d4f5', 4: '#cfe7f7' };

function createPlayerRow(player) {
  const wrapper = document.createElement('div');
  wrapper.style.marginBottom = '5px';

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
  select.innerHTML = `
    <option value="0" selected>Не был</option>
    <option value="2">Отпуск</option>
    <option value="3">Травма</option>
    <option value="4">Болел</option>
  `;

  checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
      select.innerHTML = '<option value="1" selected>Присутствовал</option>';
      select.disabled = false;
    } else {
      select.disabled = false;
      select.innerHTML = `
        <option value="0" selected>Не был</option>
        <option value="2">Отпуск</option>
        <option value="3">Травма</option>
        <option value="4">Болел</option>
      `;
    }
  });

  wrapper.appendChild(checkbox);
  wrapper.appendChild(label);
  wrapper.appendChild(hiddenPlayerId);
  wrapper.appendChild(select);
  return wrapper;
}

async function loadPlayers(teamId) {
  const listDiv = document.getElementById('players-list');
  listDiv.innerHTML = 'Загрузка...';
  try {
    const res = await fetch('/api/get_players.php?team_id=' + teamId);
    let players = await res.json();
    players.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));
    listDiv.innerHTML = '';
    players.forEach(player => listDiv.appendChild(createPlayerRow(player)));
  } catch (e) {
    listDiv.innerHTML = '<p>Ошибка загрузки игроков</p>';
  }
}

async function loadAttendanceTable(teamId, month) {
    const wrapper = document.getElementById('attendance-table-wrapper');
    wrapper.innerHTML = 'Загрузка...';

    try {
        const res = await fetch(`/api/get_attendance_detailed.php?team_id=${teamId}&month=${month}`);
        if (!res.ok) throw new Error("Ошибка запроса");

        const { dates, players } = await res.json();

        if (!dates.length || !players.length) {
            wrapper.innerHTML = '<p>Нет данных за выбранный месяц.</p>';
            return;
        }

        // ✅ сортировка игроков по имени
        players.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));

       let html = '<table><thead><tr><th>Игрок</th>';
dates.forEach(dateStr => {
  const d = new Date(dateStr);
  const formatted = d.toLocaleDateString('ru-RU', { day: '2-digit', month: 'short' });
  html += `<th>${formatted}</th>`;
});
html += '<th>Итого</th><th>%</th><th>Редактировать</th></tr></thead><tbody>';

        // Для подсчёта суммарных посещений по каждой дате
        const totalPerDate = {};
        dates.forEach(date => totalPerDate[date] = 0);

        // Игроки
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
            console.log('DEBUG p:', p);
html += `<td><button class="edit-btn" data-player='${JSON.stringify(p)}'>Редактировать</button></td>`;
        });

        // ✅ Добавим строку "Итого" по колонкам (сколько игроков были)
        html += `<tr style="font-weight:bold; background:#f0f0f0"><td>Присутствовали</td>`;
        dates.forEach(date => {
            html += `<td>${totalPerDate[date]}</td>`;
        });
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
        console.error("Ошибка загрузки посещаемости:", e);
        wrapper.innerHTML = '<p>Ошибка загрузки таблицы</p>';
    }
}

// обработчики
const teamSelect = document.getElementById('teamSelect');
const monthSelect = document.getElementById('monthSelect');

teamSelect.addEventListener('change', () => {
  const teamId = teamSelect.value;
  const month = monthSelect.value;
  if (teamId) loadPlayers(teamId);
  if (teamId && month) loadAttendanceTable(teamId, month);
});

monthSelect.addEventListener('change', () => {
  const teamId = teamSelect.value;
  const month = monthSelect.value;
  if (teamId && month) loadAttendanceTable(teamId, month);
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

</body>
</html>
