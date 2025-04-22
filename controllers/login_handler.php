<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $felhasznalonev = $_POST['felhasznalonev'];
    $jelszo = $_POST['jelszo'];

    $sql = "SELECT * FROM FELHASZNALO WHERE FELHASZNALONEV = :felhasznalonev";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':felhasznalonev', $felhasznalonev);
    oci_execute($stid);

    $felhasznalo = oci_fetch_assoc($stid);

    if (!$felhasznalo || !password_verify($jelszo, $felhasznalo['JELSZO'])) {
        $_SESSION['hiba'] = "Hibás felhasználónév vagy jelszó!";
        header('Location: ../pages/login.php');
        exit;
    }

    $telepules_id = $felhasznalo['TELEPULES_ID'];
    $sql_telepules = "SELECT * FROM TELEPULES WHERE ID = :telepules_id";
    $stid_telepules = oci_parse($conn, $sql_telepules);
    oci_bind_by_name($stid_telepules, ':telepules_id', $telepules_id);
    oci_execute($stid_telepules);

    $telepules = oci_fetch_assoc($stid_telepules);

    $_SESSION['felhasznalo'] = [
        'felhasznalonev' => $felhasznalo['FELHASZNALONEV'],
        'vezeteknev' => $felhasznalo['VEZETEKNEV'],
        'keresztnev' => $felhasznalo['KERESZTNEV'],
        'email' => $felhasznalo['EMAIL'],
        'telepules_id' => $felhasznalo['TELEPULES_ID'],
        'telepules_nev' => $telepules['TELEPULES'],
        'telepules_orszag' => $felhasznalo['ORSZAG'],
        'telepules_megye' => $felhasznalo['MEGYE'],
        'admin' => $felhasznalo['ADMIN']
    ];
    header('Location: ../index.php');
    exit;
}