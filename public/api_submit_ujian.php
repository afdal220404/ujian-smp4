<?php
// PAKSA PHP UNTUK MENGELUARKAN SEMUA ERROR KE DALAM FORMAT JSON
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Penangkap Error Ekstrem (Agar tidak 0-byte jika terjadi Crash)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            "status" => "error", 
            "message" => "FATAL CRASH: " . $error['message'] . " (Baris: " . $error['line'] . ")"
        ]);
        exit;
    }
});

header('Content-Type: application/json; charset=utf-8');

try {
    require 'db_config.php';

    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!$data || !isset($data['hasil_ujian_id'])) {
        echo json_encode(["status" => "error", "message" => "Input JSON tidak valid atau kosong."]);
        exit;
    }

    $hasil_ujian_id = (int)$data['hasil_ujian_id'];
    $jawaban_siswa = $data['jawaban_siswa'] ?? [];
    $waktu_selesai = date('Y-m-d H:i:s');
    
    $jumlah_benar = 0;
    $total_soal = 0;

    // 1. HAPUS JAWABAN LAMA (Agar tidak terjadi Duplicate Key Error MySQL)
    $stmt_del = $conn->prepare("DELETE FROM jawaban_siswas WHERE hasil_ujian_id = ?");
    if (!$stmt_del) {
        throw new Exception("Error Query Delete: " . $conn->error);
    }
    $stmt_del->bind_param("i", $hasil_ujian_id);
    $stmt_del->execute();
    $stmt_del->close();

    // 2. PERSIAPKAN INSERT
    // PENTING: Menambahkan created_at & updated_at untuk mencegah Error Strict Mode Laravel
    $q_jawaban = "INSERT INTO jawaban_siswas (hasil_ujian_id, soal_id, jawaban_dipilih, is_correct, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
    $stmt_jawaban = $conn->prepare($q_jawaban);
    
    if (!$stmt_jawaban) {
        throw new Exception("Error Query Insert Jawaban: " . $conn->error);
    }

    // 3. PERSIAPKAN QUERY SOAL
    $q_soal = "SELECT s.id, b.tipe, b.kunci_jawaban, b.data_soal FROM soals s JOIN bank_soal_items b ON s.bank_soal_id = b.id WHERE s.id = ?";
    $stmt_s = $conn->prepare($q_soal);
    if (!$stmt_s) {
        throw new Exception("Error Query Select Soal: " . $conn->error);
    }

    // 4. PROSES JAWABAN
    foreach ($jawaban_siswa as $jawaban) {
        $soal_id = (int)($jawaban['soal_id'] ?? 0);
        $jawaban_input = (string)($jawaban['jawaban_dipilih'] ?? '');
        $is_correct = 0;

        if ($soal_id > 0) {
            $stmt_s->bind_param("i", $soal_id);
            $stmt_s->execute();
            $res_s = $stmt_s->get_result()->fetch_assoc();

            if ($res_s) {
                $total_soal++;
                $tipe = $res_s['tipe'];
                $kunci_db = trim($res_s['kunci_jawaban'] ?? "");
                
                // KOREKSI
                if ($tipe == 'pilihan_ganda') {
                    if (strtoupper(trim($jawaban_input)) === strtoupper($kunci_db)) {
                        $is_correct = 1;
                    }
                } elseif ($tipe == 'jawaban_ganda') {
                    $arr_kunci = explode(',', strtoupper($kunci_db));
                    $arr_input = explode(',', strtoupper($jawaban_input));
                    $arr_kunci = array_map('trim', $arr_kunci);
                    $arr_input = array_map('trim', $arr_input);
                    sort($arr_kunci);
                    sort($arr_input);
                    if ($arr_kunci === $arr_input) {
                        $is_correct = 1;
                    }
                } elseif ($tipe == 'benar_salah') {
                    $db_data = json_decode($res_s['data_soal'], true);
                    $input_obj = json_decode($jawaban_input, true); 
                    if ($db_data && isset($db_data['pernyataan']) && is_array($input_obj)) {
                        $semua_cocok = true;
                        foreach ($db_data['pernyataan'] as $index => $item) {
                            $kunci = strtoupper($item['correct'] ?? "");
                            $jawab = isset($input_obj[$index]) ? strtoupper($input_obj[$index]) : "";
                            if ($kunci !== $jawab) {
                                $semua_cocok = false;
                                break;
                            }
                        }
                        if ($semua_cocok && count($input_obj) === count($db_data['pernyataan'])) {
                            $is_correct = 1;
                        }
                    }
                } elseif ($tipe == 'menjodohkan') {
                    $db_data = json_decode($res_s['data_soal'], true);
                    $input_obj = json_decode($jawaban_input, true); 
                    if ($db_data && isset($db_data['matches']) && is_array($input_obj)) {
                        $semua_cocok = true;
                        $matches_db = $db_data['matches'];
                        if (count($input_obj) !== count($matches_db)) {
                            $semua_cocok = false;
                        } else {
                            foreach ($matches_db as $index => $item) {
                                $left_id = "L" . $index;
                                $right_id_correct = "R" . $index; 
                                if (!isset($input_obj[$left_id]) || $input_obj[$left_id] !== $right_id_correct) {
                                    $semua_cocok = false;
                                    break;
                                }
                            }
                        }
                        if ($semua_cocok) {
                            $is_correct = 1;
                        }
                    }
                }

                if ($is_correct) {
                    $jumlah_benar++;
                }

                // SIMPAN JAWABAN
                $stmt_jawaban->bind_param("iisi", $hasil_ujian_id, $soal_id, $jawaban_input, $is_correct);
                if (!$stmt_jawaban->execute()) {
                    throw new Exception("Gagal simpan jawaban untuk soal ID " . $soal_id . ": " . $stmt_jawaban->error);
                }
            }
        }
    }
    $stmt_jawaban->close();
    $stmt_s->close();

    // 5. HITUNG NILAI AKHIR
    $nilai = ($total_soal > 0) ? round(($jumlah_benar / $total_soal) * 100, 2) : 0;

    // 6. UPDATE HASIL UJIAN
    $q_update = "UPDATE hasil_ujians SET nilai = ?, jumlah_benar = ?, waktu_selesai = ? WHERE id = ?";
    $stmt_update = $conn->prepare($q_update);
    if (!$stmt_update) {
        throw new Exception("Error Query Update Hasil: " . $conn->error);
    }
    $stmt_update->bind_param("disi", $nilai, $jumlah_benar, $waktu_selesai, $hasil_ujian_id);
    $stmt_update->execute();
    $stmt_update->close();

    // KIRIM BALASAN SUKSES KE ANDROID
    echo json_encode([
        "status" => "success",
        "message" => "Ujian berhasil dikumpulkan.",
        "data" => [ 
            "nilai_akhir" => (float)$nilai,
            "jumlah_benar" => (int)$jumlah_benar,
            "total_soal" => (int)$total_soal
        ]
    ]);

} catch (Throwable $e) {
    // MENANGKAP SEMUA ERROR & DITAMPILKAN KE LOGCAT ANDROID
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "DB Error: " . $e->getMessage()
    ]);
}
?>