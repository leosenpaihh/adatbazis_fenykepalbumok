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

if (isset($_POST['edit'])) {
    $sql_check = "SELECT ID FROM FENYKEPALBUM 
                  WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :felhasznalonev";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':album_id', $album_id);
    oci_bind_by_name($stid_check, ':felhasznalonev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_execute($stid_check);
    $album = oci_fetch_assoc($stid_check);

    if (!$album) {
        $_SESSION['hiba'] = "Nincs jogosultságod ennek az albumnak a módosításához!";
        header("Location: " . BASE_URL . "pages/album_list.php");
        exit;
    }

    header("Location: " . BASE_URL . "pages/album_edit.php?album_id=" . intval($album_id));
    exit;
}

if (isset($_POST['delete'])) {
    $sql_check = "SELECT ID FROM FENYKEPALBUM 
                  WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :felhasznalonev";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':album_id', $album_id);
    oci_bind_by_name($stid_check, ':felhasznalonev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_execute($stid_check);
    $album = oci_fetch_assoc($stid_check);

    if (!$album) {
        $_SESSION['hiba'] = "Nincs jogosultságod ennek az albumnak a törléséhez!";
        header("Location: " . BASE_URL . "pages/album_list.php");
        exit;
    }

    $sql_delete_associations = "DELETE FROM KEPFENYKEPALBUM WHERE FENYKEPALBUM_ID = :album_id";
    $stid_delete_associations = oci_parse($conn, $sql_delete_associations);
    oci_bind_by_name($stid_delete_associations, ':album_id', $album_id);
    oci_execute($stid_delete_associations);

    $sql_delete_album = "DELETE FROM FENYKEPALBUM 
                         WHERE ID = :album_id AND FELHASZNALO_FELHASZNALONEV = :felhasznalonev";
    $stid_delete_album = oci_parse($conn, $sql_delete_album);
    oci_bind_by_name($stid_delete_album, ':album_id', $album_id);
    oci_bind_by_name($stid_delete_album, ':felhasznalonev', $_SESSION['felhasznalo']['felhasznalonev']);
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
