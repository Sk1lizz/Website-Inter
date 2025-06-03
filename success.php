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
    <title>Достижения игроков</title>
    <style>
        body { font-family: sans-serif; background: #f9f9f9; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        select, button { padding: 10px; margin-top: 10px; width: 100%; }
        .success-list { margin-top: 20px; columns: 2; }
        .success-item { margin-bottom: 8px; }
        .success-item label { cursor: pointer; }
        .hidden { display: none; }
        .notice { color: green; }
    </style>
</head>
<body>
<div class="container">
    <h2>Присвоение достижений</h2>

    <label>Команда:</label>
    <select id="teamSelect"></select>

    <label>Игрок:</label>
    <select id="playerSelect" disabled></select>

    <div id="successContainer" class="success-list hidden"></div>

    <button id="saveBtn" class="hidden">💾 Сохранить</button>

    <p id="statusMsg" class="notice hidden">✔ Сохранено</p>
</div>

<script>
const teamSelect = document.getElementById('teamSelect');
const playerSelect = document.getElementById('playerSelect');
const successContainer = document.getElementById('successContainer');
const saveBtn = document.getElementById('saveBtn');
const statusMsg = document.getElementById('statusMsg');

let allSuccesses = [];

async function fetchJSON(url) {
    const res = await fetch(url);
    return res.json();
}

async function loadTeams() {
    const teams = await fetchJSON('/api/get_teams.php');
    teamSelect.innerHTML = '<option value="">-- Выберите команду --</option>';
    teams.forEach(team => {
        const opt = document.createElement('option');
        opt.value = team.id;
        opt.textContent = team.name;
        teamSelect.appendChild(opt);
    });
}

async function loadPlayers(teamId) {
    const players = await fetchJSON(`/api/get_players.php?team_id=${teamId}`);
    playerSelect.innerHTML = '<option value="">-- Выберите игрока --</option>';
    players.forEach(player => {
        const opt = document.createElement('option');
        opt.value = player.id;
        opt.textContent = player.name;
        playerSelect.appendChild(opt);
    });
    playerSelect.disabled = false;
}

async function loadSuccessList() {
    allSuccesses = await fetchJSON('/api/get_success_list.php');
}

async function loadPlayerSuccess(playerId) {
    const assigned = await fetchJSON(`/api/get_player_success.php?player_id=${playerId}`);
    renderSuccessCheckboxes(assigned);
}

function renderSuccessCheckboxes(assignedIds) {
    successContainer.innerHTML = '';
    allSuccesses.forEach(s => {
        const div = document.createElement('div');
        div.className = 'success-item';
        const checked = assignedIds.includes(s.id) ? 'checked' : '';
        div.innerHTML = `<label><input type="checkbox" value="${s.id}" ${checked}> <strong>${s.title}</strong>: ${s.description}</label>`;
        successContainer.appendChild(div);
    });
    successContainer.classList.remove('hidden');
    saveBtn.classList.remove('hidden');
}

saveBtn.addEventListener('click', async () => {
    const playerId = playerSelect.value;
    const checked = [...successContainer.querySelectorAll('input[type=checkbox]:checked')].map(cb => parseInt(cb.value));

    const res = await fetch('/api/set_player_success.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({player_id: parseInt(playerId), success_ids: checked})
    });

    if (res.ok) {
        statusMsg.classList.remove('hidden');
        setTimeout(() => statusMsg.classList.add('hidden'), 2000);
    }
});

teamSelect.addEventListener('change', async () => {
    const teamId = teamSelect.value;
    if (teamId) await loadPlayers(teamId);
    playerSelect.value = '';
    successContainer.classList.add('hidden');
    saveBtn.classList.add('hidden');
});

playerSelect.addEventListener('change', async () => {
    const playerId = playerSelect.value;
    if (playerId) await loadPlayerSuccess(playerId);
});

(async () => {
    await loadTeams();
    await loadSuccessList();
})();
</script>
</body>
</html>