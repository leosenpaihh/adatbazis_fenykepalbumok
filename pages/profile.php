<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
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
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profil</title>
    <base href="<?php echo BASE_URL; ?>">

</head>
<body>

<div class="profile-card">
    <h1>Felhasználói adataid</h1>
    <p><strong>Felhasználónév:</strong> <?= htmlspecialchars($felhasznalo['felhasznalonev']) ?></p>
    <p><strong>Vezetéknév:</strong> <?= htmlspecialchars($felhasznalo['vezeteknev']) ?></p>
    <p><strong>Keresztnév:</strong> <?= htmlspecialchars($felhasznalo['keresztnev']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($felhasznalo['email']) ?></p>
    <p><strong>Település:</strong> <?= htmlspecialchars($felhasznalo['telepules_nev']) ?></p>
    <p><strong>Admin státusz:</strong> <?= $felhasznalo['admin'] == 1 ? 'Admin' : 'Normál felhasználó' ?></p><br>
    <a href="pages/profile_upload_photos.php" class="profile-button">Feltöltött Képek</a>
    <a href="pages/profile_created_albums.php" class="profile-button">Létrehozott Albumok</a>
</div>
<div class="page-container">
    <div class="wrapper">
        <h2>Profil módosítása</h2>

        <?php if (isset($_SESSION['hiba'])): ?>
            <p><?= $_SESSION['hiba'] ?></p>
            <?php unset($_SESSION['hiba']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            <p><?= $_SESSION['message'] ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>controllers/profile_handler.php" method="post">
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
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>
