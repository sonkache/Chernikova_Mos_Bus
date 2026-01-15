<?php
require_once __DIR__ . "/config.php";
session_start();
$isAuth = isset($_SESSION["user_id"]);
$userName = $isAuth ? ($_SESSION["name"] ?? "") : "";
$userStatus = $isAuth ? ($_SESSION["status"] ?? "active") : "";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
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
      <a href="/auth/logout.php">Выход</a>
    <?php else: ?>
      <a href="/auth/login.php">Вход</a>
      <a href="/auth/register.php">Регистрация</a>
    <?php endif; ?>
  </nav>
</header>

<h1 style="text-align:center;margin-top:20px;">
  MosBus - загруженность общественного транспорта
</h1>

<section style="max-width:600px;margin:20px auto;">
  <input type="text"
         id="stopSearch"
         placeholder="Введите название остановки"
         style="width:100%;padding:10px;">
  <div id="searchResults"></div>
</section>

<div id="map" class="map"></div>
<section id="stopPanel" class="panel-block" style="display:none;max-width:600px;margin:20px auto;">
  <h2>Информация по остановке</h2>

  <div class="panel">
    <div class="panel-row">
      <div class="panel-title">Остановка:</div>
      <div id="stopTitle" class="panel-value muted">
        Выберите остановку на карте
      </div>
    </div>

    <div class="panel-row">
      <div class="panel-title">Маршруты:</div>
      <div id="routesList" class="routes-list muted">
        -
      </div>
    </div>

    <div class="panel-row">
      <div class="panel-title">Интервалы (каждые 30 минут):</div>
      <div id="slotsGrid" class="slots-grid muted">
        Выберите маршрут:
      </div>
    </div>

    <div class="panel-row">
      <div class="panel-title">Статистика:</div>
      <div id="statsBox" class="stats-box muted">
        Выберите интервал
      </div>
    </div>
  </div>
</section>

<script>
  window.IS_AUTH = <?= $isAuth ? "true" : "false" ?>;
  window.USER_NAME = <?= json_encode($userName) ?>;
  window.USER_STATUS = <?= json_encode($userStatus) ?>;
</script>

<script src="https://api-maps.yandex.ru/2.1/?apikey=07131a5d-203a-4059-89aa-fb4a2eceefdb&lang=ru_RU"></script>
<script src="/js/map.js"></script>
<script src="/js/search.js"></script>
<script src="/js/stop_panel.js"></script>
</body>
</html>
