<?php
require_once '../includes/base.php';
session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['felhasznalo']['felhasznalonev'];
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
          WHERE k.FELHASZNALO_FELHASZNALONEV = :felhasznalonev
          ORDER BY k.FELTOLTESI_DATUM DESC";

$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ':felhasznalonev', $user);
oci_execute($stid);

$images = [];
while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS)) {
    $images[] = $row;
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Feltöltött Képek - Profil</title>
    <link rel="stylesheet" href="../styles/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <base href="<?= BASE_URL ?>">
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
        <div class="profile-gallery-container">
            <h2><?= htmlspecialchars($user) ?> által feltöltött képek</h2>
            <div class="gallery-grid">
                <?php foreach ($images as $img): ?>
                    <div class="gallery-item">
                        <?php
                        $kepData = $img['KEP_BINARIS'] ?? '';
                        if (!empty($kepData)) {
                            $base64 = base64_encode($kepData);
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
                                $leiras = htmlspecialchars($img['LEIRAS'] ?? 'Nincs leírás');
                                if (mb_strlen($leiras) > $maxLength) {
                                    $roviditett = mb_substr($leiras, 0, $maxLength) . '...';
                                    echo nl2br($roviditett);
                                    echo ' <a href="pages/photo_review.php?kep_id=' . urlencode($img['ID']) . '">Tovább</a>';
                                } else {
                                    echo nl2br($leiras);
                                }
                                ?>
                            </p>
                            <?php if (isset($_SESSION['felhasznalo']) && $_SESSION['felhasznalo']['felhasznalonev'] == $img['FELHASZNALO_FELHASZNALONEV']): ?>
                                <form action="controllers/delete_handler.php" method="post" onsubmit="return confirm('Biztosan törölni szeretnéd a képet?');" class="location_torles">
                                    <input type="hidden" name="kep_id" value="<?= htmlspecialchars($img['ID']) ?>">
                                    <button type="submit" class="delete-button">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>
