<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$album_id = $_GET['album_id'] ?? null;
$album_owner = $_GET['album_owner'] ?? null;

if (!$album_id || !$album_owner) {
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

$sql = "SELECT * FROM FENYKEPALBUM 
        WHERE ID = :album_id 
        AND (FELHASZNALO_FELHASZNALONEV = :felhasznalonev OR :admin = 1)";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':album_id', $album_id);
oci_bind_by_name($stid, ':felhasznalonev', $_SESSION['felhasznalo']['felhasznalonev']);
oci_bind_by_name($stid, ':admin', $_SESSION['felhasznalo']['admin']);
oci_execute($stid);
$album = oci_fetch_assoc($stid);

if (!$album) {
    $_SESSION['hiba'] = "Nem található ilyen album, vagy nem jogosult a módosításra.";
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

$leiras = oci_lob_read($album['LEIRAS'], $album['LEIRAS']->size());

$sql_images = "SELECT * FROM KEP 
               WHERE FELHASZNALO_FELHASZNALONEV = :felhasznalonev 
               AND ID NOT IN (SELECT KEP_ID FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id)";
$stid_images = oci_parse($conn, $sql_images);
oci_bind_by_name($stid_images, ':felhasznalonev', $album_owner);
oci_bind_by_name($stid_images, ':album_id', $album_id);
oci_execute($stid_images);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album módosítása</title>
    <base href="<?php echo BASE_URL; ?>">

</head>
<body>
<h1>Album módosítása</h1>

<form action="<?php echo BASE_URL; ?>controllers/album_edit_handler.php" method="post">
    <input type="hidden" name="album_id" value="<?= htmlspecialchars($album['ID']) ?>">
    <input type="hidden" name="album_owner" value="<?= htmlspecialchars($album_owner) ?>">

    <label for="album_nev">Album Címe:</label><br>
    <input type="text" id="album_nev" name="album_nev" value="<?= htmlspecialchars($album['NEV']) ?>" required><br><br>

    <label for="album_leiras">Leírás:</label><br>
    <textarea id="album_leiras" name="album_leiras" rows="4" cols="50"><?= htmlspecialchars($leiras) ?></textarea><br>

    <label for="kepek">Képek hozzáadása:</label><br>
    <?php
    $sql_kepek = "SELECT * FROM KEP WHERE FELHASZNALO_FELHASZNALONEV = :felhasznalonev";
    $stid_kepek = oci_parse($conn, $sql_kepek);
    oci_bind_by_name($stid_kepek, ':felhasznalonev', $album_owner);
    oci_execute($stid_kepek);

    $sql_album_kepek = "SELECT KEP_ID FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
    $stid_album_kepek = oci_parse($conn, $sql_album_kepek);
    oci_bind_by_name($stid_album_kepek, ':album_id', $album['ID']);
    oci_execute($stid_album_kepek);

    $album_kepek = [];
    while ($row = oci_fetch_assoc($stid_album_kepek)) {
        $album_kepek[] = $row['KEP_ID'];
    }

    while ($kep = oci_fetch_assoc($stid_kepek)) {
        $checked = in_array($kep['ID'], $album_kepek) ? 'checked' : '';
        echo '<input type="checkbox" name="kepek[]" 
        value="' . $kep['ID'] . '" ' . $checked . '> ' . htmlspecialchars($kep['CIM']) . '<br>';
    }
    ?>

    <input type="submit" value="Módosítás Mentése">
</form>

</body>
</html>
