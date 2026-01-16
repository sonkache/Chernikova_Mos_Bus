<?php
require_once __DIR__ . "/db.php";
session_start();
$isAuth = isset($_SESSION["user_id"]);
$status = $isAuth ? ($_SESSION["status"] ?? "active") : "guest";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Поддержка - MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">MosBus</div>
  <nav class="menu">
    <a href="/">Главная</a>
    <?php if ($isAuth): ?>
      <a href="/favorites.php">Избранное</a>
      <a href="/auth/logout.php">Выход</a>
    <?php else: ?>
      <a href="/auth/login.php">Вход</a>
      <a href="/auth/register.php">Регистрация</a>
    <?php endif; ?>
  </nav>
</header>

<section class="panel-block" style="max-width:900px;margin:0 auto;">
  <h2>Поддержка</h2>

  <?php if ($isAuth && $status === "blocked"): ?>
    <div class="statCard" style="border-color:#ffd2d2;">
        Вы заблокированы. Напишите обращение - администратор рассмотрит.
    </div>
  <?php endif; ?>

  <div class="panel">
    <form id="feedbackForm" style="display:grid;gap:10px;">
      <label>Телефон</label>
      <input name="phone" required placeholder="+7...">
      <label>Сообщение</label>
      <textarea name="message" rows="6" required></textarea>
      <button class="btn primary" type="submit">Отправить</button>
      <div id="fbMsg" class="muted"></div>
    </form>
  </div>
</section>


<script>
document.getElementById("feedbackForm").addEventListener("submit", async function (e) {
  e.preventDefault();
  var msg = document.getElementById("fbMsg");
  msg.textContent = "Отправка…";
  var body = new URLSearchParams(new FormData(this));
  var res = await fetch("/api/add_feedback.php", {
    method: "POST",
    body: body
  });
  var data = await res.json();
  if (data.ok) {
    msg.textContent = "Обращение отправлено";
    this.reset();
  } else {
    msg.textContent = "Ошибка " + (data.error || "unknown");
  }
});
</script>

</body>
</html>
