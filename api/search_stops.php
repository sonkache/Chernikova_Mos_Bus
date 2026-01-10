<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');
$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
  echo json_encode([]);
  exit;
}

$items = dbQuery("
  SELECT id, name, latitude, longitude
  FROM stops
  WHERE name LIKE :q
  ORDER BY name LIMIT 20", ['q' => "%$q%"])->fetchAll();
echo json_encode($items, JSON_UNESCAPED_UNICODE);
