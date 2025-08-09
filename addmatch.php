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

    for (const playerId in players) {
    try {
        const id = parseInt(playerId);

        // Получаем текущие ачивки
        const successRes = await fetch(`/api/get_player_success.php?player_id=${id}`);
        const currentSuccesses = await successRes.json(); // [1, 18, 23, ...]

        // Получаем статистику
        const statsRes = await fetch(`/api/player_statistics_all.php?id=${id}`);
        const stats = await statsRes.json();
        const totalGoals = (parseInt(stats.goals) || 0) + (parseInt(stats?.season?.goals) || 0);
        const totalAssists = (parseInt(stats.assists) || 0) + (parseInt(stats?.season?.assists) || 0);
        const totalMatches = (parseInt(stats.matches) || 0) + (parseInt(stats?.season?.matches) || 0);
        const totalCleanSheets = (parseInt(stats.zeromatch) || 0) + (parseInt(stats?.season?.zeromatch) || 0);

        // Получаем награды
        const awardsRes = await fetch(`/api/achievements.php?player_id=${id}`);
        const awards = await awardsRes.json();
        const awardCount = Array.isArray(awards) ? awards.length : 0;

        // Получаем инфу о игроке (позиция, фото, дата присоединения)
        const playerMetaRes = await fetch(`/api/get_player.php?id=${id}`);
        const playerMeta = await playerMetaRes.json();
        const joinDate = new Date(playerMeta?.join_date);
        const position = playerMeta?.position?.toLowerCase() || '';
        const photo = playerMeta?.photo;
        const today = new Date();
        const diffMonths = (today.getFullYear() - joinDate.getFullYear()) * 12 + (today.getMonth() - joinDate.getMonth());

        const newAchievements = [];

        // === Ачивки по одному действию ===
        if (totalGoals >= 1 && !currentSuccesses.includes(23)) newAchievements.push(23); // Первый гол
        if (totalMatches >= 1 && !currentSuccesses.includes(1)) newAchievements.push(1); // Первый матч
        if (awardCount >= 1 && !currentSuccesses.includes(18)) newAchievements.push(18); // 1 награда
        if (awardCount >= 5 && !currentSuccesses.includes(19)) newAchievements.push(19); // 5 наград
        if (awardCount >= 10 && !currentSuccesses.includes(20)) newAchievements.push(20); // 10 наград
        if (totalAssists >= 1 && !currentSuccesses.includes(24)) newAchievements.push(24); // Первый ассист

        // === Кол-во матчей ===
        if (totalMatches >= 25 && !currentSuccesses.includes(27)) newAchievements.push(27);
        if (totalMatches >= 50 && !currentSuccesses.includes(28)) newAchievements.push(28);
        if (totalMatches >= 100 && !currentSuccesses.includes(29)) newAchievements.push(29);
        if (totalMatches >= 250 && !currentSuccesses.includes(30)) newAchievements.push(30);
        if (totalMatches >= 500 && !currentSuccesses.includes(31)) newAchievements.push(31);

        // === Голы ===
        if (totalGoals >= 10 && !currentSuccesses.includes(32)) newAchievements.push(32);
        if (totalGoals >= 50 && !currentSuccesses.includes(34)) newAchievements.push(34);
        if (totalGoals >= 100 && !currentSuccesses.includes(36)) newAchievements.push(36);
        if (totalGoals >= 250 && !currentSuccesses.includes(38)) newAchievements.push(38);
        if (totalGoals >= 500 && !currentSuccesses.includes(40)) newAchievements.push(40);

        // === Ассисты ===
        if (totalAssists >= 10 && !currentSuccesses.includes(33)) newAchievements.push(33);
        if (totalAssists >= 50 && !currentSuccesses.includes(35)) newAchievements.push(35);
        if (totalAssists >= 100 && !currentSuccesses.includes(37)) newAchievements.push(37);
        if (totalAssists >= 250 && !currentSuccesses.includes(39)) newAchievements.push(39);
        if (totalAssists >= 500 && !currentSuccesses.includes(41)) newAchievements.push(41);

        // === Голы в текущем матче ===
        const goalsInThisMatch = players[playerId]?.goals || 0;
        if (goalsInThisMatch === 2 && !currentSuccesses.includes(42)) newAchievements.push(42);
        if (goalsInThisMatch === 3 && !currentSuccesses.includes(43)) newAchievements.push(43);
        if (goalsInThisMatch === 4 && !currentSuccesses.includes(44)) newAchievements.push(44);
        if (goalsInThisMatch >= 5 && !currentSuccesses.includes(45)) newAchievements.push(45);

        // === Фото есть
        if (photo && !currentSuccesses.includes(55)) newAchievements.push(55);

        // === Время в команде
        if (diffMonths >= 6 && !currentSuccesses.includes(60)) newAchievements.push(60);
        if (diffMonths >= 12 && !currentSuccesses.includes(61)) newAchievements.push(61);
        if (diffMonths >= 36 && !currentSuccesses.includes(62)) newAchievements.push(62);
        if (diffMonths >= 60 && !currentSuccesses.includes(63)) newAchievements.push(63);
        if (diffMonths >= 120 && !currentSuccesses.includes(64)) newAchievements.push(64);

        // === Вратарь сыграл на 0 в этом матче
        const playedThisMatch = players[playerId]?.played;
        const cleanSheetThisMatch = players[playerId]?.clean_sheet;
        if (playedThisMatch && cleanSheetThisMatch && position.includes('вратарь') && !currentSuccesses.includes(70)) {
            newAchievements.push(70);
        }

        // === Сухие матчи для вратаря или защитника
        if (position.includes('вратарь') || position.includes('защитник')) {
            if (totalCleanSheets >= 5 && !currentSuccesses.includes(83)) newAchievements.push(83);
            if (totalCleanSheets >= 15 && !currentSuccesses.includes(84)) newAchievements.push(84);
            if (totalCleanSheets >= 25 && !currentSuccesses.includes(85)) newAchievements.push(85);
        }

        // === Отправляем, если есть что присваивать
        if (newAchievements.length > 0) {
            const combined = [...new Set([...currentSuccesses, ...newAchievements])];

            await fetch('/api/set_player_success.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    player_id: id,
                    success_ids: combined
                })
            });

            console.log(`🎖 Ачивки выданы игроку ${id}: ${newAchievements.join(", ")}`);
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
