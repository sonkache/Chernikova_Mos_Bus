<?php
require_once __DIR__ . "/config.php";
session_start();
$isAuth = isset($_SESSION["user_id"]);
$userName = $isAuth ? ($_SESSION["name"] ?? "") : "";
$isAdmin = $isAuth && (($_SESSION["role"] ?? "user") === "admin");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>MosBus - Загруженность транспорта Москвы</title>
  <link rel="stylesheet" href="/css/style.css">
  <script src="/js/search.js?v=301" defer></script>
  <script src="/js/banner.js" defer></script>
  <script src="/js/stop_panel.js?v=1" defer></script>
</head>
<body>
  <header class="header">
  <div class="logo">MosBus</div>

  <nav class="menu">
    <a href="/">Главная</a>

    <?php if ($isAuth): ?>
     <span class="hello">
            Здравствуйте, <?= htmlspecialchars($userName ?: "пользователь") ?>
          </span>
      <a href="/favorites.php">Избранное</a>
      <a href="/feedback.php">Поддержка</a>

      <?php if ($isAdmin): ?>
        <a href="/admin/index.php">Админка</a>
      <?php endif; ?>

      <a href="/auth/logout.php">Выход</a>
    <?php else: ?>
      <a href="/auth/login.php">Вход</a>
      <a href="/auth/register.php">Регистрация</a>
    <?php endif; ?>
  </nav>
</header>

<div class="banner-container">
  <div class="banner-slide fade"><img src="/banners/banner1.jpg" alt="Баннер 1"></div>
  <div class="banner-slide fade"><img src="/banners/banner2.jpg" alt="Баннер 2"></div>
  <div class="banner-slide fade"><img src="/banners/banner3.jpg" alt="Баннер 3"></div>
</div>

<section class="search-block">
  <h2>Найдите остановку</h2>
  <div class="search-container">
    <input type="text" id="stopSearch" placeholder="Введите остановку">
    <div id="searchResults" class="search-results"></div>
  </div>
</section>


<section class="map-block">
  <div id="map" class="map"></div>
</section>

<section id="stopPanel" class="panel-block" style="display:none;max-width:600px;margin:20px auto;">
  <h2>Информация по остановке</h2>

  <div class="panel">
    <div class="panel-row">
      <div class="panel-title">Остановка:</div>
      <div id="stopTitle" class="panel-value muted">
        Выберите остановку
      </div>
    </div>

    <div class="panel-row">
      <div class="panel-title">Маршруты:</div>
      <div id="routesList" class="routes-list muted">
        -
      </div>
    </div>

    <div class="panel-row">
      <div class="panel-title">Интервалы:</div>
      <div id="slotsGrid" class="slots-grid muted">
        Выберите маршрут
      </div>
    </div>

    <div class="panel-row">
      <div class="panel-title">Статистика:</div>
      <div id="statsBox" class="stats-box muted">
        Выберите маршрут
      </div>
    </div>
  </div>
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

<script>
  window.IS_AUTH = <?= $isAuth ? "true" : "false" ?>;
</script>

<script src="https://api-maps.yandex.ru/2.1/?apikey=07131a5d-203a-4059-89aa-fb4a2eceefdb&lang=ru_RU"></script>
<script src="/js/map.js?v=1"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const stopId = params.get("stop_id");
  const route = params.get("route");
  if (!stopId) return;
  const waitPanel = setInterval(() => {
    if (!window.onStopClick) return;
    clearInterval(waitPanel);
    window.onStopClick(stopId, "Остановка");
    if (route) {
      setTimeout(() => {
        window.onRouteClick(route);
      }, 500);
    }
  }, 200);
});
</script>
</body>
</html>
