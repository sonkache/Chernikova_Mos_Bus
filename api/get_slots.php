<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

function out($arr) {
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

$stop_id = isset($_GET['stop_id']) ? (int)$_GET['stop_id'] : 0;
$route   = trim($_GET['route'] ?? '');
$weekday = isset($_GET['weekday']) ? (int)$_GET['weekday'] : 0;

if ($stop_id <= 0 || $route === '' || $weekday < 1 || $weekday > 7) {
  out(["ok"=>false, "error"=>"bad_params"]);
}

$sql = "
  SELECT
    slot_idx,
    COUNT(*) AS votes,
    ROUND(AVG(load_level), 2) AS avg_load,
    MIN(ride_time) AS any_time
  FROM (
    SELECT
      ( (HOUR(ride_time)*60 + MINUTE(ride_time)) / 30 ) AS slot_idx,
      load_level,
      ride_time
    FROM ratings
    WHERE stop_id = :sid
      AND route_name = :route
      AND weekday = :wd
      AND ride_time IS NOT NULL
  ) t
  GROUP BY slot_idx
  ORDER BY slot_idx
";

try {
  if (function_exists('dbQuery')) {
    $stmt = dbQuery($sql, ["sid"=>$stop_id, "route"=>$route, "wd"=>$weekday]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    if (!isset($pdo) || !($pdo instanceof PDO)) out(["ok"=>false,"error"=>"no_pdo"]);
    $st = $pdo->prepare($sql);
    $st->execute(["sid"=>$stop_id, "route"=>$route, "wd"=>$weekday]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Throwable $e) {
  out(["ok"=>false, "error"=>$e->getMessage()]);
}

$slots = [];

foreach ($rows as $r) {
  $idx = (int)$r["slot_idx"];
  $votes = (int)$r["votes"];
  $avg = (float)$r["avg_load"];

  $startMin = $idx * 30;
  $endMin   = ($startMin + 30) % (24*60);

  $slot_start = sprintf("%02d:%02d", intdiv($startMin, 60), $startMin % 60);
  $slot_end   = sprintf("%02d:%02d", intdiv($endMin, 60), $endMin % 60);

  $color = "green";
  if ($avg >= 4) $color = "red";
  else if ($avg >= 2) $color = "yellow";

  $slots[] = [
    "slot_start" => $slot_start,
    "slot_end"   => $slot_end,
    "votes"      => $votes,
    "avg_load"   => $avg,
    "color"      => $color
  ];
}

out([
  "ok" => true,
  "stop_id" => $stop_id,
  "route" => $route,
  "weekday" => $weekday,
  "slots" => $slots
]);
