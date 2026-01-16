<?php
require_once __DIR__ . "/_admin.php";
requireAdmin();
$status = $_GET["status"] ?? "open";
if (!in_array($status, ["open", "closed"], true)) $status = "open";

$rows = dbQuery("
  SELECT
    f.id, f.user_id, f.contact_phone, f.contact_email,
    f.message, f.status, f.admin_answer, f.created_at,
    u.email AS user_email, u.name
  FROM feedback f
  LEFT JOIN users u ON u.id = f.user_id
  WHERE f.status = :st
  ORDER BY f.created_at DESC
  LIMIT 200",
  ["st" => $status]
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Обращения - Админка</title>
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
  <h2>Обращения</h2>
  <div class="panel">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="btn" href="/admin/feedback.php?status=open">Открытые</a>
      <a class="btn" href="/admin/feedback.php?status=closed">Закрытые</a>
    </div>
    <hr>
    <?php if (!$rows): ?>
      <div class="muted">Пусто</div>
    <?php endif; ?>

    <?php foreach ($rows as $f): ?>
      <?php
        $emailShow = trim((string)($f["user_email"] ?? ""));
        if ($emailShow === "") $emailShow = trim((string)($f["contact_email"] ?? ""));

        $nameShow = trim((string)($f["name"] ?? ""));
        $who = $nameShow !== "" ? $nameShow : "Без аккаунта";

        $phoneShow = trim((string)($f["contact_phone"] ?? ""));
      ?>

      <div class="card">
        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
          <div>
            <b>#<?= (int)$f["id"] ?></b>
            • <?= htmlspecialchars($emailShow ?: "email не указан") ?>
            • <?= htmlspecialchars($who) ?>
          </div>
          <div class="muted" style="font-size:12px;">
            <?= htmlspecialchars($f["created_at"]) ?>
            • статус: <b><?= htmlspecialchars($f["status"]) ?></b>
          </div>
        </div>

        <div class="muted" style="font-size:12px;margin-top:4px;">
          <?php if ($phoneShow !== ""): ?>
            Телефон: <b><?= htmlspecialchars($phoneShow) ?></b>
          <?php else: ?>
            <span>Телефон: <b>не указан</b></span>
          <?php endif; ?>

          <?php if (!empty($f["user_id"])): ?>
            • user_id: <b><?= (int)$f["user_id"] ?></b>
          <?php endif; ?>
        </div>
        <hr>
        <div><?= nl2br(htmlspecialchars($f["message"])) ?></div>
        <hr>

        <form method="post" action="/admin/feedback_answer.php">
          <input type="hidden" name="id" value="<?= (int)$f["id"] ?>">

          <label>Ответ администратора</label>
          <textarea name="answer" rows="3"><?= htmlspecialchars($f["admin_answer"] ?? "") ?></textarea>

          <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
            <button class="btn" type="submit" name="action" value="save">Сохранить</button>
            <button class="btn" type="submit" name="action" value="close">Сохранить и закрыть</button>

            <?php if (($f["status"] ?? "open") === "closed"): ?>
              <a class="btn" href="/admin/feedback_reopen.php?id=<?= (int)$f["id"] ?>">Открыть снова</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</section>

</body>
</html>
