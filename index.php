<?php
session_start();
include __DIR__ . '/pages/shared/menu.php';
if (!isset($_SESSION['felhasznalo'])) {
    header("Location: pages/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Főoldal</title>
</head>
<body>
    <h1>
        Üdv, <?= htmlspecialchars($_SESSION['felhasznalo']['keresztnev']) ?>!
    </h1>
</body>
</html>