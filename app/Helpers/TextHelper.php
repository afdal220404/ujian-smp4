<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Konversi pecahan unicode dan sintaks pecahan kustom (\frac{a}{b} atau [frac]a/b[/frac])
     * menjadi format HTML pecahan bertingkat (atas dan bawah).
     *
     * @param string $text
     * @return string
     */
    public static function formatFractions(string $text): string
    {
        // 0. Perbaiki entitas pecahan yang mungkin bocor sebagai teks mentah
        $text = preg_replace('/&lt;span\s+class=["\']?math-frac["\']?&gt;\s*&lt;span\s+class=["\']?frac-top["\']?&gt;([\s\S]*?)&lt;\/span&gt;\s*&lt;span\s+class=["\']?frac-bottom["\']?&gt;([\s\S]*?)(?:&lt;\/span&gt;)+/ui', '\frac{$1}{$2}', $text);
        $text = preg_replace('/&lt;span\s+class=["\']?math-frac["\']?&gt;&lt;span\s+class=["\']?frac-top["\']?&gt;([\s\S]*?)&lt;span\s+class=["\']?frac-bottom["\']?&gt;([^&<]*)/ui', '\frac{$1}{$2}', $text);
        $text = preg_replace('/<span\s+class=["\']?math-frac["\']?>\s*<span\s+class=["\']?frac-top["\']?>\s*([\s\S]*?)\s*<\/span>\s*<span\s+class=["\']?frac-bottom["\']?>\s*([\s\S]*?)\s*(?:<\/span>)+/ui', '\frac{$1}{$2}', $text);
        $text = preg_replace('/&lt;(\/?(?:span|sup|sub|b|strong|i|em|u|s|strike|mark|small|br|p))((?:\s+[^&<>]*?)?)&gt;/ui', '<$1$2>', $text);

        // 1. Peta pecahan Unicode bawaan
        foreach (self::unicodeFractions() as $char => $frac) {
            if (mb_strpos($text, $char) !== false) {
                $replacement = '<span class="math-frac"><span class="frac-top">' . $frac[0] . '</span><span class="frac-bottom">' . $frac[1] . '</span></span>';
                $text = str_replace($char, $replacement, $text);
            }
        }

        // 2. Format sintaks pecahan LaTeX-like: \frac{a}{b}
        $loop = 0;
        $patternLatex = '/\\\\frac\s*\{([^{}]+?)\}\s*\{([^{}]+?)\}/u';
        while (preg_match($patternLatex, $text) && $loop < 5) {
            $text = preg_replace(
                $patternLatex,
                '<span class="math-frac"><span class="frac-top">$1</span><span class="frac-bottom">$2</span></span>',
                $text
            );
            $loop++;
        }

        // 3. Format sintaks pecahan BBCode-like: [frac]a/b[/frac] atau [frac]a|b[/frac]
        $loop = 0;
        $patternBbcode = '/\[frac\]\s*([^\[\]\/\|]+?)\s*(?:\/|\|)\s*([^\[\]\/\|]+?)\s*\[\/frac\]/ui';
        while (preg_match($patternBbcode, $text) && $loop < 5) {
            $text = preg_replace(
                $patternBbcode,
                '<span class="math-frac"><span class="frac-top">$1</span><span class="frac-bottom">$2</span></span>',
                $text
            );
            $loop++;
        }

        return $text;
    }

    /**
     * Peta pecahan unicode yang didukung: karakter => [pembilang, penyebut].
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private static function unicodeFractions(): array
    {
        return [
            '½' => ['1', '2'], '⅓' => ['1', '3'], '⅔' => ['2', '3'],
            '¼' => ['1', '4'], '¾' => ['3', '4'], '⅕' => ['1', '5'],
            '⅖' => ['2', '5'], '⅗' => ['3', '5'], '⅘' => ['4', '5'],
            '⅙' => ['1', '6'], '⅚' => ['5', '6'], '⅛' => ['1', '8'],
            '⅜' => ['3', '8'], '⅝' => ['5', '8'], '⅞' => ['7', '8'],
            '⅑' => ['1', '9'], '⅒' => ['1', '10'], '↉' => ['0', '3'],
        ];
    }

    /**
     * Pecahan dalam bentuk aman untuk aplikasi mobile / endpoint API yang tidak
     * memuat CSS aplikasi web (.math-frac). Hasilnya:
     *   - karakter unicode bila ada padanannya (½, ⅓, ¾, ...)
     *   - selain itu "a/b" (diberi kurung bila memuat spasi atau operasi)
     * Tag format lain (mis. <sup>, <b>) tetap dipertahankan.
     *
     * @param string|null $text
     * @return string
     */
    public static function fractionsForMobile(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // Normalkan semua bentuk pecahan (unicode, \frac{a}{b}, [frac]a/b[/frac],
        // maupun markup .math-frac yang bocor) menjadi HTML .math-frac.
        $html = self::formatFractions($text);

        $unicodeOf = [];
        foreach (self::unicodeFractions() as $char => $pair) {
            $unicodeOf[$pair[0] . '/' . $pair[1]] = $char;
        }

        $tolerant = '/<span\b[^>]*class=["\']?math-frac["\']?[^>]*>\s*'
            . '<span\b[^>]*class=["\']?frac-top["\']?[^>]*>([\s\S]*?)<\/span>\s*'
            . '<span\b[^>]*class=["\']?frac-bottom["\']?[^>]*>([\s\S]*?)<\/span>\s*'
            . '<\/span>/ui';

        $hasil = preg_replace_callback($tolerant, function ($m) use ($unicodeOf) {
            $bersihkan = static function ($s) {
                $s = str_replace(["\u{200B}", "\u{00A0}"], ['', ' '], $s);
                return trim(preg_replace('/\s+/u', ' ', $s));
            };

            $atas = $bersihkan($m[1]);
            $bawah = $bersihkan($m[2]);

            // Cocokkan dengan pecahan unicode yang sudah dikenal (½, ⅓, ¾, ...)
            $kunci = trim(strip_tags($atas)) . '/' . trim(strip_tags($bawah));
            if (isset($unicodeOf[$kunci])) {
                return $unicodeOf[$kunci];
            }

            // Selain itu tulis sebagai a/b; beri kurung bila bukan satu kesatuan
            $rapikan = static function ($s) {
                return preg_match('/^[\p{L}\p{N}.,\-]+$/u', $s) ? $s : '(' . $s . ')';
            };

            return $rapikan($atas) . '/' . $rapikan($bawah);
        }, $html);

        return $hasil ?? $html;
    }

    /**
     * Jalankan fractionsForMobile() pada seluruh teks di dalam array (rekursif).
     * Dipakai endpoint API untuk membereskan data_soal yang berbentuk JSON.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function convertFractionsDeep($value)
    {
        if (is_string($value)) {
            return self::fractionsForMobile($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::convertFractionsDeep($item);
            }
        }

        return $value;
    }

    /**
     * Format dan sanitasi teks soal agar mendukung Bold, Italic, Underline, tag format aman,
     * serta pecahan atas-bawah (baik pecahan bawaan unicode maupun pecahan kustom).
     *
     * @param string|null $text
     * @return string
     */
    public static function formatSoal(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // 0. Format dan bersihkan pecahan terlebih dahulu
        $text = self::formatFractions($text);

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
