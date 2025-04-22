<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vezeteknev = $_POST['vezeteknev'];
    $keresztnev = $_POST['keresztnev'];
    $felhasznalonev = $_POST['felhasznalonev'];
    $email = $_POST['email'];
    $telepules = $_POST['telepules'];
    $jelszo = $_POST['jelszo'];
    $confirm_jelszo = $_POST['confirm-jelszo'];

    $hibak = [];

    if (empty($vezeteknev) || strlen($vezeteknev) < 2 || strlen($vezeteknev) > 50) {
        $hibak[] = "A vezetéknév legalább 2 karakter hosszú, maximum 50 karakter lehet!";
    }

    if (empty($keresztnev) || strlen($keresztnev) < 2 || strlen($keresztnev) > 50) {
        $hibak[] = "A keresztnév legalább 2 karakter hosszú, maximum 50 karakter lehet!";
    }

    if (empty($felhasznalonev)) {
        $hibak[] = "A felhasználónév mező kitöltése kötelező!";
    } else {
        $sql_check_user = "SELECT COUNT(*) AS darab FROM FELHASZNALO WHERE FELHASZNALONEV = :felhasznalonev";
        $stid_check = oci_parse($conn, $sql_check_user);
        oci_bind_by_name($stid_check, ':felhasznalonev', $felhasznalonev);
        oci_execute($stid_check);
        $row = oci_fetch_assoc($stid_check);

        if ($row['DARAB'] > 0) {
            $hibak[] = "Ez a felhasználónév már foglalt, válassz másikat!";
        }
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hibak[] = "Érvényes email címet adj meg!";
    }

    if (empty($telepules)) {
        $hibak[] = "Válassz egy települést!";
    }

    if (empty($jelszo) || strlen($jelszo) < 6 || strlen($jelszo) > 255) {
        $hibak[] = "A jelszónak legalább 6 karakter hosszúnak kell lennie!";
    }

    if ($jelszo !== $confirm_jelszo) {
        $hibak[] = "A jelszavak nem egyeznek meg!";
    }

    if (!empty($hibak)) {
        $_SESSION['message'] = implode("<br>", $hibak);
        $_SESSION['urlap_adatok'] = [
            'vezeteknev' => $vezeteknev,
            'keresztnev' => $keresztnev,
            'felhasznalonev' => $felhasznalonev,
            'email' => $email,
            'telepules' => $telepules
        ];
        header('Location: ../pages/registration.php');
        exit;
    }

    $titkositott_jelszo = password_hash($jelszo, PASSWORD_BCRYPT);

    $sql = "INSERT INTO FELHASZNALO (vezeteknev, keresztnev, felhasznalonev, email, admin, telepules_id, jelszo) 
            VALUES (:vezeteknev, :keresztnev, :felhasznalonev, :email, :admin, :telepules, :jelszo)";
    $stid = oci_parse($conn, $sql);

    oci_bind_by_name($stid, ':vezeteknev', $vezeteknev);
    oci_bind_by_name($stid, ':keresztnev', $keresztnev);
    oci_bind_by_name($stid, ':felhasznalonev', $felhasznalonev);
    oci_bind_by_name($stid, ':email', $email);
    oci_bind_by_name($stid, ':admin', 0);
    oci_bind_by_name($stid, ':telepules', $telepules);
    oci_bind_by_name($stid, ':jelszo', $titkositott_jelszo);

    if (oci_execute($stid)) {
        $_SESSION['message'] = "Sikeres regisztráció! Lépj be.";
        header('Location: ../pages/login.php');
        exit;
    } else {
        $e = oci_error($stid);
        $_SESSION['message'] = "Sikertelen regisztráció: " . $e['message'];
        header('Location: ../pages/registration.php');
        exit;
    }
}