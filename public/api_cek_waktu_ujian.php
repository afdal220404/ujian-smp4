<?php
// api_cek_waktu_ujian.php
require 'db_config.php';
require 'api_auth.php';

$ujian_id = $_GET['ujian_id'] ?? 0;

if ($ujian_id == 0) {
    sendResponse(["status" => "error", "message" => "ID Ujian tidak valid."], 400);
}

// Token wajib sah; waktu ujian hanya boleh dibaca untuk ujian kelas siswa itu.
$siswa = apiAuth($conn);

$stmt = $conn->prepare("
    SELECT u.waktu_selesai
    FROM ujians u
    JOIN mapels m ON u.mapel_id = m.id
    WHERE u.id = ? AND m.kelas_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $ujian_id, $siswa['kelas_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $stmt->close();
    $conn->close();
    sendResponse([
        "status" => "success",
        "message" => "Waktu ujian berhasil diambil.",
        "data" => ["waktu_selesai" => $row['waktu_selesai']]
    ]);
}

// Tidak ditemukan, atau ujian milik kelas lain.
$stmt->close();
$conn->close();
sendResponse(["status" => "error", "message" => "Ujian tidak ditemukan."], 404);
