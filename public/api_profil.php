<?php
// api_profil.php
ini_set('display_errors', 0);
error_reporting(0);
require 'db_config.php';
header('Content-Type: application/json; charset=utf-8');
ob_clean();

$action = $_GET['action'] ?? '';

if ($action == 'get_profil') {
    // --- AMBIL DATA PROFIL & STATISTIK ---
    $siswa_id = $_GET['siswa_id'] ?? 0;

    // 1. Ambil Data Siswa & Kelas
    $q_siswa = "SELECT s.nama_lengkap, s.nisn, s.username, k.kelas 
                FROM siswas s 
                JOIN kelas k ON s.kelas_id = k.id 
                WHERE s.id = ?";
    $stmt = $conn->prepare($q_siswa);
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();
    $d_siswa = $stmt->get_result()->fetch_assoc();

    if (!$d_siswa) {
        echo json_encode(["status" => "error", "message" => "Siswa tidak ditemukan"]);
        exit();
    }

    // 2. Hitung Rata-rata Total & Jumlah Ujian
    $q_stats = "SELECT AVG(nilai) as rata_total, COUNT(id) as total_ujian 
                FROM hasil_ujians WHERE siswa_id = ?";
    $stmt2 = $conn->prepare($q_stats);
    $stmt2->bind_param("i", $siswa_id);
    $stmt2->execute();
    $d_stats = $stmt2->get_result()->fetch_assoc();

    // 3. Cari Mapel Terkuat (Nilai Rata-rata Tertinggi per Mapel)
    $q_best = "SELECT m.nama_mapel, AVG(h.nilai) as rata_mapel
               FROM hasil_ujians h
               JOIN ujians u ON h.ujian_id = u.id
               JOIN mapels m ON u.mapel_id = m.id
               WHERE h.siswa_id = ?
               GROUP BY m.id, m.nama_mapel
               ORDER BY rata_mapel DESC
               LIMIT 1";
    $stmt3 = $conn->prepare($q_best);
    $stmt3->bind_param("i", $siswa_id);
    $stmt3->execute();
    $d_best = $stmt3->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "data" => [
            "profil" => $d_siswa,
            "stats" => [
                "rata_total" => number_format((float)($d_stats['rata_total'] ?? 0), 1),
                "total_ujian" => $d_stats['total_ujian'] ?? 0,
                "mapel_terkuat" => $d_best['nama_mapel'] ?? "-"
            ]
        ]
    ]);

} elseif ($action == 'change_password') {
    // --- GANTI PASSWORD ---
    // Gunakan POST raw body atau form-data
    $siswa_id = $_POST['siswa_id'] ?? 0;
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';

    if (empty($siswa_id) || empty($old_pass) || empty($new_pass)) {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
        exit();
    }

    // Ambil password lama (hashed)
    $stmt = $conn->prepare("SELECT password FROM siswas WHERE id = ?");
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($old_pass, $result['password'])) {
        // Password lama cocok, update ke baru
        $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt_up = $conn->prepare("UPDATE siswas SET password = ? WHERE id = ?");
        $stmt_up->bind_param("si", $new_hash, $siswa_id);
        
        if ($stmt_up->execute()) {
            echo json_encode(["status" => "success", "message" => "Password berhasil diubah."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal update database."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Password lama salah."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Action tidak valid."]);
}
?>