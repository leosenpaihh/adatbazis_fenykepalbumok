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

if (isset($_POST['torles']) && empty($hibak)) {
    $new_nev = htmlspecialchars($new_nev, ENT_QUOTES, 'UTF-8');

    $sql_check = "SELECT COUNT(*) AS darab FROM KEPKATEGORIA WHERE KATEGORIA_NEV = :nev";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':nev', $new_nev);
    oci_execute($stid_check);
    $row = oci_fetch_assoc($stid_check);

    if ($row['DARAB'] > 0) {
        $sql = "DELETE FROM KEPKATEGORIA WHERE KATEGORIA_NEV = :nev";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':nev', $new_nev);
        if (!oci_execute($stid)) {
            $error = oci_error($stid);
            $_SESSION['hiba'] = "Hiba történt a képkategória kapcsolatok törlése során: " . $error['message'];
            header("Location: " . BASE_URL . "pages/category.php");
            exit;
        }
    }

    $sql = "DELETE FROM KATEGORIA WHERE NEV = :nev";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':nev', $new_nev);
    if (!oci_execute($stid)) {
        $error = oci_error($stid);
        $_SESSION['hiba'] = "Hiba történt a kategória törlése során: " . $error['message'];
        header("Location: " . BASE_URL . "pages/category.php");
        exit;
    }

    oci_commit($conn);
    $_SESSION['message'] = "A kategória sikeresen törölve!";
    header("Location: " . BASE_URL . "pages/category.php");
    exit;
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

if (isset($_POST['eredeti_nev']) && empty($hibak)) {
    $sql_check = "SELECT COUNT(*) AS darab FROM KATEGORIA WHERE NEV = :nev";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':nev', $_POST['eredeti_nev']);
    oci_execute($stid_check);
    $row = oci_fetch_assoc($stid_check);

    if ($row['DARAB'] == 0) {
        $hibak[] = "A kategória amit módosítani akarsz már nem létezik!";
    }
}

if (empty($hibak)) {
    if (isset($_POST['letrehozas'])) {
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
    } elseif (isset($_POST['modositas'])) {
        $sql = "UPDATE KATEGORIA SET NEV = :uj WHERE NEV = :eredeti";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':uj', $new_nev);
        oci_bind_by_name($stid, ':eredeti', $_POST['eredeti_nev']);

        if (oci_execute($stid)) {
            $sql_child = "UPDATE KEPKATEGORIA SET KATEGORIA_NEV = :uj WHERE KATEGORIA_NEV = :eredeti";
            $stid_child = oci_parse($conn, $sql_child);
            oci_bind_by_name($stid_child, ':uj', $new_nev);
            oci_bind_by_name($stid_child, ':eredeti', $_POST['eredeti_nev']);

            if (oci_execute($stid_child)) {
                $_SESSION['message'] = "A kategória sikeresen frissítve!";
                header("Location: " . BASE_URL . "pages/category.php");
                exit;
            } else {
                $_SESSION['hiba'] = "Hiba történt a képkategóriák frissítése során!";
                header("Location: " . BASE_URL . "pages/category.php");
                exit;
            }
        } else {
            $_SESSION['hiba'] = "Hiba történt a kategória frissítése során!";
            header("Location: " . BASE_URL . "pages/category.php");
            exit;
        }
    }
} else {
    $_SESSION['hiba'] = implode("<br>", $hibak);
    header("Location: " . BASE_URL . "pages/category.php");
    exit;
}