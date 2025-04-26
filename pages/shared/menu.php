<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>



<nav>
    <a href="index.php">Kezdőlap</a>
    <?php if (isset($_SESSION['felhasznalo'])): ?>
        <a href="pages/category.php">Kategória létrehozása</a>
        <a href="pages/location.php">Település hozzáadása</a>
        <a href="pages/photo.php">Fénykép feltöltése</a>
        <a href="pages/album_list.php">Albumok</a>
        <a href="pages/album.php">Album létrehozása</a>
        <a href="pages/profile.php">Profil</a>
        <a href="controllers/logout.php">Kijelentkezés</a>
    <?php else: ?>
        <a href="pages/login.php">Bejelentkezés</a>
        <a href="pages/registration.php">Regisztráció</a>
    <?php endif; ?>
</nav>
