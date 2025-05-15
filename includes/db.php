<?php
$config = include 'config.php';

// Wallet mappa beállítása
putenv("TNS_ADMIN=" . $config['wallet']);

$tns = "
(DESCRIPTION =
    (ADDRESS_LIST =
        (ADDRESS = (PROTOCOL = TCPS)(HOST = {$config['host']})(PORT = {$config['port']}))
    )
    (CONNECT_DATA =
        (SERVICE_NAME = {$config['db_name']})
    )
    (SECURITY =
        (SSL_SERVER_DN_MATCH = yes)
    )
)";

$conn = oci_connect($config['username'], $config['password'], $tns, 'UTF8');

if (!$conn) {
    $e = oci_error();
    die("Kapcsolódás hiba: " . $e['message']);
}
?>
