<?php
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: pages/login.php");
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
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Fénykép Feltöltése</title>
</head>
<body>
<h1>Fénykép Feltöltése</h1>

<form action="../controllers/photo_handler.php" method="post" enctype="multipart/form-data">
    <label for="cim">Cím:</label><br>
    <input type="text" id="cim" name="cim" required maxlength="255"><br>

    <label for="leiras">Leírás:</label><br>
    <textarea id="leiras" name="leiras" rows="4" cols="50"></textarea><br>

    <label for="telepules">Település:</label><br>
    <select name="telepules" id="telepules" required>
        <option value="">Válassz települést</option>
        <?php
        $telepulesek = [];
        $stid = oci_parse($conn, "SELECT ID, TELEPULES FROM TELEPULES ORDER BY TELEPULES");
        oci_execute($stid);
        while ($row = oci_fetch_assoc($stid)) {
            $telepulesek[] = $row;
        }
        foreach ($telepulesek as $telepules) {
            echo "<option value=\"{$telepules['ID']}\">" . htmlspecialchars($telepules['TELEPULES']) . "</option>";
        }
        ?>
    </select><br>

    <label>Kategóriák:</label><br>
    <?php
    $stid = oci_parse($conn, "SELECT NEV FROM KATEGORIA ORDER BY NEV");
    oci_execute($stid);
    while ($row = oci_fetch_assoc($stid)) {
        $kategoria_nev = htmlspecialchars($row['NEV']);
        echo "<input type='checkbox' name='kategoriak[]' value='$kategoria_nev'> $kategoria_nev<br>";
    }
    ?>

    <label for="foto">Fénykép feltöltése:</label><br>
    <input type="file" id="foto" name="foto" accept="image/*" required><br>

    <input type="submit" value="Feltöltés">
</form>
</body>
</html>