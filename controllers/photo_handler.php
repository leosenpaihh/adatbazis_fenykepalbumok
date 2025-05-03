<?php
require_once '../includes/base.php';

session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/photo.php");
    exit;
}

$cim = $_POST['cim'];
$leiras = $_POST['leiras'];
$telepules_id = $_POST['telepules'];
$kategoriak = $_POST['kategoriak'] ?? [];

if ($_FILES['foto']['error'] == UPLOAD_ERR_OK) {
    $foto_temp = $_FILES['foto']['tmp_name'];
    $foto_type = $_FILES['foto']['type'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    if (!in_array($foto_type, $allowed_types)) {
        $_SESSION['hiba'] = "Csak képfájlok (.jpg, .png, .gif) tölthetők fel!";
        header("Location: " . BASE_URL . "pages/photo.php");
        exit;
    }

    $foto_data = file_get_contents($foto_temp);

//    ELJARASKENT MEGVALOSITVA    |
//    ------------------------    v
//    new_photo_upload.sql
//
//    $sql = "INSERT INTO KEP (CIM, LEIRAS, FELHASZNALO_FELHASZNALONEV, TELEPULES_ID)
//            VALUES (:cim, :leiras, :felhasznalonev, :telepules_id)
//            RETURNING ID INTO :id";

    $sql = "BEGIN new_photo_upload(:cim, :leiras, :binaris, :felhasznalo, :telepules_id, :uj_id); END;";
    $stid = oci_parse($conn, $sql);

    oci_bind_by_name($stid, ':cim', $cim);
    oci_bind_by_name($stid, ':leiras', $leiras);
    oci_bind_by_name($stid, ':felhasznalo', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_bind_by_name($stid, ':telepules_id', $telepules_id);

    $lob = oci_new_descriptor($conn, OCI_D_LOB);
    $lob->writeTemporary($foto_data, OCI_TEMP_BLOB);
    oci_bind_by_name($stid, ':binaris', $lob, -1, OCI_B_BLOB);

    $uj_id = 0;
//    oci_bind_by_name($stid, ':id', $id, 32);
    oci_bind_by_name($stid, ':uj_id', $uj_id, 32);

    if (oci_execute($stid)) {
//        $lob = oci_new_descriptor($conn, OCI_D_LOB);
//        $update_sql = "UPDATE KEP SET KEP_BINARIS = EMPTY_BLOB() WHERE ID = :id RETURNING KEP_BINARIS INTO :blob";
//
//        $update_stmt = oci_parse($conn, $update_sql);
//        oci_bind_by_name($update_stmt, ':id', $id);
//        oci_bind_by_name($update_stmt, ':blob', $lob, -1, OCI_B_BLOB);

//        if (oci_execute($update_stmt, OCI_DEFAULT)) {
//
//        if ($lob->save($foto_data)) {
//            oci_commit($conn);

        foreach ($kategoriak as $kategoria_nev) {
            $kat_sql = "INSERT INTO KEPKATEGORIA (KEP_ID, KATEGORIA_NEV) VALUES (:kep_id, :kategoria_nev)";
            $kat_stmt = oci_parse($conn, $kat_sql);
            oci_bind_by_name($kat_stmt, ':kep_id', $uj_id);
            oci_bind_by_name($kat_stmt, ':kategoria_nev', $kategoria_nev);
            oci_execute($kat_stmt);
        }

        $lob->free();
        $_SESSION['message'] = "A fénykép sikeresen feltöltve!";
//            header("Location: " . BASE_URL . "pages/photo.php");
//            exit;
//        }
//        }

//        if (isset($lob)) {
//            $lob->free();
//        }
//        $_SESSION['hiba'] = "Hiba történt a fénykép feltöltése során!";
    } else {
        $error = oci_error($stid);
        $_SESSION['hiba'] = "Hiba történt a fénykép feltöltése során: " . $error['message'];
    }
} else {
    $_SESSION['hiba'] = "Hiba történt a fájl feltöltése közben!";
}

header("Location: " . BASE_URL . "pages/photo.php");
exit;