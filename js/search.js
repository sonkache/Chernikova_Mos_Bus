document.addEventListener("DOMContentLoaded", function() {
const input = document.getElementById("stopSearch");
const resultsBox = document.getElementById("searchResults");
let timer = null;

function escapeHtml(str) {
  return String(str ?? "")
    .replaceAll("&","&amp;")
    .replaceAll("<","&lt;")
    .replaceAll(">","&gt;")
    .replaceAll('"',"&quot;")
    .replaceAll("'","&#039;");
}

function showBox(html) {
  resultsBox.innerHTML = html;
  resultsBox.style.display = "block";
}

function hideBox() {
  resultsBox.style.display = "none";
  resultsBox.innerHTML = "";
}

function normalizeStops(payload) {
  if (Array.isArray(payload)) return payload;
  if (payload && payload.ok === true && Array.isArray(payload.stops)) return payload.stops;
  if (payload && Array.isArray(payload.stops)) return payload.stops;
  if (payload && Array.isArray(payload.data)) return payload.data;
  return null;
}

function renderResults(stops) {
  if (!Array.isArray(stops) || stops.length === 0) {
    showBox(`<div class="sr-empty">Ничего не найдено</div>`);
    return;
  }

  showBox(stops.map(s => {
    const routes = (s.routes_text || s.routes || "").trim();
    return `
      <div class="sr-item">
        <div class="sr-main">
          <div class="sr-name">${escapeHtml(s.name)}</div>
          <div class="sr-meta">ID: ${s.id}${s.district ? " • " + escapeHtml(s.district) : ""}</div>
          ${routes ? `<div class="sr-routes">Маршруты: ${escapeHtml(routes)}</div>` : ``}
        </div>
        <div class="sr-actions">
          <button class="sr-btn"
            data-id="${s.id}"
            data-lat="${s.latitude || ''}"
            data-lng="${s.longitude || ''}"
            data-name="${escapeHtml(s.name)}">
            Открыть на карте
          </button>
         </div>
      </div>
    `;
  }).join(""));

  resultsBox.querySelectorAll(".sr-btn").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const id = parseInt(btn.dataset.id, 10);
      const lat = parseFloat(btn.dataset.lat);
      const lng = parseFloat(btn.dataset.lng);
      const name = btn.dataset.name;
      if (window.focusStop) {
        window.focusStop(id);
      } else {
        window.location.href = `/?stop_id=${id}`;
      }
      hideBox();
    });
  });
}

async function doSearch(q) {
  try {
    const r = await fetch("/api/search_stops.php?q=" + encodeURIComponent(q));
    const data = await r.json();
    const stops = normalizeStops(data);

    if (!stops) {
      showBox(`<div class="sr-empty">Ошибка ответа сервера</div>`);
      return;
    }
    renderResults(stops);
  } catch (err) {
    console.error("search fetch error:", err);
    showBox(`<div class="sr-empty">Ошибка сети</div>`);
  }
}

if (input) {
  input.addEventListener("input", () => {
    const q = input.value.trim();
    clearTimeout(timer);
    if (q.length < 2) {
      hideBox();
      return;
    }
    timer = setTimeout(() => doSearch(q), 250);
  });
}
});