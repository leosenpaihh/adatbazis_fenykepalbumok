<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: pages/login.php");
    exit;
}

if (isset($_POST['torles']) && empty($hibak)) {
     $sql = "DELETE FROM TELEPULES WHERE ID = :id";
     $stid = oci_parse($conn, $sql);
     oci_bind_by_name($stid, ':id', $_POST['id']);
     if (!oci_execute($stid)) {
         $error = oci_error($stid);
         $_SESSION['hiba'] = "Hiba történt a helyszín törlése során: " . $error['message'];
         header("Location: ../pages/location.php");
         exit;
     }

     oci_commit($conn);

     $_SESSION['message'] = "A helyszín sikeresen törölve!";
     header("Location: ../pages/location.php");
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
    if (isset($_POST['letrehozas'])) {
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
    } elseif (isset($_POST['modositas'])) {
        $sql = "UPDATE TELEPULES SET ORSZAG = :orszag, MEGYE = :megye, TELEPULES = :telepules WHERE ID = :id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':orszag', $new_orszag);
        oci_bind_by_name($stid, ':megye', $new_megye);
        oci_bind_by_name($stid, ':telepules', $new_telepules);
        oci_bind_by_name($stid, ':id', $_POST['id']);
        echo $new_orszag . $new_megye . $new_telepules . $_POST['id'];

        if (oci_execute($stid)) {
            $_SESSION['message'] = "A helyszín sikeresen frissítve!";
            header("Location: ../pages/location.php");
            exit;
        } else {
            $_SESSION['hiba'] = "Hiba történt a helyszín frissítése során!";
            header("Location: ../pages/location.php");
            exit;
        }
    }
} else {
    $_SESSION['hiba'] = implode("<br>", $hibak);
    header("Location: ../pages/location.php");
    exit;
}
