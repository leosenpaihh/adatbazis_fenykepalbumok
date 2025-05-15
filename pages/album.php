<?php
require_once '../includes/base.php';
session_start();
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
    <link rel="stylesheet" href="../styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta charset="UTF-8">
    <title>Album létrehozása</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <base href="<?= BASE_URL ?>">
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

    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>