<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Format dan sanitasi teks soal agar mendukung Bold, Italic, Underline, dan tag format aman lainnya.
     *
     * @param string|null $text
     * @return string
     */
    public static function formatSoal(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // 1. Tag HTML yang diizinkan untuk formatting soal
        $allowedTags = '<b><strong><i><em><u><s><strike><sub><sup><mark><small><span><br><p>';
        $sanitized = strip_tags($text, $allowedTags);

        // 2. Hapus atribut berbahaya seperti onerror, onclick, javascript: dsb
        $sanitized = preg_replace('/\s*(on\w+|href|src)\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/i', '', $sanitized);

        // 3. Konversi newline ke <br> jika belum ada tag block/break
        if (!preg_match('/<br\s*\/?>|<p>/i', $sanitized)) {
            $sanitized = nl2br($sanitized);
        }

        return $sanitized;
    }
}
