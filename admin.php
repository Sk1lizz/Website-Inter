<?php
session_start();

define('ADMIN_LOGIN', 'admin');
define('ADMIN_PASS', 'fcinter2025');

if (isset($_POST['auth_login'], $_POST['auth_pass'])) {
    if ($_POST['auth_login'] === ADMIN_LOGIN && $_POST['auth_pass'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

if (!isset($_SESSION['admin_logged_in'])):
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход в админку</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f4f8;
      display: flex; justify-content: center; align-items: center;
      min-height: 100vh; margin: 0;
    }
    .login-container {
      background-color: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 6px 20px rgba(0,0,0,.1);
      width: 100%; max-width: 400px;
    }
    h2 { color:#004080; text-align:center; margin:0 0 20px }
    label { display:block; margin-bottom:10px; font-weight:bold; color:#333 }
    input[type="text"], input[type="password"]{
      width:100%; padding:10px; font-size:14px;
      border:1px solid #ccc; border-radius:6px; margin-top:6px; box-sizing:border-box;
    }
    button{
      width:100%; background:#004080; color:#fff; border:none;
      padding:12px; font-size:15px; border-radius:6px; cursor:pointer; margin-top:12px;
    }
    button:hover{ background:#003060 }
    .error{ color:red; text-align:center; margin-bottom:15px; }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Авторизация</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
      <label>Логин:
        <input name="auth_login" type="text" required>
      </label>
      <label>Пароль:
        <input name="auth_pass" type="password" required>
      </label>
      <button type="submit">Войти</button>
    </form>
  </div>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админка - FC Inter Moscow</title>
  <link rel="stylesheet" href="/css/main.css"/>
  <link rel="icon" href="/img/yelowaicon.png" type="image/x-icon">
  <style>
    /* небольшой отступ под шапкой */
    .admin-panel { margin-top: 16px; }

    /* сетка страницы */
    .page-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 24px;
      align-items: start;
    }

    /* карточки */
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,.06);
      padding: 16px;
    }
    .card h2 { margin-top: 0 }

    /* правая колонка — расстояния между карточками */
    .right-col {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    /* блок «Ближайшие юбилеи» */
    .jubilees .controls {
      display: flex; gap: 12px; align-items: center; margin-bottom: 12px;
    }
    .jubilees table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
    }
    .jubilees th, .jubilees td {
      padding: 8px 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
      font-size: 14px;
    }
    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 999px;
      background: #f0f4f8;
      font-size: 12px;
    }
    .badge.success { background: #e8f8ef; color: #1e7a43; font-weight: 600; }

    /* Полоса «Юбилей сегодня» */
    .today-strip {
      display: flex; gap: 8px; align-items: center;
      padding: 10px; border-radius: 10px; background: #fff;
      box-shadow: 0 6px 20px rgba(0,0,0,.06);
      margin-bottom: 16px; overflow-x: auto;
    }
    .today-chip {
      white-space: nowrap;
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 13px;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      cursor: default;
    }
    .today-chip.matches { border-color:#cce4ff; background:#f2f8ff; }
    .today-chip.goals   { border-color:#d7f3e3; background:#f1fbf5; }
    .today-chip.assists { border-color:#ffe9c2; background:#fff8ea; }

    .today-title { font-weight:700; color:#0b3d91; margin-right: 4px; flex: 0 0 auto; }

    @media (max-width: 960px){
      .page-grid { grid-template-columns: 1fr; }
    }

    .tenure-grid{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:16px;
}
.table-wrap{ overflow-x:auto; }
.tenure-grid table{ width:100%; border-collapse:collapse; }
.tenure-grid th, .tenure-grid td{ padding:8px 10px; font-size:14px; white-space:nowrap; }

/* мобилка */
@media (max-width: 820px){
  .tenure-grid{ grid-template-columns: 1fr; }
  .tenure-grid th, .tenure-grid td{ font-size:13px; }
  .tenure-grid h4{ margin-top:8px; }
}

.tenure-grid > h4 { grid-column: 1 / -1; }

  </style>
</head>

<body>
<div class="admin-panel">
  <?php include 'headeradmin.html'; ?>

  <div class="page-grid">
    <!-- ЛЕВАЯ КОЛОНКА -->
    <section class="card jubilees" id="jubilees">
        <h3>Юбилеи по времени в команде</h3>
<div class="controls">
  <label>Показать ближайшие в днях:
    <select id="tenure-threshold">
      <option value="7">≤ 7 дней</option>
      <option value="14" selected>≤ 14 дней</option>
      <option value="30">≤ 30 дней</option>
    </select>
  </label>
</div>

<!-- ВАЖНО: две явные колонки внутри сетки -->
<div class="tenure-grid">
  <div>
    <h4 style="margin-top:0">Скоро 5/10/15 лет</h4>
    <div class="table-wrap">
      <table id="tbl-tenure-upcoming">
        <thead><tr>
          <th>Игрок</th><th>Команда</th><th>Стаж</th><th>Юбилей</th><th>Через</th>
        </tr></thead>
        <tbody><tr><td colspan="5">Загрузка…</td></tr></tbody>
      </table>
    </div>

    <h4>5+ лет в клубе</h4>
    <div class="table-wrap">
      <table id="tbl-tenure-5plus">
        <thead><tr>
          <th>Игрок</th><th>Команда</th><th>Стаж</th><th>С нами с</th>
        </tr></thead>
        <tbody><tr><td colspan="4">Загрузка…</td></tr></tbody>
      </table>
    </div>
  </div>

  <div>
    <h4 style="margin-top:0">Юбилей в этом месяце</h4>
    <div class="table-wrap">
      <table id="tbl-tenure-this-month">
        <thead><tr>
          <th>Игрок</th><th>Команда</th><th>Дата</th><th>Стаж</th>
        </tr></thead>
        <tbody><tr><td colspan="4">Загрузка…</td></tr></tbody>
      </table>
    </div>
  </div>
</div>
<!-- .tenure-grid ЗАКРЫТА, дальше идут остальные секции -->

<h3>Дни рождения в этом месяце</h3>
<div class="table-wrap">
  <table id="tbl-birthdays-month">
    <thead><tr>
      <th>Игрок</th><th>Команда</th><th>Дата</th><th>Исполняется</th>
    </tr></thead>
    <tbody><tr><td colspan="4">Загрузка…</td></tr></tbody>
  </table>
</div>

<!-- Полоса «Юбилей сегодня» -->
<div class="today-strip" id="today-strip" style="display:none;">
  <span class="today-title">ЮБИЛЕЙ СЕГОДНЯ:</span>
</div>

      <h2>Ближайшие юбилеи</h2>
      <div class="controls">
        <label>Порог близости:
          <select id="near-threshold">
            <option value="3">≤ 3</option>
            <option value="5" selected>≤ 5</option>
            <option value="10">≤ 10</option>
          </select>
        </label>
        <span class="badge">Команды: #1 и #2</span>
      </div>

      <h3>Матчи</h3>
      <table id="tbl-matches">
        <thead><tr>
          <th>Игрок</th><th>Команда</th><th>Текущее</th><th>След. юбилей</th><th>Осталось</th><th></th>
        </tr></thead>
        <tbody><tr><td colspan="6">Загрузка…</td></tr></tbody>
      </table>

      <h3>Голы</h3>
      <table id="tbl-goals">
        <thead><tr>
          <th>Игрок</th><th>Команда</th><th>Текущее</th><th>След. юбилей</th><th>Осталось</th><th></th>
        </tr></thead>
        <tbody><tr><td colspan="6">Загрузка…</td></tr></tbody>
      </table>

      <h3>Ассисты</h3>
      <table id="tbl-assists">
        <thead><tr>
          <th>Игрок</th><th>Команда</th><th>Текущее</th><th>След. юбилей</th><th>Осталось</th><th></th>
        </tr></thead>
        <tbody><tr><td colspan="6">Загрузка…</td></tr></tbody>
      </table>
    </section>

    <!-- ПРАВАЯ КОЛОНКА -->
    <div class="right-col">
      <!-- Добавить игрока -->
      <section class="card">
        <h2>Добавить игрока</h2>
        <form id="add-player-form">
          <label>Выберите команду:
            <select id="team-select" required></select>
          </label>
          <label>Фамилия и имя (полное имя): <input name="name" required placeholder="Иванов Иван"></label>
          <label>Отчество: <input name="patronymic"></label>
          <label>Игровой номер: <input name="number" type="number" required></label>
          <label>Позиция:
            <select name="position" required>
              <option value="">-- Выберите позицию --</option>
              <option value="Вратарь">Вратарь</option>
              <option value="Защитник">Защитник</option>
              <option value="Полузащитник">Полузащитник</option>
              <option value="Нападающий">Нападающий</option>
            </select>
          </label>
          <label>Дата рождения: <input name="birth_date" type="date" required></label>
          <label>Дата присоединения: <input name="join_date" type="date" required></label>
          <label>Рост (см): <input name="height_cm" type="number"></label>
          <label>Вес (кг): <input name="weight_kg" type="number"></label>
          <button type="submit">Добавить игрока</button>
        </form>
      </section>

      <!-- Управление достижениями -->
      <section class="card admin-achievements">
        <h2>Управление достижениями</h2>

        <label for="achv-team-select">Выберите команду:</label>
        <select id="achv-team-select"></select>

        <label for="achv-player-select">Выберите игрока:</label>
        <select id="achv-player-select"></select>

        <h3>Текущие достижения</h3>
        <table id="achievements-table"></table>

        <h3>Добавить достижение</h3>
        <form id="add-achievement-form">
          <input type="text" name="award_year" placeholder="Год (например, 2023)" maxlength="4" required>
          <input type="text" name="award_title" placeholder="Название награды" required>
          <select name="team_name">
            <option>FC Inter Moscow 8х8</option>
            <option>FC Inter Moscow</option>
            <option>FC Inter Moscow Pro</option>
            <option>FC Inter Moscow U18</option>
            <option>FC Inter Moscow U21</option>
            <option>Primavera FC Inter Moscow</option>
            <option>FC Inter Moscow forever</option>
            <option>FC Inter Moscow-2</option>
          </select>
          <button type="submit">Добавить</button>
        </form>
      </section>
    </div>

    <section class="card">
  <h2>Новички команды (≤20 тренировок)</h2>
  <div class="table-wrap">
    <table id="tbl-rookies">
      <thead>
        <tr>
          <th>Игрок</th>
          <th>Посещено тренировок</th>
        </tr>
      </thead>
      <tbody><tr><td colspan="2">Загрузка…</td></tr></tbody>
    </table>
  </div>
</section>

      <!-- Игроки, давно не игравшие -->
<section class="card">
  <h2>Игроки, давно не играли</h2>
  <div class="table-wrap">
    <table id="tbl-inactive">
      <thead>
        <tr>
          <th>Игрок</th>
          <th>Последний матч</th>
          <th>Дней назад</th>
        </tr>
      </thead>
      <tbody><tr><td colspan="3">Загрузка…</td></tr></tbody>
    </table>
  </div>
</section>

  </div>


</div>

<script>
  const teamSelectForm = document.getElementById('team-select');
  const addPlayerForm = document.getElementById('add-player-form');

  async function loadTeamsForPlayerForm() {
    const res = await fetch('api/get_teams.php');
    const teams = await res.json();

    teamSelectForm.innerHTML = '<option value="">-- Выберите команду --</option>';
    teams.forEach(team => {
      const option = document.createElement('option');
      option.value = team.id;
      option.textContent = team.name;
      teamSelectForm.appendChild(option);
    });
  }

  addPlayerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(addPlayerForm);
    const data = Object.fromEntries(formData.entries());
    data.team_id = teamSelectForm.value;

    const res = await fetch('api/add_player.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (res.ok) {
      const json = await res.json();
      alert(`Игрок добавлен!\nЛогин: ${json.login}\nПароль: ${json.password}`);
      addPlayerForm.reset();
    } else {
      alert('Ошибка при добавлении игрока');
    }
  });

  loadTeamsForPlayerForm();
</script>

<script>
  async function loadTeamsAndPlayers() {
    const teamSelect = document.getElementById('achv-team-select');
    const playerSelect = document.getElementById('achv-player-select');

    const teams = await fetch('api/get_teams.php').then(res => res.json());
    teamSelect.innerHTML = teams.map(t => `<option value="${t.id}">${t.name}</option>`).join('');

    teamSelect.addEventListener('change', async () => {
      const teamId = teamSelect.value;
      const players = await fetch(`api/get_players.php?team_id=${teamId}`).then(res => res.json());

      players.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
      playerSelect.innerHTML = players.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

      if (players.length > 0) {
        loadAchievements(players[0].id);
      } else {
        document.getElementById('achievements-table').innerHTML = '<tr><td colspan="4">Нет игроков</td></tr>';
      }
    });

    playerSelect.addEventListener('change', () => {
      loadAchievements(playerSelect.value);
    });

    document.getElementById('add-achievement-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const data = {
        player_id: playerSelect.value,
        award_year: form.award_year.value,
        award_title: form.award_title.value,
        team_name: form.team_name.value
      };

      await fetch('api/achievements.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      form.reset();
      loadAchievements(playerSelect.value);
    });

    if (teams.length > 0) {
      teamSelect.value = teams[0].id;
      teamSelect.dispatchEvent(new Event('change'));
    }
  }

  async function loadAchievements(playerId) {
    const table = document.getElementById('achievements-table');
    const data = await fetch(`api/achievements.php?player_id=${playerId}`).then(res => res.json());

    if (!data || !data.length) {
      table.innerHTML = '<tr><td colspan="4">Нет достижений</td></tr>';
      return;
    }

    table.innerHTML = data.map(d => `
      <tr>
        <td>${d.award_year}</td>
        <td>${d.award_title}</td>
        <td>${d.team_name}</td>
        <td><button onclick="deleteAchievement(${d.id}, ${playerId})">Удалить</button></td>
      </tr>
    `).join('');
  }

  async function deleteAchievement(id, playerId) {
    await fetch(`api/achievements.php?id=${id}`, { method: 'DELETE' });
    loadAchievements(playerId);
  }

  document.addEventListener('DOMContentLoaded', loadTeamsAndPlayers);
</script>

<script>
  // ====== Ближайшие юбилеи (адаптация под api/get_players.php + fallback top-5) ======
  const TEAM_IDS = [1, 2];
  const PLAYERS_API = 'api/get_players.php';
  let   STATS_API   = 'api/player_statistics_all.php';

  const tblMatches   = document.querySelector('#tbl-matches tbody');
  const tblGoals     = document.querySelector('#tbl-goals tbody');
  const tblAssists   = document.querySelector('#tbl-assists tbody');
  const thresholdSel = document.getElementById('near-threshold');
  const todayStrip   = document.getElementById('today-strip');

  // Проверим, где лежит player_statistics_all.php
  async function resolveStatsApi(){
    try {
      const r = await fetch(`${STATS_API}?id=0`);
      if (r.ok) return STATS_API;
    } catch(e){}
    STATS_API = '/player_statistics_all.php';
    return STATS_API;
  }

  function nextHundred(n){
    const v = Number(n)||0;
    const up = Math.ceil(v/100)*100;
    return up === 0 ? 100 : up;
  }
  function pickCombined(obj, key){
    const season = Number(obj?.season?.[key]) || 0;
    const all    = Number(obj?.all?.[key])    || 0;
    const comb   = obj?.combined?.[key];
    const nComb  = Number(comb);
    return Number.isFinite(nComb) ? nComb : season + all;
  }
  function addTodayChip(type, player, team, value){
    const chip = document.createElement('span');
    chip.className = `today-chip ${type}`;
    const label = type === 'matches' ? 'матчей' : (type === 'goals' ? 'голов' : 'ассистов');
    chip.textContent = `${player} (${team}) — ${value} ${label}`;
    todayStrip.appendChild(chip);
  }
  function toRows(items){
    items.sort((a,b)=> a.left - b.left || a.next - b.next || a.player.localeCompare(b.player));
    return items.map(it=>`
      <tr>
        <td>${it.player}</td>
        <td>${it.team}</td>
        <td>${it.current}</td>
        <td>${it.next}</td>
        <td>${it.left}</td>
        <td>${it.left === 0 ? '<span class="badge success">ЮБИЛЕЙ!</span>' : ''}</td>
      </tr>
    `).join('');
  }
  function withEmpty(rowsHtml, note){
    return rowsHtml || `<tr><td colspan="6">Нет данных${note ? ` — ${note}` : ''}</td></tr>`;
  }

  async function fetchPlayers(teamId){
    const r = await fetch(`${PLAYERS_API}?team_id=${teamId}`);
    if (!r.ok) throw new Error('players fetch failed');
    const arr = await r.json(); // [{id, name, stats:{...}}]
    return (Array.isArray(arr) ? arr : []).map(p => ({
      id: p.id,
      name: p.name || 'Без имени',
      team: `#${teamId}`
    }));
  }

  async function loadJubilees(){
    const near = Number(thresholdSel.value);
    tblMatches.innerHTML = `<tr><td colspan="6">Загрузка…</td></tr>`;
    tblGoals.innerHTML   = `<tr><td colspan="6">Загрузка…</td></tr>`;
    tblAssists.innerHTML = `<tr><td colspan="6">Загрузка…</td></tr>`;
    todayStrip.style.display = 'none';
    todayStrip.innerHTML = `<span class="today-title">ЮБИЛЕЙ СЕГОДНЯ:</span>`;

    try {
      await resolveStatsApi();
      // Игроки двух команд параллельно
      const players = (await Promise.all(TEAM_IDS.map(fetchPlayers))).flat();

      // Тянем суммарную стату по каждому игроку
      const stats = await Promise.all(players.map(async p => {
        try {
          const r = await fetch(`${STATS_API}?id=${encodeURIComponent(p.id)}`);
          if (!r.ok) throw 0;
          const data = await r.json();
          return {
            ...p,
            matches: pickCombined(data, 'matches'),
            goals:   pickCombined(data, 'goals'),
            assists: pickCombined(data, 'assists')
          };
        } catch {
          return null;
        }
      }));

      const clean = stats.filter(Boolean);

      const rowsM = [], rowsG = [], rowsA = [];
      const poolM = [], poolG = [], poolA = [];
      let hasToday = false;

      for (const s of clean) {
  // === МАТЧИ ===
  const nextM = nextHundred(s.matches);
  const leftM = Math.max(0, nextM - s.matches);
  poolM.push({ player: s.name, team: s.team, current: s.matches, next: nextM, left: leftM });
  if (leftM > 0 && leftM <= near) rowsM.push(poolM.at(-1));

  const isRecentM = s.matches >= 100 && s.matches % 100 <= 10;
  if (leftM === 0 || isRecentM) {
    addTodayChip('matches', s.name, s.team, Math.floor(s.matches / 100) * 100);
    hasToday = true;
  }

  // === ГОЛЫ ===
  const nextG = nextHundred(s.goals);
  const leftG = Math.max(0, nextG - s.goals);
  poolG.push({ player: s.name, team: s.team, current: s.goals, next: nextG, left: leftG });
  if (leftG > 0 && leftG <= near) rowsG.push(poolG.at(-1));

  const isRecentG = s.goals >= 100 && s.goals % 100 <= 10;
  if (leftG === 0 || isRecentG) {
    addTodayChip('goals', s.name, s.team, Math.floor(s.goals / 100) * 100);
    hasToday = true;
  }

  // === АССИСТЫ ===
  const nextA = nextHundred(s.assists);
  const leftA = Math.max(0, nextA - s.assists);
  poolA.push({ player: s.name, team: s.team, current: s.assists, next: nextA, left: leftA });
  if (leftA > 0 && leftA <= near) rowsA.push(poolA.at(-1));

  const isRecentA = s.assists >= 100 && s.assists % 100 <= 10;
  if (leftA === 0 || isRecentA) {
    addTodayChip('assists', s.name, s.team, Math.floor(s.assists / 100) * 100);
    hasToday = true;
  }
}

      // Если никто не попал в порог — показываем топ‑5 ближайших
      const note = `попробуйте увеличить порог`;
      const top5 = (arr)=> arr.sort((a,b)=> a.left - b.left).slice(0,5);

      tblMatches.innerHTML = withEmpty(toRows(rowsM), rowsM.length ? '' : note);
      if (!rowsM.length && poolM.length) tblMatches.innerHTML = toRows(top5(poolM));

      tblGoals.innerHTML   = withEmpty(toRows(rowsG), rowsG.length ? '' : note);
      if (!rowsG.length && poolG.length) tblGoals.innerHTML = toRows(top5(poolG));

      tblAssists.innerHTML = withEmpty(toRows(rowsA), rowsA.length ? '' : note);
      if (!rowsA.length && poolA.length) tblAssists.innerHTML = toRows(top5(poolA));

      if (hasToday) todayStrip.style.display = 'flex';
    } catch (e){
      console.error(e);
      const err = `<tr><td colspan="6">Ошибка загрузки данных</td></tr>`;
      tblMatches.innerHTML = err; tblGoals.innerHTML = err; tblAssists.innerHTML = err;
    }
  }

  thresholdSel?.addEventListener('change', loadJubilees);
  document.addEventListener('DOMContentLoaded', loadJubilees);
</script>

<script>
  // ====== Юбилеи по времени в команде (5/10/15, этот месяц, 5+ лет) ======
  const TENURE_API = 'api/get_player_jubilees.php';
  const tenureUpcomingBody = document.querySelector('#tbl-tenure-upcoming tbody');
  const tenureMonthBody    = document.querySelector('#tbl-tenure-this-month tbody');
  const tenure5plusBody    = document.querySelector('#tbl-tenure-5plus tbody');
  const tenureThresholdSel = document.getElementById('tenure-threshold');

  function fmtDays(n){ return n === 0 ? 'сегодня' : `через ${n} дн.`; }
  function fmtYears(y){ return `${y} ${y % 10 === 1 && y % 100 !== 11 ? 'год' : ( [2,3,4].includes(y%10) && ![12,13,14].includes(y%100) ? 'года' : 'лет')}`; }

  async function fetchTenure(teamId){
    const r = await fetch(`${TENURE_API}?team_id=${teamId}`);
    if (!r.ok) throw new Error('tenure fetch failed');
    const arr = await r.json();
    return (Array.isArray(arr) ? arr : []).map(p => ({
      team: `#${teamId}`,
      ...p
    }));
  }

  function renderUpcoming(rows){
    if (!rows.length){
      tenureUpcomingBody.innerHTML = `<tr><td colspan="5">Нет ближайших юбилеев — попробуйте увеличить порог дней</td></tr>`;
      return;
    }
    rows.sort((a,b)=> (a.days_until_next_jubilee??1e9) - (b.days_until_next_jubilee??1e9) || a.name.localeCompare(b.name));
    tenureUpcomingBody.innerHTML = rows.map(r => `
      <tr>
        <td>${r.name}</td>
        <td>${r.team}</td>
        <td>${fmtYears(r.years_in_team)}</td>
        <td>${r.next_jubilee_year ? r.next_jubilee_year + ' лет' : '—'} (${r.next_jubilee ?? '—'})</td>
        <td>${r.days_until_next_jubilee != null ? fmtDays(r.days_until_next_jubilee) : '—'}</td>
      </tr>
    `).join('');
  }

  function renderThisMonth(rows){
    if (!rows.length){
      tenureMonthBody.innerHTML = `<tr><td colspan="4">В этом месяце юбилеев нет</td></tr>`;
      return;
    }
    rows.sort((a,b)=> a.anniversary_date_this_year.localeCompare(b.anniversary_date_this_year));
    tenureMonthBody.innerHTML = rows.map(r => `
      <tr>
        <td>${r.name}</td>
        <td>${r.team}</td>
        <td>${r.anniversary_date_this_year}</td>
        <td>${fmtYears(r.years_in_team)}</td>
      </tr>
    `).join('');
  }

  function render5plus(rows){
    if (!rows.length){
      tenure5plusBody.innerHTML = `<tr><td colspan="4">Пока нет игроков со стажем 5+ лет</td></tr>`;
      return;
    }
    rows.sort((a,b)=> b.years_in_team - a.years_in_team || a.name.localeCompare(b.name));
    tenure5plusBody.innerHTML = rows.map(r => `
      <tr>
        <td>${r.name}</td>
        <td>${r.team}</td>
        <td>${fmtYears(r.years_in_team)}</td>
        <td>${r.join_date}</td>
      </tr>
    `).join('');
  }

  async function loadTenureJubilees(){
    const dayThreshold = Number(tenureThresholdSel.value);
    tenureUpcomingBody.innerHTML = `<tr><td colspan="5">Загрузка…</td></tr>`;
    tenureMonthBody.innerHTML    = `<tr><td colspan="4">Загрузка…</td></tr>`;
    tenure5plusBody.innerHTML    = `<tr><td colspan="4">Загрузка…</td></tr>`;

    try{
      const data = (await Promise.all(TEAM_IDS.map(fetchTenure))).flat();

      // 1) ближайшие 5/10/15 (по days_until_next_jubilee и next_jubilee_year в {5,10,15})
      const upcoming = data.filter(r =>
        r.next_jubilee_year && [5,10,15].includes(Number(r.next_jubilee_year)) &&
        r.days_until_next_jubilee != null && r.days_until_next_jubilee <= dayThreshold
      );
      renderUpcoming(upcoming);

      // 2) юбилей в этом месяце (по is_anniversary_month)
      const thisMonth = data.filter(r => r.is_anniversary_month && r.years_in_team >= 1);
      renderThisMonth(thisMonth);

      // 3) 5+ лет в клубе
      const fivePlus = data.filter(r => r.has_5_plus);
      render5plus(fivePlus);

    } catch(e){
      console.error(e);
      const err5 = `<tr><td colspan="5">Ошибка загрузки</td></tr>`;
      const err4 = `<tr><td colspan="4">Ошибка загрузки</td></tr>`;
      tenureUpcomingBody.innerHTML = err5;
      tenureMonthBody.innerHTML    = err4;
      tenure5plusBody.innerHTML    = err4;
    }
  }

  tenureThresholdSel?.addEventListener('change', loadTenureJubilees);
  document.addEventListener('DOMContentLoaded', loadTenureJubilees);
</script>

<script>
  // ====== Дни рождения в этом месяце (команды #1 и #2) ======
  const BIRTHDAYS_API = 'api/get_birthdays_month.php';
  const tblBirthMonthBody = document.querySelector('#tbl-birthdays-month tbody');

  function fmtAge(n){
    const mod10 = n % 10, mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return `${n} год`;
    if ([2,3,4].includes(mod10) && ![12,13,14].includes(mod100)) return `${n} года`;
    return `${n} лет`;
  }

  async function loadBirthdaysThisMonth(){
    if (!tblBirthMonthBody) return;
    tblBirthMonthBody.innerHTML = `<tr><td colspan="4">Загрузка…</td></tr>`;
    try{
      const month = new Date().getMonth()+1; // 1..12
      const team_ids = TEAM_IDS.join(',');
      const r = await fetch(`${BIRTHDAYS_API}?month=${month}&team_ids=${encodeURIComponent(team_ids)}`);
      if (!r.ok) throw 0;
      const data = await r.json();

      if (!Array.isArray(data) || !data.length){
        tblBirthMonthBody.innerHTML = `<tr><td colspan="4">В этом месяце дней рождений нет</td></tr>`;
        return;
      }

      tblBirthMonthBody.innerHTML = data.map(p => `
        <tr>
          <td>${p.name}</td>
          <td>#${p.team_id}</td>
          <td>${p.this_year_birthday}</td>
          <td>${fmtAge(p.age_turning)}</td>
        </tr>
      `).join('');
    } catch(e){
      console.error(e);
      tblBirthMonthBody.innerHTML = `<tr><td colspan="4">Ошибка загрузки</td></tr>`;
    }
  }

  document.addEventListener('DOMContentLoaded', loadBirthdaysThisMonth);
</script>

<script>
async function loadRookies() {
  const tbody = document.querySelector('#tbl-rookies tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="2">Загрузка…</td></tr>';

  try {
    const res = await fetch('api/get_rookies.php');
    if (!res.ok) throw new Error();
    const data = await res.json();

    if (!data.length) {
      tbody.innerHTML = '<tr><td colspan="2">Нет новичков с ≤20 тренировками</td></tr>';
      return;
    }

    tbody.innerHTML = data.map(p => {
      const color = p.trainings >= 15 ? 'style="color:green; font-weight:bold;"' : '';
      return `<tr>
        <td>${p.name}</td>
        <td ${color}>${p.trainings}</td>
      </tr>`;
    }).join('');
  } catch (e) {
    console.error(e);
    tbody.innerHTML = '<tr><td colspan="2">Ошибка загрузки</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', loadRookies);
</script>

<script>
async function loadInactivePlayers() {
  const tbody = document.querySelector('#tbl-inactive tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="3">Загрузка…</td></tr>';

  try {
    const res = await fetch('api/get_inactive_players.php');
    if (!res.ok) throw new Error();
    const data = await res.json();

    if (!data.length) {
      tbody.innerHTML = '<tr><td colspan="3">Все игроки недавно играли</td></tr>';
      return;
    }

    tbody.innerHTML = data.map(p => {
      const color = p.days_since >= 60 ? 'style="color:red; font-weight:bold;"' :
                    p.days_since >= 40 ? 'style="color:orange;"' : '';
      const dateStr = new Date(p.last_match_date).toLocaleDateString('ru-RU');
      return `<tr>
        <td>${p.name}</td>
        <td>${dateStr}</td>
        <td ${color}>${p.days_since}</td>
      </tr>`;
    }).join('');
  } catch (e) {
    console.error(e);
    tbody.innerHTML = '<tr><td colspan="3">Ошибка загрузки</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', loadInactivePlayers);
</script>
</body>
</html>
