<?php
require_once '../includes/base.php';
session_start();
include('../includes/db.php');

if (!isset($_POST['album_id'])) {
    $_SESSION['hiba'] = "Érvénytelen album azonosító!";
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

$album_id = $_POST['album_id'];
$album_owner = $_POST['album_owner'];

$sql_check = "SELECT ID, FELHASZNALO_FELHASZNALONEV FROM FENYKEPALBUM WHERE ID = :album_id";
$stid_check = oci_parse($conn, $sql_check);
oci_bind_by_name($stid_check, ':album_id', $album_id);
oci_execute($stid_check);
$album = oci_fetch_assoc($stid_check);

if (!$album) {
    $_SESSION['hiba'] = "Az album nem található!";
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

if (strtolower($album['FELHASZNALO_FELHASZNALONEV']) !== strtolower($_SESSION['felhasznalo']['felhasznalonev']) && $_SESSION['felhasznalo']['admin'] != 1) {
    $_SESSION['hiba'] = "Nincs jogosultságod ennek az albumnak a módosításához vagy törléséhez!";
    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

if (isset($_POST['edit'])) {
    header("Location: " . BASE_URL . "pages/album_edit.php?album_id=" . intval($album_id) . "&album_owner=" . htmlspecialchars($album_owner));
    exit;
}

if (isset($_POST['delete'])) {
    $sql_delete_associations = "DELETE FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
    $stid_delete_associations = oci_parse($conn, $sql_delete_associations);
    oci_bind_by_name($stid_delete_associations, ':album_id', $album_id);
    oci_execute($stid_delete_associations);

    $sql_delete_album = "DELETE FROM FENYKEPALBUM WHERE ID = :album_id";
    $stid_delete_album = oci_parse($conn, $sql_delete_album);
    oci_bind_by_name($stid_delete_album, ':album_id', $album_id);
    $result = oci_execute($stid_delete_album);

    if ($result) {
        $_SESSION['message'] = "Album sikeresen törölve!";
    } else {
        $_SESSION['hiba'] = "Hiba történt az album törlése során!";
    }

    header("Location: " . BASE_URL . "pages/album_list.php");
    exit;
}

header("Location: " . BASE_URL . "pages/album_list.php");
exit;