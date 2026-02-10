<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "aspirasi_haidar";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars($data));
}
