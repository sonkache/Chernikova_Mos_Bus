<?php
require_once __DIR__ . "/_admin.php";
requireAdmin();
$id = (int)($_POST["id"] ?? 0);
$answer = trim($_POST["answer"] ?? "");
$action = $_POST["action"] ?? "save";
if ($id <= 0) {
  header("Location: /admin/feedback.php");
  exit;
}
$status = ($action === "close") ? "closed" : "open";

dbQuery(
  "UPDATE feedback SET admin_answer = :a, status = :s WHERE id = :id",
  ["a" => $answer, "s" => $status, "id" => $id]
);

header("Location: /admin/feedback.php?status=" . $status);
exit;
