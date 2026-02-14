<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "db_warung";

$conn = new mysqli($hostname, $username, $password, $database_name);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
