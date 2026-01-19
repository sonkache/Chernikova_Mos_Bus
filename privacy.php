<?php
require_once __DIR__ . "/config.php";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Согласие на обработку персональных данных - MosBus</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="header">
  <div class="logo">MosBus</div>
  <nav class="menu">
    <a href="/">Главная</a>
  </nav>
</header>

<section class="panel-block" style="max-width:900px;">
  <h2>Согласие на обработку персональных данных</h2>

  <div class="panel">
    <p>
      Настоящим я, действуя добровольно и в своих интересах, даю согласие сервису <b>MosBus</b> на
обработку моих персональных данных на следующих условиях:
    </p>

    <p><b>Персональные данные:</b></p>
    <ul>
      <li>адрес электронной почты</li>
      <li>имя пользователя</li>
      <li>оценки загруженности транспорта</li>
      <li>сообщения, отправленные в поддержку</li>
    </ul>

    <p><b>Цели обработки:</b></p>
    <ul>
      <li>работа сервиса и отображение статистики</li>
      <li>обратная связь и поддержка пользователей</li>
      <li>предотвращение злоупотреблений</li>
    </ul>

    <p>
      Обработка осуществляется с использованием средств автоматизации
      и без передачи данных третьим лицам, за исключением случаев,
      предусмотренных законодательством РФ.
    </p>

    <p>
      Согласие действует бессрочно и может быть отозвано путём обращения
      в службу поддержки.
    </p>

    <p class="muted" style="margin-top:16px;">
      Дата последнего обновления: <?= date("d.m.Y") ?>
    </p>
  </div>
</section>

<footer class="footer">
  <p>
    MosBus - сервис на основе
    <a href="https://data.mos.ru/opendata/752?pageSize=10&pageIndex=0&isRecommendationData=false&isDynamic=false&version=8&release=82"
       target="_blank"
       rel="noopener noreferrer">
      открытых данных
    </a>
    Правительства Москвы.
	<p class="muted">
    Пользуясь сайтом, вы соглашаетесь с
    <a href="/privacy.php">обработкой персональных данных</a>.
  </p>
  </p>
</footer>

</body>
</html>
