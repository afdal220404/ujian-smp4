<?php
/**
 * Autentikasi token untuk endpoint API siswa (public/api_*.php).
 *
 * Cara pakai di setiap endpoint:
 *
 *     require 'db_config.php';
 *     require 'api_auth.php';
 *
 *     $siswa = apiAuth($conn);                 // token wajib sah
 *     $siswa = apiAuth($conn, $siswa_id);      // + pastikan pemilik data
 *
 * Klien (aplikasi Android) mengirimkan token yang didapat dari
 * api_login_siswa.php melalui salah satu header berikut:
 *
 *     X-Api-Token: <token>
 *     Authorization: Bearer <token>
 *
 * Token disimpan pada kolom siswas.api_token dan berlaku selama
 * API_TOKEN_TTL_DAYS hari (default 30), diperbarui setiap kali login.
 */

if (!defined('API_TOKEN_TTL_DAYS')) {
    define('API_TOKEN_TTL_DAYS', 30);
}

if (!function_exists('apiTokenFromRequest')) {
    /**
     * Baca token dari header permintaan.
     */
    function apiTokenFromRequest(): string
    {
        $token = '';

        if (!empty($_SERVER['HTTP_X_API_TOKEN'])) {
            $token = $_SERVER['HTTP_X_API_TOKEN'];
        } elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            // Sebagian konfigurasi Apache/PHP-FPM memindahkan header ke sini
            $token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        $token = trim($token);

        if (stripos($token, 'bearer ') === 0) {
            $token = trim(substr($token, 7));
        }

        return $token;
    }
}

if (!function_exists('apiTokenExpiry')) {
    /**
     * Waktu kedaluwarsa token (format MySQL DATETIME).
     */
    function apiTokenExpiry(int $days = API_TOKEN_TTL_DAYS): string
    {
        return date('Y-m-d H:i:s', strtotime('+' . $days . ' days'));
    }
}

if (!function_exists('apiIssueToken')) {
    /**
     * Terbitkan token baru untuk seorang siswa (dipakai saat login).
     */
    function apiIssueToken(mysqli $conn, int $siswaId, int $days = API_TOKEN_TTL_DAYS): string
    {
        $token   = bin2hex(random_bytes(32)); // 64 karakter
        $expires = apiTokenExpiry($days);

        $stmt = $conn->prepare('UPDATE siswas SET api_token = ?, api_token_expires_at = ? WHERE id = ?');
        $stmt->bind_param('ssi', $token, $expires, $siswaId);
        $stmt->execute();
        $stmt->close();

        return $token;
    }
}

if (!function_exists('apiAuth')) {
    /**
     * Wajibkan token yang sah.
     *
     * @param mysqli   $conn
     * @param int|null $expectedSiswaId Bila diisi, pastikan token milik siswa tsb.
     *
     * @return array Baris siswa: id, nama_lengkap, kelas_id
     */
    function apiAuth(mysqli $conn, $expectedSiswaId = null): array
    {
        $token = apiTokenFromRequest();

        if ($token === '') {
            sendResponse([
                'status'  => 'error',
                'message' => 'Tidak terautentikasi. Sertakan token pada header X-Api-Token.',
            ], 401);
        }

        $stmt = $conn->prepare(
            'SELECT id, nama_lengkap, kelas_id, api_token_expires_at
             FROM siswas
             WHERE api_token = ?
             LIMIT 1'
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $siswa = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$siswa) {
            sendResponse([
                'status'  => 'error',
                'message' => 'Token tidak dikenali. Silakan login ulang.',
            ], 401);
        }

        if (!empty($siswa['api_token_expires_at']) && strtotime($siswa['api_token_expires_at']) < time()) {
            sendResponse([
                'status'  => 'error',
                'message' => 'Masa berlaku token sudah habis. Silakan login ulang.',
            ], 401);
        }

        if ($expectedSiswaId !== null && (int) $expectedSiswaId !== (int) $siswa['id']) {
            sendResponse([
                'status'  => 'error',
                'message' => 'Anda tidak berhak mengakses data siswa lain.',
            ], 403);
        }

        return $siswa;
    }
}

if (!function_exists('apiRequireKelas')) {
    /**
     * Pastikan kelas yang diminta memang kelas milik siswa yang login.
     */
    function apiRequireKelas(array $siswa, $kelasId): void
    {
        if ((int) $kelasId !== (int) $siswa['kelas_id']) {
            sendResponse([
                'status'  => 'error',
                'message' => 'Anda tidak berhak mengakses data kelas lain.',
            ], 403);
        }
    }
}

if (!function_exists('apiRequireHasilOwner')) {
    /**
     * Ambil satu baris hasil ujian dan pastikan itu milik siswa yang login.
     *
     * @return array Baris hasil ujian (id, ujian_id, siswa_id, waktu_selesai, ...)
     */
    function apiRequireHasilOwner(mysqli $conn, $hasilUjianId, int $siswaId): array
    {
        $stmt = $conn->prepare(
            'SELECT id, ujian_id, siswa_id, waktu_selesai, nilai, jumlah_benar
             FROM hasil_ujians
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->bind_param('i', $hasilUjianId);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$hasil) {
            sendResponse([
                'status'  => 'error',
                'message' => 'Data hasil ujian tidak ditemukan.',
            ], 404);
        }

        if ((int) $hasil['siswa_id'] !== $siswaId) {
            sendResponse([
                'status'  => 'error',
                'message' => 'Anda tidak berhak mengakses hasil ujian siswa lain.',
            ], 403);
        }

        return $hasil;
    }
}
