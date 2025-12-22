<?php
// api_detail_hasil.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require 'db_config.php';

header('Content-Type: application/json; charset=utf-8');
ob_clean();

$hasil_ujian_id = $_GET['hasil_ujian_id'] ?? 0;

if ($hasil_ujian_id == 0) {
    echo json_encode(["status" => "error", "message" => "ID Hasil Ujian tidak valid."]);
    exit();
}

// --- QUERY 1: INFO DETAIL ---
// Kita cek apakah query ini valid
$query_info = "
    SELECT 
        h.nilai, 
        h.jumlah_benar,
        h.waktu_mulai AS mhs_mulai,
        h.waktu_selesai AS mhs_selesai,
        u.nama_ujian,
        u.jenis_ujian,
        u.tanggal_ujian,
        u.waktu_mulai AS jadwal_mulai,
        u.waktu_selesai AS jadwal_selesai,
        (SELECT COUNT(*) FROM soals WHERE ujian_id = u.id) AS total_soal
    FROM hasil_ujians h
    JOIN ujians u ON h.ujian_id = u.id
    WHERE h.id = ?
";

$stmt = $conn->prepare($query_info);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL Error (Info): " . $conn->error
    ]);
    exit();
}
$stmt->bind_param("i", $hasil_ujian_id);
$stmt->execute();
$info_result = $stmt->get_result();

if ($info_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Data ujian tidak ditemukan."]);
    exit();
}

$info = $info_result->fetch_assoc();
$stmt->close();


// --- QUERY 2: DETAIL SOAL ---
$query_detail = "
    SELECT 
        s.pertanyaan, 
        s.gambar,
        s.kunci_jawaban, 
        s.opsi_a, s.opsi_b, s.opsi_c, s.opsi_d, s.opsi_e,
        j.jawaban_dipilih, 
        j.is_correct
    FROM jawaban_siswas j
    JOIN soals s ON j.soal_id = s.id
    WHERE j.hasil_ujian_id = ?
    ORDER BY s.id ASC
";

$stmt_detail = $conn->prepare($query_detail);

// [CEK ERROR SQL LAGI]
if (!$stmt_detail) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL Error (Detail): " . $conn->error
    ]);
    exit();
}

$stmt_detail->bind_param("i", $hasil_ujian_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

$list_soal = [];
while ($row = $result_detail->fetch_assoc()) {
    $list_soal[] = $row;
}

// Output JSON Akhir
echo json_encode([
    "status" => "success",
    "data" => [
        "info" => $info,
        "detail" => $list_soal
    ]
]);

$conn->close();
