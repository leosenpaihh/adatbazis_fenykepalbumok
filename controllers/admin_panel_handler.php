<?php
require_once '../includes/base.php';
session_start();
include('../includes/db.php');

if (empty($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

if ($_SESSION['felhasznalo']['admin'] != 1) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

if (isset($_POST['update_admin'])) {
    $felhasznalo = $_POST['felhasznalo'];
    $admin_status = $_POST['admin_status'];

    $sql_check = "SELECT * FROM FELHASZNALO WHERE FELHASZNALONEV = :felhasznalo";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':felhasznalo', $felhasznalo);
    oci_execute($stid_check);
    $user = oci_fetch_assoc($stid_check);

    if ($user) {
        $sql_update = "UPDATE FELHASZNALO SET ADMIN = :admin_status WHERE FELHASZNALONEV = :felhasznalo";
        $stid_update = oci_parse($conn, $sql_update);
        oci_bind_by_name($stid_update, ':admin_status', $admin_status);
        oci_bind_by_name($stid_update, ':felhasznalo', $felhasznalo);
        if (oci_execute($stid_update)) {
            $_SESSION['message'] = "A felhasználó admin státusza sikeresen frissítve!";
            header("Location: " . BASE_URL . "pages/admin_panel.php");
            exit;
        } else {
            $_SESSION['hiba'] = "Hiba történt a felhasználó admin státuszának frissítése során!";
            header("Location: " . BASE_URL . "pages/admin_panel.php");
            exit;
        }
    } else {
        $_SESSION['hiba'] = "Felhasználó nem található!";
        header("Location: " . BASE_URL . "pages/admin_panel.php");
        exit;
    }
}

if (isset($_POST['delete_user'])) {
    $felhasznalo = $_POST['felhasznalo'];

    $sql_check = "SELECT * FROM FELHASZNALO WHERE FELHASZNALONEV = :felhasznalo";
    $stid_check = oci_parse($conn, $sql_check);
    oci_bind_by_name($stid_check, ':felhasznalo', $felhasznalo);
    oci_execute($stid_check);
    $user = oci_fetch_assoc($stid_check);

    if ($user) {
        $sql_delete = "DELETE FROM FELHASZNALO WHERE FELHASZNALONEV = :felhasznalo";
        $stid_delete = oci_parse($conn, $sql_delete);
        oci_bind_by_name($stid_delete, ':felhasznalo', $felhasznalo);

        if (oci_execute($stid_delete)) {
            $_SESSION['message'] = "A felhasználó sikeresen törölve lett!";
            header("Location: " . BASE_URL . "pages/admin_panel.php");
            exit;
        } else {
            $_SESSION['hiba'] = "Hiba történt a felhasználó törlésénél!";
            header("Location: " . BASE_URL . "pages/admin_panel.php");
            exit;
        }
    } else {
        $_SESSION['hiba'] = "A kiválasztott felhasználó nem található!";
        header("Location: " . BASE_URL . "pages/admin_panel.php");
        exit;
    }
}