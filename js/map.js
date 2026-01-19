ymaps.ready(init);

let map;
let objectManager;
let activeStopId = null;

function init() {
  map = new ymaps.Map("map", {
    center: [55.7558, 37.6176],
    zoom: 11,
    controls: ["zoomControl"]
  });

  objectManager = new ymaps.ObjectManager({
    clusterize: false
  });
  map.geoObjects.add(objectManager);
  loadStops();
}

function loadStops() {

  fetch("/api/get_stops.php")
    .then(r => r.json())
    .then(stops => {
      const features = stops.map(s => ({
        type: "Feature",
        id: parseInt(s.id, 10),
        geometry: {
          type: "Point",
          coordinates: [
            parseFloat(s.latitude),
            parseFloat(s.longitude)
          ]
        },

        properties: {
          name: s.name,
          balloonContent: `
            <b>${escapeHtml(s.name)}</b><br><br>
            <button id="btnStop_${s.id}" style="width:100%;padding:8px;">
              📍 Показать маршруты
            </button>
          `
        },

        options: {
          preset: "islands#blueIcon"
        }

      }));

      objectManager.add({
        type: "FeatureCollection",
        features
      });

      objectManager.objects.events.add("balloonopen", e => {
        const id = e.get("objectId");
        const obj = objectManager.objects.getById(id);
        const btn = document.getElementById(`btnStop_${id}`);
        if (!btn) return;
        const [lat, lon] = obj.geometry.coordinates;
        btn.onclick = ev => {
          ev.preventDefault();
          window.focusStop(id, lat, lon, obj.properties.name);
        };

      });

      objectManager.objects.events.add("click", e => {
        const id = e.get("objectId");
        const obj = objectManager.objects.getById(id);
        const [lat, lon] = obj.geometry.coordinates;
        window.focusStop(id, lat, lon, obj.properties.name);

      });

    });
}

window.focusStop = function(id, lat, lon, name) {
  if (!map || isNaN(lat) || isNaN(lon)) return;
  if (activeStopId !== null) {
    objectManager.objects.setObjectOptions(activeStopId, {
      preset: "islands#blueIcon"
    });
  }
  objectManager.objects.setObjectOptions(id, {
    preset: "islands#redIcon"
  });
  activeStopId = id;
  map.panTo([lat, lon], { duration: 400 });
  map.setZoom(16, { duration: 300 });
  objectManager.objects.balloon.open(id);
  if (window.onStopClick) {
    setTimeout(() => {
      window.onStopClick(id, name);
    }, 300);
  }
};

function escapeHtml(s) {
  return String(s ?? "")
    .replaceAll("&","&amp;")
    .replaceAll("<","&lt;")
    .replaceAll(">","&gt;");
}

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const stopId = params.get("stop_id");
  const highlight = params.get("highlight");
  if (!stopId || highlight !== "1") return;
  const waitObjects = setInterval(() => {
    if (!objectManager) return;
    const id = Number(stopId);
    const obj = objectManager.objects.getById(id);
    if (!obj) return;
    clearInterval(waitObjects);
    const [lat, lon] = obj.geometry.coordinates;
    window.focusStop(id, lat, lon, obj.properties.name);
  }, 300);

});
