<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

$telepulesek = [];
$regi = $_SESSION['urlap_adatok'] ?? [];
$stid = oci_parse($conn, "SELECT id, TELEPULES AS nev FROM TELEPULES ORDER BY TELEPULES");
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
    $telepulesek[] = $row;
}

$hiba = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
unset($_SESSION['urlap_adatok']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <base href="<?php echo BASE_URL; ?>">
</head>
<body>
<h1>Regisztráció</h1>

<?php if ($hiba): ?>
    <p><?= htmlspecialchars($hiba) ?></p>
<?php endif; ?>

<form action="controllers/registration_handler.php" method="post">
    <label for="vezeteknev">Vezetéknév:</label><br>
    <input type="text" id="vezeteknev" name="vezeteknev" required minlength="2" maxlength="50"
           value="<?= htmlspecialchars($regi['vezeteknev'] ?? '') ?>"><br>

    <label for="keresztnev">Keresztnév:</label><br>
    <input type="text" id="keresztnev" name="keresztnev" required minlength="2" maxlength="50"
           value="<?= htmlspecialchars($regi['keresztnev'] ?? '') ?>"><br>

    <label for="felhasznalonev">Felhasználónév:</label><br>
    <input type="text" id="felhasznalonev" name="felhasznalonev" required maxlength="50"
           value="<?= htmlspecialchars($regi['felhasznalonev'] ?? '') ?>"><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required maxlength="100"
           value="<?= htmlspecialchars($regi['email'] ?? '') ?>"><br>

    <label for="telepules">Település:</label><br>
    <select name="telepules" id="telepules" required>
        <option value="">Válassz települést</option>
        <?php foreach ($telepulesek as $telepules): ?>
            <option value="<?= $telepules['ID'] ?>"
                <?= (isset($regi['telepules']) && $regi['telepules'] == $telepules['ID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($telepules['NEV']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label for="jelszo">Jelszó:</label><br>
    <input type="password" id="jelszo" name="jelszo" required minlength="6" maxlength="255"><br>

    <label for="confirm-jelszo">Jelszó megerősítése:</label><br>
    <input type="password" id="confirm-jelszo" name="confirm-jelszo" required minlength="6" maxlength="255"><br>

    <input type="submit" value="Regisztráció">
</form>
</body>
</html>