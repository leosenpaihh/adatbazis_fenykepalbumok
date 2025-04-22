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
    <title>Település hozzáadása</title>
</head>
<body>
<h1>Település hozzáadása</h1>

<form action="../controllers/location_handler.php" method="post">
    <label for="orszag">Ország:</label><br>
    <input type="text" id="orszag" name="orszag" required maxlength="100"><br>

    <label for="megye">Megye:</label><br>
    <input type="text" id="megye" name="megye" required maxlength="100"><br>

    <label for="telepules">Település:</label><br>
    <input type="text" id="telepules" name="telepules" required maxlength="100"><br>

    <input type="submit" value="Helyszín hozzáadása">
</form>
</body>
</html>