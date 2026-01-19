<?php
date_default_timezone_set("Europe/Moscow");

$DB_HOST = "sql303.infinityfree.com";
$DB_USER = "if0_40660086";
$DB_PASS = "Dunsy28bn3udb9";
$DB_NAME = "if0_40660086_transport";

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    $pdo->exec("SET time_zone = '+03:00'");

} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>