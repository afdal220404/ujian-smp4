<?php
// api_detail_hasil.php

// 1. AKTIFKAN DEBUGGING (Agar kita tahu error aslinya apa)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. HEADER JSON
header('Content-Type: application/json; charset=utf-8');

// 3. CLEAN BUFFER (Mencegah error header)
// Cek apakah buffer aktif sebelum di-clean
if (ob_get_length()) ob_clean();

try {
    // Cek file koneksi
    if (!file_exists('db_config.php')) {
        throw new Exception("File db_config.php tidak ditemukan.");
    }
    require 'db_config.php';

    // Cek koneksi database
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Koneksi database gagal.");
    }

    $hasil_ujian_id = $_GET['hasil_ujian_id'] ?? 0;

    if ($hasil_ujian_id == 0) {
        throw new Exception("ID Hasil Ujian tidak valid (0 atau kosong).");
    }

    // --- QUERY 1: INFO DETAIL ---
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
        throw new Exception("SQL Error (Info): " . $conn->error);
    }
    
    $stmt->bind_param("i", $hasil_ujian_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute Error (Info): " . $stmt->error);
    }
    
    $info_result = $stmt->get_result();

    if ($info_result->num_rows === 0) {
        throw new Exception("Data ujian tidak ditemukan untuk ID: " . $hasil_ujian_id);
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
    if (!$stmt_detail) {
        throw new Exception("SQL Error (Detail): " . $conn->error);
    }

    $stmt_detail->bind_param("i", $hasil_ujian_id);
    if (!$stmt_detail->execute()) {
        throw new Exception("Execute Error (Detail): " . $stmt_detail->error);
    }
    
    $result_detail = $stmt_detail->get_result();
    $list_soal = [];

    // --- KONFIGURASI URL ---
    // Pastikan IP ini sesuai dengan yang muncul di ipconfig komputer Anda
    // Jangan gunakan localhost atau 127.0.0.1 jika diakses dari HP fisik
    $BASE_URL = "https://ujian.smpn4tilkam.cloud/"; 

    while ($row = $result_detail->fetch_assoc()) {
        // Logika Gambar
        if (!empty($row['gambar'])) {
            // Jika path gambar belum ada http, kita tambahkan
            if (strpos($row['gambar'], 'http') !== 0) {
                // Hati-hati dengan slash (/)
                $row['gambar'] = $BASE_URL . "/storage/soal/" . basename($row['gambar']);
            }
        }
        $list_soal[] = $row;
    }
    
    $stmt_detail->close();
    $conn->close();

    // KIRIM RESPON SUKSES
    echo json_encode([
        "status" => "success",
        "data" => [
            "info" => $info,
            "detail" => $list_soal
        ]
    ]);

} catch (Exception $e) {
    // KIRIM RESPON ERROR YANG JELAS
    http_response_code(500); // Set HTTP code error
    echo json_encode([
        "status" => "error",
        "message" => "Server Error: " . $e->getMessage()
    ]);
}
?>