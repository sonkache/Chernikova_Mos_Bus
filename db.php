<?php
require_once __DIR__ . '/config.php';

function dbQuery(string $sql, array $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
