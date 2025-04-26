<?php
require_once '../includes/base.php';

session_start();
if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

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

    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kategória létrehozása</title>
    <base href="<?php echo BASE_URL; ?>">
</head>
<body>
<h1>Kategória létrehozása</h1>

<form action="controllers/category_handler.php" method="post">
    <label for="nev">Kategória neve:</label><br>
    <input type="text" id="nev" name="nev" required maxlength="100"><br>
    <input type="submit" value="Kategória létrehozása">
</form>
</body>
</html>
