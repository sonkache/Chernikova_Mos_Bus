<?php
require_once __DIR__ . "/../db.php";
header("Content-Type: application/json; charset=utf-8");

$stop_id = isset($_GET["stop_id"]) ? (int)$_GET["stop_id"] : 0;
if ($stop_id <= 0) {
  echo json_encode(["ok" => false, "error" => "stop_id is required"]);
  exit;
}

$stmt = $pdo->prepare(
  "SELECT route_name FROM stop_routes WHERE stop_id = :stop ORDER BY route_name"
);
$stmt->execute(["stop" => $stop_id]);
$routes = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(
  ["ok" => true, "stop_id" => $stop_id, "routes" => $routes],
  JSON_UNESCAPED_UNICODE
);
