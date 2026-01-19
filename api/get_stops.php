<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');
try {
$rows = dbQuery("
  SELECT id, name, district, latitude, longitude
  FROM stops
  WHERE latitude IS NOT NULL AND longitude IS NOT NULL
  AND latitude <> 0 AND longitude <> 0
      ORDER BY id
")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
