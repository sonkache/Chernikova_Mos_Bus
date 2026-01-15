<?php
require_once __DIR__ . "/../db.php";
header('Content-Type: application/json; charset=utf-8');

$stop_id = (int)($_GET["stop_id"] ?? 0);
$route = trim($_GET["route"] ?? "");
$weekday = (int)($_GET["weekday"] ?? 0);
$slot_start = trim($_GET["slot_start"] ?? "");

if (
  $stop_id <= 0 || $route === "" || $weekday < 1 || $weekday > 7 || !preg_match('/^\d{2}:\d{2}$/', $slot_start)
) {
  echo json_encode(["ok" => false, "error" => "bad_params"], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $slot_end = dbQuery(
    "SELECT ADDTIME(:t, '00:30:00')",
    ["t" => $slot_start]
  )->fetchColumn();

  $s = dbQuery("
    SELECT
      COUNT(*) AS votes,
      AVG(load_level) AS avg_load,
      AVG(pensioners) AS avg_pensioners,
      AVG(children) AS avg_children,
      AVG(strollers) AS avg_strollers
    FROM ratings
    WHERE stop_id = :stop_id
      AND route_name = :route
      AND weekday = :weekday
      AND ride_time BETWEEN :ts AND :te
  ", ["stop_id" => $stop_id, "route" => $route, "weekday" => $weekday,
    "ts" => $slot_start, "te" => $slot_end])->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    "ok" => true,
    "slot_end" => substr($slot_end, 0, 5),
    "stats" => [
      "votes" => (int)$s["votes"],
      "avg_load" => round((float)$s["avg_load"], 2),
      "avg_pensioners" => round((float)$s["avg_pensioners"], 2),
      "avg_children" => round((float)$s["avg_children"], 2),
      "avg_strollers" => round((float)$s["avg_strollers"], 2)
    ]
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok" => false, "error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
