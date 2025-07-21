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
    <title>Управление штрафами</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        h2 {
            margin-top: 30px;
            color: #333;
        }

        .buttons-wrapper form {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
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

        button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            margin: 5px 5px 10px 0;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background-color: #003060;
        }

        select, input[type="date"], input[type="number"], input[type="text"] {
            padding: 6px;
            font-size: 14px;
            margin: 5px 0;
        }

        .modal {
            display: none;
            position: fixed;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
        }

        .modal-content .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        .modal-content label {
            margin-bottom: 4px;
            font-weight: bold;
        }

        .modal-content input,
        .modal-content select {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="buttons-wrapper">
    <form action="success.php" method="get"><button type="submit">Ачивки</button></form>
    <form action="addmatch.php" method="get"><button type="submit">Добавить матч</button></form>
    <form action="statisticsall.php" method="get"><button type="submit">Статистика общая</button></form>
    <form action="background.php" method="get"><button type="submit">Фон</button></form>
    <form action="freenumbers.php" method="get"><button type="submit">Номера</button></form>
    <form action="training.php" method="get"><button type="submit">Посещаемость</button></form>
    <form action="payments.php" method="get"><button type="submit">Взносы</button></form>
    <form action="admin.php" method="get"><button type="submit">Назад</button></form>
</div>

<h2>Управление штрафами</h2>

<label>Команда:</label>
<select id="team-select">
    <option value="">-- выберите --</option>
    <?php foreach ($teams as $team): ?>
        <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
    <?php endforeach; ?>
</select>

<label>Игрок:</label>
<select id="player-select" disabled>
    <option value="">-- выберите --</option>
</select>

<button id="add-fine-btn" disabled>Добавить штраф</button>

<table class="styled-table" id="fines-table">
    <thead>
        <tr>
            <th>Сумма (₽)</th>
            <th>Причина</th>
            <th>Дата</th>
            <th>Удалить</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- Модальное окно -->
<div class="modal" id="fine-modal">
  <div class="modal-content">
    <h3>Добавить штраф</h3>

    <div class="form-group">
      <label for="reason-select">Причина:</label>
      <select id="reason-select">
    <option value="">-- выберите --</option>
    <option value="Опоздание на тренировку" data-amount="250">Опоздание на тренировку</option>
    <option value="Предупреждение игроком о неявке на тренировку менее чем за 3 часа" data-amount="500">Предупреждение игроком о неявке на тренировку менее чем за 3 часа</option>
    <option value="Неявка на тренировку" data-amount="750">Неявка на тренировку</option>
    <option value="Опоздание на игру" data-amount="250">Опоздание на игру</option>
    <option value="Неучастие в опросе на тренировку" data-amount="200">Неучастие в опросе на тренировку</option>
    <option value="Отсутствие щитков" data-amount="250">Отсутствие щитков</option>
    <option value="Другое">Другое</option>
</select>
      <input type="text" id="custom-reason" placeholder="Своя причина" style="display:none; margin-top: 5px;">
    </div>

    <div class="form-group">
      <label for="fine-date">Дата:</label>
      <input type="date" id="fine-date">
    </div>

    <div class="form-group">
      <label for="fine-amount">Сумма (₽):</label>
      <input type="number" id="fine-amount">
    </div>

    <div style="margin-top: 10px;">
      <button onclick="submitFine()">Сохранить</button>
      <button onclick="closeModal()">Отмена</button>
    </div>
  </div>
</div>

<script>
let currentPlayerId = null;

document.getElementById('team-select').addEventListener('change', function () {
    const teamId = this.value;
    fetch(`/api/get_players_by_team.php?team_id=${teamId}`)
        .then(res => res.json())
        .then(players => {
            const select = document.getElementById('player-select');
            select.innerHTML = '<option value="">-- выберите --</option>';
            players.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.name} ${p.patronymic || ''}`;
                select.appendChild(opt);
            });
            select.disabled = false;
        });
});

document.getElementById('player-select').addEventListener('change', function () {
    currentPlayerId = this.value;
    loadFines(currentPlayerId);
    document.getElementById('add-fine-btn').disabled = !currentPlayerId;
});

function loadFines(playerId) {
    fetch(`/api/get_fines.php?player_id=${playerId}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#fines-table tbody');
            tbody.innerHTML = '';
            data.forEach(fine => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${fine.amount}</td>
                    <td>${fine.reason}</td>
                    <td>${fine.date}</td>
                    <td><button onclick="deleteFine(${fine.id})">Удалить</button></td>
                `;
                tbody.appendChild(row);
            });
        });
}

document.getElementById('add-fine-btn').addEventListener('click', () => {
    document.getElementById('fine-modal').style.display = 'flex';
});

function closeModal() {
    document.getElementById('fine-modal').style.display = 'none';
    document.getElementById('custom-reason').style.display = 'none';
    document.getElementById('fine-amount').value = '';
    document.getElementById('reason-select').value = '';
    document.getElementById('fine-date').value = '';
}

document.getElementById('reason-select').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    if (this.value === 'Другое') {
        document.getElementById('custom-reason').style.display = 'block';
        document.getElementById('fine-amount').value = '';
    } else {
        document.getElementById('custom-reason').style.display = 'none';
        document.getElementById('fine-amount').value = selected.dataset.amount || '';
    }
});

function submitFine() {
    const reason = document.getElementById('reason-select').value === 'Другое'
        ? document.getElementById('custom-reason').value
        : document.getElementById('reason-select').value;

    const amount = parseFloat(document.getElementById('fine-amount').value);
    const date = document.getElementById('fine-date').value;

    if (!reason || isNaN(amount) || !date) {
        alert('Укажите причину, сумму и дату');
        return;
    }

    fetch('/api/add_fine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ player_id: currentPlayerId, reason, amount, date })
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            closeModal();
            loadFines(currentPlayerId);
        } else {
            alert('Ошибка: ' + (resp.error || ''));
        }
    });
}

function deleteFine(fineId) {
    if (!confirm('Удалить штраф?')) return;
    fetch('/api/delete_fine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fine_id: fineId })
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            loadFines(currentPlayerId);
        } else {
            alert('Ошибка удаления');
        }
    });
}
</script>
</body>
</html>
