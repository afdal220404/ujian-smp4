<?php
require 'db_config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Metode tidak diizinkan."], 405);
}
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    sendResponse(["status" => "error", "message" => "Username dan password harus diisi."], 400);
}
$stmt = $conn->prepare("
    SELECT s.id, s.nama_lengkap, s.kelas_id, s.nisn, s.password, k.kelas AS nama_kelas 
    FROM siswas s
    LEFT JOIN kelas k ON s.kelas_id = k.id
    WHERE s.username = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $siswa = $result->fetch_assoc();

    if (password_verify($password, $siswa['password'])) {
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