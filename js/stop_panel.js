let currentStopId = null;
let currentRoute = null;
async function loadFavorites(stopId) {
  try {
    const res = await fetch("/api/favorites_list.php?stop_id=" + stopId);
    const data = await res.json();
    if (!data.ok) return [];
    return data.routes || [];
  } catch (e) {
    return [];
  }
}
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
  const favoriteRoutes = await loadFavorites(stopId);
  let html = "";
  data.routes.forEach(function (r) {
    const isFav = favoriteRoutes.includes(r);
    html += '<button class="routeBtn" data-route="' + r + '">' + r + "</button>" + '<button class="favBtn ' +
    active + '" data-route="' + r + '" title="Избранное">★</button>' + "</div>";

  });
  routesBox.innerHTML = html;
  document.querySelectorAll(".routeBtn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      onRouteClick(this.dataset.route);
    });
  });
    document.querySelectorAll(".favBtn").forEach(function (btn) {
      btn.addEventListener("click", async function (e) {
        e.stopPropagation();
        const route = btn.dataset.route;
        const res = await fetch("/api/toggle_favorite.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            stop_id: currentStopId,
            route_name: route
          })
        });
        const data = await res.json();
        if (!data.ok) {
          alert(data.error || "Ошибка добавления в избранное");
          return;
        }
        btn.classList.toggle("active", data.is_favorite);
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
    " / 5" + "</div>" + "<hr>" + renderRatingForm(slotStart);
    bindRatingForm();
}
function renderRatingForm(time) {
  if (!time) time = "08:00";

  return (
    '<form id="rateForm">' + '<input type="hidden" name="stop_id" value="' + currentStopId +
    '">' + '<input type="hidden" name="route_name" value="' +
    currentRoute + '">' + '<input type="hidden" name="weekday" value="1">' +
    '<input type="hidden" name="ride_time" value="' + time +
    '">' + "<label>Загруженность</label>" + '<input type="range" name="load_level" min="0" max="5" value="3">' +
    "<label>Пенсионеры</label>" + '<input type="range" name="pensioners" min="0" max="5" value="0">' +
    "<label>Дети</label>" + '<input type="range" name="children" min="0" max="5" value="0">' +
    "<label>Коляски</label>" + '<input type="range" name="strollers" min="0" max="5" value="0">' +
    '<button class="btn primary" type="submit">Отправить оценку</button>' +
    '<div id="rateMsg" class="muted"></div>' + "</form>"
  );
}

function bindRatingForm() {
  const form = document.getElementById("rateForm");
  const msg = document.getElementById("rateMsg");
  if (!form) return;

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    msg.textContent = "Отправка…";

    const body = new URLSearchParams(new FormData(form));
    const res = await fetch("/api/add_rating.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body
    });
    const data = await res.json();

    if (data.ok) {
      msg.textContent = "Оценка сохранена, спасибо!";
      onRouteClick(currentRoute);
    } else {
      msg.textContent = "Ошибка: " + data.error;
    }
  });