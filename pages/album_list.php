<?php
require_once '../includes/base.php';
session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$sql = "SELECT * FROM FENYKEPALBUM ORDER BY letrehozasi_datum DESC";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

if ($stid === false) {
    $error = oci_error($conn);
    echo "<p>Hiba történt a lekérdezés végrehajtása közben: " . $error['message'] . "</p>";
    exit;
}

if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
if (isset($_SESSION['hiba'])) {
    echo "<p>" . $_SESSION['hiba'] . "</p>";
    unset($_SESSION['hiba']);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Albumok</title>
    <base href="<?php echo BASE_URL; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="icon" href="<?php echo BASE_URL; ?>styles/favicon.ico" type="image/ico">
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

<div class="page-container">
    <div class="wrapper">
        <h1>Albumok</h1>
        <div class="album-container">
            <?php
            $album_found = false;
            while ($row = oci_fetch_assoc($stid)):
                $album_found = true;
                $album_id = $row['ID'];

//    FUGGVENYKENT MEGVALOSITVA    |
//    ------------------------     v
//    img_count_in_albums.sql
//
//                $sql_fenykepek = "SELECT COUNT(*) AS fenykepek_szama FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
//                $stid_fenykepek = oci_parse($conn, $sql_fenykepek);
//                oci_bind_by_name($stid_fenykepek, ':album_id', $album_id);
//                oci_execute($stid_fenykepek);
//                $fenykepek_row = oci_fetch_assoc($stid_fenykepek);
//                $fenykepek_szama = $fenykepek_row['FENYKEPEK_SZAMA'];
                $sql_fuggveny = "BEGIN :szam := img_count_in_albums(:album_id); END;";
                $stid_fuggveny = oci_parse($conn, $sql_fuggveny);

                oci_bind_by_name($stid_fuggveny, ':album_id', $album_id);
                oci_bind_by_name($stid_fuggveny, ':szam', $fenykepek_szama, 32);

                oci_execute($stid_fuggveny);


                $sql_images = "SELECT k.id, k.cim 
                               FROM Kep k
                               INNER JOIN KEPFENYKEPALBUM kfa ON k.id = kfa.KEP_ID
                               WHERE kfa.FENYKEPALBUM_ID = :album_id
                               ORDER BY kfa.SORSZAM";
                $stid_images = oci_parse($conn, $sql_images);
                oci_bind_by_name($stid_images, ':album_id', $album_id);
                oci_execute($stid_images);
                ?>
                <div class="album-box">
                    <div class="album-title"><?= htmlspecialchars($row['NEV']) ?: 'Nincs cím' ?></div>
                    <div class="album-description">
                        <?php
                        $leiras = '';
                        if (isset($row['LEIRAS']) && !is_null($row['LEIRAS'])) {
                            $leiras = oci_lob_read($row['LEIRAS'], $row['LEIRAS']->size());
                        }
                        if (strlen($leiras) > 100) {
                            $leiras_rovid = htmlspecialchars(mb_substr($leiras, 0, 100)) . "...";
                            echo $leiras_rovid;
                            ?>
                            <a href="pages/album_preview.php?album_id=<?= htmlspecialchars($album_id) ?>">Tovább</a>
                            <?php
                        } else {
                            echo htmlspecialchars($leiras) ?: 'Nincs leírás';
                        }
                        ?>
                    </div>
                    <p><strong>Fényképek száma:</strong> <?= $fenykepek_szama ?></p>

                    <div class="album-images">
                        <?php
                        $count = 0;
                        while (($img = oci_fetch_assoc($stid_images)) && ($count < 4)) :
                            $count++;
                            ?>
                            <a href="pages/album_preview.php?album_id=<?= htmlspecialchars($album_id) ?>">
                                <img src="controllers/show_image.php?image_id=<?= htmlspecialchars($img['ID']) ?>"
                                     alt="<?= htmlspecialchars($img['CIM']) ?>" class="album-image">
                            </a>
                        <?php endwhile; ?>
                    </div>

                    <div class="album-footer">
                        <p>Készítette: <?= htmlspecialchars($row['FELHASZNALO_FELHASZNALONEV']) ?></p>
                        <?php
                        if (strtolower($row['FELHASZNALO_FELHASZNALONEV']) === strtolower($_SESSION['felhasznalo']['felhasznalonev']) || $_SESSION['felhasznalo']['admin'] == 1):
                            ?>
                            <form action="<?php echo BASE_URL; ?>controllers/album_list_handler.php" method="post"
                                  style="display:inline-block;" class="location_torles">
                                <input type="hidden" name="album_owner"
                                       value="<?= htmlspecialchars($row['FELHASZNALO_FELHASZNALONEV']) ?>">
                                <input type="hidden" name="album_id" value="<?= htmlspecialchars($row['ID']) ?>">
                                <button type="submit" name="edit" class="edit-button">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                            </form>
                            <form action="<?php echo BASE_URL; ?>controllers/album_list_handler.php" method="post"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Biztosan törölni szeretnéd ezt az albumot? Ez a művelet nem visszavonható!');"
                                  class="location_torles">
                                <input type="hidden" name="album_id" value="<?= htmlspecialchars($row['ID']) ?>">
                                <button type="submit" name="delete" class="delete-button">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (!$album_found): ?>
                <p>Nem találtunk albumot.</p>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>
</body>
</html>
