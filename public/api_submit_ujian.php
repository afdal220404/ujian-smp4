<?php
// api_submit_ujian.php
ob_clean(); // Bersihkan output buffer sebelumnya
header('Content-Type: application/json'); // Pastikan header JSON

require 'db_config.php';

// Pastikan permintaan adalah POST dan menerima JSON body
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Metode tidak diizinkan."], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

$hasil_ujian_id = $data['hasil_ujian_id'] ?? 0;
$jawaban_siswa = $data['jawaban_siswa'] ?? []; // Array of {soalId, jawabanPilihan}
$waktu_selesai = date('Y-m-d H:i:s');

if ($hasil_ujian_id == 0 || empty($jawaban_siswa)) {
    sendResponse(["status" => "error", "message" => "Data submit tidak lengkap."], 400);
}

// 1. Ambil Semua Kunci Jawaban
$soal_ids = array_map(function ($j) {
    return $j['soalId'];
}, $jawaban_siswa);
$soal_ids_str = implode(',', $soal_ids);

$result_kunci = $conn->query("SELECT id AS soal_id, kunci_jawaban FROM soals WHERE id IN ($soal_ids_str)");
$kunci_map = [];
while ($row = $result_kunci->fetch_assoc()) {
    $kunci_map[$row['soal_id']] = $row['kunci_jawaban'];
}
$total_soal = count($kunci_map);
$jumlah_benar = 0;

// 2. Simpan Jawaban Siswa & Hitung Jumlah Benar
$stmt_jawaban = $conn->prepare("INSERT INTO jawaban_siswas (hasil_ujian_id, soal_id, jawaban_dipilih, is_correct) VALUES (?, ?, ?, ?)");
$conn->begin_transaction(); // Mulai transaksi

try {
    foreach ($jawaban_siswa as $jawaban) {
        $soal_id = $jawaban['soalId'];
        $pilihan = $jawaban['jawabanPilihan'];
        $kunci = $kunci_map[$soal_id] ?? null;

        $is_correct = ($kunci !== null && $pilihan === $kunci) ? 1 : 0;
        if ($is_correct) {
            $jumlah_benar++;
        }

        $stmt_jawaban->bind_param("iisi", $hasil_ujian_id, $soal_id, $pilihan, $is_correct);
        if (!$stmt_jawaban->execute()) throw new Exception("Gagal simpan jawaban.");
    }

    // 3. Hitung Nilai Akhir
    $nilai = ($total_soal > 0) ? round(($jumlah_benar / $total_soal) * 100, 2) : 0.00;

    // 4. Update Hasil Ujian
    $stmt_update = $conn->prepare("UPDATE hasil_ujians SET nilai = ?, jumlah_benar = ?, waktu_selesai = ? WHERE id = ?");
    $stmt_update->bind_param("disi", $nilai, $jumlah_benar, $waktu_selesai, $hasil_ujian_id);
    if (!$stmt_update->execute()) throw new Exception("Gagal update hasil ujian.");

    $conn->commit(); // Commit transaksi

    sendResponse([
        "status" => "success",
        "message" => "Ujian berhasil dikumpulkan.",
        "data" => [ // PENTING: Bungkus dalam objek 'data' agar sesuai dengan ApiResponse<T>
            "nilai_akhir" => (float)$nilai,
            "jumlah_benar" => (int)$jumlah_benar
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback(); // Rollback jika ada error
    sendResponse(["status" => "error", "message" => "Proses submit gagal: " . $e->getMessage()], 500);
}

$stmt_jawaban->close();
$conn->close();
