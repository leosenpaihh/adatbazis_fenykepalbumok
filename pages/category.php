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

$kategoriak = [];

$stid = oci_parse($conn, "SELECT * FROM KATEGORIA");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $kategoriak[] = $row;
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script type="text/javascript" src="category.js"></script>
    <title>Kategória létrehozása</title>
</head>
<body>
<h1>Kategória létrehozása</h1>

<form action="../controllers/category_handler.php" method="post">
    <label for="nev">Kategória neve:</label><br>
    <input type="text" id="nev" name="nev" required maxlength="100"><br>

    <input type="submit" name="letrehozas" value="Kategória létrehozása">
</form>
<h2>Létrehozott kategóriák</h2>
<table>
    <thead>
        <tr>
            <th>Név</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($kategoriak) > 0): ?>
            <?php foreach ($kategoriak as $kategoria): ?>
                <tr>
                    <td><?= htmlspecialchars($kategoria['NEV']) ?></td>
                    <td>
                        <button onclick='modifyCategory(<?= json_encode($kategoria["NEV"]) ?>)'>
                            Módosítás
                        </button>
                    </td>
                    <td>
                        <form action="../controllers/category_handler.php" method="post">
                            <input type="hidden" id="nev" name="nev" value='<?= $kategoria["NEV"] ?>'>

                            <input type="submit" name="torles" value="Törlés">
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
<form action="../controllers/category_handler.php" method="post">
    <label for="nev">Módosítsd a kategória nevét:</label><br>
    <input type="text" id="modositas_nev" name="nev" required maxlength="100"><br>
    <input type="hidden" id="eredeti_nev" name="eredeti_nev" value="">

    <input type="submit" name="modositas" value="Módosítás mentése">
</form>
<button name="megse" onclick="closeModificationForm()">Mégse</button>
</div>
</body>
</html>
