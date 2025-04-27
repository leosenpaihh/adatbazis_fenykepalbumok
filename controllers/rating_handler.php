<?php
require_once '../includes/base.php';
session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo']) || !isset($_GET['kep_id']) || !isset($_POST['rating'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

if(isset($_POST['letrehozas'])) {
    $sql = "INSERT INTO ERTEKELES (FELHASZNALO_FELHASZNALONEV, KEP_ID, PONTSZAM) VALUES (:nev, :kep, :pontszam)";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':nev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_bind_by_name($stid, ':kep', $_GET['kep_id']);
    oci_bind_by_name($stid, ':pontszam', $_POST['rating']);

    if (oci_execute($stid)) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    } else {
        $_SESSION['hiba'] = "Hiba történt az értékelés hozzáadása közben!";
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
} elseif (isset($_POST['modositas'])) {
    $sql = "UPDATE ERTEKELES SET PONTSZAM = :pontszam WHERE FELHASZNALO_FELHASZNALONEV = :nev AND KEP_ID = :kep";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':pontszam', $_POST['rating']);
    oci_bind_by_name($stid, ':nev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_bind_by_name($stid, ':kep', $_GET['kep_id']);

    if (oci_execute($stid)) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    } else {
        $_SESSION['hiba'] = "Hiba történt az értékelés módosítása közben!";
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
} elseif (isset($_POST['torles'])) {
    $sql = "DELETE FROM ERTEKELES WHERE FELHASZNALO_FELHASZNALONEV = :nev AND KEP_ID = :kep";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':nev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_bind_by_name($stid, ':kep', $_GET['kep_id']);

    if (oci_execute($stid)) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    } else {
        $_SESSION['hiba'] = "Hiba történt az értékelés törlése közben!";
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}
