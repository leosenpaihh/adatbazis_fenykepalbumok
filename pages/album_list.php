<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$felhasznalo_felhasznalonev = $_SESSION['felhasznalo']['felhasznalonev'];

$sql = "SELECT * FROM FENYKEPALBUM WHERE FELHASZNALO_FELHASZNALONEV = :felhasznalonev ORDER BY LETREHOZASI_DATUM DESC";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':felhasznalonev', $felhasznalo_felhasznalonev);
oci_execute($stid);

if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
if (isset($_SESSION['hiba'])) {
    echo "<p>" . $_SESSION['hiba'] . "</p>";
    unset($_SESSION['hiba']);
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/css.css">

    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Albumok</title>
    <base href="<?php echo BASE_URL; ?>">

</head>
<body>
<h1>Albumok</h1>

<table>
    <tr>
        <th>Album Cím</th>
        <th>Leírás</th>
        <th>Műveletek</th>
    </tr>
    <?php while ($row = oci_fetch_assoc($stid)): ?>
        <tr>
            <td><?= htmlspecialchars($row['NEV']) ?></td>
            <td> <?php
                $leiras = oci_lob_read($row['LEIRAS'], $row['LEIRAS']->size());
                echo htmlspecialchars($leiras);
                ?>
            </td>
            <td>
                <a href="<?php echo BASE_URL; ?>pages/album_edit.php?album_id=<?= $row['ID'] ?>">Módosítás</a>
                <form class="delete-form" action="<?php echo BASE_URL; ?>controllers/album_list_handler.php" method="post"
                    onsubmit="
                      return confirm('Biztosan törölni szeretnéd ezt az albumot? Ez a művelet nem visszavonható!');">
                    <input type="hidden" name="album_id" value="<?= $row['ID'] ?>">
                    <button type="submit" class="delete-btn">Törlés</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>