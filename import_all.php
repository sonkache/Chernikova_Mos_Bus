<?php
require_once __DIR__ . "/db.php";

$file = __DIR__ . "/uploads/stops_utf8.csv";
if (!file_exists($file)) die("CSV файл не найден: uploads/stops_utf8.csv");

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("В db.php должен быть PDO в переменной \$pdo");
}
$fh = fopen($file, "r");
if (!$fh) die(" Не удалось открыть CSV");

$firstLine = fgets($fh);
rewind($fh);
$delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
echo "Разделитель: <b>$delimiter</b><br><br>";

$header = fgetcsv($fh, 0, $delimiter);
if (!$header) die("Не удалось прочитать заголовки CSV");

foreach ($header as &$h) {
    $h = preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
}
$col_id     = array_search("ID", $header, true);
$col_name   = array_search("Name", $header, true);
$col_lon    = array_search("Longitude_WGS84", $header, true);
$col_lat    = array_search("Latitude_WGS84", $header, true);
$col_dist   = array_search("District", $header, true);
$col_routes = array_search("RouteNumbers", $header, true);

if ($col_id === false || $col_name === false || $col_lat === false || $col_lon === false) {
    die("Не найдены обязательные колонки ID/Name/Latitude_WGS84/Longitude_WGS84");
}
try {
    $pdo->exec("DELETE FROM favorites");
    $pdo->exec("DELETE FROM ratings");
    $pdo->exec("DELETE FROM stop_routes");
    $pdo->exec("DELETE FROM stops");
    echo "Таблицы очищены<br>";
} catch (PDOException $e) {
    die("Ошибка при очистке таблиц: " . $e->getMessage());
}

$stStop = $pdo->prepare("
    INSERT INTO stops (id, name, district, latitude, longitude)
    VALUES (:id, :name, :district, :lat, :lon)
");
$batch = [];
$batchSize = 500;

function normalizeRouteName(string $r): string
{
    $r = trim($r);
    if ($r === '') return '';
    $r = preg_replace('/^А/u', '', $r);
    return trim($r);
}

function flushBatch(PDO $pdo, array &$batch): void
{
    if (empty($batch)) return;

    $ph = [];
    $params = [];
    foreach ($batch as $item) {
        $ph[] = "(?, ?)";
        $params[] = $item[0];
        $params[] = $item[1];
    }

    $sql = "INSERT IGNORE INTO stop_routes (stop_id, route_name) VALUES " . implode(",", $ph);
    $pdo->prepare($sql)->execute($params);
    $batch = [];
}

$stopsInserted = 0;
$linksApprox = 0;
while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
    $id = trim($row[$col_id] ?? '');
    if ($id === '') continue;

    $name = trim($row[$col_name] ?? '');
    $lat  = (float)str_replace(',', '.', trim($row[$col_lat] ?? '0'));
    $lon  = (float)str_replace(',', '.', trim($row[$col_lon] ?? '0'));
    $dist = ($col_dist !== false) ? trim($row[$col_dist] ?? '') : '';

    if ($name === '' || $lat == 0 || $lon == 0) continue;

    try {
        $stStop->execute([
            "id" => (int)$id,
            "name" => $name,
            "district" => $dist,
            "lat" => $lat,
            "lon" => $lon
        ]);
        $stopsInserted++;
    } catch (PDOException $e) {
        continue;
    }
    if ($col_routes !== false) {
        $rawRoutes = trim($row[$col_routes] ?? '');
        if ($rawRoutes !== '') {
            $parts = preg_split('/\s*[;,]\s*|\s+/u', $rawRoutes);

            foreach ($parts as $p) {
                $r = normalizeRouteName($p);
                if ($r === '' || mb_strlen($r) > 20) continue;

                $batch[] = [(int)$id, $r];
                $linksApprox++;

                if (count($batch) >= $batchSize) {
                    flushBatch($pdo, $batch);
                }
            }
        }
    }
}

flushBatch($pdo, $batch);
fclose($fh);

echo "<br>ИМПОРТ ЗАВЕРШЁН<br>";
echo "Остановок добавлено: <b>$stopsInserted</b><br>";
echo "Связей stop-route обработано: <b>$linksApprox</b><br>";
echo "<br><a href='/'>Вернуться на сайт</a>";
