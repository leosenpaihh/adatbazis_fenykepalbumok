<?php
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: pages/login.php");
    exit;
}

$felhasznalo = $_SESSION['felhasznalo'];

$telepulesek = [];
$stid = oci_parse($conn, "SELECT id, TELEPULES AS nev FROM TELEPULES ORDER BY TELEPULES");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $telepulesek[] = $row;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profil</title>
</head>
<body>
<h1>Felhasználói adataid</h1>

<p>Felhasználónév: <?= htmlspecialchars($felhasznalo['felhasznalonev']) ?></p>
<p>Vezetéknév: <?= htmlspecialchars($felhasznalo['vezeteknev']) ?></p>
<p>Keresztnév: <?= htmlspecialchars($felhasznalo['keresztnev']) ?></p>
<p>Email: <?= htmlspecialchars($felhasznalo['email']) ?></p>
<p>Település: <?= htmlspecialchars($felhasznalo['telepules_nev']) ?></p>
<p>Admin státusz: <?= $felhasznalo['admin'] == 1 ? 'Admin' : 'Normál felhasználó' ?></p>

<h2>Profil módosítása</h2>

<?php if (isset($_SESSION['hiba'])): ?>
    <p><?= $_SESSION['hiba'] ?></p>
    <?php unset($_SESSION['hiba']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['message'])): ?>
    <p><?= $_SESSION['message'] ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<form action="../controllers/profile_handler.php" method="post">
    <label for="felhasznalonev">Felhasználónév:</label><br>
    <input type="text" id="felhasznalonev" name="felhasznalonev" value="<?= htmlspecialchars($felhasznalo['felhasznalonev']) ?>" required maxlength="50"><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($felhasznalo['email']) ?>" required maxlength="100"><br>

    <label for="telepules">Település:</label><br>
    <select name="telepules" id="telepules" required>
        <option value="">Válassz települést</option>
        <?php foreach ($telepulesek as $telepules): ?>
            <option value="<?= $telepules['ID'] ?>" <?= ($felhasznalo['telepules_id'] == $telepules['ID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($telepules['NEV']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label for="jelszo">Új jelszó:</label><br>
    <input type="password" id="jelszo" name="jelszo" minlength="6" maxlength="255"><br>

    <label for="confirm-jelszo">Jelszó megerősítése:</label><br>
    <input type="password" id="confirm-jelszo" name="confirm-jelszo" minlength="6" maxlength="255"><br>

    <input type="submit" value="Profil frissítése">
</form>
</body>
</html>
