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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Назначение отпуска</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .on-holiday {
            background-color: #fcbcbc;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .styled-table th, .styled-table td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .styled-table thead {
            background-color: #009879;
            color: white;
        }

        .styled-table tr:nth-child(even) {
            background-color: #f3f3f3;
        }

        select {
            padding: 5px 10px;
            margin-bottom: 10px;
        }

        button {
            padding: 6px 12px;
            background-color: #004080;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        button:hover {
            background-color: #003060;
        }
    </style>
</head>
<body>

<h2>Назначение отпуска игрокам</h2>

<!-- Командное меню -->
<label for="teamSelect">Команда:</label>
<select id="teamSelect">
    <option value="">-- выберите --</option>
    <?php foreach ($teams as $team): ?>
        <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
    <?php endforeach; ?>
</select>

<!-- Месяц -->
<label for="monthSelect">Месяц отпуска:</label>
<select id="monthSelect"></select>

<table class="styled-table" id="playerTable">
    <thead>
        <tr>
            <th>Имя</th>
<th>Отпуск</th>
<th>Действие</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
function getMonths() {
    const select = document.getElementById('monthSelect');
    const now = new Date();
    for (let i = 0; i < 3; i++) {
        const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
        const value = d.getFullYear().toString() + String(d.getMonth() + 1).padStart(2, '0');
        const label = d.toLocaleString('ru-RU', { month: 'long', year: 'numeric' });
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        select.appendChild(option);
    }
}

async function loadPlayers(teamId) {
    const month = document.getElementById('monthSelect').value;
    if (!teamId || !month) return;

    try {
        const resPlayers = await fetch(`api/get_players_by_team.php?team_id=${teamId}`);
        const resHolidays = await fetch(`api/get_holidays.php?month=${month}`);

        const players = await resPlayers.json();
        const holidayIds = await resHolidays.json();

        if (!Array.isArray(players)) {
            alert('Ошибка загрузки игроков');
            return;
        }

        const tbody = document.querySelector('#playerTable tbody');
        tbody.innerHTML = '';

       players.forEach(player => {
    const isHoliday = holidayIds.includes(parseInt(player.id));
    const tr = document.createElement('tr');
    if (isHoliday) tr.classList.add('on-holiday');

    tr.innerHTML = `
        <td>${player.name} ${player.patronymic || ''}</td>
        <td>${isHoliday ? 'Да' : 'Нет'}</td>
        <td>
            ${isHoliday
                ? `<button onclick="removeHoliday(${player.id})">Отменить</button>`
                : `<button onclick="assignHoliday(${player.id})">Отправить</button>`}
        </td>
    `;

    tbody.appendChild(tr); // ← Вот это обязательно
});

    } catch (err) {
        console.error('Ошибка при загрузке:', err);
        alert('Не удалось загрузить игроков или отпуска');
    }
}

async function assignHoliday(playerId) {
    const month = document.getElementById('monthSelect').value;
    if (!month) {
        alert('Выберите месяц');
        return;
    }

    const res = await fetch('api/set_holiday.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ player_id: playerId, month })
    });

    const data = await res.json();
    if (data.success) {
        alert('Отпуск назначен');
        loadPlayers(document.getElementById('teamSelect').value);
    } else {
        alert('Ошибка при назначении: ' + (data.message || 'неизвестно'));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    getMonths();

    document.getElementById('teamSelect').addEventListener('change', (e) => {
        const teamId = e.target.value;
        if (teamId) loadPlayers(teamId);
    });

    document.getElementById('monthSelect').addEventListener('change', () => {
        const teamId = document.getElementById('teamSelect').value;
        if (teamId) loadPlayers(teamId);
    });
});

async function removeHoliday(playerId) {
    const month = document.getElementById('monthSelect').value;
    if (!month) {
        alert('Выберите месяц');
        return;
    }

    const res = await fetch('api/remove_holiday.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ player_id: playerId, month })
    });

    const data = await res.json();
    if (data.success) {
        alert('Отпуск отменён');
        loadPlayers(document.getElementById('teamSelect').value);
    } else {
        alert('Ошибка при отмене: ' + (data.message || 'неизвестно'));
    }
}
</script>
</body>
</html>
