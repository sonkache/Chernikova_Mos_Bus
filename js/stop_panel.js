window.onStopClick = async function(stopId, stopName) {
  const panel = document.getElementById("stopPanel");
  const title = document.getElementById("stopTitle");
  const routesBox = document.getElementById("routesList");

  if (!panel || !title || !routesBox) return;

  title.textContent = stopName;
  routesBox.innerHTML = "<span class='muted'>Загрузка маршрутов...</span>";

  const res = await fetch(`/api/get_routes_by_stop.php?stop_id=${stopId}`);
  const data = await res.json();

  if (!data.ok || !data.routes.length) {
    routesBox.innerHTML = "<span class='muted'>Маршрутов нет</span>";
    return;
  }
  var html = "";
  data.routes.forEach(function (r) {
    html += '<button class="routeBtn">' + r + "</button>";
  });
  routesBox.innerHTML = html;

  panel.scrollIntoView({ behavior: "smooth", block: "start" });
};
