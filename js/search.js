document.addEventListener("DOMContentLoaded", function () {
  const input = document.getElementById("stopSearch");
  const box = document.getElementById("searchResults");
  input.addEventListener("input", async function() {


    const q = input.value.trim();
    if (q.length < 2) { //если мало введено
      box.innerHTML = "";
      box.style.display = "none";
      return;
    }

    const r = await fetch("/api/search_stops.php?q=" + encodeURIComponent(q));
    const items = await r.json();
    if (!items.length) {
      box.innerHTML = "<div class='srItem'>Ничего не найдено</div>";
      box.style.display = "block";
      return;
    }

    box.innerHTML = items.map(function (it) {
      return `
        <div class="srItem"
             data-lat="${it.latitude}"
             data-lon="${it.longitude}">
          ${it.name}
        </div>
      `;
    }).join("");
    box.style.display = "block";

    box.querySelectorAll(".srItem").forEach(function (el) {
      el.addEventListener("click", function () {
        const lat = el.dataset.lat;
        const lon = el.dataset.lon;

        if (window.focusStop) {
          window.focusStop(lat, lon);
        }

        box.style.display = "none";
        input.value = el.textContent;
      });
    });
  });
});
