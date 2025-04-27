<?php
require_once '../includes/base.php';  // BASE_URL tartalmazza: http://localhost/adatbazis_fenykepalbumok/
session_start();
include('../includes/db.php');

if (empty($_SESSION['felhasznalo']) || $_SESSION['felhasznalo']['admin'] != 1) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$new_nev = $_POST['nev'];

$hibak = [];

if (empty($new_nev)) {
    $hibak[] = "A kategória neve nem lehet üres!";
}

if (empty($hibak)) {
    $sql_check = "SELECT COUNT(*) AS darab FROM KATEGORIA WHERE NEV = :nev";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':nev', $new_nev);
    oci_execute($stid_check);
    $row = oci_fetch_assoc($stid_check);

    if ($row['DARAB'] > 0) {
        $hibak[] = "Ez a kategória név már létezik!";
    }
}

if (empty($hibak)) {
    $sql = "INSERT INTO KATEGORIA (NEV) VALUES (:nev)";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':nev', $new_nev);

    if (oci_execute($stid)) {
        $_SESSION['message'] = "A kategória sikeresen létrehozva!";
        header("Location: " . BASE_URL . "pages/category.php");
        exit;
    } else {
        $_SESSION['hiba'] = "Hiba történt a kategória létrehozása során!";
        header("Location: " . BASE_URL . "pages/category.php");
        exit;
    }
} else {
    $_SESSION['hiba'] = implode("<br>", $hibak);
    header("Location: " . BASE_URL . "pages/category.php");
    exit;
}
