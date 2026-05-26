<?php
require 'db_config.php';
header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

$ujian_id = $_GET['ujian_id'] ?? 0;
if (empty($ujian_id) || !is_numeric($ujian_id)) {
    echo json_encode(["status" => "error", "message" => "ID Ujian tidak valid."]); exit();
}

try {
    // JOIN soals dengan bank_soal_items
    $stmt = $conn->prepare("
        SELECT 
            s.id AS soal_id, 
            b.tipe, b.pertanyaan, b.gambar, 
            b.opsi_a, b.gambar_a,
            b.opsi_b, b.gambar_b,
            b.opsi_c, b.gambar_c,
            b.opsi_d, b.gambar_d,
            b.data_soal
        FROM soals s
        JOIN bank_soal_items b ON s.bank_soal_id = b.id
        WHERE s.ujian_id = ?
        ORDER BY s.id ASC
    ");
    $stmt->bind_param("i", $ujian_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $daftar_soal = [];
    $base_url = "https://ujian.smpn4tilkam.cloud/storage/"; 

    while ($row = $result->fetch_assoc()) {
        
        // Buat fungsi helper kecil agar kode lebih rapi dan "Anti-Gagal"
        $formatImageUrl = function($path) use ($base_url) {
            if (empty($path) || $path === 'null') return null;
            // Jika path sudah mengandung http (sudah berupa URL penuh), jangan ditimpa
            if (strpos($path, 'http') === 0) return $path;
            
            // Hapus prefix 'public/' jika Laravel tidak sengaja menyimpannya di DB
            $path = preg_replace('/^public\//', '', ltrim($path, '/'));
            return $base_url . $path;
        };

        // Format URL Gambar Opsi dengan aman
        $row['gambar']   = $formatImageUrl($row['gambar']);
        $row['gambar_a'] = $formatImageUrl($row['gambar_a']);
        $row['gambar_b'] = $formatImageUrl($row['gambar_b']);
        $row['gambar_c'] = $formatImageUrl($row['gambar_c']);
        $row['gambar_d'] = $formatImageUrl($row['gambar_d']);
        
        // Sanitasi JSON & Tambah URL Gambar di dalam JSON Kompleks
        if (!empty($row['data_soal'])) {
            $json_data = json_decode($row['data_soal'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                
                if ($row['tipe'] == 'benar_salah' && isset($json_data['pernyataan'])) {
                    foreach ($json_data['pernyataan'] as &$item) {
                        if (isset($item['correct'])) unset($item['correct']);
                        $item['gambar'] = $formatImageUrl($item['gambar'] ?? '');
                    }
                } else if ($row['tipe'] == 'menjodohkan' && isset($json_data['matches'])) {
                    foreach ($json_data['matches'] as &$item) {
                        $item['gambar_left']  = $formatImageUrl($item['gambar_left'] ?? '');
                        $item['gambar_right'] = $formatImageUrl($item['gambar_right'] ?? '');
                    }
                } else if ($row['tipe'] == 'jawaban_ganda' && isset($json_data['options'])) {
                    foreach ($json_data['options'] as &$item) {
                        $item['gambar'] = $formatImageUrl($item['gambar'] ?? '');
                    }
                }
                $row['data_soal'] = json_encode($json_data);
            }
        }
        $daftar_soal[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $daftar_soal]);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>