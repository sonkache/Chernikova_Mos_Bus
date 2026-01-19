let currentStopId = null;
let currentRoute = null;
let currentWeekday = new Date().getDay() || 7;

function api(url, opts) {
  return fetch(url, opts).then(r => r.json());
}

function setHTML(id, html) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = html;
}

function weekdayLabel(n) {
  return ["","Пн","Вт","Ср","Чт","Пт","Сб","Вс"][n] || "";
}

function colorClass(avg) {
  if (avg >= 4) return "c-red";
  if (avg >= 2.5) return "c-yellow";
  return "c-green";
}

window.onStopClick = async function(stopId, stopName) {
  currentStopId = stopId;
  currentRoute = null;

  setHTML("stopTitle", stopName);
  setHTML("routesList", "Загрузка маршрутов…");
  setHTML("slotsGrid", "Выберите маршрут");
  setHTML("statsBox", "");
  const data = await api(`/api/get_routes_by_stop.php?stop_id=${stopId}`);
  if (!data.ok) {
    setHTML("routesList", "Ошибка загрузки маршрутов");
    return;
  }
  setHTML("routesList", data.routes.map(r => `
    <div class="route-item">
      <button class="routeBtn" data-route="${r}">${r}</button>
      ${window.IS_AUTH ? `<button class="favBtn" data-route="${r}">★</button>` : ""}
    </div>
  `).join(""));

  document.querySelectorAll(".routeBtn").forEach(b => {
    b.onclick = () => onRouteClick(b.dataset.route);
  });

  document.querySelectorAll(".favBtn").forEach(b => {
    b.onclick = e => {
      e.stopPropagation();
      addToFavorites(b.dataset.route, b);
    };
  });

};

async function onRouteClick(route) {

  currentRoute = route;
  setHTML("slotsGrid", "Загрузка интервалов…");
  setHTML("statsBox", renderRateBlock());
  bindRateBlock();

  const data = await api(`/api/get_slots.php?stop_id=${currentStopId}&route=${encodeURIComponent(route)}&weekday=${currentWeekday}`);

  if (!data.ok || !data.slots.length) {
    setHTML("slotsGrid", `<div class="muted">Пока нет оценок</div>`);
    return;
  }

  setHTML("slotsGrid", data.slots.map(s => `
    <button class="slot ${colorClass(s.avg_load)}" data-start="${s.slot_start}">
      <div class="slotT">${s.slot_start}–${s.slot_end}</div>
      <div class="slotS">${s.votes} оцен.</div>
    </button>
  `).join(""));

  document.querySelectorAll(".slot").forEach(b => {
    b.onclick = () => onSlotClick(b.dataset.start);
  });

}

async function onSlotClick(slotStart) {

  const data = await api(
    `/api/get_slot_stats.php?stop_id=${currentStopId}&route=${encodeURIComponent(currentRoute)}&weekday=${currentWeekday}&slot_start=${slotStart}`
  );

  if (!data.ok) {
    setHTML("statsBox", "Ошибка статистики");
    return;
  }
  const s = data.stats;
  setHTML("statsBox", `
    <div class="statCard">
      <b>${currentRoute}</b> ${slotStart}–${data.slot_end}<br>
      Оценок: ${s.votes}<br><br>

      Средняя загрузка: <b>${s.avg_load}</b> / 5<br>
      Пенсионеры: ${s.avg_pensioners} / 5<br>
      Дети: ${s.avg_children} / 5<br>
      Коляски: ${s.avg_strollers} / 5
    </div>
    ${renderRateBlock(slotStart)}
  `);

  bindRateBlock(slotStart);
}

function renderRateBlock(time = "") {

  if (!window.IS_AUTH) {
    return `<div class="muted">Войдите, чтобы оставить оценку</div>`;
  }
  return `
    <div class="rateBox">

      <div>
        День:
        <select id="daySel">
          ${[1,2,3,4,5,6,7].map(d =>
            `<option value="${d}" ${d===currentWeekday?'selected':''}>${weekdayLabel(d)}</option>`
          ).join("")}
        </select>
      </div>

      <div style="margin-top:8px;">
        Время поездки:
        <input type="time" id="timeInput" value="${time}">
      </div>

      <button class="btn primary" id="btnShowRate" style="margin-top:10px;">
        📊 Оценить маршрут
      </button>

      <div id="rateForm" style="display:none;margin-top:16px;">
        ${renderSliders()}
        <button class="btn primary" id="btnSendRate">Отправить</button>
        <div id="rateMsg" class="muted"></div>
      </div>

    </div>
  `;
}

function renderSliders() {
  return `
    <label>Загруженность</label>
    <input type="range" id="load" min="0" max="5" value="3">

    <label>Пенсионеры</label>
    <input type="range" id="pens" min="0" max="5" value="0">

    <label>Дети</label>
    <input type="range" id="kids" min="0" max="5" value="0">

    <label>Коляски</label>
    <input type="range" id="st" min="0" max="5" value="0">
  `;
}

function bindRateBlock(defaultTime = "") {
  const daySel = document.getElementById("daySel");
  if (daySel) {
    daySel.onchange = () => {
      currentWeekday = Number(daySel.value);
      if (currentRoute) {
        onRouteClick(currentRoute);
      }
    };
  }

  const btn = document.getElementById("btnShowRate");
  const form = document.getElementById("rateForm");
  if (btn && form) {
    btn.onclick = () => {
      form.style.display = "block";
      btn.style.display = "none";
    };
  }

  const send = document.getElementById("btnSendRate");
  if (!send) return;
  send.onclick = async () => {
    const time = document.getElementById("timeInput").value;
    if (!time) {
      document.getElementById("rateMsg").textContent = "Укажите время";
      return;
    }

    currentWeekday = Number(document.getElementById("daySel").value);
    const body = new URLSearchParams({
      stop_id: currentStopId,
      route_name: currentRoute,
      weekday: currentWeekday,
      ride_time: time,
      load_level: document.getElementById("load").value,
      pensioners: document.getElementById("pens").value,
      children: document.getElementById("kids").value,
      strollers: document.getElementById("st").value
    });

    const res = await api("/api/add_rating.php", {
      method: "POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded"},
      body
    });

    document.getElementById("rateMsg").textContent =
      res.ok ? "✅ Оценка сохранена" : "Ошибка";
    if (res.ok) onRouteClick(currentRoute);
  };
}

async function addToFavorites(route, btn) {
  if (!currentStopId || !route) return;
  const body = new URLSearchParams({
    stop_id: currentStopId,
    route_name: route
  });

  try {
    const res = await fetch("/api/toggle_favorite.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body
    });

    const data = await res.json();
    if (data.ok) {
      btn.classList.toggle("favActive");
    }
  } catch (e) {
    console.error("Favorite error:", e);
  }
}
