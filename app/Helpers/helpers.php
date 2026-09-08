<?php

use App\Helpers\TextHelper;

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
