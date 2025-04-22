<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['felhasznalo'])) {
    header("Location: pages/login.php");
    exit;
}

if ($_POST['muv'] === 'letrehozas') {
    $nev = $_POST['nev'];
    $leiras = $_POST['leiras'];
    $valasztott_kepek = $_POST['kepek'] ?? [];
    $felhasznalonev = $_SESSION['felhasznalo']['felhasznalonev'];

    $ellenorzes = oci_parse($conn, "SELECT COUNT(*) AS CNT FROM FENYKEPALBUM WHERE NEV = :nev AND FELHASZNALO_FELHASZNALONEV = :fnev");
    oci_bind_by_name($ellenorzes, ":nev", $nev);
    oci_bind_by_name($ellenorzes, ":fnev", $felhasznalonev);
    oci_execute($ellenorzes);
    $row = oci_fetch_assoc($ellenorzes);

    if ($row['CNT'] > 0) {
        $_SESSION['hiba'] = "Már van ilyen nevű albumod!";
        header("Location: ../pages/album.php");
        exit;
    }

    $stmt = oci_parse($conn, "
                                    INSERT INTO FENYKEPALBUM (NEV, LEIRAS, FELHASZNALO_FELHASZNALONEV) 
                                    VALUES (:nev, :leiras, :fnev) RETURNING ID INTO :uj_album_id
                                    ");

    oci_bind_by_name($stmt, ":nev", $nev);
    oci_bind_by_name($stmt, ":leiras", $leiras);
    oci_bind_by_name($stmt, ":fnev", $felhasznalonev);
    oci_bind_by_name($stmt, ":uj_album_id", $uj_album_id, -1, SQLT_INT);

    if (oci_execute($stmt)) {
        $sorszam = 1;
        foreach ($valasztott_kepek as $kep_id) {
            $kep_stmt = oci_parse($conn, "INSERT INTO KEPFENYKEPALBUM (KEP_ID, FENYKEPALBUM_ID, SORSZAM) 
                                          VALUES (:kep_id, :album_id, :sorszam)");
            oci_bind_by_name($kep_stmt, ":kep_id", $kep_id);
            oci_bind_by_name($kep_stmt, ":album_id", $uj_album_id);
            oci_bind_by_name($kep_stmt, ":sorszam", $sorszam);
            oci_execute($kep_stmt);
            $sorszam++;
        }

        $_SESSION['message'] = "Album létrehozva, képek hozzáadva!";
    } else {
        $_SESSION['hiba'] = "Hiba az album létrehozásakor!";
    }

    header("Location: ../pages/album.php");
    exit;
}