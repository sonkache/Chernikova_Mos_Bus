<?php
require_once __DIR__ . "/../db.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["ok"=>false, "error"=>"auth_required"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$user = dbQuery(
  "SELECT status FROM users WHERE id = :id",
  ["id"=>$user_id]
)->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['status'] === 'blocked') {
  echo json_encode(["ok"=>false, "error"=>"blocked"]);
  exit;
}

$stop_id = (int)($_POST['stop_id'] ?? 0);
$route   = trim($_POST['route_name'] ?? '');
$weekday = (int)($_POST['weekday'] ?? 0);
$ride_raw = trim($_POST['ride_time'] ?? '');
$load  = (int)($_POST['load_level'] ?? 0);
$pens  = (int)($_POST['pensioners'] ?? 0);
$kids  = (int)($_POST['children'] ?? 0);
$strol = (int)($_POST['strollers'] ?? 0);

if (
  $stop_id <= 0 ||
  $route === '' ||
  $weekday < 1 || $weekday > 7
) {
  echo json_encode(["ok"=>false, "error"=>"bad_params"]);
  exit;
}

function roundTo30($time) {
  if (!preg_match('/^(\d{2}):(\d{2})$/', $time, $m)) {
    return null;
  }
  $h = (int)$m[1];
  $min = (int)$m[2];
  if ($h < 0 || $h > 23 || $min < 0 || $min > 59) {
    return null;
  }
  $min = ($min < 30) ? 0 : 30;
  return sprintf('%02d:%02d', $h, $min);
}

$ride_time = roundTo30($ride_raw);
if ($ride_time === null) {
  echo json_encode(["ok"=>false, "error"=>"bad_time"]);
  exit;
}

try {
  dbQuery("
    INSERT INTO ratings
      (user_id, stop_id, route_name, weekday, ride_time,
       load_level, pensioners, children, strollers, created_at)
    VALUES
      (:user_id, :stop_id, :route, :weekday, :ride_time,
       :load, :pens, :kids, :strol, NOW())
  ", [
    "user_id"   => $user_id,
    "stop_id"   => $stop_id,
    "route"     => $route,
    "weekday"   => $weekday,
    "ride_time" => $ride_time,
    "load"      => $load,
    "pens"      => $pens,
    "kids"      => $kids,
    "strol"     => $strol
  ]);
  echo json_encode([
    "ok" => true,
    "ride_time" => $ride_time
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "ok"=>false,
    "error"=>"db_error"
  ]);
}
