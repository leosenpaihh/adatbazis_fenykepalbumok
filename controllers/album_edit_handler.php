<?php
require_once '../includes/base.php';
session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$album_id = $_POST['album_id'] ?? null;
$album_owner = $_POST['album_owner'] ?? null;
$album_nev = $_POST['album_nev'] ?? null;
$album_leiras = $_POST['album_leiras'] ?? null;
$kepek = $_POST['kepek'] ?? [];

if (!$album_id || !$album_nev || !$album_owner) {
    $_SESSION['hiba'] = "Minden mezőt ki kell tölteni!";
    header("Location: " . BASE_URL . "pages/album_edit.php?album_id=" . $album_id);
    exit;
}

$sql_check = "SELECT * FROM FENYKEPALBUM WHERE ID = :album_id AND (FELHASZNALO_FELHASZNALONEV = :felhasznalonev OR :admin = 1)";
$stid_check = oci_parse($conn, $sql_check);
oci_bind_by_name($stid_check, ':album_id', $album_id);
oci_bind_by_name($stid_check, ':felhasznalonev', $album_owner);
oci_bind_by_name($stid_check, ':admin', $_SESSION['felhasznalo']['admin']);
oci_execute($stid_check);
$album = oci_fetch_assoc($stid_check);

if (!$album) {
    $_SESSION['hiba'] = "Nincs jogosultságod ennek az albumnak a módosítására!";
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

$sql_update = "UPDATE FENYKEPALBUM
               SET NEV = :album_nev, LEIRAS = :album_leiras
               WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :felhasznalonev";
$stid_update = oci_parse($conn, $sql_update);
oci_bind_by_name($stid_update, ':album_nev', $album_nev);
oci_bind_by_name($stid_update, ':album_leiras', $album_leiras);
oci_bind_by_name($stid_update, ':album_id', $album_id);
oci_bind_by_name($stid_update, ':felhasznalonev', $album_owner);

if (oci_execute($stid_update)) {
    $sql_existing = "SELECT KEP_ID FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
    $stid_existing = oci_parse($conn, $sql_existing);
    oci_bind_by_name($stid_existing, ':album_id', $album_id);
    oci_execute($stid_existing);

    $existing_images = [];
    while ($row = oci_fetch_assoc($stid_existing)) {
        $existing_images[] = $row['KEP_ID'];
    }

    $images_to_delete = array_diff($existing_images, $kepek);
    if (!empty($images_to_delete)) {
        foreach ($images_to_delete as $kep_id) {
            $sql_delete = "DELETE FROM KEPFENYKEPALBUM WHERE KEP_ID = :kep_id AND FENYKEPALBUM_ID = :album_id";
            $stid_delete = oci_parse($conn, $sql_delete);
            oci_bind_by_name($stid_delete, ':kep_id', $kep_id);
            oci_bind_by_name($stid_delete, ':album_id', $album_id);
            oci_execute($stid_delete);
        }
    }

    $images_to_add = array_diff($kepek, $existing_images);
    if (!empty($images_to_add)) {
        foreach ($images_to_add as $kep_id) {
            $sql_max = "SELECT MAX(SORSZAM) AS MAX_SORSZAM FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
            $stid_max = oci_parse($conn, $sql_max);
            oci_bind_by_name($stid_max, ':album_id', $album_id);
            oci_execute($stid_max);
            $max_row = oci_fetch_assoc($stid_max);
            $sorszam = ($max_row['MAX_SORSZAM'] !== null) ? $max_row['MAX_SORSZAM'] + 1 : 1;

            $sql_link_image = "
                INSERT INTO KEPFENYKEPALBUM (KEP_ID, FENYKEPALBUM_ID, SORSZAM) VALUES (:kep_id, :album_id, :sorszam)
            ";
            $stid_link_image = oci_parse($conn, $sql_link_image);
            oci_bind_by_name($stid_link_image, ':kep_id', $kep_id);
            oci_bind_by_name($stid_link_image, ':album_id', $album_id);
            oci_bind_by_name($stid_link_image, ':sorszam', $sorszam);
            oci_execute($stid_link_image);
        }
    }

    $_SESSION['message'] = "Album sikeresen módosítva!";
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
} else {
    $_SESSION['hiba'] = "Hiba történt az album módosítása során!";
    header("Location: " . BASE_URL . "pages/album_edit.php?album_id=" . intval($album_id) . "&album_owner=" . htmlspecialchars($album_owner));
    exit;
}