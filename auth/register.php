<?php
require_once __DIR__ . "/../db.php";
session_start();
$err = "";
$ok = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $name  = trim($_POST["name"] ?? "");
  $pass  = (string)($_POST["password"] ?? "");
  $pass2 = (string)($_POST["password2"] ?? "");

  if ($email === "" || $name === "" || $pass === "" || $pass2 === "") {
    $err = "Заполните все поля.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = "Неверный email.";
  } elseif (mb_strlen($name) < 2) {
    $err = "Имя слишком короткое.";
  } elseif (strlen($pass) < 6) {
    $err = "Пароль минимум 6 символов.";
  } elseif ($pass !== $pass2) {
    $err = "Пароли не совпадают.";
  } else {
    try {
      $st = dbQuery(
        "SELECT id FROM users WHERE email = :email LIMIT 1",
        ["email" => $email]
      );
      if ($st->fetchColumn()) {
        $err = "Этот email уже зарегистрирован";
      } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        dbQuery(
          "INSERT INTO users (email, name, password_hash, role, status)
           VALUES (:email, :name, :hash, 'user', 'active')",
          ["email" => $email, "name" => $name, "hash" => $hash]
        );
        $ok = "Регистрация успешна. Теперь войдите.";
      }
    } catch (Throwable $e) {
      $err = "Ошибка " . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Регистрация - MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">MosBus</div>
  <nav class="menu">
    <a href="/">Главная</a>
    <a href="/auth/login.php">Вход</a>
  </nav>
</header>

<section class="panel-block">
  <h2>Регистрация</h2>

  <div class="panel">
    <?php if ($err): ?>
      <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <?php if ($ok): ?>
      <div class="alert alert-ok"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Email</label>
      <input name="email" type="email" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">

      <label>Имя</label>
      <input name="name" required value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">

      <label>Пароль</label>
      <input name="password" type="password" required>

      <label>Повторите пароль</label>
      <input name="password2" type="password" required>

      <button class="btn primary" type="submit">Зарегистрироваться</button>
    </form>

    <div class="muted" style="margin-top:12px;">
      Уже есть аккаунт? <a href="/auth/login.php">Войти</a>
    </div>
  </div>
</section>

</body>
</html>
