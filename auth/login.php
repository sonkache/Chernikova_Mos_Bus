<?php
require_once __DIR__ . "/../db.php";
session_start();
$error = "";

function safeFetchUserByEmail($email) {
    try {
        $u = dbQuery(
            "SELECT id, email, password_hash, role, status, name
             FROM users
             WHERE email = :email
             LIMIT 1",
            ["email" => $email]
        )->fetch(PDO::FETCH_ASSOC);

        if ($u) {
            $u["name"] = $u["name"] ?? "";
        }
        return $u;

    } catch (Throwable $e) {
        $u = dbQuery(
            "SELECT id, email, password_hash, role, status
             FROM users
             WHERE email = :email
             LIMIT 1",
            ["email" => $email]
        )->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $u["name"] = "";
        }
        return $u;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass  = trim($_POST["password"] ?? "");
    if ($email === "" || $pass === "") {
        $error = "Заполните email и пароль.";
    } else {
        $user = safeFetchUserByEmail($email);

        if (!$user || !password_verify($pass, $user["password_hash"])) {
            $error = "Неверный email или пароль.";
        } else {
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["email"]   = $user["email"];
            $_SESSION["role"]    = $user["role"] ?? "user";
            $_SESSION["status"]  = $user["status"] ?? "active";
            $_SESSION["name"]    = $user["name"] ?? "";
            if (($_SESSION["status"] ?? "active") === "blocked") {
                header("Location: /feedback.php?blocked=1");
                exit;
            }

            if (($_SESSION["role"] ?? "user") === "admin") {
                header("Location: /admin/index.php");
                exit;
            }
            header("Location: /");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - MosBus</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
    <div class="logo">MosBus</div>
    <nav class="menu">
        <a href="/">Главная</a>
        <a href="/auth/register.php">Регистрация</a>
    </nav>
</header>

<section class="panel-block" style="max-width:520px;margin:0 auto;">
    <h2>Вход</h2>
    <?php if ($error): ?>
        <div class="statCard" style="border-color:#ffd2d2;">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="panel">
        <form method="post" style="display:grid;gap:10px;">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            <label>Пароль</label>
            <input type="password" name="password" required>
            <button class="btn primary" type="submit">Войти</button>
            <div class="muted">
                Нет аккаунта? <a href="/auth/register.php" style="color:#0b5ed7;font-weight:800;">Регистрация</a>
            </div>
        </form>
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
