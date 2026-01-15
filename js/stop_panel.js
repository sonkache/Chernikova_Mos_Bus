let currentStopId = null;
let currentRoute = null;
window.onStopClick = async function(stopId, stopName) {
  const panel = document.getElementById("stopPanel");
  const title = document.getElementById("stopTitle");
  const routesBox = document.getElementById("routesList");
  const slotsBox = document.getElementById("slotsGrid");
  const statsBox = document.getElementById("statsBox");

  if (!panel || !title || !routesBox) return;
  currentStopId = stopId;
  currentRoute = null;

  title.textContent = stopName;
  routesBox.innerHTML = "<span class='muted'>Загрузка маршрутов...</span>";
  if (slotsBox) slotsBox.innerHTML = "<span class='muted'>Выберите маршрут</span>";
  if (statsBox) statsBox.innerHTML = "<span class='muted'>Выберите интервал</span>";

  const res = await fetch(`/api/get_routes_by_stop.php?stop_id=${stopId}`);
  const data = await res.json();

  if (!data.ok || !data.routes.length) {
    routesBox.innerHTML = "<span class='muted'>Маршрутов нет</span>";
    return;
  }
  let html = "";
  data.routes.forEach(function (r) {
    html += '<button class="routeBtn" data-route="' + r + '">' + r + "</button>";
  });
  routesBox.innerHTML = html;
  document.querySelectorAll(".routeBtn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      onRouteClick(this.dataset.route);
    });
  });
  panel.scrollIntoView({ behavior: "smooth", block: "start" });
};

async function onRouteClick(route) {
  currentRoute = route;

  const slotsBox = document.getElementById("slotsGrid");
  const statsBox = document.getElementById("statsBox");

  if (!slotsBox) return;

  slotsBox.innerHTML = "<span class='muted'>Загрузка интервалов</span>";
  if (statsBox) statsBox.innerHTML = "<span class='muted'>Выберите интервал:</span>";
  const res = await fetch("/api/get_slots.php?stop_id=" + currentStopId + "&route=" + encodeURIComponent(route) + "&weekday=1");
  const data = await res.json();
  if (!data.ok || !data.slots.length) {
    slotsBox.innerHTML = "<span class='muted'>Нет данных</span>";
    return;
  }

  let html = "";
  data.slots.forEach(function (s) {
    html += '<button class="slot" data-start="' + s.slot_start + '">' + s.slot_start + "–" + s.slot_end + " (" + s.votes + ")" + "</button>";
  });
  slotsBox.innerHTML = html;

  document.querySelectorAll(".slot").forEach(function (btn) {
    btn.addEventListener("click", function () {
      onSlotClick(this.dataset.start);
    });
  });
}

async function onSlotClick(slotStart) {
  const statsBox = document.getElementById("statsBox");
  if (!statsBox) return;

  statsBox.innerHTML = "<span class='muted'>Загрузка статистики</span>";

  const res = await fetch(
    "/api/get_slot_stats.php?stop_id=" + currentStopId + "&route=" + encodeURIComponent(currentRoute) + "&weekday=1&slot_start=" + slotStart
  );
  const data = await res.json();

  if (!data.ok) {
    statsBox.innerHTML = "<span class='muted'>Ошибка</span>";
    return;
  }

  const s = data.stats;

  statsBox.innerHTML = "<div>" + "<b>" + currentRoute + "</b><br>" + "Интервал: " + slotStart + "–" +
    data.slot_end + "<br>" + "Оценок: " + s.votes + "<br><br>" + "Средняя загрузка: " + s.avg_load +
    " / 5" + "</div>";
}