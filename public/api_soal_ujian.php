<?php
// api_soal_ujian.php
require 'db_config.php';

// Asumsi: Aplikasi Android akan mengirimkan ujian_id via GET parameter
$ujian_id = $_GET['ujian_id'] ?? 0;

if (empty($ujian_id) || !is_numeric($ujian_id)) {
    sendResponse(["status" => "error", "message" => "ID Ujian tidak valid."], 400);
}

// Query untuk mengambil semua detail soal
// PENTING: Jangan sertakan kolom 'kunci_jawaban' karena ini akan dikirim ke klien (siswa)
$stmt = $conn->prepare("
    SELECT 
        id AS soal_id, pertanyaan, gambar, 
        opsi_a, opsi_b, opsi_c, opsi_d, opsi_e
    FROM soals 
    WHERE ujian_id = ?
    ORDER BY id ASC
");
$stmt->bind_param("i", $ujian_id);
$stmt->execute();
$result = $stmt->get_result();

$daftar_soal = [];
while ($row = $result->fetch_assoc()) {
    // Normalisasi URL gambar jika ada
    if (!empty($row['gambar'])) {
    $row['gambar'] = "http://10.0.2.2:8000/proyek_ujian_php/storage/soal/" . basename($row['gambar']);
}
    $daftar_soal[] = $row;
}

if (!empty($daftar_soal)) {
    sendResponse([
        "status" => "success",
        "message" => "Soal ujian berhasil dimuat.",
        "data" => $daftar_soal
    ]);
} else {
    sendResponse([
        "status" => "error",
        "message" => "Ujian tidak ditemukan atau belum ada soal.",
        "data" => []
    ], 404);
}

$stmt->close();
$conn->close();
?>