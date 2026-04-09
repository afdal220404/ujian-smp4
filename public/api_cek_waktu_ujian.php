<?php
// api_cek_waktu_ujian.php
require 'db_config.php';

$ujian_id = $_GET['ujian_id'] ?? 0;

if ($ujian_id == 0) {
    sendResponse(["status" => "error", "message" => "ID Ujian tidak valid."], 400);
}

$stmt = $conn->prepare("SELECT waktu_selesai FROM ujians WHERE id = ?");
$stmt->bind_param("i", $ujian_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    sendResponse([
        "status" => "success",
        "message" => "Waktu ujian berhasil diambil.",
        "data" => ["waktu_selesai" => $row['waktu_selesai']]
    ]);
} else {
    sendResponse(["status" => "error", "message" => "Ujian tidak ditemukan."], 404);
}

$stmt->close();
$conn->close();
?>
