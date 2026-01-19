<?php
require_once __DIR__ . "/db.php";
session_start();

if (!isset($_SESSION["user_id"])) {
  header("Location: /auth/login.php");
  exit;
}

$user_id = (int)$_SESSION["user_id"];

$rows = dbQuery("
  SELECT f.id, f.stop_id, f.route_name, f.created_at, s.name AS stop_name, s.latitude, s.longitude
  FROM favorites f
  LEFT JOIN stops s ON s.id = f.stop_id
  WHERE f.user_id = :u
  ORDER BY f.created_at DESC
", ["u"=>$user_id])->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Избранное - MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">MosBus</div>
  <nav class="menu">
    <a href="/">Главная</a>
    <a href="/feedback.php">Поддержка</a>
    <a href="/auth/logout.php">Выход</a>
  </nav>
</header>

<section class="panel-block" style="max-width:1000px;margin:0 auto;">
  <h2>Избранное</h2>

  <?php if (!$rows): ?>
    <div class="statCard">Пока ничего не добавлено.</div>
  <?php else: ?>
    <?php foreach ($rows as $r): ?>
      <div class="statCard" style="margin-bottom:10px;">
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
          <div>
            <div><b><?= htmlspecialchars($r["route_name"]) ?></b></div>
            <div class="smallMuted">
              Остановка: <?= htmlspecialchars($r["stop_name"] ?: ("ID ".$r["stop_id"])) ?> (<?= (int)$r["stop_id"] ?>)
            </div>
          </div>

          <a class="btn primary"
             href="/?stop_id=<?= (int)$r["stop_id"] ?>&route=<?= urlencode($r["route_name"]) ?>">
            Показать на карте
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
<footer class="footer">
  <p>
    MosBus - сервис на основе
    <a href="https://data.mos.ru/opendata/752?pageSize=10&pageIndex=0&isRecommendationData=false&isDynamic=false&version=8&release=82"
       target="_blank"
       rel="noopener noreferrer">
      открытых данных
    </a>
    Правительства Москвы.
	<p class="muted">
    Пользуясь сайтом, вы соглашаетесь с
    <a href="/privacy.php">обработкой персональных данных</a>.
  </p>
  </p>
</footer>
</body>
</html>
