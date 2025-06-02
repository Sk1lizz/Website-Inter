<?php
session_start();

$login = 'admin';
$password = 'fcinter2025';

if (isset($_POST['auth_login'], $_POST['auth_pass'])) {
    if ($_POST['auth_login'] === $login && $_POST['auth_pass'] === $password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход в админку</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f9f9f9;
                padding: 50px;
            }
            h2 {
                color: #333;
            }
            form {
                background: white;
                padding: 20px;
                border-radius: 8px;
                max-width: 400px;
                margin: auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            input {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            button {
                background-color: #004080;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
            }
            button:hover {
                background-color: #003366;
            }
            p {
                color: red;
            }
        </style>
    </head>
    <body>
        <h2>Авторизация</h2>
        <?php if (!empty($error)) echo "<p>$error</p>"; ?>
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

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка FC Inter</title>
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
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        th, td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #004080;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            padding: 8px;
            margin: 4px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        button {
            padding: 8px 16px;
            background-color: #004080;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
        }
        button:hover {
            background-color: #003366;
        }
        a {
            color: #004080;
            text-decoration: none;
            margin-left: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
        .logout-button {
            float: right;
            margin-top: -40px;
        }
    </style>
</head>
<body>

<h1>Админка</h1>

<form method="post" action="logout.php" class="logout-button">
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
<table>
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
                <td><input name="number" type="number" value="<?= $player['number'] ?>"></td>
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

</body>
</html>