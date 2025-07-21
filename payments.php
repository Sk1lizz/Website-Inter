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
    <title>Управление взносами</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        h2, h3 {
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

        .styled-table input[type="number"] {
            width: 80px;
            padding: 5px;
            text-align: center;
        }

        .styled-table button {
            padding: 6px 12px;
            background-color: #009879;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .styled-table button:hover {
            background-color: #007f65;
        }

        label {
            font-weight: bold;
        }

        select, input[type="month"] {
            padding: 5px 10px;
            margin-bottom: 10px;
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

    </style>
</head>
<body>

<div class="buttons-wrapper">
    <form method="post" action="logout.php"><button type="submit">Выйти</button></form>
    <form action="success.php" method="get"><button type="submit">Ачивки</button></form>
    <form action="addmatch.php" method="get"><button type="submit">Добавить матч</button></form>
    <form action="statisticsall.php" method="get"><button type="submit">Статистика общая</button></form>
    <form action="background.php" method="get"><button type="submit">Фон</button></form>
    <form action="freenumbers.php" method="get"><button type="submit">Номера</button></form>
    <form action="training.php" method="get"><button type="submit">Посещаемость</button></form>
    <form action="fines.php" method="get"><button type="submit">Штрафы</button></form>
    <form action="admin.php" method="get"><button type="submit">Назад</button></form>
</div>


<h2>Взносы по командам</h2>
<label for="team-select">Команда:</label>
<select id="team-select">
    <option value="">-- выберите --</option>
    <?php foreach ($teams as $team): ?>
        <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
    <?php endforeach; ?>
</select>

<div style="display: flex; align-items: center; gap: 10px; margin: 15px 0;">
  <label for="mass-add-amount" style="font-weight: bold; white-space: nowrap;">Начислить всем:</label>
  <input type="number" id="mass-add-amount" placeholder="₽" style="width: 100px; padding: 5px;">
  <button onclick="applyAmountToAll()">Начислить всем</button>
  <button onclick="saveAllPayments()">Сохранить всех</button>
</div>

<table class="styled-table" id="payments-table">
    <thead>
        <tr>
            <th>Игрок</th>
            <th>Сумма (₽)</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody id="players-container">
        <!-- JS заполнит -->
    </tbody>
</table>

<h3>История взносов</h3>
<label>Команда:</label>
<select id="history-team-select">
    <option value="">-- выберите --</option>
    <?php foreach ($teams as $team): ?>
        <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
    <?php endforeach; ?>
</select>

<label>Месяц:</label>
<input type="month" id="history-month">
<button onclick="loadHistory()">Показать</button>
<div id="history-result" style="margin-top: 10px;"></div>

<script>
    document.getElementById('team-select').addEventListener('change', function () {
        const teamId = this.value;
        if (!teamId) return;

        fetch(`api/get_players_by_team.php?team_id=${teamId}`)
            .then(res => res.json())
            .then(players => {
                const container = document.getElementById('players-container');
                container.innerHTML = '';
                players.forEach(player => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${player.name} ${player.patronymic || ''}</td>
                        <td><input type="number" value="${player.payment || ''}" placeholder="₽"></td>
                        <td>
                            <button onclick="savePayment(${player.id}, this)">Сохранить</button>
                            <button onclick="markAsPaid(${player.id}, this)">Оплачен</button>
                        </td>
                    `;
                    container.appendChild(row);
                });
            });
    });

    function savePayment(playerId, btn) {
        const amount = btn.closest('tr').querySelector('input').value;
        fetch('api/save_payment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ player_id: playerId, amount })
        })
        .then(res => res.json())
        .then(resp => {
            alert(resp.success ? 'Сохранено' : 'Ошибка при сохранении');
        });
    }

    function markAsPaid(playerId, btn) {
        const row = btn.closest('tr');
        const amountInput = row.querySelector('input');
        const amount = parseFloat(amountInput.value) || 0;
        const teamId = document.getElementById('team-select').value;

        if (amount <= 0) {
            alert('Сумма взноса должна быть больше 0');
            return;
        }

        if (!confirm("Подтвердить оплату?")) return;

        fetch('api/mark_as_paid.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ player_id: playerId, amount, team_id: teamId })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                alert("Отмечено как оплачено");
                amountInput.value = 0;
            } else {
                alert("Ошибка при оплате: " + (resp.error || ''));
            }
        });
    }

    function loadHistory() {
        const teamId = document.getElementById('history-team-select').value;
        const month = document.getElementById('history-month').value;

        if (!teamId || !month) {
            alert('Выберите команду и месяц');
            return;
        }

        fetch(`api/get_payments_summary.php?team_id=${teamId}&month=${month}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('history-result').innerHTML =
                    `<p>Сумма взносов за ${month}: <strong>${data.total || 0}</strong> ₽</p>`;
            })
            .catch(error => {
                console.error('Ошибка при загрузке истории:', error);
                alert('Ошибка при загрузке истории');
            });
    }

    function saveAllPayments() {
    const rows = document.querySelectorAll('#players-container tr');
    const updates = [];

    rows.forEach(row => {
        const input = row.querySelector('input[type="number"]');
        const amount = parseFloat(input.value) || 0;
        const playerId = row.querySelector('button').getAttribute('onclick').match(/savePayment\((\d+)/)?.[1];
        if (playerId !== null) {
            updates.push({ player_id: parseInt(playerId), amount });
        }
    });

    if (updates.length === 0) {
        alert('Нет данных для сохранения');
        return;
    }

    fetch('api/save_payments_bulk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ payments: updates })
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            alert('Все взносы сохранены!');
        } else {
            alert('Ошибка при массовом сохранении: ' + (resp.error || ''));
        }
    })
    .catch(err => {
        console.error('Ошибка при массовом сохранении:', err);
        alert('Ошибка при сохранении');
    });
}

function applyAmountToAll() {
    const addValue = parseFloat(document.getElementById('mass-add-amount').value);
    if (isNaN(addValue) || addValue <= 0) {
        alert('Введите корректную сумму для начисления');
        return;
    }

    const inputs = document.querySelectorAll('#players-container input[type="number"]');
    inputs.forEach(input => {
        const current = parseFloat(input.value) || 0;
        input.value = current + addValue;
    });
}
</script>

</body>
</html>