<?php
// fantasy.php — СТРАНИЦА ВХОДА
ob_start();
session_start();

require_once __DIR__ . '/db.php';

// Безопасный инклюд хедера/футера
function include_block($name) {
    $base = __DIR__ . '/blocks/';
    $php  = $base . $name . '.php';
    $html = $base . $name . '.html';
  if (file_exists($html)) {
    include $html;
} elseif (file_exists($php)) {
    include $php;
    } else {
        if ($name === 'header') {
            echo '<header style="padding:10px 16px;background:#00296B;color:#FDC500;">Header placeholder — blocks/'.$name.'.php|.html не найден</header>';
        } else {
            echo '<footer style="padding:10px 16px;background:#f2f2f2;color:#333;margin-top:24px;">Footer placeholder — blocks/'.$name.'.php|.html не найден</footer>';
        }
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Введите email и пароль.';
    } else {
        if ($stmt = $db->prepare("SELECT id, team_name, password_plain FROM fantasy_users WHERE email = ? LIMIT 1")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res && $password === $res['password_plain']) {
                $_SESSION['fantasy_user_id'] = (int)$res['id'];
                $_SESSION['fantasy_team']    = (string)$res['team_name'];

                $target = '/fantasy_cabinet.php';
                if (!headers_sent()) {
                    header("Location: {$target}");
                    exit;
                } else {
                    echo "<!doctype html><meta charset='utf-8'><p>Перейдите в <a href='{$target}'>личный кабинет</a>.</p>";
                    exit;
                }
            } else {
                $error = 'Неверный email или пароль.';
            }
        } else {
            $error = 'Ошибка сервера: не удалось подготовить запрос.';
            error_log('fantasy login prepare failed: ' . $db->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- FAVICONS -->
  <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/img/favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="/img/favicon-16x16.png" sizes="16x16" type="image/png">
  <link rel="apple-touch-icon" href="/img/apple-touch-icon.png" sizes="180x180">
  <link rel="icon" sizes="192x192" href="/img/android-chrome-192x192.png">
  <link rel="icon" sizes="512x512" href="/img/android-chrome-512x512.png">

  <title>Fantasy лига</title>
  <link rel="stylesheet" href="/css/main.css" />
  <style>
    .Fantasy {
      padding: 19svh 20px 40px;
      background: url('/img/fantasy-backgound.jpg') center/cover no-repeat fixed;
      font-family: PLAY-REGULAR, Arial, sans-serif;
      min-height: 100svh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* ЛОГО */
    .fantasy-logo {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 30px;
    }
    .fantasy-logo img {
      max-width: 240px;
      height: auto;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,.5));
    }

    /* ОСНОВНОЙ КОНТЕНТ */
    .fantasy-wrap {
      width: 100%;
      max-width: 1100px;
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 24px;
    }

    .promo {
      background: rgba(0,0,0,0.45);
      color: #fff;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 6px 18px rgba(0,0,0,.25);
      backdrop-filter: blur(3px);
    }
    .promo h1 {
      font-family: PLAY-BOLD, Arial, sans-serif;
      font-size: 32px;
      margin-bottom: 14px;
      color: #FDC500;
      text-align: center;
    }
    .promo h2 { margin: 18px 0 8px; font-size: 20px; color: #FDC500; }
    .promo p { margin: 0 0 8px; color: #f3f3f3; }
    .promo .muted { color: #e9e9e9; font-size: 13px; opacity: .9; }

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
    }
    .field { margin-bottom: 16px; }
    label { display: block; margin-bottom: 6px; font-weight: bold; color: #1a3c72; }
    input[type="email"], input[type="password"] {
      width: 100%; padding: 10px 12px;
      border: 1px solid #ccc; border-radius: 8px; background: #fff; font-size: 15px;
    }
    .btn {
      background: #00296B; color: #FDC500; border: 2px solid #FDC500;
      border-radius: 10px; padding: 10px 16px; font-size: 16px; cursor: pointer;
    }
    .btn:hover { background: #000; color: #fff; border-color: #fff; }
    .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
    .alert.error { background: #fdeceb; color: #7d1c1a; border: 1px solid #f5c6cb; }
    .muted { color:#666; font-size:13px; }

    /* МОБИЛЬНАЯ ВЕРСИЯ */
 @media (max-width: 980px) {
  .fantasy-wrap {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* порядок на мобильных */
  .auth-card {
    order: 1; /* форма первой */
  }
  .promo {
    order: 2; /* текст после формы */
  }

  .fantasy-logo img {
    max-width: 160px;
  }
}

      @media (max-width: 768px) {
        .Fantasy {
 padding: 10svh 20px 40px;
        }
      }
    

  </style>
</head>
<body>
<?php include_block('header'); ?>

<div class="Fantasy">
  <!-- ЛОГО ВСЕГДА СВЕРХУ -->
  <div class="fantasy-logo">
    <img src="/img/icon/fantasy_logo.png" alt="Fantasy Logo">
  </div>

  <div class="fantasy-wrap">
    <!-- ЛЕВО: ПРОМО -->
    <section class="promo">
      <h1>Фэнтези-футбол: Твой вызов!</h1>
      <h2>Сформируй состав</h2>
      <p>В твоём распоряжении бюджет в 50 миллионов. Выбери с умом: нападающие приносят очки за голы, защитники — за сухие матчи, а ассистенты — за голевые передачи. Собери сбалансированную команду из футболистов FC Inter Moscow!</p>
      <h2>Управляй и улучшай</h2>
      <p>Следи за формой игроков! Если кто-то травмирован или не в духе, не беда. Каждый тур делай до двух замен в составе, чтобы всегда выставлять на игру самых сильных и мотивированных.</p>
      <h2>Борись за первое место</h2>
      <p>Твоя главная цель — взойти на вершину общей лиги. Сравнивай свои очки с результатами других менеджеров и докажи, что именно ты — самый проницательный футбольный стратег.</p>
      <p class="muted" style="margin-top:10px;">Сезон: 2025</p>
    </section>

    <!-- ПРАВО: ФОРМА -->
    <section class="auth-card">
  <h1>Вход</h1>
  <?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <form method="post" action="fantasy.php" autocomplete="off" novalidate>
    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="field">
      <label for="password">Пароль</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button class="btn" type="submit">Войти</button>
  </form>

  <div class="muted" style="margin-top:12px;">
    Нет аккаунта? <a href="fantasy_login.php">Зарегистрироваться</a>
  </div>
  <div class="muted" style="margin-top:8px;">
    <a href="https://www.fcintermoscow.com/contacts.html">Забыли пароль? Напишите нам</a>
  </div>
</section>
  </div>
</div>

<?php include_block('footer'); ?>
<script src="./js/index.bundle.js"></script>
</body>
</html>
