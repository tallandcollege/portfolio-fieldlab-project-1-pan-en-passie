<?php

//$pdo = include(config.php);

$term = $_get['term'];



$term = isset($_get['term']) ? trim($_get['term']) : '';
if ($term === '') {
    echo '';
    exit;
}

$like = "%{$term}%";
$stmt = $pdo->prepare("SELECT * FROM Recipe WHERE naam LIKE :term LIMIT 10");
$stmt ->execute(['term' => $like]);
$rows = $stmt->fetchall();

if(!$rows){
    echo '<div class="leeg">Geen resultaten gevonden</div>';
    exit;
}
$fotopad = 'fotos/';

foreach ($rows as $resultaten) {
    $naam = htmlspecialchars($resultaten['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $ing  = htmlspecialchars($r['ingredienten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $foto = $r['foto'] ? htmlspecialchars($fotoPad . $r['foto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'fotos/default.png';
    echo "<div class='kaart' data-naam='{$naam}'>
            <img src='{$foto}' alt='Foto van {$naam}' onerror=\"this.src='fotos/default.png'\" />
            <div>
              <div class='naam'>{$naam}</div>
              <div class='ingredienten'>Ingrediënten: {$ing}</div>
            </div>
          </div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <input type="text" id="zoekvak" placeholder="Zoek..." autocomplete="off" style="width:250px; padding:10px;">

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
                    return
                }

                timer = setTimeout(() => {
                    fetch('zoke.php?term=' + encodeURIComponent(term))
                        .then(resp => {
                            if (!resp.ok) throw new error('netwerkfout');
                            return resp.text();
                        })
                        .then(html => {
                            resultaten.innerhtml();
                        })
                        .catch(er => {
                            console.error(err);
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