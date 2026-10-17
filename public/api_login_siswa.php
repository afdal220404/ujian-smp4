<?php
// api_login_siswa.php
require 'db_config.php';
require 'api_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Metode tidak diizinkan."], 405);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    sendResponse(["status" => "error", "message" => "Username dan password harus diisi."], 400);
}

$stmt = $conn->prepare("
    SELECT s.id, s.nama_lengkap, s.kelas_id, s.nisn, s.password, k.kelas AS nama_kelas
    FROM siswas s
    LEFT JOIN kelas k ON s.kelas_id = k.id
    WHERE s.username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Pesan dibuat seragam agar tidak bisa dipakai menebak username yang ada.
if (!$siswa || !password_verify($password, $siswa['password'])) {
    sendResponse(["status" => "error", "message" => "Username atau password salah."], 401);
}

unset($siswa['password']);

// Terbitkan token akses. Token ini WAJIB dikirim pada setiap permintaan
// berikutnya melalui header: X-Api-Token atau Authorization: Bearer <token>.
$siswa['api_token'] = apiIssueToken($conn, (int) $siswa['id']);
$siswa['api_token_expires_at'] = apiTokenExpiry();

sendResponse([
    "status"  => "success",
    "message" => "Login berhasil.",
    "data"    => $siswa,
]);
