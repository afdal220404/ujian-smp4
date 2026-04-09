<?php
// api_detail_ujian.php
ini_set('display_errors', 0);
error_reporting(0);

require 'db_config.php';
header('Content-Type: application/json; charset=utf-8');
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Metode tidak diizinkan."]);
    exit();
}

// Ambil ID dari parameter GET
$hasil_ujian_id = $_GET['hasil_ujian_id'] ?? 0;

if ($hasil_ujian_id == 0) {
    echo json_encode(["status" => "error", "message" => "ID Hasil Ujian tidak valid."]);
    exit();
}

// ==========================================
// 1. QUERY INFO UJIAN & NILAI
// ==========================================
$q_info = "
    SELECT 
        h.nilai, h.jumlah_benar, h.waktu_mulai AS mhs_mulai, h.waktu_selesai AS mhs_selesai,
        u.nama_ujian, u.jenis_ujian, u.tanggal_ujian, u.waktu_mulai AS jadwal_mulai, u.waktu_selesai AS jadwal_selesai
    FROM hasil_ujians h
    JOIN ujians u ON h.ujian_id = u.id
    WHERE h.id = ?
";
$stmt_info = $conn->prepare($q_info);

if (!$stmt_info) {
    echo json_encode(["status" => "error", "message" => "SQL Info Error: " . $conn->error]);
    exit();
}

$stmt_info->bind_param("i", $hasil_ujian_id);
$stmt_info->execute();
$res_info = $stmt_info->get_result()->fetch_assoc();
$stmt_info->close();

if (!$res_info) {
    echo json_encode(["status" => "error", "message" => "Data hasil ujian tidak ditemukan di database."]);
    exit();
}

// ==========================================
// 2. QUERY DETAIL SOAL & JAWABAN SISWA
// PENTING: Menggunakan JOIN ke bank_soal_items
// ==========================================
$q_detail = "
    SELECT 
        js.soal_id, 
        b.pertanyaan, b.gambar, b.tipe, b.data_soal, b.kunci_jawaban,
        js.jawaban_dipilih AS jawaban_siswa, js.is_correct,
        b.opsi_a, b.opsi_b, b.opsi_c, b.opsi_d,
        b.gambar_a, b.gambar_b, b.gambar_c, b.gambar_d
    FROM jawaban_siswas js
    JOIN soals s ON js.soal_id = s.id
    JOIN bank_soal_items b ON s.bank_soal_id = b.id
    WHERE js.hasil_ujian_id = ?
    ORDER BY s.id ASC
";

$stmt_detail = $conn->prepare($q_detail);

if (!$stmt_detail) {
    echo json_encode(["status" => "error", "message" => "SQL Detail Error: " . $conn->error]);
    exit();
}

$stmt_detail->bind_param("i", $hasil_ujian_id);
$stmt_detail->execute();
$res_detail = $stmt_detail->get_result();

$detail_soal = [];
$total_soal = 0;
// Sesuaikan base_url ini jika perlu
$base_url = "https://ujian.smpn4tilkam.cloud/storage/";

while ($row = $res_detail->fetch_assoc()) {
    $total_soal++;
    
    // Format URL Gambar
    if (!empty($row['gambar'])) $row['gambar'] = $base_url . ltrim($row['gambar'], '/');
    if (!empty($row['gambar_a'])) $row['gambar_a'] = $base_url . ltrim($row['gambar_a'], '/');
    if (!empty($row['gambar_b'])) $row['gambar_b'] = $base_url . ltrim($row['gambar_b'], '/');
    if (!empty($row['gambar_c'])) $row['gambar_c'] = $base_url . ltrim($row['gambar_c'], '/');
    if (!empty($row['gambar_d'])) $row['gambar_d'] = $base_url . ltrim($row['gambar_d'], '/');
    
    // Mapping Data
    $detail_soal[] = [
        "soal_id" => (int)$row['soal_id'],
        "pertanyaan" => $row['pertanyaan'] ?? "",
        "gambar" => $row['gambar'],
        "tipe" => $row['tipe'] ?? "pilihan_ganda",
        "data_soal" => $row['data_soal'],
        "kunci_jawaban" => $row['kunci_jawaban'],
        "jawaban_siswa" => $row['jawaban_siswa'],
        "is_correct" => (int)$row['is_correct'],
        "opsi_a" => $row['opsi_a'],
        "opsi_b" => $row['opsi_b'],
        "opsi_c" => $row['opsi_c'],
        "opsi_d" => $row['opsi_d'],
        "gambar_a" => $row['gambar_a'],
        "gambar_b" => $row['gambar_b'],
        "gambar_c" => $row['gambar_c'],
        "gambar_d" => $row['gambar_d']
    ];
}
$stmt_detail->close();

// Set Total Soal Aktual yang Dijawab
$res_info['total_soal'] = $total_soal;

// Pastikan tipe datanya pas (Mencegah Android Error Parse)
$res_info['nilai'] = (float)$res_info['nilai'];
$res_info['jumlah_benar'] = (int)$res_info['jumlah_benar'];
$res_info['jenis_ujian'] = $res_info['jenis_ujian'] ?? "Ujian";

echo json_encode([
    "status" => "success",
    "data" => [
        "info" => $res_info,
        "detail" => $detail_soal
    ]
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>