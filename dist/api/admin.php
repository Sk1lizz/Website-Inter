<?php

session_start();

$login = 'admin';
$password = 'fcinter2025'; // Задайте свой пароль

// Обработка формы входа
if (isset($_POST['auth_login'], $_POST['auth_pass'])) {
    if ($_POST['auth_login'] === $login && $_POST['auth_pass'] === $password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Проверка входа
if (!isset($_SESSION['admin_logged_in'])) {
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
}

require_once 'db.php';

$teams = $db->query("SELECT id, name FROM teams ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$selectedTeamId = $_GET['team_id'] ?? $teams[0]['id'];
$players = $db->query("SELECT * FROM players WHERE team_id = $selectedTeamId")->fetch_all(MYSQLI_ASSOC);
?>

<h1>Админка</h1>

<form method="post" action="logout.php" style="float: right;">
    <button type="submit">Выйти</button>
</form>

<form method="GET" action="admin.php">
    <label>Выберите команду:</label>
    <select name="team_id" onchange="this.form.submit()">
        <?php foreach ($teams as $team): ?>
            <option value="<?= $team['id'] ?>" <?= $team['id'] == $selectedTeamId ? 'selected' : '' ?>>
                <?= htmlspecialchars($team['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<h2>Игроки</h2>
<table border="1">
    <tr>
        <th>Имя</th>
        <th>Номер</th>
        <th>Позиция</th>
        <th>Дата рождения</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($players as $player): ?>
        <tr>
            <form method="POST" action="update_player.php">
                <input type="hidden" name="id" value="<?= $player['id'] ?>">
                <td><input name="name" value="<?= htmlspecialchars($player['name']) ?>"></td>
                <td><input name="number" value="<?= $player['number'] ?>"></td>
                <td><input name="position" value="<?= htmlspecialchars($player['position']) ?>"></td>
                <td><input name="birth_date" type="date" value="<?= $player['birth_date'] ?>"></td>
                <td>
                    <button type="submit">Сохранить</button>
                    <a href="archive_player.php?id=<?= $player['id'] ?>">В архив</a>
                </td>
            </form>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Добавить игрока</h2>
<form method="POST" action="add_player.php">
    <input type="hidden" name="team_id" value="<?= $selectedTeamId ?>">
    <input name="name" placeholder="ФИО" required>
    <input name="number" type="number" placeholder="№" required>
    <input name="position" placeholder="Позиция" required>
    <input name="birth_date" type="date" required>
    <button type="submit">Добавить</button>
</form>