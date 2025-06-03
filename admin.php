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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: #004080;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            background-color: #004080;
            color: white;
            border: none;
            padding: 12px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #003060;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
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
<?php
exit;
endif;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админка - FC Inter Moscow</title>
  <link rel="stylesheet" href="/css/main.css"/>
</head>
<body>
  <div class="admin-panel">
    <form method="post" action="logout.php" style="float:right;">
      <button type="submit">Выйти</button>
    </form>
    
    <form action="success.php" method="get" style="display: inline-block; margin-top: 20px;">
    <button type="submit">Ачивки</button>
</form>

    <button id="openAddMatchModal" class="admin-button">➕ Добавить матч</button>

<div id="addMatchModal" class="modal-backdrop">
  <div class="modal-content">
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
      <button type="submit">Добавить матч</button>
      <button type="button" onclick="closeAddMatchModal()">Отмена</button>
    </form>
  </div>
</div>

    <h1>Выберите команду</h1>
    <select id="teamSelect"></select>
    <section>
    <h2>Список игроков</h2>
    <table id="playersTable">
      <thead>
        <tr>
          <th>Имя</th>
          <th>Матчи</th>
          <th>Голы</th>
          <th>Ассисты</th>
          <th>Матчи без пропущенных</th>
          <th>Пропущенные мячи</th>
          <th>Тренировки</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <button id="saveStatsBtn">Сохранить изменения</button>
    </section>
    <section>
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

    <section class="admin-achievements">
  <h2>Управление достижениями</h2>

  <label for="team-select">Выберите команду:</label>
  <select id="achv-team-select"></select>

  <label for="player-select">Выберите игрока:</label>
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

    <div id="editPlayerModal" style="display:none;">
      <div class="modal-content">
        <h2>Редактировать игрока</h2>
        <form id="editPlayerForm">
          <label>ФИО: <input type="text" name="name" required></label>
          <label>Отчество: <input type="text" name="patronymic"></label>
          <label>Дата рождения: <input type="date" name="birth_date" required></label>
          <label>Номер: <input type="number" name="number" required></label>
          <label>Позиция: <input type="text" name="position" required></label>
          <label>Рост (см): <input type="number" name="height_cm"></label>
          <label>Вес (кг): <input type="number" name="weight_kg"></label>
          <label>Команда:
            <select name="team_id" id="editPlayerTeamSelect"></select>
          </label>
          <button type="submit">Сохранить</button>
          <button type="button" onclick="closeEditPlayerModal()">Отмена</button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>

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
                const res = await fetch(`api/get_players.php?team_id=${teamId}`);
                const players = await res.json();
                players.sort((a, b) => a.name.localeCompare(b.name));
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

                document.getElementById('editPlayerModal').style.display = 'flex';
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


<script>
async function loadTeamsAndPlayers() {
    const teamSelect = document.getElementById('achv-team-select');
    const playerSelect = document.getElementById('achv-player-select');

    // Загружаем список команд
    const teams = await fetch('api/get_teams.php').then(res => res.json());
    teamSelect.innerHTML = teams.map(t => `<option value="${t.id}">${t.name}</option>`).join('');

    // Обработчик выбора команды
    teamSelect.addEventListener('change', async () => {
        const teamId = teamSelect.value;

    const players = await fetch(`api/get_players.php?team_id=${teamId}`).then(res => res.json());

        playerSelect.innerHTML = players.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

        // Если игроки есть — загружаем достижения первого
        if (players.length > 0) {
            loadAchievements(players[0].id);
        } else {
            document.getElementById('achievements-table').innerHTML = '<tr><td colspan="4">Нет игроков</td></tr>';
        }

        players.sort((a, b) => a.name.localeCompare(b.name));

playerSelect.innerHTML = players.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

if (players.length > 0) {
    loadAchievements(players[0].id);
} else {
    document.getElementById('achievements-table').innerHTML = '<tr><td colspan="4">Нет игроков</td></tr>';
}
    });

    // Обработчик выбора игрока
    playerSelect.addEventListener('change', () => {
        loadAchievements(playerSelect.value);
    });

    // Обработчик добавления достижения
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

    // Автозагрузка первой команды при старте
    if (teams.length > 0) {
        teamSelect.value = teams[0].id;
        teamSelect.dispatchEvent(new Event('change'));
    }
}

// Загрузка достижений игрока
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

// Удаление достижения
async function deleteAchievement(id, playerId) {
    await fetch(`api/achievements.php?id=${id}`, { method: 'DELETE' });
    loadAchievements(playerId);
}

// Запуск при загрузке страницы
document.addEventListener('DOMContentLoaded', loadTeamsAndPlayers);
</script>

<script>
const addMatchForm = document.getElementById("addMatchForm");
const matchTeamSelect = document.getElementById("matchTeamSelect");

document.addEventListener("DOMContentLoaded", async () => {
    async function l(url, options = {}) {
        try {
            const res = await fetch(url, options);
            if (!res.ok) throw new Error(`Ошибка ${res.status} при загрузке ${url}`);
            return await res.json();
        } catch (err) {
            alert(err.message);
            console.error(err);
            return null;
        }
    }

    async function loadTeamsIntoMatchForm() {
        const teams = await l("/api/get_teams.php");
        if (teams) {
            matchTeamSelect.innerHTML = "";
            teams.forEach(team => {
                const option = document.createElement("option");
                option.value = team.id;
                option.textContent = team.name;
                matchTeamSelect.appendChild(option);
            });
            matchTeamSelect.value = matchTeamSelect.options[0]?.value || '';
        }
    }

    addMatchForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(addMatchForm);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        // Явно передаём ID и название команды
        data.teams_id = matchTeamSelect.value;
        data.our_team = matchTeamSelect.options[matchTeamSelect.selectedIndex]?.textContent || '';

        console.log("📤 Данные для отправки:", data); // ← лог перед отправкой

        const res = await fetch("/api/matches.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(data)
        });

        let result;
        try {
            result = await res.json();
            console.log("📦 Ответ от сервера:", result);
        } catch (err) {
            console.error("❌ Не удалось распарсить JSON-ответ:", err);
        }

        if (res.ok && result?.success) {
            alert("✅ Матч добавлен! ID: " + result.match_id);
            console.log("📋 Полученные данные:", result.received_data);
            addMatchForm.reset();
        } else {
            alert("❌ Ошибка при добавлении матча: " + (result?.error || "неизвестная"));
            console.error("🪵 Сервер вернул:", result);
        }
    });

    await loadTeamsIntoMatchForm();
});
</script>

<script>
  const addMatchModal = document.getElementById('addMatchModal');
  const openBtn = document.getElementById('openAddMatchModal');

  openBtn.addEventListener('click', () => {
    addMatchModal.style.display = 'flex';
  });

  function closeAddMatchModal() {
    addMatchModal.style.display = 'none';
  }
</script>


</body>

</html>