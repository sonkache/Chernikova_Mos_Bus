<?php
require_once __DIR__ . "/../db.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["user_id"])) {
  echo json_encode(["ok"=>false, "error"=>"auth_required"], JSON_UNESCAPED_UNICODE);
  exit;
}

$user_id = (int)$_SESSION["user_id"];
$stop_id = (int)($_GET["stop_id"] ?? 0);

if ($stop_id <= 0) {
  echo json_encode(["ok"=>false, "error"=>"bad_params"], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $routes = dbQuery(
    "SELECT route_name FROM favorites WHERE user_id=:u AND stop_id=:s ORDER BY route_name",
    ["u"=>$user_id, "s"=>$stop_id]
  )->fetchAll(PDO::FETCH_COLUMN);

  echo json_encode(["ok"=>true, "routes"=>$routes], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
