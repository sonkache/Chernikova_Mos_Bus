<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');
$rows = dbQuery("
  SELECT id, name, latitude, longitude
  FROM stops
  WHERE latitude IS NOT NULL
    AND longitude IS NOT NULL
")->fetchAll();
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
