<?php
require_once 'includes/base.php';
session_start();

require_once 'includes/db.php';

// Szűrési paraméterek előkészítése
$filter = '';
$params = [];

if (isset($_GET['telepules'])) {
    $filter = " WHERE t.TELEPULES = :telepules";
    $params[':telepules'] = $_GET['telepules'];
} elseif (isset($_GET['kategoria'])) {
    $filter = " WHERE EXISTS (
        SELECT 1 FROM KepKategoria kk
        WHERE kk.kep_id = k.id
          AND kk.kategoria_nev = :kategoria
    )";
    $params[':kategoria'] = $_GET['kategoria'];
}

// Képek lekérdezése
$query = "SELECT 
            k.ID, 
            TO_CHAR(k.FELTOLTESI_DATUM, 'YYYY.MM.DD') AS FELTOLTESI_DATUM, 
            k.CIM, 
            k.KEP_BINARIS,
            dbms_lob.getlength(k.KEP_BINARIS) AS BLOB_LENGTH, 
            k.LEIRAS, 
            k.FELHASZNALO_FELHASZNALONEV, 
            t.TELEPULES AS TELEPULES_NEV,
            NVL(cat.KATEGORIANK, 'nincs kategória hozzárendelve') AS KATEGORIANK
          FROM Kep k
          LEFT JOIN Telepules t ON k.TELEPULES_ID = t.ID
          LEFT JOIN (
              SELECT kep_id, LISTAGG(kategoria_nev, ', ') WITHIN GROUP (ORDER BY kategoria_nev) AS KATEGORIANK
              FROM KepKategoria
              GROUP BY kep_id
          ) cat ON k.ID = cat.kep_id
          $filter
          ORDER BY k.FELTOLTESI_DATUM DESC";

$stid = oci_parse($conn, $query);

foreach ($params as $key => $value) {
    oci_bind_by_name($stid, $key, $params[$key]);
}

oci_execute($stid);
$images = [];
while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS)) {
    $images[] = $row;
}

// Települések lekérdezése
$queryTelepules = "SELECT 
                      t.telepules, 
                      COUNT(k.id) AS FOTO_SZAM 
                   FROM Telepules t 
                   LEFT JOIN Kep k ON t.id = k.telepules_id 
                   GROUP BY t.telepules 
                   ORDER BY t.telepules ASC";

$stidTelepules = oci_parse($conn, $queryTelepules);
oci_execute($stidTelepules);
$telepulesek = [];
while ($row = oci_fetch_array($stidTelepules, OCI_ASSOC)) {
    $telepulesek[] = $row;
}

// Kategóriák lekérdezése
$queryKategoria = "SELECT 
                      k.nev AS KATEGORIA_NEV, 
                      COUNT(kk.kep_id) AS FOTO_SZAM 
                   FROM Kategoria k 
                   LEFT JOIN KepKategoria kk ON k.nev = kk.kategoria_nev 
                   GROUP BY k.nev 
                   ORDER BY k.nev ASC";

$stidKategoria = oci_parse($conn, $queryKategoria);
oci_execute($stidKategoria);
$kategoriak = [];
while ($row = oci_fetch_array($stidKategoria, OCI_ASSOC)) {
    $kategoriak[] = $row;
}

$prErtekelesek = [];
if (isset($_SESSION['felhasznalo'])) {
    $ertekelesek = [];
    $queryErtekeles = "SELECT * FROM ERTEKELES WHERE FELHASZNALO_FELHASZNALONEV = :felhasznalonev";

    $stidErtekeles = oci_parse($conn, $queryErtekeles);
    oci_bind_by_name($stidErtekeles, ':felhasznalonev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_execute($stidErtekeles);

    while ($sor = oci_fetch_assoc($stidErtekeles)) {
        $ertekelesek[] = $sor;
    }

    if ($ertekelesek) {
        foreach ($ertekelesek as $ertekeles) {
            $prErtekelesek[$ertekeles["KEP_ID"]] = $ertekeles["PONTSZAM"];
        }
    } else {
        $prErtekelesek = [];
    }
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Főoldal - Képgaléria</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="icon" href="styles/favicon.ico" type="image/ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <base href="<?php echo BASE_URL; ?>">
</head>
<body>

<!-- Logó hozzáadása -->
<div class="logo-container">
    <a href="index.php">
        <img src="styles/banner.png" alt="Logo" class="logo">
    </a>
</div>

<!-- Menü betöltése -->
<?php
include __DIR__ . '/pages/shared/menu.php';
?>

<!-- Main Content -->

<div class="sidebar">
    <div class="box">
        <h3>Települések</h3>
        <ul>
            <?php foreach ($telepulesek as $telepules): ?>
                <?php
                $isActive = (isset($_GET['telepules']) && $_GET['telepules'] === $telepules['TELEPULES']) ? 'active' : '';
                ?>
                <li>
                    <a href="?telepules=<?= urlencode($telepules['TELEPULES']) ?>" class="<?= $isActive ?>">
                        <?= htmlspecialchars($telepules['TELEPULES']) ?> (<?= htmlspecialchars($telepules['FOTO_SZAM']) ?>)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="box">
        <h3>Kategóriák</h3>
        <ul>
            <?php foreach ($kategoriak as $kategoria): ?>
                <?php
                $isActive = (isset($_GET['kategoria']) && $_GET['kategoria'] === $kategoria['KATEGORIA_NEV']) ? 'active' : '';
                ?>
                <li>
                    <a href="?kategoria=<?= urlencode($kategoria['KATEGORIA_NEV']) ?>" class="<?= $isActive ?>">
                        <?= htmlspecialchars($kategoria['KATEGORIA_NEV']) ?> (<?= htmlspecialchars($kategoria['FOTO_SZAM']) ?>)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Gallery Container -->
<div class="gallery-container">
    <div class="gallery-grid">
        <?php foreach ($images as $img): ?>
            <div class="gallery-item">
                <?php
                $kepData = $img['KEP_BINARIS'] ?? '';
                if (!empty($kepData)) {
                    $base64 = base64_encode($kepData);
                    // Link hozzáadása a képre kattintás esetén
                    echo "<a href='pages/photo_review.php?kep_id=" . urlencode($img['ID']) . "'><img src='data:image/jpeg;base64,{$base64}' alt='" . htmlspecialchars($img['CIM']) . "'></a>";
                } else {
                    echo "Nincs kép";
                }
                ?>
                <div class="metadata">
                    <p><strong>Cím:</strong> <?= htmlspecialchars($img['CIM']) ?></p>
                    <p><strong>Feltöltötte:</strong> <?= htmlspecialchars($img['FELHASZNALO_FELHASZNALONEV']) ?></p>
                    <p><strong>Feltöltési dátum:</strong> <?= htmlspecialchars($img['FELTOLTESI_DATUM']) ?></p>
                    <p><strong>Település:</strong> <?= htmlspecialchars($img['TELEPULES_NEV'] ?? 'Nincs település hozzárendelve') ?></p>
                    <p><strong>Kategóriák:</strong> <?= htmlspecialchars($img['KATEGORIANK']) ?></p>
                    <p><strong>Leírás:</strong>
                        <?php
                        $maxLength = 100;
                        $leiras = htmlspecialchars($img['LEIRAS'] ?? 'Nincs megadva leírás');
                        if (mb_strlen($leiras) > $maxLength) {
                            $roviditett = mb_substr($leiras, 0, $maxLength) . '...';
                            echo nl2br($roviditett);
                            echo ' <a href="pages/photo_review.php?kep_id=' . urlencode($img['ID']) . '">Tovább</a>';
                        } else {
                            echo nl2br($leiras);
                        }
                        ?>
                    </p>
                    <?php if (isset($_SESSION['felhasznalo']) && isset($prErtekelesek[$img['ID']])): ?>
                    <p><strong>Értékelésed:</strong>
                        <?php for ($i = 0; $i < 5; $i++) { ?>
                            <?php if ($i < $prErtekelesek[$img['ID']]): ?>
                                <i class="fa-solid fa-star rating-preview"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-star rating-preview rating-preview-gray"></i>
                            <?php endif; ?>
                        <?php } ?>
                    <?php endif ; ?>
                    <?php if (isset($_SESSION['felhasznalo']) && $_SESSION['felhasznalo']['felhasznalonev'] == $img['FELHASZNALO_FELHASZNALONEV']): ?>
                        <form action="controllers/delete_handler.php" method="post" onsubmit="return confirm('Biztosan törölni szeretnéd a képet?');" class="location_torles">
                            <input type="hidden" name="kep_id" value="<?= htmlspecialchars($img['ID']) ?>">
                            <button type="submit" class="delete-button">Törlés</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div style="clear: both;"></div>

<footer>
    <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
</footer>
</body>
</html>
