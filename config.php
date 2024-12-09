<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'online_shop';

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Remove the session_start() call from here
?>