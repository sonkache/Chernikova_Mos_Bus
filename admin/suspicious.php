<?php
require_once __DIR__ . "/_admin.php";
requireAdmin();

$day = dbQuery(
  "
  SELECT u.id, u.email, u.name, u.status, COUNT(r.id) AS cnt
  FROM ratings r
  JOIN users u ON u.id = r.user_id
  WHERE r.created_at >= NOW() - INTERVAL 1 DAY
  GROUP BY u.id
  ORDER BY cnt DESC
  LIMIT 50
  "
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Подозрительные - Админка</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">Админка</div>
  <nav class="menu">
    <a href="/admin/index.php">Главная</a>
    <a href="/admin/users.php">Пользователи</a>
    <a href="/admin/feedback.php">Обращения</a>
    <a href="/admin/suspicious.php">Подозрительные</a>
    <a href="/auth/logout.php">Выход</a>
  </nav>
</header>

<section class="panel-block">
  <h2>Подозрительная активность за последние сутки</h2>
  <div class="panel">

    <table class="tbl">
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Имя</th>
        <th>Статус</th>
        <th>Оценок</th>
        <th></th>
      </tr>

      <?php foreach ($day as $u): ?>
        <tr>
          <td><?= (int)$u["id"] ?></td>
          <td><?= htmlspecialchars($u["email"]) ?></td>
          <td><?= htmlspecialchars($u["name"]) ?></td>
          <td><b><?= htmlspecialchars($u["status"]) ?></b></td>
          <td><b><?= (int)$u["cnt"] ?></b></td>
          <td style="text-align:right;">
            <?php if ($u["status"] === "active"): ?>
              <a class="btn" href="/admin/user_toggle.php?id=<?= (int)$u["id"] ?>&to=blocked">Заблокировать</a>
            <?php else: ?>
              <a class="btn" href="/admin/user_toggle.php?id=<?= (int)$u["id"] ?>&to=active">Разблокировать</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>

    </table>

    <?php if (!$day): ?>
      <div class="muted" style="margin-top:10px;">Активности не обнаружено.</div>
    <?php endif; ?>

  </div>
</section>

</body>
</html>
