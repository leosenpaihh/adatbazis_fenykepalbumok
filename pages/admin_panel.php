<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (empty($_SESSION['felhasznalo']) || $_SESSION['felhasznalo']['admin'] != 1) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

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
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <base href="<?= BASE_URL ?>">
</head>
<body>

<div class="page-container">
    <div class="wrapper">
        <h1>Admin Panel</h1>

        <?php if (!empty($_SESSION['felhasznalo']['admin'])): ?>
            <h2>Kategóriák kezelése</h2>

            <form action="<?php echo BASE_URL; ?>pages/category.php" method="get" class="location_torles">
                <button type="submit">Menj a Kategóriákhoz</button>
            </form>

            <h2>Települések kezelése</h2>

            <form action="<?php echo BASE_URL; ?>pages/location.php" method="get" class="location_torles">
                <button type="submit">Menj a Települések oldalra</button>
            </form>

            <h2>Felhasználók kezelése</h2>

            <form action="<?php echo BASE_URL; ?>controllers/admin_panel_handler.php" method="post">
                <label for="felhasznalo">Válassz felhasználót:</label>
                <select name="felhasznalo" id="felhasznalo">
                    <?php
                    $stid = oci_parse($conn, "SELECT FELHASZNALONEV, VEZETEKNEV, KERESZTNEV FROM FELHASZNALO WHERE ADMIN = 0");
                    oci_execute($stid);
                    while ($row = oci_fetch_assoc($stid)) {
                        echo "<option value='" . htmlspecialchars($row['FELHASZNALONEV']) . "'>" . htmlspecialchars($row['VEZETEKNEV']) . " " . htmlspecialchars($row['KERESZTNEV']) . "</option>";
                    }
                    ?>
                </select><br><br>

                <label for="admin_status">Admin jogok:</label>
                <select name="admin_status" id="admin_status">
                    <option value="1">Admin</option>
                    <option value="0">Nem Admin</option>
                </select><br><br>

                <input type="submit" name="update_admin" value="Admin jogok módosítása">
            </form>

            <form action="<?php echo BASE_URL; ?>controllers/admin_panel_handler.php" method="post">
                <label for="felhasznalo">Válassz felhasználót:</label>
                <select name="felhasznalo" id="felhasznalo">
                    <?php
                    $stid = oci_parse($conn, "SELECT FELHASZNALONEV, VEZETEKNEV, KERESZTNEV FROM FELHASZNALO WHERE ADMIN = 1");
                    oci_execute($stid);
                    while ($row = oci_fetch_assoc($stid)) {
                        echo "<option value='" . htmlspecialchars($row['FELHASZNALONEV']) . "'>" . htmlspecialchars($row['VEZETEKNEV']) . " " . htmlspecialchars($row['KERESZTNEV']) . "</option>";
                    }
                    ?>
                </select><br><br>

                <input type="submit" name="remove_admin" value="Admin jogok eltávolítása">
            </form>

        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>controllers/admin_panel_handler.php" method="post">
            <label for="felhasznalo">Válassz felhasználót a törléshez:</label>
            <select name="felhasznalo" id="felhasznalo_to_delete">
                <?php
                $stid = oci_parse($conn, "SELECT FELHASZNALONEV, VEZETEKNEV, KERESZTNEV FROM FELHASZNALO WHERE ADMIN = 0");
                oci_execute($stid);
                while ($row = oci_fetch_assoc($stid)) {
                    echo "<option value='" . htmlspecialchars($row['FELHASZNALONEV']) . "'>" . htmlspecialchars($row['VEZETEKNEV']) . " " . htmlspecialchars($row['KERESZTNEV']) . "</option>";
                }
                ?>
            </select><br><br>

            <input type="submit" name="delete_user" value="Felhasználó törlése">
        </form>

    </div>

    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>

</body>
</html>