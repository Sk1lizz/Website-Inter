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
    margin: 10px 0;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.player-row {
    display: grid;
    grid-template-columns: 200px repeat(6, auto);
    align-items: center;
    gap: 10px;
}

.player-name {
    font-weight: bold;
    color: #333;
    white-space: nowrap;
}

.player-row label {
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.player-row input[type="number"] {
    width: 50px;
    padding: 4px;
}


     </style>

    <title>Добавить матч</title>

</head>

<body>
<div class="admin-panel">

    <form method="post" action="logout.php" style="float:right;">
        <button type="submit">Выйти</button>
    </form>

    <form action="admin.php" method="get" style="display: inline-block; margin-top: 20px;">
        <button type="submit">Статистика</button>
    </form>

    <form action="success.php" method="get" style="display: inline-block; margin-top: 20px;">
        <button type="submit">Ачивки</button>
    </form>

    <form action="statisticsall.php" method="get" style="display: inline-block; margin-top: 20px;">
    <button type="submit">Статистика общая</button>
</form>


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
    <div class="player-row">
        <div class="player-name">${p.name}</div>
        <label><input type="checkbox" name="players[${p.id}][played]"> Играл</label>
        <label><input type="checkbox" name="players[${p.id}][late]"> Опоздание</label>
        <label>Голы: <input type="number" name="players[${p.id}][goals]" value="0" min="0"></label>
        <label>Ассисты: <input type="number" name="players[${p.id}][assists]" value="0" min="0"></label>
        <label>Пропущено: <input type="number" name="players[${p.id}][goals_conceded]" value="0" min="0"></label>
        <label>На 0: <input type="checkbox" name="players[${p.id}][clean_sheet]"></label>
    </div>
`;

container.appendChild(div);
        });
    } catch (err) {
        console.error(err);
        container.innerHTML = "<p style='color:red;'>Ошибка загрузки игроков</p>";
    }
}

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

        // === 2. Собираем данные по игрокам ===
        const players = {};
const playerDivs = document.querySelectorAll("#playerStatsContainer > div");

for (const div of playerDivs) {
    const checkbox = div.querySelector("input[type='checkbox'][name*='played']");
    const match = checkbox?.name.match(/players\[(\d+)\]/);
    if (!match) continue;

    const playerId = match[1];
    if (!checkbox.checked) continue;

    players[playerId] = {
        played: checkbox.checked,
        goals: +div.querySelector(`input[name="players[${playerId}][goals]"]`)?.value || 0,
        assists: +div.querySelector(`input[name="players[${playerId}][assists]"]`)?.value || 0,
        goals_conceded: +div.querySelector(`input[name="players[${playerId}][goals_conceded]"]`)?.value || 0,
        clean_sheet: !!div.querySelector(`input[name="players[${playerId}][clean_sheet]"]`)?.checked
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
            body: JSON.stringify({ match_id: matchId, year, players })
        });

        if (!playerRes.ok) {
            const err = await playerRes.json();
            throw new Error("Матч добавлен, но ошибка при сохранении игроков: " + (err.error || "неизвестная"));
        }

        messageDiv.className = "success-message";
        messageDiv.textContent = `✅ Матч и игроки добавлены! ID: ${matchId}`;
        form.reset();
        document.getElementById("playerStatsContainer").innerHTML = "";

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
