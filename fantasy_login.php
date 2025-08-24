<?php
// fantasy_login.php
ob_start();                           // на случай BOM/пробелов до PHP
session_start();
ini_set('display_errors', 1);         // временно для отладки
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

$error = '';
$didRedirect = false;

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

                $target = '/fantasy_cabinet.php'; // цель редиректа (нижний регистр и со слэшем — надежнее)
                if (!headers_sent()) {
                    header("Location: {$target}");
                    $didRedirect = true;
                    exit;
                } else {
                    // headers уже отправлены — покажем fallback-ссылку
                    echo "<!doctype html><meta charset='utf-8'><p>Перейдите в <a href='{$target}'>личный кабинет</a>.</p>";
                    $didRedirect = true;
                    exit;
                }
            } else {
                $error = 'Неверный email или пароль.';
            }
        } else {
            $error = 'Ошибка сервера: не удалось подготовить запрос.';
            error_log('fantasy_login prepare failed: ' . $db->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход в Fantasy</title>
  <link rel="stylesheet" href="/css/main.css">
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
    .fantasy-card {
        width: 100%;
        max-width: 640px;
        background: rgba(0,0,0,0.03);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 5px rgba(0,0,0,.08);
    }
    .fantasy-card h1 {
        font-family: PLAY-BOLD, Arial, sans-serif;
        color: #00296B;
        font-size: 28px;
        margin-bottom: 12px;
    }
    .field { margin-bottom: 16px; }
    label { display: block; margin-bottom: 6px; font-weight: bold; color: #1a3c72; }
    input[type="email"], input[type="password"] {
        width: 100%; padding: 10px 12px;
        border: 1px solid #ccc; border-radius: 8px; font-size: 15px;
    }
    .btn {
        background: #00296B; color: #FDC500; border: 2px solid #FDC500;
        border-radius: 10px; padding: 10px 16px; font-size: 16px; cursor: pointer;
    }
    .btn:hover { background: #000; color: #fff; border-color: #fff; }
    .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
    .alert.error { background: #fdeceb; color: #7d1c1a; border: 1px solid #f5c6cb; }
    .muted { color:#666; font-size:13px; }
  </style>
</head>
<body>
<?php include __DIR__ . '/blocks/header.html'; ?>

<div class="Fantasy">
  <div class="fantasy-card">
    <h1>Вход</h1>
    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" action="fantasy_login.php" autocomplete="off" novalidate>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="field">
        <label>Пароль</label>
        <input type="password" name="password" required>
      </div>
      <button class="btn" type="submit">Войти</button>
    </form>
    <div class="muted" style="margin-top:12px;">
      Нет аккаунта? <a href="fantasy.php">Зарегистрироваться</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/blocks/footer.html'; ?>

 <script src="./js/index.bundle.js"></script>
</body>
</html>
