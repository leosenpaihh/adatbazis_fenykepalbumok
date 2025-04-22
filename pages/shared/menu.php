<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav>
    <?php if (isset($_SESSION['felhasznalo'])): ?>
        <a href="../../index.php">Kezdőlap</a>
        <a href="/pages/category.php">Kategória létrehozása</a>
        <a href="/pages/location.php">Település hozzáadása</a>
        <a href="/pages/photo.php">Fénykép Feltöltése</a>
        <a href="/pages/profile.php">Profil</a>
        <a href="../../controllers/logout.php">Kijelentkezés</a>
    <?php else: ?>
        <a href="/pages/login.php">Bejelentkezés</a>
        <a href="/pages/registration.php">Regisztráció</a>
    <?php endif; ?>
</nav>