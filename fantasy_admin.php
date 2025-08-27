<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}
require_once __DIR__ . '/db.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Fantasy — админка</title>
  <link rel="stylesheet" href="css/main.css">
  <style>
    body { padding:20px; font-family:Arial,sans-serif; }
    h1 { margin-bottom:20px; }
    .styled-table {
        width:100%; border-collapse:collapse; font-size:14px;
        box-shadow:0 0 10px rgba(0,0,0,.1); margin-top:20px;
    }
    .styled-table th, .styled-table td {
        padding:10px; border:1px solid #ddd; text-align:center;
    }
    .styled-table thead { background:#00296B; color:#fff; }
    input[type=number] { width:80px; padding:5px; text-align:center; }
    .save-btn {
        padding:5px 10px; background:#009879; color:#fff; border:none; border-radius:4px; cursor:pointer;
    }
    .save-btn:hover { background:#007f65; }
  </style>
</head>
<body>

  <?php include 'headeradmin.html'; ?>

  

<h1>Fantasy: управление игроками</h1>

<div style="margin:10px 0;">
  <button id="save-all" class="save-btn">Сохранить всё</button>
  <span id="save-all-status" style="margin-left:10px;color:#555;"></span>
  <button id="calc-week" class="save-btn" style="margin-left:20px;background:#00509D;">Рассчитать очки за неделю</button>
  <span id="calc-week-status" style="margin-left:10px;color:#555;"></span>
</div>

<table class="styled-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Игрок</th>
      <th>Команда</th>
      <th>Стоимость</th>
      <th>Очки</th>
      <th>Сохранить</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $sql = "SELECT p.id, p.name, p.team_id, f.cost, f.points
            FROM players p
            LEFT JOIN fantasy_players f ON p.id = f.player_id
            WHERE p.team_id IN (1,2)
            ORDER BY p.team_id, p.name";
    $res = $db->query($sql);
    while ($row = $res->fetch_assoc()):
    ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= $row['team_id'] ?></td>
      <td>
        <input type="number" step="0.1" value="<?= number_format((float)($row['cost'] ?? 0), 1, '.', '') ?>"
       data-field="cost" data-player-id="<?= $row['id'] ?>">
      </td>
      <td>
        <input type="number" value="<?= (int)($row['points'] ?? 0) ?>"
               data-field="points" data-player-id="<?= $row['id'] ?>">
      </td>
      <td><button class="save-btn" data-player-id="<?= $row['id'] ?>">Сохранить</button></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<script>
// утилиты
function toNumber(value, allowFloat = true) {
  if (typeof value === 'string') {
    value = value.replace(',', '.').trim();
    const num = allowFloat ? parseFloat(value) : parseInt(value, 10);
    return isNaN(num) ? 0 : num;
  }
  return typeof value === 'number' && !isNaN(value) ? value : 0;
}

async function saveItems(items) {
  try {
    const response = await fetch('/api/save_fantasy_player.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items })
    });
    const result = await response.json();
    if (!result.success) {
      throw new Error(result.message || 'Ошибка сохранения');
    }
    return result;
  } catch (err) {
    throw new Error(err.message || 'Ошибка сети');
  }
}

// сохранение по кнопке в строке
document.addEventListener('click', async (e) => {
  if (!e.target.classList.contains('save-btn')) return;
  const playerId = e.target.getAttribute('data-player-id');
  if (!playerId) return;

  const costEl = document.querySelector(`input[data-field="cost"][data-player-id="${playerId}"]`);
  const pointsEl = document.querySelector(`input[data-field="points"][data-player-id="${playerId}"]`);

  const item = {
    player_id: Number(playerId),
    cost: toNumber(costEl?.value, true),
    points: toNumber(pointsEl?.value, false)
  };

  try {
    await saveItems([item]);
    alert('Сохранено');
  } catch (err) {
    alert('Ошибка: ' + err.message);
    console.error(err);
  }
});

// массовое сохранение
const saveAllBtn = document.getElementById('save-all');
const saveAllStatus = document.getElementById('save-all-status');

saveAllBtn?.addEventListener('click', async () => {
  saveAllBtn.disabled = true;
  saveAllBtn.textContent = 'Сохраняю...';
  saveAllStatus.textContent = '';

  const rows = Array.from(document.querySelectorAll('table.styled-table tbody tr'));
  const items = rows.map(tr => {
    const playerId = tr.querySelector('.save-btn')?.getAttribute('data-player-id');
    const costEl = tr.querySelector('input[data-field="cost"]');
    const pointsEl = tr.querySelector('input[data-field="points"]');
    return {
      player_id: Number(playerId),
      cost: toNumber(costEl?.value, true),
      points: toNumber(pointsEl?.value, false)
    };
  }).filter(x => Number.isFinite(x.player_id) && x.player_id > 0);

  try {
    const res = await saveItems(items);
    saveAllStatus.style.color = '#2e7d32';
    saveAllStatus.textContent = `Готово: сохранено ${res.saved}.`;
    alert(`Готово: сохранено ${res.saved}.`);
  } catch (err) {
    saveAllStatus.style.color = '#d32f2f';
    saveAllStatus.textContent = 'Ошибка массового сохранения: ' + err.message;
    alert('Ошибка массового сохранения: ' + err.message);
  } finally {
    saveAllBtn.disabled = false;
    saveAllBtn.textContent = 'Сохранить всё';
  }
});

const calcBtn = document.getElementById('calc-week');
const calcStatus = document.getElementById('calc-week-status');

calcBtn?.addEventListener('click', async () => {
  calcBtn.disabled = true;
  calcBtn.textContent = 'Считаю...';
  calcStatus.textContent = '';
  try {
    const resp = await fetch('/api/calc_fantasy_points.php');
    const data = await resp.json();
    if (data.success) {
      calcStatus.style.color = '#2e7d32';
      calcStatus.textContent = `Готово: обновлены очки для ${data.updated} команд.`;
      alert(`Готово: обновлены очки для ${data.updated} команд`);
    } else {
      throw new Error(data.message || 'Ошибка расчета');
    }
  } catch (err) {
    calcStatus.style.color = '#d32f2f';
    calcStatus.textContent = 'Ошибка: ' + err.message;
    alert('Ошибка: ' + err.message);
  } finally {
    calcBtn.disabled = false;
    calcBtn.textContent = 'Рассчитать очки за неделю';
  }
});

// Принудительное форматирование ввода в <input> (замена запятой на точку)
document.querySelectorAll('input[data-field="cost"]').forEach(input => {
  input.addEventListener('input', (e) => {
    let value = e.target.value;
    if (value.includes(',')) {
      e.target.value = value.replace(',', '.');
    }
  });
});

</script>

