<?php
require_once __DIR__ . "/../db.php";
header('Content-Type: application/json; charset=utf-8');

$stop_id = (int)($_GET["stop_id"] ?? 0);
$route   = trim($_GET["route"] ?? "");
$weekday = (int)($_GET["weekday"] ?? 0);

if ($stop_id <= 0 || $route === "" || $weekday < 1 || $weekday > 7) {
  echo json_encode(["ok" => false, "error" => "bad_params"], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $rows = dbQuery("
    SELECT
      FLOOR(HOUR(ride_time) * 2 + MINUTE(ride_time) / 30) AS slot_idx,
      COUNT(*) AS votes,
      ROUND(AVG(load_level), 2) AS avg_load
    FROM ratings
    WHERE stop_id = :stop_id
      AND route_name = :route
      AND weekday = :weekday
    GROUP BY slot_idx
    ORDER BY slot_idx
  ", [
    "stop_id" => $stop_id,
    "route" => $route,
    "weekday" => $weekday
  ])->fetchAll(PDO::FETCH_ASSOC);
  $slots = [];

  foreach ($rows as $r) {
    $slotIdx = (int)$r["slot_idx"];
    $startSec = $slotIdx * 1800;
    $endSec = $startSec + 1800;

    $avg = (float)$r["avg_load"];
    $color = "green";
    if ($avg >= 4) {
      $color = "red";
    } elseif ($avg >= 2.5) {
      $color = "yellow";
    }

    $slots[] = [
      "slot_start" => gmdate("H:i", $startSec),
      "slot_end"   => gmdate("H:i", $endSec),
      "votes"      => (int)$r["votes"],
      "avg_load"   => $avg,
      "color"      => $color
    ];
  }

  echo json_encode(["ok" => true, "slots" => $slots], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
