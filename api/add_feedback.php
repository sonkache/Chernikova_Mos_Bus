<?php
require_once __DIR__ . "/../db.php";
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["ok" => false, "error" => "auth_required"], JSON_UNESCAPED_UNICODE);
  exit;
}

$user_id = (int)$_SESSION["user_id"];

$user = dbQuery(
  "SELECT status FROM users WHERE id = :id LIMIT 1",
  ["id" => $user_id]
)->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["status"] !== "active") {
  echo json_encode(["ok" => false, "error" => "blocked"], JSON_UNESCAPED_UNICODE);
  exit;
}

$phone = trim($_POST["phone"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($phone === "" || mb_strlen($message) < 5) {
  echo json_encode(["ok" => false, "error" => "bad_params"], JSON_UNESCAPED_UNICODE);
  exit;
}

dbQuery(
  "INSERT INTO feedback (user_id, contact_phone, message, status)
   VALUES (:u, :p, :m, 'open')",
  ["u" => $user_id, "p" => $phone, "m" => $message]
);

echo json_encode(["ok" => true], JSON_UNESCAPED_UNICODE);
