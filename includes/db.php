<?php
$config = include 'config.php';

$tns = "
(DESCRIPTION =
    (ADDRESS_LIST =
        (ADDRESS = (PROTOCOL = TCP)(HOST = {$config['host']})(PORT = {$config['port']}))
    )
    (CONNECT_DATA =
        (SID = {$config['sid']})
    )
)";

$conn = oci_connect($config['username'], $config['password'], $tns, 'UTF8');

if (!$conn) {
    die('Adatbázis kapcsolat hiba.');
}