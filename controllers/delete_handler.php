<?php
require_once '../includes/db.php';
session_start();

// Ellenőrizzük, hogy POST kérés és van-e kép ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kep_id'])) {
    $kepId = (int)$_POST['kep_id'];

    // Először ellenőrizzük, hogy a bejelentkezett user törölheti-e a képet
    $query = "SELECT FELHASZNALO_FELHASZNALONEV FROM Kep WHERE ID = :id";
    $stid = oci_parse($conn, $query);
    oci_bind_by_name($stid, ':id', $kepId);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);

    if (!$row) {
        echo "A kép nem található.";
        exit;
    }

    if ($row['FELHASZNALO_FELHASZNALONEV'] !== $_SESSION['felhasznalo']['felhasznalonev']) {
        echo "Nincs jogosultságod törölni ezt a képet.";
        exit;
    }

    // Jogosultság rendben, töröljük a képet
    $deleteQuery = "DELETE FROM Kep WHERE ID = :id";
    $deleteStid = oci_parse($conn, $deleteQuery);
    oci_bind_by_name($deleteStid, ':id', $kepId);

    // Az OCI_COMMIT_ON_SUCCESS kapcsolóval automatikusan elmentjük a változtatást
    $result = oci_execute($deleteStid, OCI_COMMIT_ON_SUCCESS);
    if (!$result) {
        $error = oci_error($deleteStid);
        echo "Hiba történt a törlés során: " . $error['message'];
        exit;
    }

    // Sikeres törlés után visszairányítunk
    header("Location: ../index.php");
    exit;
} else {
    echo "Hibás kérés.";
}
?>
