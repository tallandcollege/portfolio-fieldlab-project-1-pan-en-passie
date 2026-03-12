<div class="search-shell" id="searchShell">
  <form id="searchForm" role="search" action="recepten.php">
    <span class="search-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M10.5 4a6.5 6.5 0 1 0 4.03 11.6l4.43 4.44 1.41-1.42-4.43-4.43A6.5 6.5 0 0 0 10.5 4zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9z" />
      </svg>
    </span>
    <input
      name="search"
      type="text"
      id="zoekvak"
      placeholder="Zoek een recept..."
      autocomplete="off"
      aria-controls="resultaten"
      aria-expanded="false">
  </form>

  <div id="resultaten" aria-live="polite" hidden></div>
</div>

<script>
  const input = document.getElementById("zoekvak");
  const resultaten = document.getElementById("resultaten");
  const searchShell = document.getElementById("searchShell");
  let t;

  function setResultsOpen(isOpen) {
    searchShell.classList.toggle("is-open", isOpen);
    input.setAttribute("aria-expanded", isOpen ? "true" : "false");
    resultaten.hidden = !isOpen;
  }

  async function fetchResults(term) {
    try {
      const resp = await fetch('includes/zoekresultaten.php?term=' + encodeURIComponent(term));
      if (!resp.ok) throw new Error('Network response was not ok');
      const html = await resp.text();
      resultaten.innerHTML = html;

      if (html.trim() === '') {
        resultaten.innerHTML = '<div class="leeg">Geen recepten gevonden</div>';
      }
    } catch (err) {
      resultaten.innerHTML = '<div class="leeg">Er is een fout opgetreden</div>';
    }
  }

  input.addEventListener("input", () => {
    clearTimeout(t);

    const term = input.value.trim();

    if (term === "") {
      setResultsOpen(false);
      resultaten.innerHTML = '';
      return;
    }

    setResultsOpen(true);

    t = setTimeout(() => {
      fetchResults(term);
    }, 250);
  });

  document.addEventListener("click", (event) => {
    if (!searchShell.contains(event.target)) {
      setResultsOpen(false);
    }
  });

  input.addEventListener("focus", () => {
    if (input.value.trim() !== "") {
      setResultsOpen(true);
    }
  });
</script>
