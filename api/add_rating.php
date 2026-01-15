<?php
require_once __DIR__ . "/../db.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["ok" => false, "error" => "auth_required"], JSON_UNESCAPED_UNICODE);
  exit;
}
$user_id = (int)$_SESSION['user_id'];
$u = dbQuery(
  "SELECT status FROM users WHERE id = :id LIMIT 1",
  ["id" => $user_id]
)->fetch(PDO::FETCH_ASSOC);
if (!$u) {
  echo json_encode(["ok" => false, "error" => "user_not_found"], JSON_UNESCAPED_UNICODE);
  exit;
}
if (($u["status"] ?? "active") !== "active") {
  echo json_encode(["ok" => false, "error" => "blocked"], JSON_UNESCAPED_UNICODE);
  exit;
}

$stop_id = (int)($_POST["stop_id"] ?? 0);
$route_name = trim($_POST["route_name"] ?? "");
$weekday = (int)($_POST["weekday"] ?? 0);
$ride_time = trim($_POST["ride_time"] ?? "");
$load_level = (int)($_POST["load_level"] ?? -1);
$pensioners = (int)($_POST["pensioners"] ?? 0);
$children = (int)($_POST["children"] ?? 0);
$strollers = (int)($_POST["strollers"] ?? 0);

if ($stop_id <= 0 || $route_name === "") {
  echo json_encode(["ok" => false, "error" => "bad_params"], JSON_UNESCAPED_UNICODE);
  exit;
}
if ($weekday < 1 || $weekday > 7) {
  echo json_encode(["ok" => false, "error" => "bad_weekday"], JSON_UNESCAPED_UNICODE);
  exit;
}
if (!preg_match('/^\d{2}:\d{2}$/', $ride_time)) {
  echo json_encode(["ok" => false, "error" => "bad_time_format"], JSON_UNESCAPED_UNICODE);
  exit;
}

$ride_time_sql = $ride_time . ":00";
$clamp = function ($v, $min, $max) {
  return max($min, min($max, $v));
};

$load_level = $clamp($load_level, 0, 5);
$pensioners = $clamp($pensioners, 0, 5);
$children = $clamp($children, 0, 5);
$strollers = $clamp($strollers, 0, 5);
try {
  dbQuery("
    INSERT INTO ratings
      (user_id, stop_id, route_name, weekday, ride_time, load_level, pensioners, children, strollers)
    VALUES
      (:u, :s, :r, :w, :t, :l, :p, :c, :st)
  ", [
    "u" => $user_id,
    "s" => $stop_id,
    "r" => $route_name,
    "w" => $weekday,
    "t" => $ride_time_sql,
    "l" => $load_level,
    "p" => $pensioners,
    "c" => $children,
    "st" => $strollers
  ]);

  dbQuery(
    "UPDATE users SET rating_count = rating_count + 1 WHERE id = :id",
    ["id" => $user_id]
  );

  echo json_encode(["ok" => true], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  echo json_encode(["ok" => false, "error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
