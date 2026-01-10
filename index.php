<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
</head>

<body>
<h1 style="text-align:center;margin-top:20px;">
  MosBus - загруженность общественного транспорта
</h1>

<section style="max-width:600px;margin:20px auto;">
  <input type="text"
         id="stopSearch"
         placeholder="Введите название остановки"
         style="width:100%;padding:10px;">
  <div id="searchResults"></div>
</section>

<div id="map" class="map"></div>
<script src="https://api-maps.yandex.ru/2.1/?apikey=07131a5d-203a-4059-89aa-fb4a2eceefdb&lang=ru_RU"></script>
<script src="/js/map.js"></script>
</body>
</html>
