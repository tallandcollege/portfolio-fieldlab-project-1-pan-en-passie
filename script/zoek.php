<?php
if (isset($_GET['Term'])) {
    //$pdo = include(config.php);

    $term = isset($_GET['Term']) ? trim($_GET['Term']) : '';
    if ($term === '') {
        echo '';
        exit;
    }

    $like = "%{$term}%";
    $stmt = $pdo->prepare("SELECT * FROM Recipe WHERE naam LIKE :term LIMIT 10");
    $stmt->execute(['term' => $like]);
    $rows = $stmt->fetchAll();

    if (!$rows) {
        echo '<div class="leeg">Geen resultaten gevonden</div>';
        exit;
    }
    $fotopad = 'fotos/';

    foreach ($rows as $resultaten) {
        $naam = htmlspecialchars($resultaten['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ing  = htmlspecialchars($resultaten['ingredienten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $foto = $resultaten['foto'] ? htmlspecialchars($fotopad . $resultaten['foto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'fotos/default.png';
        echo "<div class='kaart' data-naam='{$naam}'>
                <img src='{$foto}' alt='Foto van {$naam}' onerror=\"this.src='fotos/default.png'\" />
                <div>
                  <div class='naam'>{$naam}</div>
                  <div class='ingredienten'>Ingrediënten: {$ing}</div>
                </div>
              </div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Document</title>
</head>

<body>
    <input type="text" id="zoekvak" placeholder="Zoek..." autocomplete="off">

    <div id="resultaten" aria-live="polite"> </div>

    <script>
        (function() {
            const zoekvak = document.getElementById('zoekvak');
            const resultaten = document.getElementById('resultaten');
            let timer = null;

            zoekvak.addEventListener('input', function() {
                const term = this.value.trim();

                if (timer) clearTimeout(timer);

                if (term === '') {
                    resultaten.innerHTML = '';
                    resultaten.style.display = 'none';
                    return;
                }

                resultaten.style.display = 'block';

                timer = setTimeout(() => {
                    fetch('zoek.php?Term=' + encodeURIComponent(term))
                        .then(resp => {
                            if (!resp.ok) throw new Error('netwerkfout');
                            return resp.text();
                        })
                        .then(html => {
                            resultaten.innerHTML = html;
                        })
                        .catch(er => {
                            console.error(er);
                            resultaten.innerHTML = '<div class="leeg">Er is iets misgegaan.</div>';

                        });
                }, 250);
            });

            resultaten.addEventListener('click', function(e) {
                let kaart = e.target.closest('.kaart');
                if (kaart) {
                    const naam = kaart.dataset.naam || '';
                    zoekvak.value = naam;
                    resultaten.innerHTML = '';
                }
            });

        })();
    </script>


</body>

</html>