<?php
// db_config.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$host = "127.0.0.1"; // Host lokal Anda
$user = "root";      // User MySQL Anda
$pass = "";          // Password MySQL Anda (kosong jika XAMPP/Laragon default)
$db   = "ujian_smp4"; // Nama database sesuai file SQL yang Anda lampirkan

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Periksa koneksi
if ($conn->connect_error) {
    // Keluarkan pesan error dalam format JSON yang mudah dibaca aplikasi
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $conn->connect_error]);
    die();
}
// Setel karakter ke utf8mb4 jika diperlukan untuk mendukung karakter kompleks
$conn->set_charset("utf8mb4");

// Fungsi helper untuk mengirim respons JSON
function sendResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    die();
}