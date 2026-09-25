<?php
// =========================================================
// FILE KONEKSI DATABASE
// Hosting: InfinityFree
// =========================================================

$host     = "sql303.infinityfree.com";
$username = "if0_42986988";
$password = "fZBKCGrcC819y";
$database = "if0_42986988_datamahasiswaumb";

// Membuat koneksi menggunakan MySQLi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set charset agar aman untuk karakter khusus (opsional tapi disarankan)
mysqli_set_charset($koneksi, "utf8mb4");

// Jika file ini di-include di file lain, variabel $koneksi bisa langsung dipakai
// Contoh penggunaan di file lain:
// require_once 'koneksi.php';
// $result = mysqli_query($koneksi, "SELECT * FROM mahasiswa");
?>
