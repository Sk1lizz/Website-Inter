<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// === Авторизация ===
if (!isset($_SESSION['player_id'])) {
  header('Location: user.php');
  exit;
}

$playerId = (int)$_SESSION['player_id'];

// === CSRF ===
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$CSRF = $_SESSION['csrf'];

// === Данные игрока ===
$stmt = $db->prepare("SELECT name, photo, xp_total, xp_spent FROM players WHERE id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

$playerName = htmlspecialchars($me['name'] ?? 'Игрок');
$playerPhoto = !empty($me['photo']) ? $me['photo'] : '/img/player/player_0.png';

$xp_total = (int)($me['xp_total'] ?? 0);
$xp_spent = (int)($me['xp_spent'] ?? 0);
$xp_available = max(0, $xp_total - $xp_spent);

// --- допустимые варианты для футболок ---
$TSHIRT_SIZES  = ['S','M','L','XL','XXL','XXXL'];
$TSHIRT_HEIGHT = ['170','176','182','188','194','200'];

// === Обработка покупки ===
$flash = ['type' => '', 'msg' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_product_id'])) {
  $posted_csrf = isset($_POST['csrf']) ? $_POST['csrf'] : '';
  if ($posted_csrf !== $CSRF) {
    $flash = ['type' => 'error', 'msg' => 'Неверный CSRF-токен. Обновите страницу.'];
  } else {
    $productId = (int)$_POST['buy_product_id'];

    // Берём товар
    $stmt = $db->prepare("
      SELECT id, name, category, price, description, IFNULL(is_active,1) AS is_active
      FROM shop_products
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
      $flash = ['type' => 'error', 'msg' => 'Товар не найден.'];
    } elseif (!(int)$product['is_active']) {
      $flash = ['type' => 'error', 'msg' => 'Товар недоступен для покупки.'];
    } else {
      $price = (int)$product['price'];

      // --- проверка вариантов для футболок ---
      $variant_size = null;
      $variant_height = null;

      if ($product['category'] === 'Футболки') {
        $variant_size = isset($_POST['variant_size']) ? strtoupper(trim($_POST['variant_size'])) : null;
        $variant_height = isset($_POST['variant_height']) ? trim($_POST['variant_height']) : null;

        if (!in_array($variant_size, $TSHIRT_SIZES) || !in_array($variant_height, $TSHIRT_HEIGHT)) {
          $flash = ['type' => 'error', 'msg' => 'Выберите рост и размер.'];
          goto SKIP_PURCHASE;
        }
      }

      // --- транзакция ---
      $db->begin_transaction();
      try {
        $stmt = $db->prepare("SELECT xp_total, xp_spent FROM players WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $playerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) throw new Exception('Профиль не найден.');

        $xp_total_db = (int)$row['xp_total'];
        $xp_spent_db = (int)$row['xp_spent'];
        $xp_available_db = $xp_total_db - $xp_spent_db;

        if ($price < 0) $price = 0;
        if ($xp_available_db < $price) throw new Exception('Недостаточно очков опыта для покупки.');

        $stmt = $db->prepare("UPDATE players SET xp_spent = xp_spent + ? WHERE id = ?");
        $stmt->bind_param("ii", $price, $playerId);
        if (!$stmt->execute()) throw new Exception('Не удалось списать очки.');

        // запись покупки
        $stmt = $db->prepare("
          INSERT INTO shop_purchases (player_id, product_id, price, variant_size, variant_height, status, purchased_at)
          VALUES (?, ?, ?, ?, ?, 'ожидает', NOW())
        ");
        $stmt->bind_param("iiiss", $playerId, $productId, $price, $variant_size, $variant_height);
        if (!$stmt->execute()) throw new Exception('Не удалось записать покупку.');

        $db->commit();
        $flash = ['type' => 'ok', 'msg' => 'Покупка успешна: ' . htmlspecialchars($product['name']) . ' — ' . $price . ' XP'];
        $xp_spent += $price;
        $xp_available = max(0, $xp_total - $xp_spent);
      } catch (Exception $e) {
        $db->rollback();
        $flash = ['type' => 'error', 'msg' => $e->getMessage()];
      }
    }
  }
}
SKIP_PURCHASE:;

// === Фильтры по категориям ===
$category = isset($_GET['cat']) ? trim($_GET['cat']) : '';
if ($category !== '') {
  $stmt = $db->prepare("
    SELECT id, name, category, price, description, IFNULL(is_active,1) AS is_active
    FROM shop_products
    WHERE IFNULL(is_active,1) = 1 AND category = ?
    ORDER BY id DESC
  ");
  $stmt->bind_param("s", $category);
} else {
  $stmt = $db->prepare("
    SELECT id, name, category, price, description, IFNULL(is_active,1) AS is_active
    FROM shop_products
    WHERE IFNULL(is_active,1) = 1
    ORDER BY id DESC
  ");
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Категории
$catsRes = $db->query("SELECT DISTINCT category FROM shop_products WHERE IFNULL(is_active,1)=1 ORDER BY category");
$cats = [];
if ($catsRes) {
  while ($r = $catsRes->fetch_assoc()) {
    if ($r['category'] !== null && $r['category'] !== '') $cats[] = $r['category'];
  }
}

// === Параметры фона ===
$stmt = $db->prepare("SELECT background_key, can_change_background FROM player_backgrounds WHERE player_id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$bg = $stmt->get_result()->fetch_assoc() ?: ['background_key' => '', 'can_change_background' => 0];

$currentBgKey = $bg['background_key'];
$canChangeBackground = (int)$bg['can_change_background'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Магазин клуба</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="css/main.css">
</head>

<?php include 'headerlk.html'; ?>

<body>
<div class="user_page">
  <div class="shop-wrap">
    <div class="lk-header card">
      <div class="lk-title">Магазин клуба</div>
    </div>

    <div class="card" style="width:100%;max-width:1200px;">
      <div class="topbar" style="margin-bottom:10px; display:flex; align-items:center;">
        <a href="user.php" id="viewPublicProfile" style="margin-right:auto;">← Личный кабинет</a>
        <span style="font-weight:700;color:#66a1fd;">Доступно: <?= number_format($xp_available, 0, '.', ' ') ?> XP</span>
      </div>

      <?php if ($flash['msg']): ?>
        <div class="flash <?= $flash['type']==='ok'?'ok':'error' ?>"><?= htmlspecialchars($flash['msg']) ?></div>
      <?php endif; ?>

      <div class="shop-filters">
        <a href="shop.php" class="<?= $category==='' ? 'active':'' ?>">Все</a>
        <?php foreach ($cats as $c): ?>
          <a href="shop.php?cat=<?= urlencode($c) ?>" class="<?= ($category===$c ? 'active' : '') ?>"><?= htmlspecialchars($c) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($products)): ?>
        <p>Нет доступных товаров.</p>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($products as $p):
            $pid = (int)$p['id'];
            $pname = htmlspecialchars($p['name']);
            $pcat = htmlspecialchars($p['category']);
            $pprice = (int)$p['price'];
            $pdesc = htmlspecialchars($p['description']);
            $img = "/img/shop/{$pid}.jpg";
            $canBuy = ($xp_available >= $pprice);
          ?>
            <div class="product-card">
              <img class="product-thumb" src="<?= $img ?>" alt="<?= $pname ?>" onerror="this.src='/img/shop/placeholder.jpg'">
              <div class="product-body">
                <div class="product-title"><?= $pname ?></div>
                <div class="product-cat"><?= $pcat ?></div>
                <div class="product-desc"><?= $pdesc ?></div>
                <div class="product-price"><?= $pprice ?> XP</div>
                <div class="product-actions">
                 <form method="POST" class="buy-form" data-product="<?= $pname ?>" data-price="<?= $pprice ?>">
  <input type="hidden" name="csrf" value="<?= $CSRF ?>">
  <input type="hidden" name="buy_product_id" value="<?= $pid ?>">

  <?php if ($pcat === 'Футболки'): ?>
    <div class="variant-select">
      <select name="variant_height" required>
        <option value="" disabled selected>Рост</option>
        <?php foreach ($TSHIRT_HEIGHT as $h): ?>
          <option value="<?= $h ?>"><?= $h ?></option>
        <?php endforeach; ?>
      </select>
      <select name="variant_size" required>
        <option value="" disabled selected>Размер</option>
        <?php foreach ($TSHIRT_SIZES as $s): ?>
          <option value="<?= $s ?>"><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>

  <button type="button" class="btn-buy" <?= $canBuy ? '' : 'disabled' ?>>
    <?= $canBuy ? 'Купить' : 'Недостаточно XP' ?>
  </button>
</form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card" style="width:100%;max-width:1200px; margin-top:16px;">
      <h2>История покупок</h2>
      <?php
        $stmt = $db->prepare("
          SELECT sp.id, sp.price, sp.purchased_at, sp.variant_size, sp.variant_height, sp.status, pr.name
          FROM shop_purchases sp
          JOIN shop_products pr ON pr.id = sp.product_id
          WHERE sp.player_id = ?
          ORDER BY sp.purchased_at DESC
          LIMIT 20
        ");
        $stmt->bind_param("i", $playerId);
        $stmt->execute();
        $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      ?>
      <?php if (empty($history)): ?>
        <p>Пока нет покупок.</p>
      <?php else: ?>
        <table class="attendance-table">
          <thead>
            <tr><th>Товар</th><th>Цена</th><th>Дата</th><th>Статус</th></tr>
          </thead>
          <tbody>
          <?php foreach ($history as $h): ?>
            <tr>
              <td><?= htmlspecialchars($h['name']) ?></td>
              <td><?= (int)$h['price'] ?> XP</td>
              <td>
                <?= date('d.m.Y H:i', strtotime($h['purchased_at'])) ?>
                <?php if ($h['variant_size'] || $h['variant_height']): ?>
                  <div style="color:#9aa3b2; font-size:12px;">
                    <?= $h['variant_height'] ? 'Рост: '.$h['variant_height'] : '' ?>
                    <?= $h['variant_size'] ? ' • Размер: '.$h['variant_size'] : '' ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <?php
  $status = htmlspecialchars($h['status']);
  $color = '#ccc';

  // поддержка всех статусов
  if ($status == 'ожидает')           $color = '#FDC500'; // жёлтый
  elseif ($status == 'в обработке')   $color = '#00BFFF'; // голубой
  elseif ($status == 'принят')        $color = '#00BFFF'; // тот же, можно другой, если хочешь
  elseif ($status == 'выполнен')      $color = '#2ecc71'; // зелёный
  elseif ($status == 'выдано')        $color = '#2ecc71';
  elseif ($status == 'отменено')      $color = '#e74c3c'; // красный

  // для красоты: переводим статус с заглавной буквы
  $statusText = ucfirst($status);
?>
<span style="color:<?= $color ?>; font-weight:bold;"><?= $statusText ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function confirmBuy(name, price, height, size) {
  var extra = '';
  if (height || size) extra = "\nРост: " + (height || '—') + "\nРазмер: " + (size || '—');
  return confirm("Подтвердить покупку «" + name + "» за " + price + " XP?" + extra);
}
</script>

<!-- === МОДАЛЬНОЕ ОКНО ПОДТВЕРЖДЕНИЯ ПОКУПКИ === -->
<div class="shop-modal-overlay" id="shopConfirmModal">
  <div class="modal-content">
    <h3 id="modalTitle">Подтверждение покупки</h3>
    <div id="modalBody" class="modal-body"></div>
    <div class="modal-buttons">
      <button id="confirmBuyBtn">Подтвердить</button>
      <button id="cancelBuyBtn">Отмена</button>
    </div>
  </div>
</div>


</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('shopConfirmModal');
  var modalBody = document.getElementById('modalBody');
  var confirmBtn = document.getElementById('confirmBuyBtn');
  var cancelBtn = document.getElementById('cancelBuyBtn');
  var currentForm = null;

  document.querySelectorAll('.buy-form .btn-buy').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var form = btn.closest('form');
      if (!form) return;

      var product = form.dataset.product;
      var price = form.dataset.price;
      var height = form.querySelector('[name="variant_height"]') ? form.querySelector('[name="variant_height"]').value : '';
      var size = form.querySelector('[name="variant_size"]') ? form.querySelector('[name="variant_size"]').value : '';

      if (form.querySelector('[name="variant_height"]') && (!height || !size)) {
        alert("Пожалуйста, выберите рост и размер перед покупкой.");
        return;
      }

      modalBody.innerHTML = `
        <p><strong>${product}</strong></p>
        <p>Стоимость: <span style="color:#FDC500; font-weight:bold;">${price} XP</span></p>
        ${height || size ? `<p>Рост: ${height || '—'}<br>Размер: ${size || '—'}</p>` : ''}
      `;
      modal.style.display = 'flex';
      currentForm = form;
    });
  });

  confirmBtn.addEventListener('click', function() {
    if (currentForm) currentForm.submit();
    modal.style.display = 'none';
  });

  cancelBtn.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  window.addEventListener('click', function(e) {
    if (e.target === modal) modal.style.display = 'none';
  });
});
</script>
</html>
