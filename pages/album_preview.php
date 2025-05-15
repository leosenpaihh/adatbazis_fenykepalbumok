<?php

require_once '../includes/base.php';
session_start();
include('../includes/db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

if (empty($_GET['album_id'])) {
    echo "<p>Hiba: nincs megadva album azonosító.</p>";
    exit;
}
$album_id = (int)$_GET['album_id'];

$sql_album = "SELECT * FROM Fenykepalbum WHERE id = :album_id";
$stid_album = oci_parse($conn, $sql_album);
oci_bind_by_name($stid_album, ':album_id', $album_id);
oci_execute($stid_album);
$album = oci_fetch_assoc($stid_album);

if (!$album) {
    echo "<p>Hiba: nem található ilyen album.</p>";
    exit;
}

$album_leiras = '';
if (isset($album['LEIRAS']) && !is_null($album['LEIRAS'])) {
    if (is_object($album['LEIRAS'])) {
        $album_leiras = oci_lob_read($album['LEIRAS'], $album['LEIRAS']->size());
    } else {
        $album_leiras = $album['LEIRAS'];
    }
}

$sql_images = "SELECT 
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
               INNER JOIN KepFenykepalbum kfa ON k.ID = kfa.KEP_ID
               WHERE kfa.fenykepalbum_id = :album_id
               ORDER BY kfa.sorszam";

$stid_images = oci_parse($conn, $sql_images);
oci_bind_by_name($stid_images, ':album_id', $album_id);
oci_execute($stid_images);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($album['NEV'], ENT_QUOTES) ?> – Album</title>
    <base href="<?= BASE_URL ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" href="<?= BASE_URL ?>styles/favicon.ico" type="image/ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php
include __DIR__ . '/shared/menu.php';
?>
<button class="menu-toggle" onclick="toggleMenu()">☰ Menü</button>
<script>
    function toggleMenu() {
        let nav = document.querySelector('nav');
        nav.classList.toggle('active');
    }
</script>


<div class="page-container">
    <div class="wrapper">
        <div class="album-title-preview"><?= htmlspecialchars($album['NEV'], ENT_QUOTES) ?></div>
        <p class="description">
            <?= nl2br(htmlspecialchars($album_leiras, ENT_QUOTES)) ?: 'Nincs leírás'; ?>
        </p>

        <div class="gallery-grid">
            <?php while ($img = oci_fetch_assoc($stid_images)): ?>
                <div class="gallery-item">
                    <a href="pages/photo_review.php?kep_id=<?= urlencode($img['ID']) ?>" class="image-link">
                        <img src="controllers/show_image.php?image_id=<?= urlencode($img['ID']) ?>"
                             alt="<?= htmlspecialchars($img['CIM'], ENT_QUOTES) ?>">
                    </a>
                    <div class="metadata">
                        <p><strong>Cím:</strong> <?= htmlspecialchars($img['CIM'], ENT_QUOTES) ?></p>
                        <p><strong>Feltöltötte:</strong> <?= htmlspecialchars($img['FELHASZNALO_FELHASZNALONEV'], ENT_QUOTES) ?></p>
                        <p><strong>Feltöltési dátum:</strong> <?= htmlspecialchars($img['FELTOLTESI_DATUM'], ENT_QUOTES) ?></p>
                        <p><strong>Település:</strong> <?= htmlspecialchars($img['TELEPULES_NEV'] ?? 'Nincs település hozzárendelve', ENT_QUOTES) ?></p>
                        <p><strong>Kategóriák:</strong> <?= htmlspecialchars($img['KATEGORIANK'], ENT_QUOTES) ?></p>
                        <p><strong>Leírás:</strong>
                            <?php
                            $maxLength = 100;
                            $leiras = $img['LEIRAS'];
                            if (is_object($leiras)) {
                                $leiras = oci_lob_read($leiras, $leiras->size());
                            }
                            $leiras = htmlspecialchars($leiras, ENT_QUOTES);

                            if (mb_strlen($leiras) > $maxLength) {
                                $roviditett = mb_substr($leiras, 0, $maxLength) . '...';
                                echo nl2br($roviditett);
                                echo ' <a href="pages/photo_review.php?kep_id=' . urlencode($img['ID']) . '">Tovább</a>';
                            } else {
                                echo nl2br($leiras);
                            }
                            ?>
                        </p>
                        <?php if (isset($_SESSION['felhasznalo']) && $_SESSION['felhasznalo']['felhasznalonev'] === $img['FELHASZNALO_FELHASZNALONEV']): ?>
                            <form action="controllers/delete_handler.php" method="post" onsubmit="return confirm('Biztosan törölni szeretnéd a képet?');" class="location_torles">
                                <input type="hidden" name="kep_id" value="<?= htmlspecialchars($img['ID'], ENT_QUOTES) ?>">
                                <button type="submit" class="delete-button">Törlés</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
</footer>
</body>
</html>
