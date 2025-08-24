<?php
// fantasy.php
// PHP 7.1

session_start();

// Подключение к БД
require_once __DIR__ . '/db.php'; // mysqli в $db

// Функция безопасного инклюда хедера/футера (ищет .php, затем .html)
function include_block($name) {
    $base = __DIR__ . '/blocks/';
    $php  = $base . $name . '.php';
    $html = $base . $name . '.html';
    if (file_exists($php)) {
        include $php;
    } elseif (file_exists($html)) {
        include $html;
    } else {
        // Временный заглушечный хедер/футер, чтобы страница не “ломалась”
        if ($name === 'header') {
            echo '<header style="padding:10px 16px;background:#00296B;color:#FDC500;">Header placeholder — blocks/'.$name.'.php|.html не найден</header>';
        } else {
            echo '<footer style="padding:10px 16px;background:#f2f2f2;color:#333;margin-top:24px;">Footer placeholder — blocks/'.$name.'.php|.html не найден</footer>';
        }
    }
}

// Генерация/обновление капчи
function generate_captcha() {
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['fantasy_captcha_answer'] = (string)($a + $b);
    $_SESSION['fantasy_captcha_text'] = "{$a} + {$b} = ?";
}
if (!isset($_SESSION['fantasy_captcha_answer']) || isset($_GET['refresh_captcha'])) {
    generate_captcha();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Забираем поля
    $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password   = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $team_name  = isset($_POST['team_name']) ? trim($_POST['team_name']) : '';
    $consent    = isset($_POST['consent']) ? (bool)$_POST['consent'] : false;
    $captcha    = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

    // Валидация
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if ($password === '') {
        $errors[] = 'Введите пароль.';
    }
    if ($team_name === '') {
        $errors[] = 'Введите название команды.';
    }
    if (!$consent) {
        $errors[] = 'Необходимо согласиться на обработку персональных данных.';
    }
    if ($captcha === '' || !isset($_SESSION['fantasy_captcha_answer']) || $captcha !== $_SESSION['fantasy_captcha_answer']) {
        $errors[] = 'Неверная капча.';
        generate_captcha();
    }

    // Проверка уникальности email
    if (!$errors) {
        if ($stmt = $db->prepare("SELECT id FROM fantasy_users WHERE email = ? LIMIT 1")) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Пользователь с таким email уже существует.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Ошибка подготовки запроса (проверка email).';
        }
    }

    // Вставка
    if (!$errors) {
        $password_plain = $password; // по вашему требованию — без хеширования
        $points_2025 = 0;
        $consent_ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $consent_time = date('Y-m-d H:i:s');

        if ($stmt = $db->prepare("INSERT INTO fantasy_users (email, password_plain, team_name, points_2025, consent_ip, consent_time) VALUES (?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param('sssiss', $email, $password_plain, $team_name, $points_2025, $consent_ip, $consent_time);
            if ($stmt->execute()) {
                $success = true;
                generate_captcha();
                $_POST = [];
            } else {
                $errors[] = 'Не удалось сохранить данные. Попробуйте позже.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Ошибка подготовки запроса (вставка).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fantasy league</title>
    <link rel="stylesheet" href="/css/main.css" />
    <style>
        .Fantasy {
            padding-top: 20svh;
            padding-left: 20px;
            padding-right: 20px;
            min-height: 70vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background: #fff;
            font-family: PLAY-REGULAR, Arial, sans-serif;
        }
        .fantasy-card { width:100%; max-width:640px; background:rgba(0,0,0,.03); border-radius:12px; padding:24px; box-shadow:0 2px 5px rgba(0,0,0,.08); }
        .fantasy-card h1 { font-family: PLAY-BOLD, Arial, sans-serif; color:#00296B; font-size:28px; margin-bottom:12px; }
        .fantasy-card p.lead { color:#333; margin-bottom:16px; }
        form .field { margin-bottom:16px; }
        form label { display:block; margin-bottom:6px; font-weight:bold; color:#1a3c72; }
        form input[type="text"], form input[type="email"], form input[type="password"] { width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:8px; background:#fff; font-size:15px; }
        .consent { display:flex; align-items:center; gap:8px; font-size:14px; }
        .consent a { color:#00509D; text-decoration:underline; }
        .captcha-row { display:flex; gap:10px; align-items:center; }
        .btn { background:#00296B; color:#FDC500; border:2px solid #FDC500; border-radius:10px; padding:10px 16px; font-size:16px; cursor:pointer; }
        .btn:hover { background:#000; color:#fff; border-color:#fff; }
        .small { font-size:13px; color:#555; margin-top:-8px; margin-bottom:12px; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .alert.success { background:#e7f6e7; color:#155724; border:1px solid #c3e6cb; }
        .alert.error { background:#fdeceb; color:#7d1c1a; border:1px solid #f5c6cb; }
        .flex-between { display:flex; justify-content:space-between; align-items:center; }
        .muted { color:#666; font-size:13px; }

        .field.consent input[type="checkbox"] {
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #00296B;
    border-radius: 4px;
    display: inline-block;
    position: relative;
    cursor: pointer;
}
.field.consent input[type="checkbox"]:checked::after {
    content: "✔";
    font-size: 14px;
    position: absolute;
    top: -2px;
    left: 2px;
    color: #00296B;
}
.field.consent label {
    cursor: pointer;
}

    </style>
</head>
<body>

<?php include_block('header'); ?>

<div class="Fantasy">
    <div class="fantasy-card">
        <h1>Регистрация в Fantasy лиге</h1>
        <p class="lead">Создайте команду, участвуйте в сезоне 2025 и набирайте очки!</p>

        <?php if ($success): ?>
            <div class="alert success">Готово! Аккаунт создан. Теперь вы можете авторизоваться на сайте и управлять своей Fantasy‑командой.</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <strong>Проверьте форму:</strong>
                <ul style="margin-top:8px; padding-left:18px; list-style:disc;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="fantasy.php" autocomplete="off" novalidate>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
                       placeholder="you@example.com" required>
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="field">
                <label for="team_name">Название команды</label>
                <input type="text" id="team_name" name="team_name"
                       value="<?= isset($_POST['team_name']) ? htmlspecialchars($_POST['team_name'], ENT_QUOTES, 'UTF-8') : '' ?>"
                       placeholder="Интер Дрим Тим" maxlength="100" required>
            </div>

            <div class="field">
                <label>Капча</label>
                <div class="captcha-row">
                    <div class="muted">Решите: <strong><?= htmlspecialchars($_SESSION['fantasy_captcha_text'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <a class="muted" href="fantasy.php?refresh_captcha=1" style="text-decoration: underline;">обновить</a>
                </div>
                <input type="text" name="captcha" placeholder="Ответ" required>
            </div>

            <div class="field consent">
                <input type="checkbox" id="consent" name="consent" value="1" <?= isset($_POST['consent']) ? 'checked' : '' ?>>
                <label for="consent" style="margin:0;">
                    Согласен на обработку персональных данных
                    (<a href="personal_data.html" target="_blank" rel="noopener">прочитать</a>)
                </label>
            </div>

            <div class="field" style="margin-top:16px;">
                <button class="btn" type="submit">Зарегистрироваться</button>
            </div>

            <div class="flex-between">
                <span class="muted">Сезон: 2025 • Стартовые очки: 0</span>
            </div>
        </form>

        <div class="field" style="text-align:center; margin-top:20px;">
            Уже есть аккаунт? <a href="fantasy_login.php">Войти</a>
        </div>
    </div>
</div>

<?php include_block('footer'); ?>

 <script src="./js/index.bundle.js"></script>
</body>
</html>
