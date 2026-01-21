<?php
require_once __DIR__ . "/_admin.php";
requireAdmin();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Админка - MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">🛠 Админка</div>
  <nav class="menu">
    <a href="/">Сайт</a>
    <a href="/admin/users.php">Пользователи</a>
    <a href="/admin/feedback.php">Обращения</a>
    <a href="/admin/suspicious.php">Подозрительные</a>
    <a href="/auth/logout.php">Выход</a>
  </nav>
</header>

<section class="panel-block">
  <h2>Панель администратора</h2>
  <div class="panel">
    <div>+ Просмотр пользователей</div>
    <div>+ Блокировка / разблокировка</div>
    <div>+ Просмотр обращений / ответы</div>
    <div>+ Активность пользователей</div>
  </div>
</section>

</body>
</html>
