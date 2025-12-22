<?php
// api_nilai_siswa.php

// 1. MATIKAN DISPLAY ERROR (PENTING!)
// Agar jika ada warning PHP, tidak merusak struktur JSON
ini_set('display_errors', 0);
error_reporting(0);

require 'db_config.php';

// Pastikan Header JSON
header('Content-Type: application/json; charset=utf-8');

// Bersihkan buffer output sebelumnya (mencegah spasi kosong di awal)
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Metode tidak diizinkan."]);
    exit();
}

$siswa_id = $_GET['siswa_id'] ?? 0;
$kelas_id = $_GET['kelas_id'] ?? 0;

if ($siswa_id == 0 || $kelas_id == 0) {
    echo json_encode(["status" => "error", "message" => "Parameter kurang."]);
    exit();
}

// Ambil Mapel
$query_mapel = "
    SELECT m.id as mapel_id, m.nama_mapel, g.nama_lengkap as nama_guru
    FROM mapels m
    JOIN gurus g ON m.guru_id = g.id
    WHERE m.kelas_id = ?
    ORDER BY m.nama_mapel ASC
";

$stmt = $conn->prepare($query_mapel);
$stmt->bind_param("i", $kelas_id);
$stmt->execute();
$result_mapel = $stmt->get_result();

$data_final = [];

while ($mapel = $result_mapel->fetch_assoc()) {
    $mapel_id = $mapel['mapel_id'];

    // Query Nilai (Gunakan try-catch block logic jika perlu, tapi query SQL jarang crash PHP)
    $query_nilai = "
        SELECT h.id AS hasil_ujian_id, u.nama_ujian, u.tanggal_ujian, h.nilai
        FROM hasil_ujians h
        JOIN ujians u ON h.ujian_id = u.id
        WHERE h.siswa_id = ? AND u.mapel_id = ?
        ORDER BY u.tanggal_ujian DESC
    ";

    $stmt_nilai = $conn->prepare($query_nilai);
    $stmt_nilai->bind_param("ii", $siswa_id, $mapel_id);
    $stmt_nilai->execute();
    $result_nilai = $stmt_nilai->get_result();

    $riwayat_ujian = [];
    while ($nilai = $result_nilai->fetch_assoc()) {
        $nilai_angka = isset($nilai['nilai']) ? (float)$nilai['nilai'] : 0.0;
        
        $riwayat_ujian[] = [
            "hasil_ujian_id" => (int)$nilai['hasil_ujian_id'], // <--- TAMBAHKAN INI
            "nama_ujian" => $nilai['nama_ujian'] ?? "Ujian Tanpa Nama",
            "tanggal" => $nilai['tanggal_ujian'] ?? "-",
            "nilai" => $nilai_angka
        ];
    }

    // Pastikan structure mapel aman
    $mapel['riwayat_ujian'] = $riwayat_ujian;

    // Masukkan ke array utama
    $data_final[] = $mapel;

    // Tutup statement parsial untuk menghemat memori
    $stmt_nilai->close();
}

// OUTPUT JSON TERAKHIR
$response = [
    "status" => "success",
    "data" => $data_final
];

// Cek error JSON Encode (misal karena karakter aneh)
$json_output = json_encode($response, JSON_UNESCAPED_UNICODE);

if ($json_output === false) {
    // Jika gagal encode, kirim error manual
    echo json_encode(["status" => "error", "message" => "Gagal encode JSON: " . json_last_error_msg()]);
} else {
    echo $json_output;
}

$stmt->close();
$conn->close();
