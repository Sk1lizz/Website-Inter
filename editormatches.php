<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
require_once 'db.php';
$conn = $db;

$result = $conn->query("SELECT id, name FROM teams WHERE id IN (1, 2)");
$teams = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$years = range(date("Y"), 2023);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Редактор матчей</title>
  <link rel="stylesheet" href="/css/main.css">

 <style>
  :root {
    --gold: #FDC500;
    --dark-light: #1a1d24;
    --dark-medium: #00296B;
    --light: #f3f6fb;
  }

  body {
    padding: 20px;
    font-family: 'Play', sans-serif;
    background: #0d1117;
    color: var(--light);
  }

  .styled-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  .styled-table th, .styled-table td { padding: 10px 15px; border: 1px solid #2b2f36; }
  .styled-table thead { background-color: #00509D; color: white; }

  .controls { margin-bottom: 20px; }
  .highlight { color: var(--gold); font-weight: bold; }
  #top3Block h3 { color: var(--gold); margin-bottom: 10px; }
  #top3List { color: var(--light); font-size: 16px; line-height: 1.6; }

  /* ====== МОДАЛКА ====== */
  #editModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.7);
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }
  #editModal .modal-content {
    background: var(--dark-light);
    padding: 30px;
    border-radius: 20px;
    width: 100%;
    max-width: 640px;
    max-height: 85vh;
    overflow: auto;
    border: 1px solid var(--gold);
  }

  /* ВЕРХ ФОРМЫ (общие поля) — широкие инпуты ок */
  #editModal label { display: block; margin: 10px 0 5px; }
  /* ВАЖНО: убираем глобальное правило ширины для всех input! */
  #editModal input[type="text"],
  #editModal input[type="date"],
  #editModal select,
  #editModal textarea {
    width: 100%;
    padding: 8px;
    border-radius: 8px;
    background: #0d1117;
    color: var(--light);
    border: 1px solid var(--gold);
  }
  #editModal button {
    margin-top: 15px;
    padding: 8px 16px;
    border-radius: 10px;
    background: var(--dark-medium);
    color: var(--light);
    border: 1px solid var(--gold);
    cursor: pointer;
  }

  /* ====== СПИСОК ИГРОКОВ ====== */
  #playersList {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
  }

  /* <<< НОВОЕ: общий контейнер карточки игрока >>> */
.player-card{
  border: 1px solid var(--gold);
  border-radius: 10px;
  background: #0d1117;
  padding: 8px 10px;
}

/* 1-я строка: имя + Г/А/Проп */
.player-row{
  display: grid;
  grid-template-columns: 1fr repeat(3, max-content);
  align-items: center;
  gap: 8px 12px;
}

.player-row .player-name{
  min-width: 160px;
  font-weight: 600;
  color: var(--light);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.player-row label{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  font-size: 14px;
  color: var(--light);
  margin: 0;
}

/* 2-я строка: ЖК/КК/Пен + рейтинг */
.player-row-extra{
  display: flex;
  align-items: center;
  gap: 14px;
  margin-top: 6px;
}

.player-row-extra label{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  font-size: 14px;
  color: var(--light);
  margin: 0;
}

.player-row-extra .rating{
  color: var(--gold);
  margin-left: auto;
}


/* компактные number-поля (под 2 символа) */
.player-card input[type="number"]{
  width: 2.5em !important;
  padding: 3px;
  text-align: center;
  border: 1px solid var(--gold);
  border-radius: 6px;
  background: #0d1117;
  color: var(--light);
  font-size: 13px;
}

/* <<< ЖЁСТКАЯ переопределялка чекбоксов >>> */
.player-card input[type="checkbox"]{
  appearance: auto !important;
  -webkit-appearance: checkbox !important;
  -moz-appearance: checkbox !important;
  display: inline-block !important;
  width: auto !important;
  height: auto !important;
  transform: scale(1.15);
  accent-color: var(--gold);
  cursor: pointer;
}

#editModal input[type="checkbox"],
.player-card input[type="checkbox"] {
  appearance: auto !important;
  -webkit-appearance: checkbox !important;
  -moz-appearance: checkbox !important;
  display: inline-block !important;
  width: auto !important;
  height: auto !important;
  transform: scale(1.15);
  accent-color: var(--gold);
  cursor: pointer;
}

/* адаптив */
@media (max-width: 560px){
  .player-row{ grid-template-columns: 1fr 1fr; }
  .player-row-extra{ flex-wrap: wrap; }
  .player-row-extra .rating{ margin-left: 0; width: 100%; }
}

</style>

    <link rel="icon" href="/img/yelowaicon.png" type="image/x-icon">
</head>
<body>
   <?php include 'headeradmin.html'; ?>
   
  <h2>Редактирование матчей</h2>
  <div class="controls">
    <label for="teamSelect">Команда:</label>
    <select id="teamSelect">
      <option value="">-- выберите --</option>
      <?php foreach ($teams as $team): ?>
        <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="yearSelect">Год:</label>
    <select id="yearSelect">
      <?php foreach ($years as $year): ?>
        <option value="<?= $year ?>"><?= $year ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div id="top3Block" class="highlight">
    <h3>Топ-3 игрока месяца</h3>
    <ol id="top3List" style="padding-left: 20px;"></ol>
  </div>

  <table class="styled-table" id="matchTable">
    <thead>
      <tr>
        <th>Дата</th>
        <th>Турнир</th>
        <th>Соперник</th>
        <th>Счёт</th>
        <th>Лучший игрок</th>
        <th>Оценка</th>
        <th>Редактировать</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <div id="editModal">
    <div class="modal-content">
      <h3>Редактировать матч</h3>
      <form id="editForm">
        <input type="hidden" id="editMatchId">
        <label>Турнир:</label>
        <input type="text" id="championship_name">

        <label>Соперник:</label>
        <input type="text" id="opponent">

        <label>Наши голы:</label>
        <input type="number" id="our_goals">

        <label>Голы соперника:</label>
        <input type="number" id="opponent_goals">

        <label>Дата:</label>
        <input type="date" id="match_date">

        <label>Результат:</label>
        <select id="match_result">
          <option value="W">Победа</option>
          <option value="L">Поражение</option>
          <option value="X">Ничья</option>
        </select>

        <label>Описание голов (текст):</label>
<textarea id="goals" rows="2" style="width:100%;"></textarea>

<label>Описание ассистов (текст):</label>
<textarea id="assists" rows="2" style="width:100%;"></textarea>

        <div id="playersList" style="margin-top: 20px;"></div>

        <button type="submit">Сохранить</button>
        <button type="button" onclick="closeModal()">Отмена</button>
      </form>
    </div>
  </div>

<script>
function closeModal() {
  document.getElementById('editModal').style.display = 'none';
}

async function openModal(match) {
  document.getElementById('editModal').style.display = 'flex';
  document.getElementById('editMatchId').value = match.id;
  document.getElementById('championship_name').value = match.championship_name;
  document.getElementById('opponent').value = match.opponent;
  document.getElementById('our_goals').value = match.our_goals;
  document.getElementById('opponent_goals').value = match.opponent_goals;
  document.getElementById('match_date').value = match.date;
  document.getElementById('match_result').value = match.match_result;
  document.getElementById('goals').value = match.goals || '';
document.getElementById('assists').value = match.assists || '';

const res = await fetch(`/api/get_match_players_with_ratings.php?match_id=${match.id}`);
if (!res.ok) {
  const txt = await res.text();
  console.error('get_match_players_with_ratings failed:', txt);
  alert('Ошибка загрузки игроков матча: ' + txt);
  return; // не продолжаем, чтобы не падать на res.json()
}
const players = await res.json();

  const container = document.getElementById('playersList');
  container.innerHTML = '';
  players.forEach(p => {

const card = document.createElement('div');
card.className = 'player-card';

const formattedRating = (!isNaN(p.rating) && p.rating !== null)
  ? parseFloat(p.rating).toFixed(2)
  : '-';

card.innerHTML = `
  <div class="player-row">
    <div class="player-name">
      <label><input type="checkbox" class="player-checkbox" value="${p.id}" checked> ${p.name}</label>
    </div>
    <label>Г: <input type="number" class="goals-input" data-player="${p.id}" value="${p.goals || 0}" min="0"></label>
    <label>А: <input type="number" class="assists-input" data-player="${p.id}" value="${p.assists || 0}" min="0"></label>
    <label>Проп: <input type="number" class="conceded-input" data-player="${p.id}" value="${p.goals_conceded || 0}" min="0"></label>
  </div>

  <div class="player-row-extra">
    <label>ЖК <input type="checkbox" class="yc-input" data-player="${p.id}" ${(+p.yellow_card) ? 'checked' : ''}></label>
    <label>КК <input type="checkbox" class="rc-input" data-player="${p.id}" ${(+p.red_card) ? 'checked' : ''}></label>
    <label>Пен: <input type="number" class="mp-input" data-player="${p.id}" value="${p.missed_penalty || 0}" min="0"></label>
    <div class="rating">Рейтинг: <strong>${formattedRating}</strong></div>
  </div>
`;

container.appendChild(card);
});
}

async function loadMatches(teamId, year) {
  const res = await fetch(`/api/get_matches_admin.php?team_id=${teamId}&year=${year}`);
  const data = await res.json();

  // Отображение матчей
  const tbody = document.querySelector("#matchTable tbody");
  tbody.innerHTML = '';
  data.matches.forEach(match => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${match.date}</td>
      <td>${match.championship_name}</td>
      <td>${match.opponent}</td>
      <td>${match.our_goals} : ${match.opponent_goals}</td>
      <td>${match.top_player || '-'}</td>
      <td>${match.top_rating || '-'}</td>
      <td><button onclick='openModal(${JSON.stringify(match)})'>Редактировать</button></td>
    `;
    tbody.appendChild(tr);
  });

  // Отображение топ-3 игроков последнего месяца
  const top3List = document.getElementById("top3List");
  top3List.innerHTML = '';

  const months = Object.keys(data.top3 || {}).sort();
  const lastMonth = months.pop();
  const topPlayers = data.top3?.[lastMonth] || [];

  if (lastMonth && topPlayers.length > 0) {
    const label = document.createElement('div');
    label.innerHTML = `<strong>Топ-3 за ${lastMonth}</strong>`;
    top3List.appendChild(label);

    topPlayers.forEach((p, i) => {
      const li = document.createElement('li');
      li.textContent = `${i + 1}. ${p.name} (${p.avg_rating})`;
      top3List.appendChild(li);
    });
  } else {
    const li = document.createElement('li');
    li.textContent = 'Нет данных';
    top3List.appendChild(li);
  }
}

document.getElementById('editForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const matchId = document.getElementById('editMatchId').value;
  const players = [];
  document.querySelectorAll('.player-checkbox:checked').forEach(cb => {
  const id = parseInt(cb.value);
  const goals    = parseInt(document.querySelector(`.goals-input[data-player="${id}"]`)?.value) || 0;
  const assists  = parseInt(document.querySelector(`.assists-input[data-player="${id}"]`)?.value) || 0;
  const conceded = parseInt(document.querySelector(`.conceded-input[data-player="${id}"]`)?.value) || 0;

  const yellow_card     = !!document.querySelector(`.yc-input[data-player="${id}"]`)?.checked;
  const red_card        = !!document.querySelector(`.rc-input[data-player="${id}"]`)?.checked;
  const missed_penalty = parseInt(document.querySelector(`.mp-input[data-player="${id}"]`)?.value) || 0;

players.push({ id, goals, assists, goals_conceded: conceded, yellow_card, red_card, missed_penalty });
});

  const payload = {
    id: matchId,
  championship_name: document.getElementById('championship_name').value,
  opponent: document.getElementById('opponent').value,
  our_goals: parseInt(document.getElementById('our_goals').value) || 0,
  opponent_goals: parseInt(document.getElementById('opponent_goals').value) || 0,
  match_date: document.getElementById('match_date').value,
  result: document.getElementById('match_result').value,
  goals: document.getElementById('goals').value,         // <-- было goals_text
  assists: document.getElementById('assists').value,     // <-- было assists_text
  players
  };

  const res = await fetch('/api/update_match.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  if (res.ok) {
    closeModal();
    const teamId = document.getElementById("teamSelect").value;
    const year = document.getElementById("yearSelect").value;
    loadMatches(teamId, year);
  } else {
    alert('Ошибка сохранения');
  }
});

document.getElementById("teamSelect").addEventListener('change', () => {
  const teamId = document.getElementById("teamSelect").value;
  const year = document.getElementById("yearSelect").value;
  if (teamId && year) loadMatches(teamId, year);
});

document.getElementById("yearSelect").addEventListener('change', () => {
  const teamId = document.getElementById("teamSelect").value;
  const year = document.getElementById("yearSelect").value;
  if (teamId && year) loadMatches(teamId, year);
});
</script>
</body>
</html>
