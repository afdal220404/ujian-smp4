<?php

use App\Helpers\TextHelper;
use Carbon\Carbon;

if (!function_exists('format_soal')) {
    /**
     * Helper global untuk memformat teks soal secara aman (Bold, Italic, Underline, dsb).
     *
     * @param string|null $text
     * @return string
     */
    function format_soal(?string $text): string
    {
        return TextHelper::formatSoal($text);
    }
}

if (!function_exists('getTahunAjaran')) {
    /**
     * Tahun ajaran yang sedang berjalan (mulai Juli).
     *
     * Contoh: Agustus 2026 -> "2026/2027", Maret 2026 -> "2025/2026".
     *
     * Satu-satunya sumber logika ini. Dipakai oleh controller, impor/ekspor
     * dan juga endpoint API (public/api_daftar_ujian.php) supaya daftar ujian
     * yang tersaring selalu memakai tahun ajaran yang sama.
     *
     * @return string
     */
    function getTahunAjaran(): string
    {
        $now = Carbon::now('Asia/Jakarta');
        $month = (int) $now->month;
        $year = (int) $now->year;

        if ($month >= 7) {
            return $year . '/' . ($year + 1);
        }

        return ($year - 1) . '/' . $year;
    }
}
