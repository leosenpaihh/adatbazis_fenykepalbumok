<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

if ($_SESSION['felhasznalo']['admin'] != 1) {
    header("Location: " . BASE_URL . "index.php");
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

$telepulesek = [];

$stid = oci_parse($conn, "SELECT * FROM TELEPULES");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $telepulesek[] = $row;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script type="text/javascript" src="shared/modify.js"></script>
    <title>Település hozzáadása</title>
    <base href="<?php echo BASE_URL; ?>">
</head>
<body>
<div class="page-container">
    <div class="wrapper">
        <h1>Település hozzáadása</h1>

        <form action="<?= BASE_URL ?>controllers/location_handler.php" method="post">
            <label for="orszag">Ország:</label><br>
            <input type="text" id="orszag" name="orszag" required maxlength="100"><br>

            <label for="megye">Megye:</label><br>
            <input type="text" id="megye" name="megye" required maxlength="100"><br>

            <label for="telepules">Település:</label><br>
            <input type="text" id="telepules" name="telepules" required maxlength="100"><br>

            <input type="submit" name="letrehozas" value="Helyszín hozzáadása">
        </form>
        <h2>Helyszínek</h2>
        <table>
            <thead>
            <tr>
                <!--        <th>ID</th>-->
                <th>Ország</th>
                <th>Megye</th>
                <th>Település</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($telepulesek) > 0): ?>
                <?php foreach ($telepulesek as $telepules): ?>
                    <tr>
                        <!--                <td>--><?php //= htmlspecialchars($telepules['ID']) ?><!--</td>-->
                        <td><?= htmlspecialchars($telepules['ORSZAG']) ?></td>
                        <td><?= htmlspecialchars($telepules['MEGYE']) ?></td>
                        <td><?= htmlspecialchars($telepules['TELEPULES']) ?></td>
                        <td>
                            <button onclick='modify(
                            <?= json_encode($telepules) ?>,
                                    "modositas_id", "eredeti_id", "modositas_orszag", "eredeti_orszag", "modositas_megye", "eredeti_megye", "modositas_telepules", "eredeti_telepules"
                                    )'>
                                Módosítás
                            </button>
                        </td>
                        <td>
                            <form action="<?= BASE_URL ?>controllers/location_handler.php" method="post" class="location_torles">
                                <input type="hidden" id="id" name="id" value='<?= $telepules["ID"] ?>'>

                                <input type="submit" name="torles" value="Törlés">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nincsenek helyszínek.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <hr />
        <div id="modositas_form" style="display: none">
            <form action="<?= BASE_URL ?>controllers/location_handler.php" method="post">
                <label for="nev">Módosítsd a települést:</label><br>
                <input type="text" id="modositas_id" name="id" required maxlength="100">
                <input type="hidden" id="eredeti_id" name="eredeti_id" value="">
                <input type="text" id="modositas_orszag" name="orszag" required maxlength="100">
                <input type="hidden" id="eredeti_orszag" name="eredeti_orszag" value="">
                <input type="text" id="modositas_megye" name="megye" required maxlength="100">
                <input type="hidden" id="eredeti_megye" name="eredeti_megye" value="">
                <input type="text" id="modositas_telepules" name="telepules" required maxlength="100">
                <input type="hidden" id="eredeti_telepules" name="eredeti_telepules" value="">

                <input type="submit" name="modositas" value="Módosítás mentése">
            </form>
            <button name="megse" onclick="closeModificationForm()">Mégse</button>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>