<?php
session_start();

// Установите логин/пароль
define('ADMIN_LOGIN', 'admin');
define('ADMIN_PASS', 'fcinter2025');

// Обработка формы входа
if (isset($_POST['auth_login'], $_POST['auth_pass'])) {
    if ($_POST['auth_login'] === ADMIN_LOGIN && $_POST['auth_pass'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Если не вошли — показать форму
if (!isset($_SESSION['admin_logged_in'])):
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
</head>
<body>
    <h2>Авторизация</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <label>Логин: <input name="auth_login" required></label><br>
        <label>Пароль: <input type="password" name="auth_pass" required></label><br>
        <button type="submit">Войти</button>
    </form>
</body>
</html>
<?php
exit;
endif;
?>

<!DOCTYPE html>
<html lang="ru">

<meta charset="UTF-8">
    <title>Админка - Статистика игроков</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f2f2f2;
            color: #333;
        }
        h1, h2 {
            color: #1a1a1a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #004080;
            color: #fff;
        }
        input[type="number"], input[type="text"], input[type="date"], select {
            width: 95%;
            padding: 6px;
            margin: 2px 0;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            padding: 6px 14px;
            background-color: #004080;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #003366;
        }
        form label {
            display: block;
            margin: 8px 0 4px;
        }
        form input, form select {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        #editPlayerModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 10;
        }
        .modal-content {
            background: white;
            padding: 20px;
            max-width: 500px;
            margin: 100px auto;
            border-radius: 8px;
        }
    </style>

<body>

<form method="post" action="logout.php" style="float:right;">
    <button type="submit">Выйти</button>
</form>
    <h1>Выберите команду</h1>
    <select id="teamSelect"></select>

    <h2>Список игроков</h2>
    <table id="playersTable" border="1">
        <thead>
            <tr>
                <th>Имя</th>
                <th>Матчи</th>
                <th>Голы</th>
                <th>Ассисты</th>
                <th>Матчи без пропущенных</th>
                <th>Пропущенные мячи</th>
                <th>Тренировки</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button id="saveStatsBtn">Сохранить изменения</button>

    <h2>Добавить игрока</h2>
    <form id="add-player-form">
        <label>Выберите команду:
            <select id="team-select" required></select>
            <option value="">-- Выберите команду --</option>
            </select>
        </label>
        <label>Фамилия и имя (полное имя): <input name="name" required placeholder="Иванов Иван"></label><br>
        <label>Отчество: <input name="patronymic"></label><br>
        <label>Игровой номер: <input name="number" type="number" required></label><br>
        <label>Позиция:
            <select name="position" required>
                <option value="">-- Выберите позицию --</option>
                <option value="Вратарь">Вратарь</option>
                <option value="Защитник">Защитник</option>
                <option value="Полузащитник">Полузащитник</option>
                <option value="Нападающий">Нападающий</option>
            </select>
        </label><br>
        <label>Дата рождения: <input name="birth_date" type="date" required></label><br>
        <label>Дата присоединения: <input name="join_date" type="date" required></label><br>
        <label>Рост (см): <input name="height_cm" type="number"></label><br>
        <label>Вес (кг): <input name="weight_kg" type="number"></label><br>
        <button type="submit">Добавить игрока</button>
        </div>
    </form>

    <div id="editPlayerModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Редактировать игрока</h2>
            <form id="editPlayerForm">
                <label>ФИО: <input type="text" name="name" required></label><br>
                <label>Отчество: <input type="text" name="patronymic"></label><br>
                <label>Дата рождения: <input type="date" name="birth_date" required></label><br>
                <label>Номер: <input type="number" name="number" required></label><br>
                <label>Позиция: <input type="text" name="position" required></label><br>
                <label>Рост (см): <input type="number" name="height_cm"></label><br>
                <label>Вес (кг): <input type="number" name="weight_kg"></label><br>
                <label>Команда:
                    <select name="team_id" id="editPlayerTeamSelect"></select>
                </label><br>
                <button type="submit">Сохранить</button>
                <button type="button" onclick="closeEditPlayerModal()">Отмена</button>
            </form>
        </div>

        <script>
            const teamSelectTop = document.getElementById('teamSelect');
            const teamSelectForm = document.getElementById('team-select');


            async function loadTeams() {
                const res = await fetch('api/get_teams.php');
                if (!res.ok) {
                    alert('Ошибка загрузки команд');
                    return;
                }
                const teams = await res.json();

                // Очистить селекты и добавить option по умолчанию
                [teamSelectTop, teamSelectForm].forEach(select => {
                    select.innerHTML = '<option value="">-- Выберите команду --</option>';
                });

                teams.forEach(team => {
                    const option1 = document.createElement('option');
                    option1.value = team.id;
                    option1.textContent = team.name;
                    teamSelectTop.appendChild(option1);

                    const option2 = document.createElement('option');
                    option2.value = team.id;
                    option2.textContent = team.name;
                    teamSelectForm.appendChild(option2);
                });
            }

            let currentTeamId = null;

            function onTeamChange(newTeamId) {
                currentTeamId = newTeamId;

                // Синхронизируем оба селектора
                teamSelectTop.value = newTeamId;
                teamSelectForm.value = newTeamId;

                if (newTeamId) {
                    fetchPlayers(newTeamId);
                } else {
                    clearPlayersTable();
                }
            }

            teamSelectTop.addEventListener('change', e => {
                onTeamChange(e.target.value);
            });

            teamSelectForm.addEventListener('change', e => {
                onTeamChange(e.target.value);
            });

            function clearPlayersTable() {
                const tbody = document.querySelector('#playersTable tbody');
                tbody.innerHTML = '';
            }

            async function fetchPlayers(teamId) {
                const res = await fetch(`api/get_players.php?team_id=${teamId}`)
                const players = await res.json();
                const tbody = document.querySelector('#playersTable tbody');
                tbody.innerHTML = '';

                players.forEach(p => {
                    const row = document.createElement('tr');
                    row.dataset.playerId = p.id;

                    row.innerHTML = `
        <td>${p.name}</td>
        <td><input type="number" name="matches" value="${p.stats.matches}" min="0"></td>
        <td><input type="number" name="goals" value="${p.stats.goals}" min="0"></td>
        <td><input type="number" name="assists" value="${p.stats.assists}" min="0"></td>
        <td><input type="number" name="zeromatch" value="${p.stats.zeromatch}" min="0"></td>
        <td><input type="number" name="lostgoals" value="${p.stats.lostgoals}" min="0"></td>
        <td><input type="number" name="zanetti_priz" value="${p.stats.zanetti_priz}" min="0"></td>
        <td>
           <button class="edit-btn" data-player-id="${p.id}">Редактировать</button>
            <button class="delete-btn" data-player-id="${p.id}">Удалить</button>
        </td>
    `;

                    tbody.appendChild(row);
                });
            }

            loadTeams().then(() => {
                // Если есть команды — выбираем первую по умолчанию
                if (teamSelectTop.options.length > 1) {
                    onTeamChange(teamSelectTop.options[1].value);
                }
            });

            document.querySelector('#playersTable tbody').addEventListener('click', async (e) => {
                // Удаление игрока
                if (e.target.classList.contains('delete-btn')) {
                    const playerId = e.target.dataset.playerId;
                    const confirmed = confirm('Вы точно хотите удалить игрока?');
                    if (!confirmed) return;

                    try {
                        const res = await fetch(`api/archive_player.php?id=${playerId}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ new_team_id: 3 })
                        });

                        if (res.ok) {
                            alert('Игрок перемещён в архив.');
                            fetchPlayers(currentTeamId);
                        } else {
                            alert('Ошибка при удалении игрока');
                        }
                    } catch (err) {
                        alert('Ошибка сети');
                        console.error(err);
                    }
                }

                // Редактирование игрока
                if (e.target.classList.contains('edit-btn')) {
                    const playerId = e.target.dataset.playerId;
                    openEditPlayerModal(playerId);
                }
            });

        </script>

        <script>
            const form = document.getElementById('add-player-form');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!currentTeamId) {
                    return alert('Выберите команду');
                }

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                data.team_id = currentTeamId;

                const res = await fetch('api/add_player.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
});

                if (res.ok) {
                    alert('Игрок добавлен!');
                    form.reset();
                    fetchPlayers(currentTeamId);
                } else {
                    alert('Ошибка при добавлении игрока');
                }
            });

        </script>

        <script>
            document.getElementById('saveStatsBtn').addEventListener('click', async () => {
                const rows = document.querySelectorAll('#playersTable tbody tr');
                const updates = [];

                rows.forEach(row => {
                    const playerId = row.dataset.playerId;
                    const stats = {
                        matches: Number(row.querySelector('input[name="matches"]').value),
                        goals: Number(row.querySelector('input[name="goals"]').value),
                        assists: Number(row.querySelector('input[name="assists"]').value),
                        zeromatch: Number(row.querySelector('input[name="zeromatch"]').value),
                        lostgoals: Number(row.querySelector('input[name="lostgoals"]').value),
                        zanetti_priz: Number(row.querySelector('input[name="zanetti_priz"]').value)
                    };
                    updates.push({ playerId, stats });
                });

                try {
                    const res = await fetch('api/update_statistics.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ updates })
});

                    if (res.ok) {
                        alert('Статистика успешно обновлена!');
                    } else {
                        alert('Ошибка при обновлении статистики');
                    }
                } catch (err) {
                    alert('Ошибка сети или сервера');
                    console.error(err);
                }
            });
        </script>

        <script>let editingPlayerId = null;

            async function openEditPlayerModal(playerId) {
                editingPlayerId = playerId;

                // Загружаем данные игрока с сервера
                console.log('Загружаем данные игрока с ID:', playerId);
                const res = await fetch(`api/get_player.php?id=${playerId}`);
                if (!res.ok) {
                    alert('Ошибка загрузки данных игрока');
                    return;
                }
                const player = await res.json();
                console.log('Данные игрока:', player);

                // Загружаем команды
                const teamSelect = document.getElementById('editPlayerTeamSelect');
                teamSelect.innerHTML = '';
                const teamsRes = await fetch('api/get_teams.php');
                const teams = await teamsRes.json();

                teams.forEach(team => {
                    const opt = document.createElement('option');
                    opt.value = team.id;
                    opt.textContent = team.name;
                    teamSelect.appendChild(opt);
                });

                // Заполняем форму
                const form = document.getElementById('editPlayerForm');
                form.name.value = player.name;
                form.patronymic.value = player.patronymic || '';
                form.birth_date.value = player.birth_date ? player.birth_date.substring(0, 10) : '';
                form.number.value = player.number !== undefined && player.number !== null ? player.number : '';
                form.position.value = player.position || '';
                form.height_cm.value = player.height_cm !== undefined && player.height_cm !== null ? player.height_cm : '';
                form.weight_kg.value = player.weight_kg !== undefined && player.weight_kg !== null ? player.weight_kg : '';
                form.team_id.value = player.team_id;

                document.getElementById('editPlayerModal').style.display = 'block';
            }

            function closeEditPlayerModal() {
                document.getElementById('editPlayerModal').style.display = 'none';
                editingPlayerId = null;
            }</script>

        <script>document.getElementById('editPlayerForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const form = e.target;
                if (!editingPlayerId) return;

                const updatedData = {
                    name: form.name.value,
                    patronymic: form.patronymic.value,
                    birth_date: form.birth_date.value,
                    number: +form.number.value,
                    position: form.position.value,
                    height_cm: form.height_cm.value || null,
                    weight_kg: form.weight_kg.value || null,
                    team_id: +form.team_id.value
                };

                const res = await fetch(`api/update_player.php?id=${editingPlayerId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updatedData)
                });

                if (res.ok) {
                    alert('Игрок обновлен');
                    closeEditPlayerModal();
                    await fetchPlayers(currentTeamId);
                } else {
                    alert('Ошибка при обновлении игрока');
                }
            });</script>


</body>

</html>