<?php
require_once __DIR__ . "/_admin.php";
requireAdmin();

$q = trim($_GET["q"] ?? "");
$params = [];
$sql = "SELECT id, email, name, role, status, rating_count, created_at FROM users";

if ($q !== "") {
  $sql .= " WHERE email LIKE :q OR name LIKE :q2";
  $params["q"] = "%$q%";
  $params["q2"] = "%$q%";
}

$sql .= " ORDER BY created_at DESC LIMIT 200";
$rows = dbQuery($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Пользователи - Админка</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">🛠 Админка</div>
  <nav class="menu">
    <a href="/admin/index.php">Главная</a>
    <a href="/admin/users.php">Пользователи</a>
    <a href="/admin/feedback.php">Обращения</a>
    <a href="/admin/suspicious.php">Подозрительные</a>
    <a href="/auth/logout.php">Выход</a>
  </nav>
</header>

<section class="panel-block">
  <h2>Пользователи</h2>

  <div class="panel">
    <form method="get" style="display:flex;gap:8px;">
      <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск (email/имя)" style="flex:1;">
      <button class="btn" type="submit">Найти</button>
    </form>
    <hr>

    <table class="tbl">
      <tr>
        <th>ID</th><th>Email</th><th>Имя</th><th>Роль</th><th>Статус</th><th>Оценок</th><th></th>
      </tr>
      <?php foreach ($rows as $u): ?>
        <tr>
          <td><?= (int)$u["id"] ?></td>
          <td><?= htmlspecialchars($u["email"]) ?></td>
          <td><?= htmlspecialchars($u["name"]) ?></td>
          <td><?= htmlspecialchars($u["role"]) ?></td>
          <td><b><?= htmlspecialchars($u["status"]) ?></b></td>
          <td><?= (int)$u["rating_count"] ?></td>
          <td style="text-align:right;">
            <?php if ($u["role"] === "admin"): ?>
              <span class="muted">админ</span>
            <?php else: ?>
              <?php if ($u["status"] === "active"): ?>
                <a class="btn" href="/admin/user_toggle.php?id=<?= (int)$u["id"] ?>&to=blocked">Заблокировать</a>
              <?php else: ?>
                <a class="btn" href="/admin/user_toggle.php?id=<?= (int)$u["id"] ?>&to=active">Разблокировать</a>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>

  </div>
</section>

</body>
</html>
