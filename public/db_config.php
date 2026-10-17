<?php
/**
 * Konfigurasi & bootstrap bersama untuk seluruh endpoint API (public/api_*.php)
 *
 * Perubahan penting:
 *  1. Kredensial database TIDAK lagi ditulis di dalam kode. Nilainya dibaca
 *     dari berkas .env milik instalasi (satu sekolah = satu instalasi = satu .env).
 *  2. Pengaturan error dipusatkan di sini supaya peringatan PHP tidak pernah
 *     merusak keluaran JSON yang dibaca aplikasi Android.
 *  3. Penyangga keluaran (output buffer) diaktifkan agar ob_clean() di endpoint
 *     bernilai sah dan JSON dijamin bersih.
 */

date_default_timezone_set('Asia/Jakarta');

$__root = dirname(__DIR__);

// ---------------------------------------------------------------------------
// 1. Muat autoloader Composer + berkas .env (bila tersedia)
// ---------------------------------------------------------------------------
if (is_file($__root . '/vendor/autoload.php')) {
    require_once $__root . '/vendor/autoload.php';

    if (is_file($__root . '/.env') && class_exists(\Dotenv\Dotenv::class)) {
        try {
            \Dotenv\Dotenv::createImmutable($__root)->safeLoad();
        } catch (\Throwable $e) {
            // .env tidak terbaca -> pakai variabel lingkungan / nilai default
        }
    }

    // Helper global aplikasi (getTahunAjaran(), format_soal(), dll.)
    if (is_file($__root . '/app/Helpers/helpers.php')) {
        require_once $__root . '/app/Helpers/helpers.php';
    }
}

if (!function_exists('envValue')) {
    /**
     * Ambil nilai konfigurasi: .env -> variabel lingkungan -> nilai default.
     */
    function envValue(string $key, $default = null)
    {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        $value = getenv($key);

        return ($value !== false && $value !== '') ? $value : $default;
    }
}

// ---------------------------------------------------------------------------
// 2. Pengaturan error (satu tempat saja untuk semua endpoint API)
//
//    API_DEBUG=1 hanya untuk pengembangan. Di produksi WAJIB 0, karena
//    peringatan PHP yang ikut tercetak akan merusak struktur JSON sehingga
//    aplikasi Android gagal membaca balasan.
// ---------------------------------------------------------------------------
$API_DEBUG = filter_var(envValue('API_DEBUG', '0'), FILTER_VALIDATE_BOOLEAN);

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', $API_DEBUG ? '1' : '0');
ini_set('display_startup_errors', $API_DEBUG ? '1' : '0');

// ---------------------------------------------------------------------------
// 3. Penyangga keluaran (dibutuhkan oleh ob_clean() di endpoint)
// ---------------------------------------------------------------------------
if (ob_get_level() === 0) {
    ob_start();
}

// ---------------------------------------------------------------------------
// 4. Koneksi database
// ---------------------------------------------------------------------------
$host = envValue('DB_HOST', '127.0.0.1');
$port = (int) envValue('DB_PORT', 3306);
$db   = envValue('DB_DATABASE', '');
$user = envValue('DB_USERNAME', 'root');
$pass = (string) envValue('DB_PASSWORD', '');

$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    error_log('[API] Koneksi database gagal: ' . $conn->connect_error);
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Koneksi database gagal. Hubungi administrator.',
    ]);
    die();
}

$conn->set_charset('utf8mb4');

if (!function_exists('sendResponse')) {
    /**
     * Kirim balasan JSON lalu hentikan skrip.
     *
     * @param array $data
     * @param int   $statusCode
     */
    function sendResponse($data, $statusCode = 200)
    {
        if (ob_get_level() > 0) {
            ob_clean(); // buang sisa keluaran (mis. peringatan PHP) sebelum JSON
        }

        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        die();
    }
}
