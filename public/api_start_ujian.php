<?php
// api_start_ujian.php
require 'db_config.php';
require 'api_auth.php';

// Pastikan permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Metode tidak diizinkan."], 405);
}

$ujian_id = $_POST['ujian_id'] ?? 0;
$siswa_id = $_POST['siswa_id'] ?? 0;

if ($ujian_id == 0 || $siswa_id == 0) {
    sendResponse(["status" => "error", "message" => "Data ujian atau siswa tidak lengkap."], 400);
}

// Token wajib sah dan harus milik siswa yang diminta.
$siswa = apiAuth($conn, $siswa_id);

// Ujian harus benar-benar milik kelas siswa tersebut.
$stmt_cek = $conn->prepare("
    SELECT u.id
    FROM ujians u
    JOIN mapels m ON u.mapel_id = m.id
    WHERE u.id = ? AND m.kelas_id = ?
    LIMIT 1
");
$stmt_cek->bind_param("ii", $ujian_id, $siswa['kelas_id']);
$stmt_cek->execute();
$boleh = $stmt_cek->get_result()->fetch_assoc();
$stmt_cek->close();

if (!$boleh) {
    sendResponse(["status" => "error", "message" => "Anda tidak berhak mengikuti ujian ini."], 403);
}

// 1. CEK APAKAH SUDAH PERNAH UJIAN
$stmt_check = $conn->prepare("SELECT id FROM hasil_ujians WHERE ujian_id = ? AND siswa_id = ? LIMIT 1");
$stmt_check->bind_param("ii", $ujian_id, $siswa_id);
$stmt_check->execute();
$hasil_lama = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if ($hasil_lama) {
    // Jika sudah ada, kembalikan ID lama dalam format 'data'
    sendResponse([
        "status"  => "success",
        "message" => "Ujian sudah dimulai sebelumnya.",
        "data"    => ["hasil_ujian_id" => (int) $hasil_lama['id']],
    ]);
}

// 2. MASUKKAN DATA BARU
$kelas_id = $siswa['kelas_id'];       // diambil dari token, lebih aman daripada input klien
$waktu_mulai = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO hasil_ujians (ujian_id, siswa_id, kelas_id, waktu_mulai) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $ujian_id, $siswa_id, $kelas_id, $waktu_mulai);

if (!$stmt->execute()) {
    error_log('[API start_ujian] Gagal insert: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    sendResponse(["status" => "error", "message" => "Gagal mencatat waktu mulai. Hubungi administrator."], 500);
}

$hasil_ujian_id = $conn->insert_id;
$stmt->close();
$conn->close();

sendResponse([
    "status"  => "success",
    "message" => "Ujian dimulai.",
    "data"    => ["hasil_ujian_id" => $hasil_ujian_id],
]);
