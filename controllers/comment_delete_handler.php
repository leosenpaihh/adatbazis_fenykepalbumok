<?php
require_once '../includes/db.php';
require_once '../includes/base.php';
session_start();

if (!isset($_GET['comment_id']) || !isset($_GET['kep_id'])) {
    die("Hibás kérés.");
}

$comment_id = intval($_GET['comment_id']);
$kep_id = intval($_GET['kep_id']);
$currentUser = $_SESSION['felhasznalo']['felhasznalonev'] ?? null;

if (!$currentUser) {
    die("Nincs bejelentkezve.");
}

// Lekérdezzük a komment szerzőjét
$query = "SELECT felhasznalo_felhasznalonev FROM Hozzaszolas WHERE id = :comment_id";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ':comment_id', $comment_id);
oci_execute($stid);
$comment = oci_fetch_array($stid, OCI_ASSOC);

if (!$comment) {
    die("Nincs ilyen hozzászólás.");
}

if ($comment['FELHASZNALO_FELHASZNALONEV'] !== $currentUser) {
    die("Csak a saját hozzászólásodat törölheted!");
}

// Komment törlése
$deleteQuery = "DELETE FROM Hozzaszolas WHERE id = :comment_id";
$stidDelete = oci_parse($conn, $deleteQuery);
oci_bind_by_name($stidDelete, ':comment_id', $comment_id);
if (oci_execute($stidDelete, OCI_COMMIT_ON_SUCCESS)) {
    header("Location: " . BASE_URL . "pages/photo_review.php?kep_id=" . urlencode($kep_id));
    exit;
} else {
    $error = oci_error($stidDelete);
    die("Hiba a törlés során: " . htmlentities($error['message']));
}
?>
