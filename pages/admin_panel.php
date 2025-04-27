<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (empty($_SESSION['felhasznalo']) || $_SESSION['felhasznalo']['admin'] != 1) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <base href="<?= BASE_URL ?>">
</head>
<body>

<div class="page-container">
    <div class="wrapper">
        <h1>Admin Panel</h1>

        <?php if (!empty($_SESSION['felhasznalo']['admin'])): ?>
            <?php
            if (isset($_SESSION['hiba'])) {
                echo "<p>" . $_SESSION['hiba'] . "</p>";
                unset($_SESSION['hiba']);
            }

            if (isset($_SESSION['message'])) {
                echo "<p>" . $_SESSION['message'] . "</p>";
                unset($_SESSION['message']);
            }
            ?>

            <h2>Kategória létrehozása</h2>
            <form action="controllers/category_handler.php" method="post">
                <label for="nev">Kategória neve:</label><br>
                <input type="text" id="nev" name="nev" required maxlength="100"><br><br>
                <input type="submit" value="Kategória létrehozása">
            </form>

        <?php else: ?>
            <p>Nincs jogosultságod az admin funkciók eléréséhez.</p>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>

</body>
</html>