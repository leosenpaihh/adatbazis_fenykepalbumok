<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['album_id'])) {
    $album_id = $_POST['album_id'];

    if (isset($_POST['edit'])) {
        $sql_check = "SELECT ID FROM FENYKEPALBUM 
                      WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :username";
        $stid_check = oci_parse($conn, $sql_check);
        oci_bind_by_name($stid_check, ':album_id', $album_id);
        oci_bind_by_name($stid_check, ':username', $_SESSION['felhasznalo']['felhasznalonev']);
        oci_execute($stid_check);
        $album = oci_fetch_assoc($stid_check);
        if (!$album) {
            $_SESSION['hiba'] = "Nincs jogosultságod ennek az albumnak a szerkesztéséhez!";
            header("Location: " . BASE_URL . "pages/profile_created_albums.php");
            exit;
        }
        header("Location: " . BASE_URL . "pages/album_edit.php?album_id=" . intval($album_id));
        exit;
    }

    if (isset($_POST['delete'])) {
        $sql_check = "SELECT ID FROM FENYKEPALBUM 
                      WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :username";
        $stid_check = oci_parse($conn, $sql_check);
        oci_bind_by_name($stid_check, ':album_id', $album_id);
        oci_bind_by_name($stid_check, ':username', $_SESSION['felhasznalo']['felhasznalonev']);
        oci_execute($stid_check);
        $album = oci_fetch_assoc($stid_check);
        if (!$album) {
            $_SESSION['hiba'] = "Nincs jogosultságod ennek az albumnak a törléséhez!";
            header("Location: " . BASE_URL . "pages/profile_created_albums.php");
            exit;
        }
        $sql_delete_associations = "DELETE FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
        $stid_delete_associations = oci_parse($conn, $sql_delete_associations);
        oci_bind_by_name($stid_delete_associations, ':album_id', $album_id);
        oci_execute($stid_delete_associations);
        $sql_delete_album = "DELETE FROM FENYKEPALBUM 
                             WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :username";
        $stid_delete_album = oci_parse($conn, $sql_delete_album);
        oci_bind_by_name($stid_delete_album, ':album_id', $album_id);
        oci_bind_by_name($stid_delete_album, ':username', $_SESSION['felhasznalo']['felhasznalonev']);
        $result = oci_execute($stid_delete_album);
        if ($result) {
            $_SESSION['message'] = "Album sikeresen törölve!";
        } else {
            $_SESSION['hiba'] = "Hiba történt az album törlése során!";
        }
        header("Location: " . BASE_URL . "pages/profile_created_albums.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Saját Albumjaim</title>
    <link rel="stylesheet" href="../styles/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../styles/favicon.ico" type="image/ico">
    <base href="<?= BASE_URL ?>">
</head>
<body>
<div class="page-container">
    <div class="wrapper">
        <h1>Saját Albumjaim</h1>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p class='message'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['hiba'])) {
            echo "<p class='message'>" . $_SESSION['hiba'] . "</p>";
            unset($_SESSION['hiba']);
        }

        $username = $_SESSION['felhasznalo']['felhasznalonev'];
        $sql = "SELECT * FROM FENYKEPALBUM 
                WHERE FELHASZNALO_FELHASZNALONEV = :username
                ORDER BY letrehozasi_datum DESC";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':username', $username);
        oci_execute($stid);
        ?>
        <div class="album-container">
            <?php
            $album_found = false;
            while ($row = oci_fetch_assoc($stid)):
                $album_found = true;
                $album_id = $row['ID'];
                $sql_fenykepek = "SELECT COUNT(*) AS fenykepek_szama 
                                  FROM KEPFENYKEPALBUM 
                                  WHERE FENYKEPALBUM_ID = :album_id";
                $stid_fenykepek = oci_parse($conn, $sql_fenykepek);
                oci_bind_by_name($stid_fenykepek, ':album_id', $album_id);
                oci_execute($stid_fenykepek);
                $fenykepek_row = oci_fetch_assoc($stid_fenykepek);
                $fenykepek_szama = $fenykepek_row['FENYKEPEK_SZAMA'];

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
                                <input type="hidden" name="album_owner" value="<?= htmlspecialchars($row['FELHASZNALO_FELHASZNALONEV']) ?>">
                                <input type="hidden" name="album_id" value="<?= htmlspecialchars($row['ID']) ?>">
                                <input type="submit" name="edit" value="Módosítás">
                            </form>
                            <form action="<?php echo BASE_URL; ?>controllers/album_list_handler.php" method="post"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Biztosan törölni szeretnéd ezt az albumot? Ez a művelet nem visszavonható!');"
                                  class="location_torles">
                                <input type="hidden" name="album_id" value="<?= htmlspecialchars($row['ID']) ?>">
                                <input type="submit" name="delete" value="Törlés">
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
