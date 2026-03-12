<form id="searchForm" method="GET" action="recepten.php">
    <input
        name="search"
        type="text"
        id="zoekvak"
        placeholder="Zoek..."
        autocomplete="off">
</form>

<div id="resultaten" aria-live="polite" style="display:none;"></div>

<script>
  const input = document.getElementById("zoekvak");
  const resultaten = document.getElementById("resultaten");
  let t;

  async function fetchResults(term) {
    try {
      const resp = await fetch('includes/zoekresultaten.php?term=' + encodeURIComponent(term));
      if (!resp.ok) throw new Error('Network response was not ok');
      const html = await resp.text();
      resultaten.innerHTML = html;
    } catch (err) {
      resultaten.innerHTML = '<div class="leeg">Er is een fout opgetreden</div>';
    }
  }

  input.addEventListener("input", () => {
    clearTimeout(t);

    const term = input.value.trim();

    if (term === "") {
      resultaten.style.display = "none";
      resultaten.innerHTML = '';
      return;
    }

    resultaten.style.display = "block";

    t = setTimeout(() => {
      fetchResults(term);
    }, 250);
  });
</script>