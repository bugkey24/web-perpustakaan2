<?php
$servername = "127.0.0.1";
$port = "3308";
$username = "root";
$password = "";
$dbname = "perpustakaan2";

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Koneksi gagal: " . $e->getMessage());
    echo "Terjadi kesalahan dalam koneksi database.";
}
