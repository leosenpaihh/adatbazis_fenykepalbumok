<?php
require_once '../includes/db.php';

if (!isset($_GET['image_id'])) {
    die("Nincs megadva képazonosító.");
}

$image_id = intval($_GET['image_id']);

$sql = "SELECT kep_binaris FROM Kep WHERE id = :image_id";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':image_id', $image_id);
oci_execute($stid);

$row = oci_fetch_assoc($stid);
if (!$row) {
    die("Nem található kép.");
}

$blob = $row['KEP_BINARIS'];

header("Content-Type: image/jpeg");
echo $blob->load();
?>
