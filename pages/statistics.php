<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

// Beállítjuk a rendezési opciót alapértelmezetten név szerint
$rendezesi_opcio = 'teljes_nev';

// Ha a felhasználó választott egy másik rendezési opciót, azt alkalmazzuk
if (isset($_POST['rendez'])) {
    $rendezesi_opcio = $_POST['rendez'];
}

// A megfelelő SQL lekérdezés
$query = "
SELECT 
    f.felhasznalonev,
    f.vezeteknev || ' ' || f.keresztnev AS teljes_nev,
    (SELECT COUNT(*) FROM Kep WHERE felhasznalo_felhasznalonev = f.felhasznalonev) AS kep_szam,
    (SELECT COUNT(*) FROM Fenykepalbum WHERE felhasznalo_felhasznalonev = f.felhasznalonev) AS album_szam,
    (SELECT COUNT(*) FROM Hozzaszolas WHERE felhasznalo_felhasznalonev = f.felhasznalonev) AS hozzaszolas_szam
FROM Felhasznalo f
ORDER BY ";

switch ($rendezesi_opcio) {
    case 'komment':
        $query .= "hozzaszolas_szam DESC, teljes_nev"; // Ha kommentek számában egyeznek, név szerint
        break;
    case 'album':
        $query .= "album_szam DESC, teljes_nev"; // Ha albumok száma egyezik, név szerint
        break;
    case 'kep':
        $query .= "kep_szam DESC, teljes_nev"; // Ha képek száma egyezik, név szerint
        break;
    default:
        $query .= "teljes_nev"; // Név szerinti alapértelmezett rendezés
}

$stid = oci_parse($conn, $query);
oci_execute($stid);

$statisztikak = [];
while (($row = oci_fetch_assoc($stid)) != false) {
    $statisztikak[] = $row;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Statisztikák</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_URL ?>">
    <style>
        .highlight {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page-container">
    <div class="wrapper">
        <h1>Felhasználói Statisztikák</h1>

        <!-- Rendezési opciók form -->
        <form method="POST" action="">
            <label for="rendez">Rendezés:</label>
            <select name="rendez" id="rendez" onchange="this.form.submit()">
                <option value="teljes_nev" <?= ($rendezesi_opcio == 'teljes_nev') ? 'selected' : '' ?>>Név szerint</option>
                <option value="kep" <?= ($rendezesi_opcio == 'kep') ? 'selected' : '' ?>>Legtöbb képpel</option>
                <option value="album" <?= ($rendezesi_opcio == 'album') ? 'selected' : '' ?>>Legtöbb albummal</option>
                <option value="komment" <?= ($rendezesi_opcio == 'komment') ? 'selected' : '' ?>>Legtöbb kommenttel</option>
            </select>
        </form>

        <div class="grid-container-xd">
            <?php foreach ($statisztikak as $sor): ?>
                <div class="user-card-statistics">
                    <h2><?= htmlspecialchars($sor['TELJES_NEV']) ?></h2>

                    <!-- Ha a rendezési opció képek szerint van, akkor kiemelés -->
                    <p class="<?= ($rendezesi_opcio == 'kep') ? 'highlight' : '' ?>">Feltöltött képek: <?= htmlspecialchars($sor['KEP_SZAM']) ?></p>

                    <!-- Ha a rendezési opció albumok szerint van, akkor kiemelés -->
                    <p class="<?= ($rendezesi_opcio == 'album') ? 'highlight' : '' ?>">Albumok száma: <?= htmlspecialchars($sor['ALBUM_SZAM']) ?></p>

                    <!-- Ha a rendezési opció kommentek szerint van, akkor kiemelés -->
                    <p class="<?= ($rendezesi_opcio == 'komment') ? 'highlight' : '' ?>">Hozzászólások száma: <?= htmlspecialchars($sor['HOZZASZOLAS_SZAM']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>
