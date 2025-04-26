<?php
require_once '../includes/db.php';
require_once '../includes/base.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['szoveg'], $_POST['kep_id'])) {
    $szoveg = trim($_POST['szoveg']);
    $kepId = intval($_POST['kep_id']);
    $parentId = null;
    if (isset($_POST['parent_id'])) {
        $temp = trim($_POST['parent_id']);
        if ($temp !== "" && $temp !== "0") {
            $parentId = intval($temp);
        }
    }
    $felhasznalo = $_SESSION['felhasznalo']['felhasznalonev'] ?? null;

    // Ellenőrizzük a parent_id létezését, ha meg van adva
    if ($parentId !== null) {
        $checkQuery = "SELECT id FROM Hozzaszolas WHERE id = :pid";
        $stidCheck = oci_parse($conn, $checkQuery);
        oci_bind_by_name($stidCheck, ':pid', $parentId);
        oci_execute($stidCheck);
        $parentExists = oci_fetch_array($stidCheck, OCI_ASSOC);
        if (!$parentExists) {
            die("A válaszhoz megadott szülő komment nem létezik (szulo_id: " . htmlspecialchars($parentId) . ").");
        }
    }

    if ($felhasznalo && !empty($szoveg)) {
        $query = "SELECT NVL(MAX(id), 0) + 1 AS uj_id FROM Hozzaszolas";
        $stid = oci_parse($conn, $query);
        oci_execute($stid);
        $row = oci_fetch_assoc($stid);
        $ujId = $row['UJ_ID'];

        $insertQuery = "INSERT INTO Hozzaszolas (id, szoveg, felhasznalo_felhasznalonev, kep_id, datum, szulo_id)
                        VALUES (:id, :szoveg, :felhasznalo, :kep_id, SYSTIMESTAMP, :szulo_id)";
        $stidInsert = oci_parse($conn, $insertQuery);
        oci_bind_by_name($stidInsert, ':id', $ujId);
        oci_bind_by_name($stidInsert, ':szoveg', $szoveg);
        oci_bind_by_name($stidInsert, ':felhasznalo', $felhasznalo);
        oci_bind_by_name($stidInsert, ':kep_id', $kepId);
        oci_bind_by_name($stidInsert, ':szulo_id', $parentId);

        if (oci_execute($stidInsert, OCI_COMMIT_ON_SUCCESS)) {
            header("Location: " . BASE_URL . "pages/photo_review.php?kep_id=" . urlencode($kepId));
            exit;
        } else {
            $error = oci_error($stidInsert);
            echo "Hiba történt a hozzászólás mentése során: " . htmlentities($error['message']);
        }
    } else {
        echo "Hiányzó adatok vagy nincs bejelentkezve.";
    }
} else {
    echo "Érvénytelen kérés.";
}
?>
