<?php
require_once '../includes/base.php';
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$felhasznalo = $_SESSION['felhasznalo'];

$new_felhasznalonev = $_POST['felhasznalonev'];
$new_email = $_POST['email'];
$new_telepules_id = $_POST['telepules'];
$new_jelszo = $_POST['jelszo'];
$new_confirm_jelszo = $_POST['confirm-jelszo'];

$hibak = [];

if (empty($new_felhasznalonev)) {
    $hibak[] = "A felhasználónév megadása kötelező!";
}

if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $hibak[] = "Érvényes email címet kell megadni!";
}

if (empty($new_telepules_id)) {
    $hibak[] = "A település kiválasztása kötelező!";
}

if ($new_felhasznalonev !== $felhasznalo['felhasznalonev']) {
    $sql_check_user = "SELECT COUNT(*) AS darab FROM FELHASZNALO WHERE FELHASZNALONEV = :felhasznalonev";
    $stid_check = oci_parse($conn, $sql_check_user);
    oci_bind_by_name($stid_check, ':felhasznalonev', $new_felhasznalonev);
    oci_execute($stid_check);
    $row = oci_fetch_assoc($stid_check);

    if ($row['DARAB'] > 0) {
        $hibak[] = "Ez a felhasználónév már foglalt, válassz másikat!";
    }
}

if ($new_jelszo !== $new_confirm_jelszo) {
    $hibak[] = "A jelszavak nem egyeznek meg!";
}

if (empty($hibak)) {
    if (!empty($new_jelszo)) {
        $new_jelszo = password_hash($new_jelszo, PASSWORD_BCRYPT);
        $sql = "UPDATE FELHASZNALO 
                    SET FELHASZNALONEV = :felhasznalonev, 
                        EMAIL = :email, 
                        TELEPULES_ID = :telepules, 
                        JELSZO = :jelszo 
                    WHERE FELHASZNALONEV = :old_felhasznalonev";
    } else {
        $sql = "UPDATE FELHASZNALO 
                    SET FELHASZNALONEV = :felhasznalonev, 
                        EMAIL = :email, 
                        TELEPULES_ID = :telepules 
                    WHERE FELHASZNALONEV = :old_felhasznalonev";
    }

    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':felhasznalonev', $new_felhasznalonev);
    oci_bind_by_name($stid, ':email', $new_email);
    oci_bind_by_name($stid, ':telepules', $new_telepules_id);
    oci_bind_by_name($stid, ':old_felhasznalonev', $felhasznalo['felhasznalonev']);

    if (!empty($new_jelszo)) {
        oci_bind_by_name($stid, ':jelszo', $new_jelszo);
    }

    if (oci_execute($stid)) {
        $_SESSION['felhasznalo']['felhasznalonev'] = $new_felhasznalonev;
        $_SESSION['felhasznalo']['email'] = $new_email;
        $_SESSION['felhasznalo']['telepules_id'] = $new_telepules_id;

        $sql_telepules = "SELECT TELEPULES, ORSZAG, MEGYE FROM TELEPULES WHERE ID = :telepules_id";
        $stid_telepules = oci_parse($conn, $sql_telepules);
        oci_bind_by_name($stid_telepules, ':telepules_id', $new_telepules_id);
        oci_execute($stid_telepules);
        $telepules = oci_fetch_assoc($stid_telepules);

        $_SESSION['felhasznalo']['telepules_nev'] = $telepules['TELEPULES'];
        $_SESSION['felhasznalo']['telepules_orszag'] = $telepules['ORSZAG'];
        $_SESSION['felhasznalo']['telepules_megye'] = $telepules['MEGYE'];


        $_SESSION['message'] = "A profil sikeresen frissítve!";
        header("Location: " . BASE_URL . "pages/profile.php");
        exit;
    } else {
        $_SESSION['hiba'] = "Hiba történt a frissítés során!";
    }
} else {
    $_SESSION['hiba'] = implode("<br>", $hibak);
    header("Location: " . BASE_URL . "pages/profile.php");
    exit;
}
