<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

if (isset($_SESSION['hiba'])) {
    echo "<p>" . $_SESSION['hiba'] . "</p>";
    unset($_SESSION['hiba']);
}

if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/css.css">
    <meta charset="UTF-8">
    <title>Album létrehozása</title>
    <base href="http://localhost/adatbazis_fenykepalbumok/">

</head>
<body>
<?php
$felhasznalonev = $_SESSION['felhasznalo']['felhasznalonev'];
$kep_lekerdezes = oci_parse($conn,
    "
            SELECT ID, CIM 
            FROM KEP 
            WHERE FELHASZNALO_FELHASZNALONEV = :fnev 
            ORDER BY FELTOLTESI_DATUM DESC
            ");
oci_bind_by_name($kep_lekerdezes, ":fnev", $felhasznalonev);
oci_execute($kep_lekerdezes);
?>
<h1>Album létrehozása</h1>

<form action="controllers/album_handler.php" method="post">
    <input type="text" name="nev" placeholder="Album neve" required maxlength="255"><br>
    <textarea name="leiras" placeholder="Album leírása"></textarea><br>

    <label for="kepek[]">Válassz képeket az albumhoz:</label><br>
    <?php while ($row = oci_fetch_assoc($kep_lekerdezes)) : ?>
        <input type="checkbox" name="kepek[]" value="<?= $row['ID'] ?>"> <?= htmlspecialchars($row['CIM']) ?><br>
    <?php endwhile; ?>

    <button type="submit" name="muv" value="letrehozas">Létrehozás</button>
</form>
</body>
</html>