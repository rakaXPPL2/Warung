<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "db_Warung";

$db = new mysqli($hostname, $username, $password, $database_name);

if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}
?>
