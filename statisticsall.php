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
    <title>Общая статистика игроков (без учета текущего сезона)</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
       body {
        padding: 20px;
    }

    h1 {
        margin-bottom: 20px;
    }

    .buttons-wrapper {
        margin-bottom: 20px;
    }

    .buttons-wrapper form {
        display: inline-block;
        margin-right: 10px;
    }

    .styled-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-top: 20px;
        font-size: 14px;
        font-family: Arial, sans-serif;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .styled-table thead tr {
        background-color: #009879;
        color: #ffffff;
        text-align: center;
    }

    .styled-table th, .styled-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }

    .styled-table tbody tr {
        background-color: #f9f9f9;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f1f1f1;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }

    .styled-table input[type="number"] {
        width: 60px;
        padding: 5px;
        text-align: center;
    }

    .styled-table button.save-btn {
        padding: 5px 10px;
        background-color: #009879;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .styled-table button.save-btn:hover {
        background-color: #007f65;
    }

    label {
        font-weight: bold;
    }

    #teamSelect {
        padding: 5px 10px;
        margin-bottom: 10px;
    }
    </style>
</head>
<body>

<h1>Общая статистика игроков (без учета текущего сезона)</h1>

<!-- Кнопки -->
<div class="buttons-wrapper">
    <form method="post" action="logout.php" style="float:right;">
        <button type="submit">Выйти</button>
    </form>

    <form action="admin.php" method="get">
        <button type="submit">Статистика</button>
    </form>

    <form action="success.php" method="get">
        <button type="submit">Ачивки</button>
    </form>

    <form action="addmatch.php" method="get">
        <button type="submit">Добавить матч</button>
    </form>
</div>

<hr>

<!-- Выбор команды -->
<label for="teamSelect">Выберите команду:</label>
<select id="teamSelect">
    <option value="">Все команды</option>
</select>

<!-- Таблица статистики -->
<table id="statsTable" class="styled-table">
    <thead>
    <tr>
        <th>№</th>
        <th>Имя</th>
        <th>Матчей</th>
        <th>Голов</th>
        <th>Ассистов</th>
        <th>Гол+пас</th>
        <th>Голов пропущено</th>
        <th>Матчей на 0</th>
        <th>Приз Дзанетти</th>
        <th>Сохранить</th>
    </tr>
    </thead>
    <tbody>
    <!-- Строки будут добавлены через JS -->
    </tbody>
</table>

<script>
    async function loadTeams() {
        try {
            const response = await fetch('/api/get_teams.php');
            const teams = await response.json();
            console.log('Загружены команды:', teams);

            const select = document.getElementById('teamSelect');
            teams.forEach(team => {
                const option = document.createElement('option');
                option.value = team.id;
                option.textContent = team.name;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Ошибка загрузки команд:', error);
        }
    }

    async function loadStats(teamId = '') {
        if (!teamId) {
            document.querySelector('#statsTable tbody').innerHTML = '<tr><td colspan="10">Выберите команду</td></tr>';
            return;
        }

        try {
            const response = await fetch(`/api/get_players_all.php?team_id=${encodeURIComponent(teamId)}`);
            const players = await response.json();
            console.log('Загружены игроки:', players);

            const tbody = document.querySelector('#statsTable tbody');
            tbody.innerHTML = '';

            players.sort((a, b) => (b.stats.matches || 0) - (a.stats.matches || 0)); // сортировка по матчам убыв.

            players.forEach(player => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${player.id}</td>
                    <td>${player.name}</td>
                    <td><input type="number" value="${player.stats.matches ?? 0}" data-field="matches" data-player-id="${player.id}"></td>
                    <td><input type="number" value="${player.stats.goals ?? 0}" data-field="goals" data-player-id="${player.id}"></td>
                    <td><input type="number" value="${player.stats.assists ?? 0}" data-field="assists" data-player-id="${player.id}"></td>
                    <td>
     <span style="display:inline-block; width:60px; text-align:center;">${(player.stats.goals ?? 0) + (player.stats.assists ?? 0)}</span>
</td>
                    <td><input type="number" value="${player.stats.lostgoals ?? 0}" data-field="lostgoals" data-player-id="${player.id}"></td>
                    <td><input type="number" value="${player.stats.zeromatch ?? 0}" data-field="zeromatch" data-player-id="${player.id}"></td>
                    <td><input type="number" value="${player.stats.zanetti_priz ?? 0}" data-field="zanetti_priz" data-player-id="${player.id}"></td>
                    <td><button class="save-btn" data-player-id="${player.id}">Сохранить</button></td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Ошибка загрузки статистики игроков:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadTeams();

        document.getElementById('teamSelect').addEventListener('change', (e) => {
            loadStats(e.target.value);
        });
    });

    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('save-btn')) {
            const playerId = e.target.getAttribute('data-player-id');
            const row = e.target.closest('tr');
            const inputs = row.querySelectorAll('input');

            const data = {
                player_id: playerId
            };

            inputs.forEach(input => {
                data[input.getAttribute('data-field')] = parseInt(input.value) || 0;
            });

            try {
                const response = await fetch('/api/save_player_stats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    alert('Данные сохранены');
                } else {
                    alert('Ошибка при сохранении: ' + (result.message || 'неизвестно'));
                }
            } catch (error) {
                console.error('Ошибка при сохранении:', error);
                alert('Ошибка при сохранении');
            }
        }
    });
</script>

</body>
</html>
