<?php
// api_daftar_ujian.php
require 'db_config.php';

// Asumsi: Aplikasi Android akan mengirimkan kelas_id via GET parameter
$kelas_id = $_GET['kelas_id'] ?? 0;

if (empty($kelas_id) || !is_numeric($kelas_id)) {
    sendResponse(["status" => "error", "message" => "ID Kelas tidak valid."], 400);
}

// Waktu saat ini untuk memfilter ujian yang sedang aktif
$current_time = date('Y-m-d H:i:s');

// Query untuk mengambil ujian yang:
// 1. Sesuai dengan kelas siswa (melalui mapel_id)
// 2. Waktu mulai <= sekarang DAN Waktu selesai >= sekarang (Ujian aktif)
$stmt = $conn->prepare("
    SELECT 
        u.id AS ujian_id, u.nama_ujian, u.jenis_ujian, u.durasi_menit,
        m.nama_mapel, k.kelas,
        u.waktu_mulai, u.waktu_selesai 
    FROM ujians u
    JOIN mapels m ON u.mapel_id = m.id
    JOIN kelas k ON m.kelas_id = k.id
    WHERE 
        m.kelas_id = ? 
    ORDER BY u.waktu_mulai DESC 
");

$stmt->bind_param("i", $kelas_id);
$stmt->execute();
$result = $stmt->get_result();

$daftar_ujian = [];
while ($row = $result->fetch_assoc()) {
    $daftar_ujian[] = $row;
}

if (!empty($daftar_ujian)) {
    sendResponse([
        "status" => "success",
        "message" => "Daftar ujian aktif berhasil dimuat.",
        "data" => $daftar_ujian
    ]);
} else {
    sendResponse([
        "status" => "success",
        "message" => "Tidak ada ujian aktif saat ini untuk kelas Anda.",
        "data" => []
    ]);
}

$stmt->close();
$conn->close();
?>