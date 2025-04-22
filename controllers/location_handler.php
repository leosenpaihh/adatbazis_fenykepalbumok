<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: pages/login.php");
    exit;
}

$new_orszag = $_POST['orszag'];
$new_megye = $_POST['megye'];
$new_telepules = $_POST['telepules'];

$hibak = [];

if (empty($new_orszag) || empty($new_megye) || empty($new_telepules)) {
    $hibak[] = "Az ország, megye és település megadása kötelező!";
}

if (empty($hibak)) {
    $sql_check = "
        SELECT COUNT(*) AS darab 
        FROM TELEPULES 
        WHERE ORSZAG = :orszag AND MEGYE = :megye AND TELEPULES = :telepules
    ";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':orszag', $new_orszag);
    oci_bind_by_name($stid_check, ':megye', $new_megye);
    oci_bind_by_name($stid_check, ':telepules', $new_telepules);
    oci_execute($stid_check);
    $row = oci_fetch_assoc($stid_check);

    if ($row['DARAB'] > 0) {
        $hibak[] = "Ez a helyszín már létezik!";
    }
}

if (empty($hibak)) {
    $sql = "INSERT INTO TELEPULES (ORSZAG, MEGYE, TELEPULES) VALUES (:orszag, :megye, :telepules)";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':orszag', $new_orszag);
    oci_bind_by_name($stid, ':megye', $new_megye);
    oci_bind_by_name($stid, ':telepules', $new_telepules);

    if (oci_execute($stid)) {
        $_SESSION['message'] = "A helyszín sikeresen hozzáadva!";
        header("Location: ../pages/location.php");
        exit;
    } else {
        $_SESSION['hiba'] = "Hiba történt a helyszín hozzáadása során!";
        header("Location: ../pages/location.php");
        exit;
    }
} else {
    $_SESSION['hiba'] = implode("<br>", $hibak);
    header("Location: ../pages/location.php");
    exit;
}
