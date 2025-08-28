<?php
// fantasy_login.php — СТРАНИЦА РЕГИСТРАЦИИ (карточка по центру, фон как на входе)
session_start();

require_once __DIR__ . '/db.php'; // mysqli в $db

function include_block($name) {
    $base = __DIR__ . '/blocks/';
    $php  = $base . $name . '.php';
    $html = $base . $name . '.html';
    if (file_exists($php)) {
        include $php;
    } elseif (file_exists($html)) {
        include $html;
    } else {
        if ($name === 'header') {
            echo '<header style="padding:10px 16px;background:#00296B;color:#FDC500;">Header placeholder — blocks/'.$name.'.php|.html не найден</header>';
        } else {
            echo '<footer style="padding:10px 16px;background:#f2f2f2;color:#333;margin-top:24px;">Footer placeholder — blocks/'.$name.'.php|.html не найден</footer>';
        }
    }
}

/** Нормализация строки для фильтров */
function norm($s) {
    $s = mb_strtolower($s, 'UTF-8');
    $s = str_replace(['ё'], ['е'], $s);        // ё -> е
    $s = preg_replace('~[^a-zа-я0-9]+~u', '', $s); // убрать пробелы/знаки
    return $s ?? '';
}

/** Проверка на оскорбления/запрещённые сочетания (в т.ч. тренер) */
function is_offensive_team_name($name) {
    $n = norm($name);

    // базовые мат/оскорбления (сокращённые основы для отсека разных форм)
    $bad_roots = [
        'хуй','хуе','пизд','бляд','еба','ебн','мудак','гандон','сука','мраз','дерьм','говн','чмо',
        'фаш','наци','расист','урод','дебил','идиот',
    ];

    foreach ($bad_roots as $r) {
        if (mb_strpos($n, $r) !== false) return true;
    }

    // Тренер и производные: Пешехонов, Пешехон, Peshehonov, Peshekhonov, Михалыч, Дмитрий Михайлович
    $coach_roots = [
        // кириллица
        'пешехонов','пешехон','михалыч','дмитриймихайлович','дмитриймихаилович','дмитриймихалыч',
        // транслитерации и близкие
        'peshehonov','peshekhonov','peshehon','peshekhon','mikhalych','dmitriimikhailovich','dmitrymikhailovich',
    ];

    // Если в названии присутствуют корни по тренеру — блокируем при сочетании с ругательством
    $hasCoach = false;
    foreach ($coach_roots as $r) {
        if (mb_strpos($n, $r) !== false) { $hasCoach = true; break; }
    }

    if ($hasCoach) {
        // даже без «жёстких» слов блокируем любые негативные оттенки вокруг фамилии
        $neg_roots = array_merge($bad_roots, ['худш','плох','нищ','слаб','позор','трус','вор','скот','дрян']);
        foreach ($neg_roots as $r) {
            if (mb_strpos($n, $r) !== false) return true;
        }
        // Также можно просто запретить использование фамилии тренера в названии команды:
        // return true;
    }

    return false;
}

// Капча
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
    $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password   = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $team_name  = isset($_POST['team_name']) ? trim($_POST['team_name']) : '';
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name'])  ? trim($_POST['last_name'])  : '';
    $consent    = isset($_POST['consent']) ? (bool)$_POST['consent'] : false;
    $captcha    = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

    // Валидация базовая
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if ($password === '') {
        $errors[] = 'Введите пароль.';
    }
    if ($team_name === '' || mb_strlen($team_name, 'UTF-8') < 3) {
        $errors[] = 'Введите название команды (не короче 3 символов).';
    }
    if ($first_name === '' || mb_strlen($first_name, 'UTF-8') < 2) {
        $errors[] = 'Введите имя.';
    }
    if ($last_name === '' || mb_strlen($last_name, 'UTF-8') < 2) {
        $errors[] = 'Введите фамилию.';
    }
    if (!$consent) {
        $errors[] = 'Необходимо согласиться на обработку персональных данных.';
    }
    if ($captcha === '' || !isset($_SESSION['fantasy_captcha_answer']) || $captcha !== $_SESSION['fantasy_captcha_answer']) {
        $errors[] = 'Неверная капча.';
        generate_captcha();
    }

    // Фильтр оскорбительных/запрещённых названий команды
    if ($team_name !== '' && is_offensive_team_name($team_name)) {
        $errors[] = 'Название команды нарушает правила. Пожалуйста, выберите другое.';
    }

    // Проверка уникальности email и названия команды
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
    if (!$errors) {
        if ($stmt = $db->prepare("SELECT id FROM fantasy_users WHERE team_name = ? LIMIT 1")) {
            $stmt->bind_param('s', $team_name);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Команда с таким названием уже существует.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Ошибка подготовки запроса (проверка названия команды).';
        }
    }

    // Вставка
    if (!$errors) {
        $password_plain = $password; // по требованию — без хеширования
        $points_2025 = 0;
        $consent_ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $consent_time = date('Y-m-d H:i:s');

        if ($stmt = $db->prepare("
            INSERT INTO fantasy_users (email, password_plain, team_name, first_name, last_name, points_2025, consent_ip, consent_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")) {
           $stmt->bind_param('sssssiss',
    $email, $password_plain, $team_name, $first_name, $last_name,
    $points_2025, $consent_ip, $consent_time
);
            if ($stmt->execute()) {
                $success = true;
                generate_captcha();
                $_POST = [];
            } else {
                // На случай срабатывания уникальных индексов — дадим дружелюбную ошибку
                if ($db->errno === 1062) {
                    $errors[] = 'Такой email или название команды уже заняты.';
                } else {
                    $errors[] = 'Не удалось сохранить данные. Попробуйте позже.';
                }
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
    <title>Регистрация в Fantasy</title>

    <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/img/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/img/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="apple-touch-icon" href="/img/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" sizes="192x192" href="/img/android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="/img/android-chrome-512x512.png">

    <link rel="stylesheet" href="/css/main.css" />
    <style>
        .Fantasy {
          padding: 22svh 20px 40px;
          background: url('/img/fantasy-backgound.jpg') center/cover no-repeat fixed;
          font-family: PLAY-REGULAR, Arial, sans-serif;
          display: flex;
          align-items: flex-start;
          justify-content: center;
          min-height: 100svh;
        }
        .fantasy-center { width: 100%; max-width: 640px; }
        .auth-card {
          background: rgba(255,255,255,0.97);
          border-radius: 16px;
          padding: 24px;
          box-shadow: 0 6px 18px rgba(0,0,0,.15);
        }
        .auth-card h1 {
          font-family: PLAY-BOLD, Arial, sans-serif;
          color: #00296B;
          font-size: 28px;
          margin: 0 0 12px;
          text-align: center;
        }
        .lead { color:#333; margin: 0 0 16px; text-align: center; }
        .field { margin-bottom: 16px; }
        label { display:block; margin-bottom:6px; font-weight:bold; color:#1a3c72; }
        input[type="text"], input[type="email"], input[type="password"] {
          width:100%; padding:10px 12px;
          border:1px solid #ccc; border-radius:8px; background:#fff; font-size:15px;
        }
        .captcha-row { display:flex; gap:10px; align-items:center; justify-content:space-between; }
        .consent { display:flex; align-items:center; gap:8px; font-size:14px; }
        .consent a { color:#00509D; text-decoration:underline; }
        .btn {
          background:#00296B; color:#FDC500; border:2px solid #FDC500;
          border-radius:10px; padding:10px 16px; font-size:16px; cursor:pointer; width:100%;
        }
        .btn:hover { background:#000; color:#fff; border-color:#fff; }
        .alert { padding:12px 14px; border-radius:8px; margin-bottom:16px; }
        .alert.success { background:#e7f6e7; color:#155724; border:1px solid #c3e6cb; }
        .alert.error { background:#fdeceb; color:#7d1c1a; border:1px solid #f5c6cb; }
        .muted { color:#666; font-size:13px; text-align:center; }

        .field.consent input[type="checkbox"] {
            appearance: none; width: 18px; height: 18px;
            border: 2px solid #00296B; border-radius: 4px; position: relative; cursor: pointer;
        }
        .field.consent input[type="checkbox"]:checked::after {
            content: "✔"; font-size: 14px; position: absolute; top: -2px; left: 2px; color: #00296B;
        }

        @media (max-width: 1024px) { .Fantasy { padding: 20svh 20px 40px; } }
        @media (max-width: 768px)  { .Fantasy { padding: 15svh 20px 40px; } }
    </style>
</head>
<body>

<?php include_block('header'); ?>

<div class="Fantasy">
  <div class="fantasy-center">
    <section class="auth-card">
      <h1>Регистрация в Fantasy лиге</h1>
      <p class="lead">Создайте команду, участвуйте в сезоне 2025 и набирайте очки!</p>

      <?php if ($success): ?>
        <div class="alert success">
          Готово! Аккаунт создан. Теперь вы можете <a href="fantasy.php">войти</a> и управлять своей Fantasy-командой.
        </div>
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

      <form method="post" action="fantasy_login.php" autocomplete="off" novalidate>
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
          <label for="first_name">Имя</label>
          <input type="text" id="first_name" name="first_name"
                 value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8') : '' ?>"
                 placeholder="Иван" maxlength="60" required>
        </div>

        <div class="field">
          <label for="last_name">Фамилия</label>
          <input type="text" id="last_name" name="last_name"
                 value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name'], ENT_QUOTES, 'UTF-8') : '' ?>"
                 placeholder="Иванов" maxlength="60" required>
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
            <div class="muted" style="text-align:left;">Решите: <strong><?= htmlspecialchars($_SESSION['fantasy_captcha_text'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong></div>
            <a class="muted" href="fantasy_login.php?refresh_captcha=1" style="text-decoration: underline;">обновить</a>
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
      </form>

      <div class="muted" style="margin-top:12px;">
        Уже есть аккаунт? <a href="fantasy.php">Войти</a>
      </div>
    </section>
  </div>
</div>

<?php include_block('footer'); ?>

<script src="./js/index.bundle.js"></script>
</body>
</html>
