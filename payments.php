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
    <link rel="icon" href="/img/yelowaicon.png" type="image/x-icon">
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

<?php include 'headeradmin.html'; ?>


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

<table class="styled-table" id="paymentsTable">
    <thead>
        <tr>
            <th>Игрок</th>
            <th>Рассчитанный взнос</th>
            <th>Возвраты</th>
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
    document.getElementById('team-select').addEventListener('change', async function () {
    const teamId = this.value;
    if (!teamId) return;

    const [players, calculated] = await Promise.all([
        fetch(`api/get_players_by_team.php?team_id=${teamId}`).then(res => res.json()),
        fetch(`api/get_payments_with_attendance.php?team_id=${teamId}`).then(res => res.json())
    ]);

    const container = document.getElementById('players-container');
    container.innerHTML = '';

    players.forEach(player => {
        const calc = calculated.find(c => c.player_id == player.id);
        const calcText = calc
            ? `${calc.final_amount} ₽ (посещаемость ${calc.attendance_percent}%)`
            : '—';
        const returnsCount = calc ? calc.returns_count : 0;

        const row = document.createElement('tr');
row.setAttribute('data-id', player.id);
row.setAttribute('data-attendance', calc.attendance_percent);
row.setAttribute('data-base', calc.base_amount);
row.innerHTML = `
    <td>${player.name} ${player.patronymic || ''}</td>
    <td class="calc-cell">${calcText}</td>
    <td>
        <input type="number" value="${returnsCount}" min="0" style="width:60px"
               onchange="updateReturnsCount(${player.id}, this.value)">
    </td>
    <td><input type="number" value="${player.payment || ''}" placeholder="₽"></td>
    <td>
        <button onclick="savePayment(${player.id}, this)">Сохранить</button>
        <button onclick="markAsPaid(${player.id}, this)">Оплачен</button>
    </td>
`;

        container.appendChild(row);
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
    const rows = document.querySelectorAll('#players-container tr');

    rows.forEach(row => {
        // Находим колонку с рассчитанным взносом (2-я колонка)
        const calcCell = row.querySelector('td:nth-child(2)');
        if (!calcCell) return;

        // Извлекаем число из строки "3950 ₽ (посещаемость 80%)"
        const match = calcCell.textContent.match(/(\d+)\s*₽/);
        if (match) {
            const amount = parseFloat(match[1]) || 0;

            // Берём текущее значение поля и прибавляем рассчитанную сумму
            const input = row.querySelector('input[type="number"]');
            if (input) {
                const current = parseFloat(input.value) || 0;
                input.value = current + amount;
            }
        }
    });
}

 function updateReturnsCount(playerId, value) {
        const returns = parseInt(value) || 0;

        fetch('api/update_returns_count.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ player_id: playerId, returns_count: returns })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                const row = document.querySelector(`#players-container tr[data-id="${playerId}"]`);
                if (row) {
                    const attendanceText = row.getAttribute('data-attendance') || '0';
                    const baseAmount = parseFloat(row.getAttribute('data-base')) || 0;
                    let finalAmount = baseAmount;

                    if (returns > 0) {
                        const bonusPercent = 0.2 * returns;
                        finalAmount = baseAmount * (1 + bonusPercent);
                        const minBonus = 200 * returns;
                        const diff = finalAmount - baseAmount;
                        if (diff < minBonus) {
                            finalAmount = baseAmount + minBonus;
                        }
                        finalAmount = Math.ceil(finalAmount / 50) * 50;
                    }

                    row.querySelector('.calc-cell').textContent = `${finalAmount} ₽ (посещаемость ${attendanceText}%)`;
                }
            } else {
                alert('Ошибка при сохранении количества возвратов');
            }
        })
        .catch(() => alert('Ошибка соединения с сервером'));
    }

</script>

</body>
</html>