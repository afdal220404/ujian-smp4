<?php
// api_bank_soal.php
ini_set('display_errors', 0);
error_reporting(0);
require 'db_config.php';
header('Content-Type: application/json; charset=utf-8');
ob_clean();

$kelas_id = $_GET['kelas_id'] ?? 0;

if ($kelas_id == 0) {
    echo json_encode(["status" => "error", "message" => "Kelas ID diperlukan"]);
    exit();
}


$query = "
    SELECT b.id, b.nama as judul_materi, b.file_path, b.created_at,
           m.nama_mapel, g.nama_lengkap as nama_guru
    FROM bank_soals b
    JOIN mapels m ON b.mapel_id = m.id
    LEFT JOIN gurus g ON b.guru_id = g.id
    WHERE m.kelas_id = ?
    AND b.visibilitas = 'Public' 
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kelas_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
?>