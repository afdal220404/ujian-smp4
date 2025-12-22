<?php
// api_login_siswa.php
require 'db_config.php';

// Pastikan permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Metode tidak diizinkan."], 405);
}

// Ambil data dari POST body (Aplikasi Android akan mengirim dalam bentuk form data)
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    sendResponse(["status" => "error", "message" => "Username dan password harus diisi."], 400);
}

// Gunakan Prepared Statements untuk keamanan
$stmt = $conn->prepare("SELECT id, nama_lengkap, kelas_id, nisn, password FROM siswas WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $siswa = $result->fetch_assoc();
    
    // Verifikasi password (database Anda menggunakan hash password)
    if (password_verify($password, $siswa['password'])) {
        // Hapus password hash sebelum dikirim ke klien
        unset($siswa['password']);
        
        sendResponse([
            "status" => "success",
            "message" => "Login berhasil.",
            "data" => $siswa
        ]);
    } else {
        sendResponse(["status" => "error", "message" => "Password salah."], 401);
    }
} else {
    sendResponse(["status" => "error", "message" => "Username tidak ditemukan."], 401);
}

$stmt->close();
$conn->close();