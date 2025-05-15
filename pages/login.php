<?php
require_once '../includes/base.php';
session_start();
$hiba = $_SESSION['hiba'] ?? null;
unset($_SESSION['hiba']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
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