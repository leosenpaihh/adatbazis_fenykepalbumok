<?php
require_once '../includes/base.php';
session_start();
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

$stid = oci_parse($conn, "SELECT * FROM KATEGORIA");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $kategoriak[] = $row;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script type="text/javascript" src="category.js"></script>
    <title>Kategória létrehozása</title>
    <base href="<?php echo BASE_URL; ?>">
</head>
<body>

<?php
include __DIR__ . '/shared/menu.php';
?>
<button class="menu-toggle" onclick="toggleMenu()">☰ Menü</button>
<script>
    function toggleMenu() {
        let nav = document.querySelector('nav');
        nav.classList.toggle('active');
    }
</script>

<h1>Kategória létrehozása</h1>

<form action="<?php echo BASE_URL; ?>controllers/category_handler.php" method="post">
    <label for="nev">Kategória neve:</label><br>
    <input type="text" id="nev" name="nev" required maxlength="100"><br>
    <input type="submit" name="letrehozas" value="Kategória létrehozása">
</form>





<div class="page-container">
    <div class="wrapper">
        <h2>Létrehozott kategóriák</h2>
        <table class="location-table">
            <thead>
            <tr>
                <th>Név</th>
                <th>Módosítás</th>
                <th>Törlés</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($kategoriak) > 0): ?>
                <?php foreach ($kategoriak as $kategoria): ?>
                    <tr>
                        <td><?= htmlspecialchars($kategoria['NEV']) ?></td>
                        <td>
                            <button type="button" onclick='modifyCategory(<?= json_encode($kategoria["NEV"]) ?>)'>
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                        </td>
                        <td>
                            <form action="<?php echo BASE_URL; ?>controllers/category_handler.php" method="post" class="location_torles">
                                <input type="hidden" name="nev" value='<?= $kategoria["NEV"] ?>'>
                                <button type="submit" name="torles">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nincsenek kategóriák.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <hr />
        <div id="modositas_form" style="display: none">
            <form action="<?php echo BASE_URL; ?>controllers/category_handler.php" method="post">
                <label for="nev">Módosítsd a kategória nevét:</label><br>
                <input type="text" id="modositas_nev" name="nev" required maxlength="100"><br>
                <input type="hidden" id="eredeti_nev" name="eredeti_nev" value="">
                <input type="submit" name="modositas" value="Módosítás mentése">
                <button type="button" name="megse" onclick="closeModificationForm()">Mégse</button>
            </form>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>
