<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// === Проверка авторизации ===
if (!isset($_SESSION['player_id'])) {
    header('Location: user.php');
    exit;
}

$playerId = (int)$_SESSION['player_id'];

// === Данные игрока для шапки ===
$stmt = $db->prepare("SELECT name, photo FROM players WHERE id = ?");
$stmt->bind_param("i", $playerId);
$stmt->execute();
$playerRow = $stmt->get_result()->fetch_assoc();

$playerName = htmlspecialchars($playerRow['name'] ?? 'Игрок');
$playerPhoto = !empty($playerRow['photo']) ? $playerRow['photo'] : '/img/player/player_0.png';

// === Текущая активная рамка ===
$cur = $db->prepare("SELECT frame_key FROM player_frames WHERE player_id = ?");
$cur->bind_param("i", $playerId);
$cur->execute();
$current = $cur->get_result()->fetch_assoc();
$currentFrame = $current['frame_key'] ?? '';

// === Доступные игроку рамки ===
$own = $db->prepare("SELECT frame_key FROM player_unlocked_frames WHERE player_id = ?");
$own->bind_param("i", $playerId);
$own->execute();
$owned = array_map(fn($r) => $r['frame_key'], $own->get_result()->fetch_all(MYSQLI_ASSOC));

// === Текущий фон и право менять фон ===
$bgStmt = $db->prepare("SELECT background_key, can_change_background FROM player_backgrounds WHERE player_id = ?");
$bgStmt->bind_param("i", $playerId);
$bgStmt->execute();
$bgRow = $bgStmt->get_result()->fetch_assoc() ?? ['background_key' => '', 'can_change_background' => 0];
$currentBgKey = $bgRow['background_key'] ?? '';
$canChangeBackground = (int)($bgRow['can_change_background'] ?? 0);

// === Доступные игроку фоны (бесплатные И/ИЛИ разблокированные) ===
$bgQ = $db->prepare("
  SELECT b.key_name, b.title, b.image_path
  FROM backgrounds b
  LEFT JOIN player_unlocked_backgrounds ub
    ON ub.background_key = b.key_name AND ub.player_id = ?
  WHERE b.is_free = 1 OR ub.player_id IS NOT NULL
  ORDER BY b.id
");
$bgQ->bind_param("i", $playerId);
$bgQ->execute();
$availableBackgrounds = $bgQ->get_result()->fetch_all(MYSQLI_ASSOC);

// helper для названия фона
function bgTitle($k, $fallbackTitle = '') {
    return $fallbackTitle !== '' ? $fallbackTitle : ucfirst(str_replace('_',' ',$k));
}

// === Вспомогательные функции ===
function frameTitle($k) {
    return match ($k) {
        'gold'        => 'Золотая рамка профиля',
        'green'       => 'Зелёная рамка профиля',
        'blue'        => 'Синяя рамка профиля',
        'purple'      => 'Фиолетовая рамка профиля',
        'gold_glow'   => 'Неоновая золотая рамка профиля',
        'green_glow'  => 'Неоновая зелёная рамка профиля',
        'blue_glow'   => 'Неоновая синяя рамка профиля',
        'purple_glow' => 'Неоновая фиолетовая рамка профиля',
        default       => ucfirst($k),
    };
}

function framePreview($k) {
    // ПРИ НУЖДЕ обнови id под свои shop_products
    $map = [
        'gold'        => 23,
        'green'       => 24,
        'gold_glow'   => 25,
        'green_glow'  => 26,
        'blue'        => 27,
        'purple'      => 28,
        'blue_glow'   => 29,
        'purple_glow' => 30,
    ];
    $id = $map[$k] ?? 0;
    if ($id > 0 && file_exists($_SERVER['DOCUMENT_ROOT']."/img/shop/{$id}.jpg")) {
        return "/img/shop/{$id}.jpg";
    }
    return "/img/shop/placeholder.jpg";
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Кастомизация профиля | FC Inter Moscow</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/css/main.css">
  <style>
    body {
      background-color: #f6f7fb;
      font-family: Arial, sans-serif;
    }
    .user_page {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.08);
      padding: 24px;
    }
    .lk-title {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 20px;
    }
   /* --- Карточки РАМOК --- */
.frame-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  border: 1px solid #2a2f3a;        /* чуть контраста под тёмную тему */
  border-radius: 12px;
  background: #1f2430;              /* тёмный фон карточки */
}
.frame-card img {
  width: 64px; height: 64px; border-radius: 10px; object-fit: contain;
}
.frame-card .title { font-weight: 700; font-size: 16px; color: #fff; }
.frame-card .desc  { color: #9aa3b2; font-size: 13px; }
.frame-card .active { margin-left: auto; color: #2ecc71; font-weight: 700; }
.frame-card form { margin-left: auto; }             /* <-- кнопка вправо */
.frame-card button {
  background: #1a73e8; border: none; color: #fff;
  padding: 8px 14px; border-radius: 10px; font-weight: 700;
  cursor: pointer; transition: .2s;
  white-space: nowrap;
}
.frame-card button:hover { background: #1669d2; }

/* --- Секция ФОНОВ --- */
.bg-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); /* крупнее плитки */
  gap: 14px;
}
.bg-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  border: 1px solid #2a2f3a;
  border-radius: 12px;
  background: #1f2430;
  min-height: 92px;                                   /* одинаковая высота */
}
.bg-card img, .bg-card .noimg {
  width: 96px; height: 64px; border-radius: 10px; object-fit: cover;
  background: #111;
}
.bg-card .noimg { display: flex; align-items:center; justify-content:center; color:#bbb; font-size:12px; }
.bg-card .meta { flex: 1; min-width: 0; }            /* текст занимает центр */
.bg-card .title { font-weight: 700; font-size: 15px; color: #fff; }
.bg-card .desc  { color: #9aa3b2; font-size: 13px; }
.bg-card .active { margin-left: auto; color: #2ecc71; font-weight: 700; }
.bg-card form { margin-left: auto; }                  /* <-- кнопка вправо */
.bg-card button {
  background: #1a73e8; border: none; color: #fff;
  padding: 8px 14px; border-radius: 10px; font-weight: 700;
  cursor: pointer; transition: .2s;
  white-space: nowrap;
}
.bg-card button:hover { background: #1669d2; }

/* подпись под заголовком секции */
.section-subtitle { margin: 6px 0 12px; color:#9aa3b2; font-size:13px; }

/* ====== Фикс аватара в шапке профиля ====== */
.profile-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 22px;
}

.profile-header img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  background: #0a1124;        /* тёмный фон внутри рамки */
  box-shadow: 0 0 10px rgba(0,0,0,0.25);
}

.profile-header div {
  display: flex;
  flex-direction: column;
}

.profile-header div > div {
  font-weight: 700;
  color: #fff;
  font-size: 18px;
}

.profile-header small {
  color: #9aa3b2;
  font-size: 13px;
}

@media (max-width: 775px) {
  .bg-grid {
    grid-template-columns: 1fr;      /* одна колонка */
  }
  .bg-card {
    flex-direction: row;              /* картинка и текст по горизонтали */
    justify-content: space-between;
  }
  .bg-card img, .bg-card .noimg {
    width: 110px;
    height: 72px;
  }
  .bg-card button {
    margin-left: 0;
    align-self: center;
  }
}

  </style>
</head>
<body>
  <?php include 'headerlk.html'; ?>

  <div class="user_page">
    <div class="card" style="max-width:800px;width:100%">
      <div class="profile-header">
        <img src="<?= htmlspecialchars($playerPhoto) ?>" alt="Аватар">
        <div>
          <div style="font-weight:700;"><?= $playerName ?></div>
          <small style="color:#888;">Настройка профиля</small>
        </div>
      </div>

      <div class="lk-title">Кастомизация профиля</div>
      <h3 style="margin-top:8px">Рамка профиля</h3>

      <?php if (empty($owned)): ?>
        <p>У вас пока нет купленных рамок. Загляните в <a href="/shop.php?cat=<?=urlencode('Рамки')?>">магазин</a>.</p>
      <?php else: foreach ($owned as $k): ?>
        <div class="frame-card">
          <img src="<?= framePreview($k) ?>" alt="<?= frameTitle($k) ?>">
          <div>
            <div class="title"><?= frameTitle($k) ?></div>
            <div class="desc">Применяется к фото на странице игрока и в личном кабинете</div>
          </div>
          <?php if ($currentFrame === $k): ?>
            <div class="active">Активно</div>
          <?php else: ?>
            <form method="post" onsubmit="return applyFrame(this)">
              <input type="hidden" name="frame_key" value="<?=$k?>">
              <button type="submit">Назначить</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
    </div>
    
<div class="card" style="max-width:800px;width:100%">
      <h3 style="margin-top:20px">Фон профиля</h3>
      <p class="section-subtitle">Фон показывается на странице игрока и в личном кабинете.</p>

      <?php if ($canChangeBackground !== 1): ?>
        <p>Смена фона недоступна для вашего профиля.</p>
      <?php else: ?>
        <div class="bg-grid">
          <!-- Вариант "Без фона" -->
          <div class="bg-card">
            <div class="noimg">—</div>
            <div>
              <div class="title">Без фона</div>
              <div class="desc">Очистить фон профиля</div>
            </div>
            <?php if ($currentBgKey === '' || $currentBgKey === null): ?>
              <div class="active">Активно</div>
            <?php else: ?>
              <form onsubmit="return applyBackground('')">
                <button type="submit">Назначить</button>
              </form>
            <?php endif; ?>
          </div>

          <!-- Доступные фоны -->
          <?php if (!empty($availableBackgrounds)): ?>
            <?php foreach ($availableBackgrounds as $bgIt): 
              $key = $bgIt['key_name'];
              $title = bgTitle($key, $bgIt['title'] ?? '');
              $img = $bgIt['image_path'] ?: '';
            ?>
              <div class="bg-card">
                <?php if ($img): ?>
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>">
                <?php else: ?>
                  <div class="noimg">нет превью</div>
                <?php endif; ?>
                <div>
                  <div class="title"><?= htmlspecialchars($title) ?></div>
                  <div class="desc">Доступный фон</div>
                </div>
                <?php if ($currentBgKey === $key): ?>
                  <div class="active">Активно</div>
                <?php else: ?>
                  <form onsubmit="return applyBackground('<?= htmlspecialchars($key) ?>')">
                    <button type="submit">Назначить</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Нет доступных фонов. Загляните позже или в магазин.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      </div>
  </div>
</div>

  <script>
  async function applyFrame(form) {
    const fd = new FormData(form);
    const res = await fetch('/api/set_player_frame.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.ok) {
      location.reload();
    } else {
      alert('Не удалось применить рамку: ' + (data.msg || 'ошибка'));
    }
    return false;
  }
  </script>

  <script>
async function applyBackground(key) {
  try {
    const res = await fetch('/api/player_set_background.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ background_key: key })
    });
    const data = await res.json();
    if (data.success || data.ok) {
      location.reload();
    } else {
      alert('Не удалось применить фон: ' + (data.message || data.msg || 'ошибка'));
    }
  } catch (e) {
    alert('Сеть/сервер: не удалось применить фон');
  }
  return false;
}
</script>

  <script src="/js/index.bundle.js"></script>
</body>
</html>
