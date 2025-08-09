<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
require_once 'db.php';

$conn = $db;
$teams = $conn->query("SELECT id, name FROM teams")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Статистика за сезон</title>
  <link rel="stylesheet" href="/css/main.css"/>
   <link rel="icon" href="/img/yelowaicon.png" type="image/x-icon">
   <style>
.admin-panel {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    padding: 20px;
    color: #333;
}

.admin-panel section {
    background-color: #ffffff;
    margin-bottom: 40px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.admin-panel section:nth-of-type(1) { background-color: #e9f5ff; }
.admin-panel section:nth-of-type(2) { background-color: #f0fff4; }
.admin-panel section:nth-of-type(3) { background-color: #fff5e6; }
.admin-panel section:nth-of-type(4) { background-color: #f8e6ff; }

.admin-panel section h2 {
    margin-top: 0;
    border-bottom: 2px solid #004080;
    padding-bottom: 8px;
    margin-bottom: 16px;
}

.admin-panel h1,
.admin-panel h2 {
    color: #004080;
}

.admin-panel label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
}

.admin-panel input,
.admin-panel select {
    width: 100%;
    padding: 8px;
    margin-top: 4px;
    margin-bottom: 12px;
    box-sizing: border-box;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.admin-panel table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.05);
    border-radius: 6px;
    overflow: hidden;
}

.admin-panel th,
.admin-panel td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

.admin-panel th {
    background-color: #004080;
    color: white;
}

.admin-panel button {
    padding: 10px 20px;
    background-color: #004080;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

.admin-panel button:hover {
    background-color: #003060;
}

#editPlayerModal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 10000;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#editPlayerModal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 10000;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#editPlayerModal form label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
    color: #333;
}

#editPlayerModal form input,
#editPlayerModal form select {
    width: 100%;
    padding: 8px;
    margin-top: 4px;
    margin-bottom: 16px;
    box-sizing: border-box;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

#editPlayerModal form button {
    padding: 10px 16px;
    margin-right: 10px;
    background-color: #004080;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

#editPlayerModal form button:hover {
    background-color: #003060;
}

</style>

</head>

<body>
    <div class="admin-panel">
<section>
  <h2>Список игроков</h2>
  <select id="teamSelect"></select>
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
  </div>

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

</section>

<script>
  const teamSelectTop = document.getElementById('teamSelect');
  let currentTeamId = null;

  async function loadTeams() {
    const res = await fetch('api/get_teams.php');
    const teams = await res.json();
    teamSelectTop.innerHTML = '<option value="">-- Выберите команду --</option>';
    teams.forEach(team => {
      const option = document.createElement('option');
      option.value = team.id;
      option.textContent = team.name;
      teamSelectTop.appendChild(option);
    });
  }

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

  teamSelectTop.addEventListener('change', e => {
    currentTeamId = e.target.value;
    if (currentTeamId) {
      fetchPlayers(currentTeamId);
    } else {
      clearPlayersTable();
    }
  });

  loadTeams().then(() => {
    if (teamSelectTop.options.length > 1) {
      teamSelectTop.selectedIndex = 1;
      currentTeamId = teamSelectTop.value;
      fetchPlayers(currentTeamId);
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

<script>
  document.querySelector('#playersTable tbody').addEventListener('click', async (e) => {
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

    if (e.target.classList.contains('edit-btn')) {
      const playerId = e.target.dataset.playerId;
      openEditPlayerModal(playerId);
    }
  });

  let editingPlayerId = null;

  async function openEditPlayerModal(playerId) {
    editingPlayerId = playerId;

    const res = await fetch(`api/get_player.php?id=${playerId}`);
    if (!res.ok) {
      alert('Ошибка загрузки данных игрока');
      return;
    }
    const player = await res.json();

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

    const form = document.getElementById('editPlayerForm');
    form.name.value = player.name;
    form.patronymic.value = player.patronymic || '';
    form.birth_date.value = player.birth_date ? player.birth_date.substring(0, 10) : '';
    form.number.value = player.number ?? '';
    form.position.value = player.position || '';
    form.height_cm.value = player.height_cm ?? '';
    form.weight_kg.value = player.weight_kg ?? '';
    form.team_id.value = player.team_id;

    document.getElementById('editPlayerModal').style.display = 'flex';
  }

  function closeEditPlayerModal() {
    document.getElementById('editPlayerModal').style.display = 'none';
    editingPlayerId = null;
  }

  document.getElementById('editPlayerForm').addEventListener('submit', async (e) => {
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
  });
</script>
</body>
</html>