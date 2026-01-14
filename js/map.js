ymaps.ready(start);

let map;

function start() {
  map = new ymaps.Map("map", {
    center: [55.7558, 37.6176],
    zoom: 11,
    controls: ["zoomControl"]
  });

  fetch("/api/get_stops.php")
    .then(function (r) {
      return r.json();
    })
    .then(function (stops) {
      stops.forEach(function (stop) {
        const placemark = new ymaps.Placemark(
          [stop.latitude, stop.longitude],
          { hintContent: stop.name }
        );
        placemark.events.add("click", function () {
            if (window.onStopClick) {
                window.onStopClick(stop.id, stop.name);
            }
        });
        map.geoObjects.add(placemark);
      });
    });
    .catch(function (err) {
      console.error("Ошибка загрузки остановок", err);
    });
}
