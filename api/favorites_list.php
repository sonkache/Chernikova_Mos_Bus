<?php
require_once __DIR__ . "/../db.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["ok" => false, "error" => "auth_required"], JSON_UNESCAPED_UNICODE);
  exit;
}
$user_id = (int)$_SESSION["user_id"];
$stop_id = (int)($_POST["stop_id"] ?? 0);
$route_name = trim($_POST["route_name"] ?? "");
if ($stop_id <= 0 || $route_name === "") {
  echo json_encode(["ok" => false, "error" => "bad_params"], JSON_UNESCAPED_UNICODE);
  exit;
}
try {
  $exists = dbQuery(
    "SELECT id FROM favorites WHERE user_id = :u AND stop_id = :s AND route_name = :r LIMIT 1",
    ["u" => $user_id, "s" => $stop_id, "r" => $route_name]
  )->fetchColumn();
  if ($exists) {
    dbQuery(
      "DELETE FROM favorites WHERE id = :id",
      ["id" => $exists]
    );
    echo json_encode(["ok" => true, "is_favorite" => false], JSON_UNESCAPED_UNICODE);
    exit;
  }
  dbQuery(
    "INSERT INTO favorites (user_id, stop_id, route_name) VALUES (:u, :s, :r)",
    ["u" => $user_id, "s" => $stop_id, "r" => $route_name]
  );
  echo json_encode(["ok" => true, "is_favorite" => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
