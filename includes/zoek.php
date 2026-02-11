<form method="GET" action="Recepten.php">
<input name="search" type="text" id="zoekvak" placeholder="Zoek..." autocomplete="off">
</form>
<div id="resultaten" aria-live="polite" style="display:none;">
  <iframe id="zoekresultaten" src="includes/zoekresultaten.php" sandbox="allow-same-origin allow-scripts allow-top-navigation"></iframe>
</div>

<script>
  const input = document.getElementById("zoekvak");
  const resultaten = document.getElementById("resultaten");
  const frame = document.getElementById("zoekresultaten");
  let t;

  input.addEventListener("input", () => {
    clearTimeout(t);

    const term = input.value.trim();

    if (term === "") {
      resultaten.style.display = "none";
      frame.src = "includes/zoekresultaten.php"; // optional: reset
      return;
    }

    resultaten.style.display = "block";

    t = setTimeout(() => {
      frame.src = "includes/zoekresultaten.php?term=" + encodeURIComponent(input.value.trim());
    }, 250);
  });
</script>
