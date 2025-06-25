<?php
session_start();
$login = 'captain';
$password = 'duty2025';

if (!isset($_SESSION['captain_auth'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['pass'])) {
        if ($_POST['login'] === $login && $_POST['pass'] === $password) {
            $_SESSION['captain_auth'] = true;
            header("Location: random.php");
            exit;
        } else {
            $error = 'Неверный логин или пароль.';
        }
    }

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Вход для капитана</title>
    <style>
        body { font-family:sans-serif; padding:20px; background:#f4f4f4; }
        form { background:white; padding:20px; max-width:400px; margin:auto; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
        input { width:100%; margin-bottom:10px; padding:10px; }
        button { padding:10px 20px; background:#007BFF; color:white; border:none; border-radius:5px; cursor:pointer; }
        h3 { margin-bottom:15px; }
    </style></head><body>';
    echo '<form method="post"><h3>Вход для капитана</h3>';
    if (!empty($error)) echo '<p style="color:red;">' . $error . '</p>';
    echo '<input name="login" placeholder="Логин" required>
          <input name="pass" type="password" placeholder="Пароль" required>
          <button type="submit">Войти</button></form></body></html>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Жеребьёвка дежурных</title>
    <style>
        body { font-family:sans-serif; max-width:800px; margin:40px auto; padding:20px; background:#fff; }
        label, h4 { font-weight:bold; display:block; margin-top:10px; }
        select { padding:8px; margin-top:5px; }
        .checkboxes { columns:2; margin-top:10px; }
        .checkboxes label { display:block; margin:3px 0; }
        table { border-collapse:collapse; width:100%; margin-top:20px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:left; }
        th { background:#f0f0f0; }
        button { margin-top:15px; padding:10px 20px; font-size:16px; background:#007BFF; color:white; border:none; border-radius:5px; cursor:pointer; }
    </style>
</head>
<body>
<h2>Жеребьёвка дежурных на месяц</h2>

<?php
require_once 'db.php';

if (!isset($_POST['generate'])) {
    // Шаг 1: Форма выбора месяца и исключения игроков
    $stmt = $db->prepare("SELECT name FROM players WHERE team_id = 2");
    $stmt->execute();
    $res = $stmt->get_result();
    $players = array_column($res->fetch_all(MYSQLI_ASSOC), 'name');

    echo '<form method="post">';
    echo '<label>Выберите месяц: 
            <select name="month" required>
                <option value="">-- Месяц --</option>';
    for ($m = 1; $m <= 12; $m++) {
        $monthName = strftime('%B', mktime(0, 0, 0, $m, 1));
        printf('<option value="%d">%s</option>', $m, ucfirst($monthName));
    }
    echo '</select></label>';

    echo '<h4>Исключить из жеребьёвки:</h4><div class="checkboxes">';
    foreach ($players as $p) {
        echo "<label><input type='checkbox' name='excluded[]' value=\"" . htmlspecialchars($p) . "\"> $p</label>";
    }
    echo '</div><button type="submit" name="generate" value="1">Сгенерировать дежурных</button></form>';
    exit;
}

// Шаг 2: Генерация
$month = (int)$_POST['month'];
$year = date('Y');
$excluded = $_POST['excluded'] ?? [];

$stmt = $db->prepare("SELECT name FROM players WHERE team_id = 2");
$stmt->execute();
$res = $stmt->get_result();
$players = array_column($res->fetch_all(MYSQLI_ASSOC), 'name');

$players = array_values(array_diff($players, $excluded));
shuffle($players);

// Определяем недели
$start = new DateTime("$year-$month-01");
$end = (clone $start)->modify('last day of this month');

$weeks = [];
$current = clone $start;
while ($current <= $end) {
    $isoWeek = $current->format("W");
    if (!isset($weeks[$isoWeek])) $weeks[$isoWeek] = [];
    $weeks[$isoWeek][] = $current->format('Y-m-d');
    $current->modify('+1 day');
}

// Генерация
$assigned = [];
$result = [];
foreach ($weeks as $weekDates) {
    $available = array_values(array_diff($players, $assigned));
    $duty = array_slice($available, 0, 5);
    $assigned = array_merge($assigned, $duty);
    $result[] = [
        'range' => date('d.m', strtotime($weekDates[0])) . ' – ' . date('d.m', strtotime(end($weekDates))),
        'duty' => $duty
    ];
}

// Вывод
echo "<h3>Дежурные на " . strftime('%B', mktime(0, 0, 0, $month, 1)) . " $year</h3>";
echo "<table><tr><th>Неделя</th><th>Период</th><th>Дежурные</th></tr>";
foreach ($result as $i => $row) {
    echo "<tr><td>Неделя " . ($i + 1) . "</td><td>{$row['range']}</td><td>" . implode(', ', $row['duty']) . "</td></tr>";
}
echo "</table>";
?>
</body>
</html>
