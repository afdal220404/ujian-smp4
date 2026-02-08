<?php
// api_daftar_ujian.php
require 'db_config.php';

$kelas_id = $_GET['kelas_id'] ?? 0;

$siswa_id = $_GET['siswa_id'] ?? 0; 

if (empty($kelas_id) || empty($siswa_id)) {
    sendResponse(["status" => "error", "message" => "ID Kelas atau Siswa tidak valid."], 400);
}

$sql = "
    SELECT 
        u.id AS ujian_id, u.nama_ujian, u.jenis_ujian, u.durasi_menit,
        m.nama_mapel, k.kelas,
        u.waktu_mulai, u.waktu_selesai,
        (SELECT COUNT(*) FROM hasil_ujians h WHERE h.ujian_id = u.id AND h.siswa_id = ?) as status_pengerjaan
    FROM ujians u
    JOIN mapels m ON u.mapel_id = m.id
    JOIN kelas k ON m.kelas_id = k.id
    WHERE 
        m.kelas_id = ? 
    ORDER BY u.waktu_mulai DESC 
";

$stmt = $conn->prepare($sql);
// Bind parameter: siswa_id (untuk subquery), kelas_id (untuk where utama)
$stmt->bind_param("ii", $siswa_id, $kelas_id); 

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