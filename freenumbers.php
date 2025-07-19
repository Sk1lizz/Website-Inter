<?php
require_once 'db.php';

// Список общих командных номеров
$teamSharedNumbers = [
    38, 53, 54, 56, 58, 61, 62, 67, 68, 83, 84, 85, 86
];

// Получаем игроков команд 1 и 2
$query = "SELECT number, name FROM players WHERE team_id IN (1, 2) AND number IS NOT NULL ORDER BY number ASC";
$result = $db->query($query);

$takenNumbers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $takenNumbers[(int)$row['number']] = $row['name'];
    }
}

// Добавляем командные номера
foreach ($teamSharedNumbers as $num) {
    if (!isset($takenNumbers[$num])) {
        $takenNumbers[$num] = 'Командный';
    }
}

$allNumbers = range(1, 99);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Свободные номера | FC Inter Moscow</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f3f6fb;
      padding: 30px;
      margin: 0;
    }
    h1 {
      text-align: center;
      color: #083c7e;
      margin-bottom: 30px;
    }
    table {
      width: 100%;
      max-width: 600px;
      margin: auto;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: center;
      font-size: 15px;
    }
    th {
      background: #e0e7f1;
      font-weight: bold;
    }
    .taken {
      background: #f9d6d5;
    }
    .free {
      background: #d4f4d2;
    }
  </style>
</head>
<body>

<h1>Свободные и занятые номера</h1>

<table>
  <thead>
    <tr>
      <th>№</th>
      <th>Статус</th>
      <th>Игрок</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($allNumbers as $num): ?>
      <?php
        $isTaken = isset($takenNumbers[$num]);
        $status = $isTaken ? 'Занят' : 'Свободен';
        $class = $isTaken ? 'taken' : 'free';
        $player = $isTaken ? htmlspecialchars($takenNumbers[$num]) : '-';
      ?>
      <tr class="<?= $class ?>">
        <td><?= $num ?></td>
        <td><?= $status ?></td>
        <td><?= $player ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
