<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav>
    <a href="index.php">
        <span class="material-symbols-outlined">home</span>Kezdőlap</a>
    <?php if (isset($_SESSION['felhasznalo'])): ?>

        <?php if (!empty($_SESSION['felhasznalo']['admin']) && $_SESSION['felhasznalo']['admin'] == 1): ?>
<!--            <a href="pages/category.php">Kategória létrehozása</a>-->
<!--            <a href="pages/location.php">Település hozzáadása</a>-->
            <a href="pages/admin_panel.php"><span class="material-symbols-outlined">admin_panel_settings</span>Admin Panel</a>
        <?php endif; ?>

        <a href="pages/photo.php"><span class="material-symbols-outlined">image_arrow_up</span>Fénykép feltöltése</a>
        <a href="pages/album_list.php"><span class="material-symbols-outlined">perm_media</span>Albumok</a>
        <a href="pages/album.php"><span class="material-symbols-outlined">library_add</span>Album létrehozása</a>
        <a href="pages/statistics.php"><span class="material-symbols-outlined">leaderboard</span>Statisztikák</a>
        <a href="pages/profile.php"><span class="material-symbols-outlined">account_circle</span>Profil</a>
        <a href="controllers/logout.php"><span class="material-symbols-outlined">logout</span>Kijelentkezés</a>
    <?php else: ?>
        <a href="pages/login.php"><span class="material-symbols-outlined">login</span>Bejelentkezés</a>
        <a href="pages/registration.php"><span class="material-symbols-outlined">how_to_reg</span>Regisztráció</a>
    <?php endif; ?>
</nav>