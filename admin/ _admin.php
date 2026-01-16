<?php
require_once __DIR__ . "/../db.php";
session_start();

function requireAdmin(): void {
  if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login.php");
    exit;
  }

  $uid = (int)$_SESSION["user_id"];

  $u = dbQuery(
    "SELECT role, status, name FROM users WHERE id = :id LIMIT 1",
    ["id" => $uid]
  )->fetch(PDO::FETCH_ASSOC);

  if (!$u) {
    $_SESSION = [];
    session_destroy();
    header("Location: /auth/login.php");
    exit;
  }

  if (($u["status"] ?? "active") !== "active") {
    http_response_code(403);
    echo "Вы заблокированы";
    exit;
  }

  $role = strtolower(trim((string)($u["role"] ?? "user")));

  $_SESSION["role"] = $role;
  $_SESSION["name"] = $u["name"] ?? ($_SESSION["name"] ?? "");

  if ($role !== "admin") {
    http_response_code(403);
    echo "Доступ только для администратора";
    exit;
  }
}
