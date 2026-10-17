<?php
// api_submit_ujian.php
//
// Penilaian TIDAK lagi dihitung sendiri di file ini. Sebelumnya file ini
// memakai rumus "jumlah benar / jumlah soal yang DIJAWAB", sedangkan aplikasi
// web memakai "jumlah benar / JUMLAH SELURUH SOAL UJIAN" (lihat
// App\Models\HasilUjian::finalizeExam()). Akibatnya nilai dari aplikasi
// Android bisa berbeda dengan yang tampil di website.
//
// Sekarang endpoint ini menyimpan jawaban lalu memanggil finalizeExam() yang
// sama dengan versi web, sehingga hasilnya dijamin identik.
require 'db_config.php';
require 'api_auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Bootstrap Laravel (model & logika penilaian ada di sana).
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
} catch (\Throwable $e) {
    error_log('[API submit_ujian] Gagal bootstrap aplikasi: ' . $e->getMessage());
    sendResponse(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.'], 500);
}

try {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!$data || !isset($data['hasil_ujian_id'])) {
        sendResponse(["status" => "error", "message" => "Input JSON tidak valid atau kosong."], 400);
    }

    $hasil_ujian_id       = (int) $data['hasil_ujian_id'];
    $jawaban_siswa        = $data['jawaban_siswa'] ?? [];
    $status_penyelesaian  = $data['status_penyelesaian'] ?? 'normal';
    $keteranganPelanggaran = $data['keterangan_pelanggaran'] ?? null;

    if (!is_array($jawaban_siswa)) {
        sendResponse(["status" => "error", "message" => "Format jawaban_siswa tidak valid."], 400);
    }

    // Token wajib sah, dan hasil ujian harus milik siswa pemilik token.
    $siswa = apiAuth($conn);
    apiRequireHasilOwner($conn, $hasil_ujian_id, (int) $siswa['id']);

    $hasilUjian = \App\Models\HasilUjian::find($hasil_ujian_id);
    if (!$hasilUjian) {
        sendResponse(["status" => "error", "message" => "Data hasil ujian tidak ditemukan."], 404);
    }

    // Permintaan ini dianggap sebagai kumpulan jawaban lengkap, maka jawaban
    // lama dihapus lebih dulu supaya tidak ada sisa dari percobaan sebelumnya.
    \App\Models\JawabanSiswa::where('hasil_ujian_id', $hasil_ujian_id)->delete();

    foreach ($jawaban_siswa as $jawaban) {
        $soal_id = (int) ($jawaban['soal_id'] ?? 0);
        if ($soal_id <= 0) {
            continue;
        }

        \App\Models\JawabanSiswa::create([
            'hasil_ujian_id'  => $hasil_ujian_id,
            'soal_id'         => $soal_id,
            'jawaban_dipilih' => (string) ($jawaban['jawaban_dipilih'] ?? ''),
        ]);
    }

    // Hitung nilai + sinkronisasi ke ujian induk (untuk ujian susulan),
    // memakai logika yang sama persis dengan aplikasi web.
    $hasilUjian->finalizeExam($status_penyelesaian, $keteranganPelanggaran);
    $hasilUjian->refresh();

    $totalSoal = (int) \App\Models\Soal::where('ujian_id', $hasilUjian->ujian_id)->count();

    sendResponse([
        "status"  => "success",
        "message" => "Ujian berhasil dikumpulkan.",
        "data"    => [
            "nilai_akhir"  => (float) $hasilUjian->nilai,
            "jumlah_benar" => (int) $hasilUjian->jumlah_benar,
            "total_soal"   => $totalSoal,
        ],
    ]);

} catch (\Throwable $e) {
    error_log('[API submit_ujian] ' . $e->getMessage());
    sendResponse([
        'status'  => 'error',
        'message' => 'Gagal memproses pengumpulan ujian. Hubungi administrator.',
    ], 500);
}
