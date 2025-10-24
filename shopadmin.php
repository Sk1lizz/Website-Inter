<?php
session_start();
require_once 'db.php';

// === Авторизация (та же, что в admin.php) ===
define('ADMIN_LOGIN', 'admin');
define('ADMIN_PASS', 'fcinter2025');

if (isset($_POST['auth_login'], $_POST['auth_pass'])) {
    if ($_POST['auth_login'] === ADMIN_LOGIN && $_POST['auth_pass'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: shopadmin.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

if (!isset($_SESSION['admin_logged_in'])):
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход в админку магазина</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial,sans-serif; background:#f0f4f8; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
    .login-container { background:#fff; padding:30px 40px; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,.1); width:100%; max-width:400px; }
    h2 { color:#004080; text-align:center; margin:0 0 20px; }
    input { width:100%; padding:10px; margin:6px 0 14px; border:1px solid #ccc; border-radius:6px; }
    button { width:100%; background:#004080; color:#fff; border:none; padding:12px; border-radius:6px; font-size:15px; cursor:pointer; }
    button:hover{ background:#003060; }
    .error{ color:red; text-align:center; margin-bottom:10px; }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Магазин — вход</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
      <input name="auth_login" type="text" placeholder="Логин" required>
      <input name="auth_pass" type="password" placeholder="Пароль" required>
      <button type="submit">Войти</button>
    </form>
  </div>
</body>
</html>
<?php exit; endif; ?>


<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Магазин клуба — управление заказами</title>
  <link rel="stylesheet" href="/css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: 'Play', sans-serif; background:#f3f6fb; padding:20px; }
    h1 { color:#1c3d7d; text-align:center; margin-bottom:30px; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,.1); }
    th, td { padding:10px 12px; border-bottom:1px solid #ddd; text-align:left; font-size:14px; }
    th { background:#083c7e; color:#fff; }
    tr:nth-child(even){ background:#f9f9f9; }
    select.status { padding:6px 10px; border-radius:8px; border:1px solid #ccc; font-size:13px; }
    .done { color:#2ecc71; font-weight:bold; }
    .pending { color:#FDC500; font-weight:bold; }
    .processing { color:#00BFFF; font-weight:bold; }
    .wrapper { max-width:1200px; margin:0 auto; }
    .logout { text-align:right; margin-bottom:10px; }
    .logout form button { background:#083c7e; color:#fff; border:none; border-radius:8px; padding:8px 14px; cursor:pointer; }
    .logout form button:hover { background:#0a2d5d; }
  </style>
</head>
<body>
    <?php include 'headeradmin.html'; ?>
  <div class="wrapper">
    <div class="logout">
      <form method="post" action="logout.php"><button>Выйти</button></form>
    </div>
    <h1>Управление заказами</h1>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Игрок</th>
          <th>Товар</th>
          <th>Рост</th>
          <th>Размер</th>
          <th>Цена</th>
          <th>Статус</th>
          <th>Дата</th>
        </tr>
      </thead>
      <tbody id="ordersBody">
        <tr><td colspan="8">Загрузка...</td></tr>
      </tbody>
    </table>
  </div>

<script>
async function loadOrders(){
  const res = await fetch('api/shop_orders.php');
  const data = await res.json();
  const body = document.getElementById('ordersBody');
  if(!data.length){
    body.innerHTML = '<tr><td colspan="8">Нет заказов</td></tr>';
    return;
  }
  body.innerHTML = data.map(o => `
    <tr>
      <td>${o.id}</td>
      <td>${o.player}</td>
      <td>${o.product}</td>
      <td>${o.variant_height || '-'}</td>
      <td>${o.variant_size || '-'}</td>
      <td>${o.price} XP</td>
      <td>
       <select class="status" onchange="updateStatus(${o.id}, this.value)">
  <option value="ожидает" ${o.status==='ожидает'?'selected':''}>ожидает</option>
  <option value="в обработке" ${o.status==='в обработке'?'selected':''}>в обработке</option>
  <option value="выдано" ${o.status==='выдано'?'selected':''}>выдано</option>
  <option value="отменено" ${o.status==='отменено'?'selected':''}>отменено</option>
</select>
      </td>
      <td>${o.purchased_at}</td>
    </tr>
  `).join('');
}

async function updateStatus(id, status){
  const res = await fetch('api/update_shop_status.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ id, status })
  });
  const data = await res.json();
  if(data.success){
    alert('Статус обновлён');
  } else {
    alert('Ошибка: '+(data.message||''));
  }
}

loadOrders();
</script>

</body>
</html>
