<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
 <link rel="icon" href="/img/yelowaicon.png" type="image/x-icon">
 
    <style>
    
    body {
     font-family: Arial, sans-serif;
     background: #f0f0f0;
     margin: 0;
     padding: 20px;
 }
 
 .admin-panel {
     max-width: 700px;
     margin: 40px auto;
     padding: 25px;
     border: 2px solid #333;
     border-radius: 10px;
     background: #fff;
     box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
 }
 
 .admin-panel h2 {
     margin-top: 0;
     text-align: center;
     color: #222;
     font-size: 24px;
 }
 
 .admin-panel form {
     display: flex;
     flex-direction: column;
     gap: 15px;
 }
 
 .admin-panel label {
     display: flex;
     flex-direction: column;
     font-weight: bold;
     color: #333;
 }
 
 .admin-panel input,
 .admin-panel select {
     width: 100%;
     padding: 8px 10px;
     margin-top: 5px;
     border: 1px solid #ccc;
     border-radius: 6px;
     font-size: 14px;
 }
 
 .admin-panel button {
     padding: 10px 20px;
     font-size: 16px;
     border: none;
     border-radius: 6px;
     cursor: pointer;
     background-color: #00509d;
     color: white;
     transition: background-color 0.3s ease;
     margin-top: 10px;
 }
 
 .admin-panel button:hover {
     background-color: #003f7d;
 }
 
 .success-message {
     text-align: center;
     color: green;
     font-weight: bold;
     margin-bottom: 15px;
 }
 
 .error-message {
     text-align: center;
     color: red;
     font-weight: bold;
     margin-bottom: 15px;
 }
 
 @media (max-width: 600px) {
     .admin-panel {
         padding: 15px;
     }
     .admin-panel h2 {
         font-size: 20px;
     }
     .admin-panel button {
         font-size: 14px;
         padding: 8px 15px;
     }
 }
 
.player-card {
  margin: 8px 0;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
}

/* 1-я строка — без "На 0" */
.player-row {
  display: grid;
  grid-template-columns: 200px repeat(4, max-content); /* было repeat(5, ...) */
  column-gap: 12px;
  row-gap: 8px;
  align-items: center;
}

/* 2-я строка — добавили "На 0:" сюда */
.player-row-extra {
  display: grid;
  grid-template-columns: 200px repeat(5, max-content); /* было repeat(4, ...) */
  column-gap: 12px;
  row-gap: 8px;
  align-items: center;
  margin-top: 6px;
}

/* «пустая» ячейка слева во 2-й строке, чтобы сетка была ровной */
.player-row-extra .player-name {
  visibility: hidden; /* место сохраняем, текста не видно */
}

.player-name {
  flex: 0 0 180px; /* фикс. ширина для фамилии */
  font-weight: bold;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.player-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 10px;
  flex: 1;
}

.player-stats label {
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  white-space: nowrap;
}

.player-stats input[type="number"] {
  width: 32px; /* максимум два знака */
  padding: 2px;
  text-align: center;
}

.player-stats input[type="checkbox"] {
  transform: scale(1.1);
}

.player-row label,
.player-row-extra label {
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.player-row input[type="number"],
.player-row-extra input[type="number"] {
  width: 56px;
  padding: 4px;
}

/* На узких экранах — перестраиваемся в две колонки, ничего не уезжает */
@media (max-width: 900px) {
  .player-row,
  .player-row-extra {
    grid-template-columns: 1fr 1fr;
  }
  .player-row .player-name,
  .player-row-extra .player-name {
    visibility: visible;
  }
}

     </style>

    <title>Добавить матч</title>

</head>

<body>
<div class="admin-panel">

   <?php include 'headeradmin.html'; ?>


    <h2>Добавить матч</h2>

    <form id="addMatchForm">
        <label>Команда:
            <select id="matchTeamSelect" name="teams_id" required></select>
        </label>
        <label>Дата матча:
            <input type="date" name="date" required>
        </label>
        <label>Год отдельно:
            <input type="number" name="year" required>
        </label>
        <label>Название чемпионата:
            <input type="text" name="championship_name" required>
        </label>
        <label>Тур:
            <input type="text" name="tour">
        </label>
        <label>Соперник:
            <input type="text" name="opponent" required>
        </label>
        <label>Наши голы:
            <input type="number" name="our_goals" required>
        </label>
        <label>Голы соперника:
            <input type="number" name="opponent_goals" required>
        </label>
        <label>Голы кто забивал (текстом):
            <input type="text" name="goals">
        </label>
        <label>Голевые кто отдавал (текстом):
            <input type="text" name="assists">
        </label>
        <label>Результат матча:
            <select name="match_result" required>
                <option value="W">Победа</option>
                <option value="L">Поражение</option>
                <option value="X">Ничья</option>
            </select>
        </label>
        <div id="playerStatsContainer"></div>
        <button type="submit">Добавить матч</button>
    </form>

    <div id="matchMessage"></div>

</div> 

<script>
async function loadTeamsIntoMatchForm() {
    try {
        const res = await fetch("/api/get_teams.php");
        if (!res.ok) throw new Error(`Ошибка ${res.status} при загрузке команд`);
        const teams = await res.json();

        const matchTeamSelect = document.getElementById("matchTeamSelect");
        matchTeamSelect.innerHTML = "";
        teams.forEach(team => {
            const option = document.createElement("option");
            option.value = team.id;
            option.textContent = team.name;
            matchTeamSelect.appendChild(option);
        });

        if (teams.length > 0) {
            matchTeamSelect.value = teams[0].id;
            loadPlayersForSelectedTeam(teams[0].id);
        }
    } catch (err) {
        alert(err.message);
        console.error(err);
    }
}

async function loadPlayersForSelectedTeam(teamId) {
    const container = document.getElementById("playerStatsContainer");
    container.innerHTML = "<h3>Участники матча:</h3>";

    try {
        const res = await fetch(`/api/get_players.php?team_id=${teamId}`);
        if (!res.ok) throw new Error("Ошибка загрузки игроков");

        const players = await res.json();
        players.forEach(p => {
            const div = document.createElement("div");

div.className = "player-card";
div.innerHTML = `
  <div class="player-name">${p.name}</div>
  <div class="player-stats">
    <label><input type="checkbox" name="players[${p.id}][played]"> Играл</label>
    <label><input type="checkbox" name="players[${p.id}][late]"> Опозд.</label>
    <label>Г: <input type="number" name="players[${p.id}][goals]" value="0" min="0"></label>
    <label>А: <input type="number" name="players[${p.id}][assists]" value="0" min="0"></label>
    <label>Проп: <input type="number" name="players[${p.id}][goals_conceded]" value="0" min="0"></label>
    <label>На0 <input type="checkbox" name="players[${p.id}][clean_sheet]"></label>
    <label>ЖК <input type="checkbox" name="players[${p.id}][yellow_card]"></label>
    <label>КК <input type="checkbox" name="players[${p.id}][red_card]"></label>
    <label>Пен <input type="checkbox" name="players[${p.id}][missed_penalty]"></label>
    <label>Не заявлен <input type="checkbox" class="unlisted-chb" name="players[${p.id}][unlisted]"></label>
  </div>
`;

container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
        container.innerHTML = "<p style='color:red;'>Ошибка загрузки игроков</p>";
    }
}

// Если "Не заявлен" — снимаем "Играл", и наоборот
document.addEventListener('change', (e) => {
  if (e.target.matches('.unlisted-chb')) {
    const wrap = e.target.closest('.player-card');
    const played = wrap.querySelector(`input[name*='[played]']`);
    if (e.target.checked) played.checked = false;
  }
  if (e.target.name.includes('[played]') && e.target.checked) {
    const wrap = e.target.closest('.player-card');
    const unlisted = wrap.querySelector('.unlisted-chb');
    if (unlisted) unlisted.checked = false;
  }
});

document.getElementById("matchTeamSelect").addEventListener("change", function () {
    const teamId = this.value;
    loadPlayersForSelectedTeam(teamId);
});

document.getElementById("addMatchForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => data[key] = value);

    const matchTeamSelect = document.getElementById("matchTeamSelect");
    data.teams_id = matchTeamSelect.value;
    data.our_team = matchTeamSelect.options[matchTeamSelect.selectedIndex]?.textContent || '';

    const messageDiv = document.getElementById("matchMessage");

    try {
        // === 1. Сохраняем матч ===
        const res = await fetch("/api/matches.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        const result = await res.json();
        if (!res.ok || !result?.success) {
            throw new Error(result?.error || "Ошибка при добавлении матча");
        }

        const matchId = result.match_id;
        const year = data.year;
        const unlisted = [];

        // === 2. Собираем данные по игрокам ===
        const players = {};
const playerDivs = document.querySelectorAll("#playerStatsContainer > div");

for (const div of playerDivs) {
    const unlistedChb = div.querySelector(`input[name*='[unlisted]']`);
if (unlistedChb && unlistedChb.checked) {
    const match = unlistedChb.name.match(/players\[(\d+)\]/);
    if (match) unlisted.push({ player_id: parseInt(match[1]) });
    continue; // если не заявлен — пропускаем игрока (не добавляем в статистику)
}

    const checkbox = div.querySelector("input[type='checkbox'][name*='played']");
    const match = checkbox?.name.match(/players\[(\d+)\]/);
    if (!match) continue;

    const playerId = match[1];
    if (!checkbox.checked) continue;

players[playerId] = {
   played: checkbox.checked ? 1 : 0,
  goals: +div.querySelector(`input[name="players[${playerId}][goals]"]`)?.value || 0,
  assists: +div.querySelector(`input[name="players[${playerId}][assists]"]`)?.value || 0,
  goals_conceded: +div.querySelector(`input[name="players[${playerId}][goals_conceded]"]`)?.value || 0,
  clean_sheet: div.querySelector(`input[name="players[${playerId}][clean_sheet]"]`)?.checked ? 1 : 0,
  yellow_cards: div.querySelector(`input[name="players[${playerId}][yellow_card]"]`)?.checked ? 1 : 0,
  red_cards: div.querySelector(`input[name="players[${playerId}][red_card]"]`)?.checked ? 1 : 0,
  missed_penalties: div.querySelector(`input[name="players[${playerId}][missed_penalty]"]`)?.checked ? 1 : 0
};

    const lateCheckbox = div.querySelector(`input[name="players[${playerId}][late]"]`);
    if (lateCheckbox?.checked) {
        await fetch("/api/add_fine.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                player_id: parseInt(playerId),
                amount: 250,
                reason: "Опоздание на игру",
                date: data.date
            })
        });
    }
}

        // === 3. Отправляем игроков ===
       const playerRes = await fetch("/api/match_players.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ match_id: matchId, players })
});

        if (!playerRes.ok) {
            const err = await playerRes.json();
            throw new Error("Матч добавлен, но ошибка при сохранении игроков: " + (err.error || "неизвестная"));
        }

// === 4. Сохраняем незаявленных игроков ===
if (unlisted.length > 0) {
    const resUnlisted = await fetch("/api/unlisted_players.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ match_id: matchId, players: unlisted })
    });
    const unlistedResult = await resUnlisted.json();
    if (!resUnlisted.ok || !unlistedResult.success) {
        throw new Error("Ошибка при добавлении незаявленных игроков");
    }
}

        messageDiv.className = "success-message";
        messageDiv.textContent = `✅ Матч и игроки добавлены! ID: ${matchId}`;
        form.reset();
        document.getElementById("playerStatsContainer").innerHTML = "";

    for (const playerId in players) {
    try {
  const id = parseInt(playerId, 10);

  // 1) Текущие ачивки -> Set<number>
  const successRes = await fetch(`/api/get_player_success.php?player_id=${id}`);
  let currentSuccessesRaw = await successRes.json();

  // нормализуем всё к числам
  const currentSet = new Set(
    (Array.isArray(currentSuccessesRaw) ? currentSuccessesRaw : [])
      .map(item => {
        // поддержка разных форматов ответа:
        // [23, "18", { success_id: 55 }, {id: 61}, {successId: "33"}]
        if (typeof item === 'number') return item;
        if (typeof item === 'string') return parseInt(item, 10);
        if (item && typeof item === 'object') {
          const v = item.success_id ?? item.id ?? item.successId ?? item.sId;
          return v != null ? parseInt(v, 10) : NaN;
        }
        return NaN;
      })
      .filter(n => Number.isFinite(n))
  );

  // 2) Собираем новые ачивки с проверкой по currentSet
  const newAchievements = [];

  const addIf = (cond, id) => { if (cond && !currentSet.has(id)) newAchievements.push(id); };

  // ==== одноразовые ====
  addIf(totalGoals >= 1, 23);     // первый гол
  addIf(totalMatches >= 1, 1);    // первый матч
  addIf(awardCount >= 1, 18);
  addIf(awardCount >= 5, 19);
  addIf(awardCount >= 10, 20);
  addIf(totalAssists >= 1, 24);

  // ==== матчи ====
  addIf(totalMatches >= 25, 27);
  addIf(totalMatches >= 50, 28);
  addIf(totalMatches >= 100, 29);
  addIf(totalMatches >= 250, 30);
  addIf(totalMatches >= 500, 31);

  // ==== голы ====
  addIf(totalGoals >= 10, 32);
  addIf(totalGoals >= 50, 34);
  addIf(totalGoals >= 100, 36);
  addIf(totalGoals >= 250, 38);
  addIf(totalGoals >= 500, 40);

  // ==== ассисты ====
  addIf(totalAssists >= 10, 33);
  addIf(totalAssists >= 50, 35);
  addIf(totalAssists >= 100, 37);
  addIf(totalAssists >= 250, 39);
  addIf(totalAssists >= 500, 41);

  // ==== голы в этом матче ====
  addIf(goalsInThisMatch === 2, 42);
  addIf(goalsInThisMatch === 3, 43);
  addIf(goalsInThisMatch === 4, 44);
  addIf(goalsInThisMatch >= 5, 45);

  // ==== фото ====
  addIf(!!photo, 55);

  // ==== стаж в месяцах ====
  addIf(diffMonths >= 6, 60);
  addIf(diffMonths >= 12, 61);
  addIf(diffMonths >= 36, 62);
  addIf(diffMonths >= 60, 63);
  addIf(diffMonths >= 120, 64);

  // ==== чистый матч вратаря сегодня ====
  addIf(playedThisMatch && cleanSheetThisMatch && position.includes('вратар'), 70);

  // ==== суммарные "на 0" для GK/DEF ====
  if (position.includes('вратар') || position.includes('защит')) {
    addIf(totalCleanSheets >= 5, 83);
    addIf(totalCleanSheets >= 15, 84);
    addIf(totalCleanSheets >= 25, 85);
  }

  if (newAchievements.length > 0) {
    // либо отправляем только дельту в add-ручку (если есть):
    // await fetch('/api/add_player_success.php', {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({ player_id: id, success_ids: newAchievements })
    // });

    // либо (если есть только set-ручка, которая ЗАМЕНЯЕТ список) —
    // объединяем ТЕКУЩИЕ + НОВЫЕ строго как числа:
    const merged = [...new Set([...currentSet, ...newAchievements])];

   await fetch('/api/add_player_success.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
      player_id: id,
      success_ids: newAchievements
  })
});

    console.log(`🎖 Выданы новые ачивки игроку ${id}: ${newAchievements.join(', ')}`);
  }
} catch (err) {
  console.error(`❌ Ошибка присвоения ачивок игроку ${playerId}:`, err);
}
}

    } catch (err) {
        console.error(err);
        messageDiv.className = "error-message";
        messageDiv.textContent = "❌ " + err.message;
    }
});

document.addEventListener("DOMContentLoaded", loadTeamsIntoMatchForm);
</script>


</body>
</html>
