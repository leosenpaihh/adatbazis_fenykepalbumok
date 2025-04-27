<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';

$hiba = $_SESSION['hiba'] ?? null;
unset($_SESSION['hiba']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <base href="<?php echo BASE_URL; ?>">
</head>
<body>
<h1>Bejelentkezés</h1>

<?php if ($hiba): ?>
    <p><?= htmlspecialchars($hiba) ?></p>
<?php endif; ?>

<form action="controllers/login_handler.php" method="post">
    <label for="username">Felhasználónév:</label><br>
    <input type="text" id="username" name="felhasznalonev" required minlength="2" maxlength="50"><br>

    <label for="password">Jelszó:</label><br>
    <input type="password" id="password" name="jelszo" required minlength="6" maxlength="255"><br>

    <input type="submit" value="Bejelentkezés">
</form>
</body>
</html>