<?php
require_once __DIR__ . "/db.php";
session_start();
$isAuth = isset($_SESSION["user_id"]);
$status = $isAuth ? ($_SESSION["status"] ?? "active") : "guest";
$userId = $isAuth ? (int)$_SESSION["user_id"] : null;
$emailAuto = $isAuth ? ($_SESSION["email"] ?? "") : "";

$ok = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? $emailAuto);
    $msg   = trim($_POST["message"] ?? "");

    if ($phone === "") $error = "Телефон обязателен.";
    elseif ($email === "") $error = "Email обязателен.";
    elseif ($msg === "") $error = "Сообщение не должно быть пустым.";

    if ($error === "") {
        $full = "Имя: {$name}\nEmail: {$email}\nТелефон: {$phone}\n\nСообщение:\n{$msg}";

        try {
            dbQuery(
                "INSERT INTO feedback (user_id, message, status) VALUES (:uid, :m, 'open', NOW())",
                ["uid" => $userId, "m" => $full]
            );
            $ok = true;
        } catch (Throwable $e) {
            $ok = false;
            $error = $e->getMessage();
        }
    } else {
        $ok = false;
    }
}
$myMessages = [];
if ($isAuth) {
  $myMessages = dbQuery("
    SELECT message, admin_answer, status, created_at
    FROM feedback
    WHERE user_id = :u
    ORDER BY created_at DESC
  ", ["u" => $userId])->fetchAll(PDO::FETCH_ASSOC);

}

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

  <?php if ($ok === true): ?>
      <div class="statCard">✅ Обращение отправлено.</div>
    <?php elseif ($ok === false): ?>
      <div class="statCard" style="border-color:#ffd2d2;">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

  <div class="panel">
    <form method="post" style="display:grid;gap:10px;">
        <label>Имя (необязательно)</label>
              <input type="text" name="name"
                value="<?= htmlspecialchars($_POST["name"] ?? ($isAuth ? ($_SESSION["name"] ?? "") : "")) ?>">

         <label>Email</label>
              <input type="email" name="email" required
                value="<?= htmlspecialchars($_POST["email"] ?? $emailAuto) ?>">

         <label>Телефон (обязательно)</label>
              <input type="text" name="phone" required placeholder="+7 ..."
                value="<?= htmlspecialchars($_POST["phone"] ?? "") ?>">

         <label>Сообщение</label>
        <textarea name="message" rows="6" required><?= htmlspecialchars($_POST["message"] ?? "") ?></textarea>

      <button class="btn primary" type="submit">Отправить</button>
    </form>
  </div>

 <?php if ($isAuth && $myMessages): ?>
    <h3 style="margin-top:30px;">Мои обращения</h3>
    <?php foreach ($myMessages as $m): ?>
      <div class="statCard" style="margin-bottom:15px;">
        <div class="smallMuted">
          <?= htmlspecialchars($m["created_at"]) ?>
        </div>
        <b>Вы:</b>
        <div style="margin-bottom:10px;">
          <?= nl2br(htmlspecialchars($m["message"])) ?>
        </div>
        <b>Поддержка:</b>
        <?php if ($m["admin_answer"]): ?>
          <div style="background:#f3f8ff;padding:10px;border-radius:6px;">
            <?= nl2br(htmlspecialchars($m["admin_answer"])) ?>
          </div>
        <?php else: ?>
          <div class="muted">
            ⏳ Ожидает ответа
          </div>
        <?php endif; ?>
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
 </footer>
</body>
</html>
