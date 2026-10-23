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
        $text = preg_replace('/<span\s+class=["\']?math-frac["\']?>\s*<span\s+class=["\']?frac-top["\']?>\s*([\s\S]*?)\s*<\/span>\s*<span\s+class=["\']?frac-bottom["\']?>\s*([\s\S]*?)\s*<\/span>\s*<\/span>/ui', '\frac{$1}{$2}', $text);
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
     * Konversi sintaks akar \sqrt{a} / \sqrt[3]{a} menjadi tampilan akar dengan
     * garis atas (radikal) yang panjangnya mengikuti isi di bawahnya.
     *
     * Kurung kurawal boleh bersarang, sehingga akar yang isinya pecahan
     * (\sqrt{\frac{1}{2}}) tetap tampil benar. Akar tunggal yang diketik
     * langsung tanpa kurung kurawal (√12, √x, ∛8) juga dirapikan.
     *
     * @param string $text
     * @return string
     */
    public static function formatRadicals(string $text): string
    {
        // 0. Normalkan markup akar yang tersimpan mentah / bocor sebagai HTML
        $text = preg_replace_callback(
            '/<span\b[^>]*class=["\']?math-sqrt["\']?[^>]*>\s*'
            . '(?:<span\b[^>]*class=["\']?sqrt-index["\']?[^>]*>\s*([^<]*)<\/span>\s*)?'
            . '<span\b[^>]*class=["\']?sqrt-sign["\']?[^>]*>\s*(?:√|&radic;|&#0*8730;|&#x0*221a;)\s*<\/span>\s*'
            . '<span\b[^>]*class=["\']?sqrt-radicand["\']?[^>]*>([\s\S]*?)<\/span>\s*<\/span>/ui',
            static function ($m) {
                $pangkat = preg_replace('/\D/', '', (string) ($m[1] ?? ''));

                return '\\sqrt' . ($pangkat !== '' ? '[' . substr($pangkat, 0, 2) . ']' : '') . '{' . $m[2] . '}';
            },
            $text
        );

        // 1. \sqrt{...} / \sqrt[n]{...} -> HTML radikal (kurung kurawal berimbang)
        $out = '';
        $len = strlen($text);
        $i = 0;
        while ($i < $len) {
            $pos = strpos($text, '\\sqrt', $i);
            if ($pos === false) {
                $out .= substr($text, $i);
                break;
            }

            $j = $pos + 5;
            while ($j < $len && ($text[$j] === ' ' || $text[$j] === "\t")) {
                $j++;
            }

            // Pangkat akar opsional, mis. \sqrt[3]{8}
            $index = '';
            if ($j < $len && $text[$j] === '[') {
                $tutup = strpos($text, ']', $j);
                if ($tutup !== false) {
                    $calon = trim(substr($text, $j + 1, $tutup - $j - 1));
                    if (preg_match('/^\d{1,2}$/', $calon)) {
                        $index = $calon;
                        $j = $tutup + 1;
                        while ($j < $len && ($text[$j] === ' ' || $text[$j] === "\t")) {
                            $j++;
                        }
                    }
                }
            }

            if ($j >= $len || $text[$j] !== '{') {
                $out .= substr($text, $i, $pos + 5 - $i);
                $i = $pos + 5;
                continue;
            }

            $depth = 0;
            $end = null;
            for ($k = $j; $k < $len; $k++) {
                if ($text[$k] === '{') {
                    $depth++;
                } elseif ($text[$k] === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $k;
                        break;
                    }
                }
            }

            if ($end === null) {
                $out .= substr($text, $i);
                break;
            }

            $out .= substr($text, $i, $pos - $i);
            $out .= self::sqrtHtml(substr($text, $j + 1, $end - $j - 1), $index);
            $i = $end + 1;
        }
        $text = $out;

        // 2. Akar yang diketik langsung: √12, √2,5, √2x, ∛8. Huruf hanya dianggap
        //    isi akar bila menempel langsung (√x), supaya "5√ dan" tidak jadi "√d".
        $text = preg_replace_callback('/(√|∛)(?:\s*([0-9]+(?:[.,][0-9]+)?[a-zA-Z]?)|([a-zA-Z]))/u', static function ($m) {
            $isi = ($m[2] ?? '') !== '' ? $m[2] : ($m[3] ?? '');

            return self::sqrtHtml($isi, $m[1] === '∛' ? '3' : '');
        }, $text);

        return $text;
    }

    /**
     * Markup tampilan satu akar (dipakai formatRadicals()).
     *
     * $index diisi '3' untuk akar pangkat tiga: angka kecil di kiri atas,
     * glyph √ tetap sama sehingga font seragam dengan akar kuadrat.
     */
    private static function sqrtHtml(string $isi, string $index = ''): string
    {
        $pangkat = $index !== '' ? '<span class="sqrt-index">' . $index . '</span>' : '';

        return '<span class="math-sqrt">' . $pangkat . '<span class="sqrt-sign">√</span>'
            . '<span class="sqrt-radicand">' . $isi . '</span></span>';
    }

    /**
     * Rapikan pecahan & akar sekaligus.
     *
     * Dijalankan dua putaran karena keduanya bisa saling membungkus:
     * \frac{\sqrt{5}}{3} baru dikenali sebagai pecahan setelah akarnya
     * berubah menjadi HTML (isi \frac tidak boleh memuat kurung kurawal).
     * Putaran kedua tidak mengubah hasil akhir (aman/idempoten).
     */
    private static function rapikanMatematika(string $text): string
    {
        for ($i = 0; $i < 2; $i++) {
            $text = self::formatRadicals(self::formatFractions($text));
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

        // Normalkan semua bentuk pecahan & akar (unicode, \frac{a}{b}, [frac]a/b[/frac],
        // \sqrt{a}, maupun markup .math-frac / .math-sqrt yang bocor) menjadi HTML.
        $html = self::rapikanMatematika($text);

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

        // Akar: .math-sqrt -> "√isi" / "∛isi" (isi yang bukan satu kesatuan diberi kurung)
        $polaAkar = '/<span\b[^>]*class=["\']?math-sqrt["\']?[^>]*>\s*'
            . '(?:<span\b[^>]*class=["\']?sqrt-index["\']?[^>]*>([\s\S]*?)<\/span>\s*)?'
            . '<span\b[^>]*class=["\']?sqrt-sign["\']?[^>]*>[\s\S]*?<\/span>\s*'
            . '<span\b[^>]*class=["\']?sqrt-radicand["\']?[^>]*>([\s\S]*?)<\/span>\s*'
            . '<\/span>/ui';

        $hasil = preg_replace_callback($polaAkar, static function ($m) {
            $pangkat = trim(preg_replace('/\D/', '', (string) ($m[1] ?? '')));
            $isi = trim(preg_replace('/\s+/u', ' ', str_replace(["\u{200B}", "\u{00A0}"], ['', ' '], $m[2])));

            // Akar pangkat tiga ditulis dengan ³ + √ (dua-duanya tersedia di font
            // bawaan Android), jadi tidak bergantung pada glyph ∛ yang belum tentu ada.
            $awalan = match ($pangkat) {
                '' => '√',
                '3' => '³√',
                default => '√[' . $pangkat . ']',
            };

            if ($isi === '') {
                return $awalan;
            }

            return preg_match('/^[\p{L}\p{N}.,\-]+$/u', $isi) ? $awalan . $isi : $awalan . '(' . $isi . ')';
        }, $hasil ?? $html);

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

        // 0. Format dan bersihkan pecahan & akar terlebih dahulu
        $text = self::rapikanMatematika($text);

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
