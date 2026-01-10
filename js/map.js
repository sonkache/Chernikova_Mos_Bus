ymaps.ready(start);

function start() {
  const map = new ymaps.Map("map", {
    center: [55.7558, 37.6176],
    zoom: 11,
    controls: ["zoomControl"]
  });
}
