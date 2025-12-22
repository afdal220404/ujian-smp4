<?php
// api_start_ujian.php
require 'db_config.php';

// Pastikan permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Metode tidak diizinkan."], 405);
}

$ujian_id = $_POST['ujian_id'] ?? 0;
$siswa_id = $_POST['siswa_id'] ?? 0;
$waktu_mulai = date('Y-m-d H:i:s');

if ($ujian_id == 0 || $siswa_id == 0) {
    sendResponse(["status" => "error", "message" => "Data ujian atau siswa tidak lengkap."], 400);
}

// 1. CEK APAKAH SUDAH PERNAH UJIAN
$stmt_check = $conn->prepare("SELECT id FROM hasil_ujians WHERE ujian_id = ? AND siswa_id = ?");
$stmt_check->bind_param("ii", $ujian_id, $siswa_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Jika sudah ada, kembalikan ID lama dalam format 'data'
    $hasil = $result_check->fetch_assoc();
    sendResponse([
        "status" => "success",
        "message" => "Ujian sudah dimulai sebelumnya.",
        "data" => [
            "hasil_ujian_id" => $hasil['id']
        ]
    ]);
}  else {
    // 2. MASUKKAN DATA BARU
    $stmt = $conn->prepare("INSERT INTO hasil_ujians (ujian_id, siswa_id, waktu_mulai) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $ujian_id, $siswa_id, $waktu_mulai);
    
    if ($stmt->execute()) {
        sendResponse([
            "status" => "success",
            "message" => "Ujian dimulai.",
            "data" => ["hasil_ujian_id" => $conn->insert_id]
        ]);
    } else {
        // MENAMPILKAN ERROR JIKA INSERT GAGAL
        sendResponse(["status" => "error", "message" => "Gagal mencatat waktu mulai: " . $conn->error], 500);
    }
}
$stmt_check->close();
$stmt->close();
$conn->close();
?>