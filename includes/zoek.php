<input type="text" id="zoekvak" placeholder="Zoek..." autocomplete="off">

<div id="resultaten" aria-live="polite" style="display:none;">
  <iframe id="zoekresultaten" src="includes/zoekresultaten.php"></iframe>
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
