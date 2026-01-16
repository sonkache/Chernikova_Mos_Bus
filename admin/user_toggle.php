<?php
require_once __DIR__ . "/_admin.php";
requireAdmin();

$id = (int)($_GET["id"] ?? 0);
$to = $_GET["to"] ?? "";

if ($id <= 0 || !in_array($to, ["active", "blocked"], true)) {
  header("Location: /admin/users.php");
  exit;
}

$u = dbQuery(
  "SELECT id, role FROM users WHERE id = :id LIMIT 1",
  ["id" => $id]
)->fetch(PDO::FETCH_ASSOC);

if (!$u || $u["role"] === "admin") {
  header("Location: /admin/users.php");
  exit;
}

dbQuery(
  "UPDATE users SET status = :s WHERE id = :id",
  ["s" => $to, "id" => $id]
);

header("Location: /admin/users.php");
exit;
