<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');
$q = trim($_GET['q'] ?? '');
$q = mb_substr($q, 0, 80);
if ($q === "" || mb_strlen($q) < 2) {
  echo json_encode(["ok"=>true, "stops"=>[]], JSON_UNESCAPED_UNICODE);
  exit;
}
try {
  try {
    $rows = dbQuery("
      SELECT
        s.id, s.name, s.district, s.latitude, s.longitude,
        sr.routes AS routes_text
      FROM stops s
      LEFT JOIN stop_routes sr ON sr.stop_id = s.id
      WHERE s.name LIKE :q OR sr.routes LIKE :q
      ORDER BY
        CASE WHEN s.name LIKE :qStart THEN 0 ELSE 1 END,
        s.name
      LIMIT 30
    ", [
      "q" => "%".$q."%",
      "qStart" => $q."%"
    ])->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["ok"=>true, "stops"=>$rows], JSON_UNESCAPED_UNICODE);
    exit;

  } catch (Throwable $eJoin) {
    $rows = dbQuery("
      SELECT id, name, district, latitude, longitude
      FROM stops
      WHERE name LIKE :q
      ORDER BY
        CASE WHEN name LIKE :qStart THEN 0 ELSE 1 END,
        name
      LIMIT 30
    ", [
      "q" => "%".$q."%",
      "qStart" => $q."%"
    ])->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
      "ok"=>true,
      "stops"=>$rows,
      "note"=>"routes_search_disabled"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
