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

        <?php if (!empty($_SESSION['felhasznalo']['admin']) && $_SESSION['felhasznalo']['admin'] == 1): ?>
            <h2>Kategóriák kezelése</h2>
            <form action="<?php echo BASE_URL; ?>/pages/category.php" method="get">
                <button type="submit">Menj a Kategóriákhoz</button>
            </form>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>

</body>
</html>