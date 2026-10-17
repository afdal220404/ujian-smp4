<?php

namespace App\Support;

/**
 * Perbaikan teks yang rusak karena salah encoding (mojibake).
 *
 * Gejala yang paling mudah dikenali: "cmÂ³" seharusnya "cm³", "30Â°C" seharusnya
 * "30°C", atau "Ã¢â‚¬Â¦" seharusnya "…". Penyebabnya adalah teks UTF-8 yang
 * dibaca sebagai Windows-1252 lalu disimpan kembali sebagai UTF-8 (bisa terjadi
 * lebih dari sekali, sehingga perlu dibalik berulang).
 *
 * Cara membaliknya: ubah setiap karakter kembali ke byte Windows-1252 aslinya,
 * lalu periksa apakah hasilnya UTF-8 yang sah. Bila tidak sah, teks aslinya
 * tidak diubah (supaya teks yang sudah benar tidak ikut dirusak).
 */
class Mojibake
{
    /** @var array<int,int>|null peta codepoint => byte */
    private static ?array $petaByte = null;

    /**
     * Apakah teks ini perlu diperbaiki? (ada perubahannya & hasilnya sah)
     */
    public static function rusak(?string $teks): bool
    {
        $hasil = self::perbaiki($teks);

        return $hasil !== null && $hasil !== $teks;
    }

    /**
     * Perbaiki teks mojibake. Diterapkan berulang sampai stabil.
     *
     * @param string|null $teks
     * @return string|null Null bila teks bukan UTF-8 sah (data biner) dan
     *                     sebaiknya tidak disentuh.
     */
    public static function perbaiki(?string $teks): ?string
    {
        if ($teks === null || $teks === '') {
            return $teks;
        }

        if (!mb_check_encoding($teks, 'UTF-8')) {
            return null;
        }

        // Ciri khas mojibake. Tanpa salah satu karakter ini, teks dianggap bersih.
        if (!preg_match('/[ÂÃ¢]/u', $teks)) {
            return $teks;
        }

        $hasil = $teks;

        // Maksimal 4 putaran: cukup untuk kasus encode berulang, tapi tetap aman.
        for ($i = 0; $i < 4; $i++) {
            $kandidat = self::balikSekali($hasil);

            if ($kandidat === $hasil || !mb_check_encoding($kandidat, 'UTF-8')) {
                break;
            }

            $hasil = $kandidat;
        }

        return $hasil;
    }

    /**
     * Perbaiki nilai string di dalam struktur JSON (mis. kolom data_soal).
     * Bila teksnya bukan JSON sah, teks apa adanya akan diperbaiki langsung.
     */
    public static function perbaikiJson(?string $json): ?string
    {
        if ($json === null || $json === '') {
            return $json;
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return self::perbaiki($json);
        }

        $baru = self::perbaikiRekursif($data);

        if ($baru === $data) {
            return $json; // tidak ada yang berubah, biarkan teks aslinya
        }

        return json_encode(
            $baru,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: $json;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private static function perbaikiRekursif($data)
    {
        if (is_array($data)) {
            foreach ($data as $kunci => $nilai) {
                $data[$kunci] = self::perbaikiRekursif($nilai);
            }

            return $data;
        }

        if (is_string($data)) {
            return self::perbaiki($data) ?? $data;
        }

        return $data;
    }

    /**
     * Satu putaran pembalikan UTF-8 -> Windows-1252.
     */
    private static function balikSekali(string $teks): string
    {
        $peta = self::petaByte();
        $hasil = '';
        $panjang = mb_strlen($teks, 'UTF-8');

        for ($i = 0; $i < $panjang; $i++) {
            $karakter = mb_substr($teks, $i, 1, 'UTF-8');
            $kode = mb_ord($karakter, 'UTF-8');

            $hasil .= isset($peta[$kode]) ? chr($peta[$kode]) : $karakter;
        }

        return $hasil;
    }

    /**
     * @return array<int,int> codepoint => byte Windows-1252
     */
    private static function petaByte(): array
    {
        if (self::$petaByte !== null) {
            return self::$petaByte;
        }

        $peta = [];

        // ASCII dan Latin-1: codepoint sama dengan byte-nya.
        for ($i = 0; $i < 0x80; $i++) {
            $peta[$i] = $i;
        }
        for ($i = 0xA0; $i <= 0xFF; $i++) {
            $peta[$i] = $i;
        }

        // Rentang 0x80-0x9F pada Windows-1252 berisi karakter khusus.
        $khusus = [
            0x80 => 0x20AC, 0x82 => 0x201A, 0x83 => 0x0192, 0x84 => 0x201E,
            0x85 => 0x2026, 0x86 => 0x2020, 0x87 => 0x2021, 0x88 => 0x02C6,
            0x89 => 0x2030, 0x8A => 0x0160, 0x8B => 0x2039, 0x8C => 0x0152,
            0x8E => 0x017D, 0x91 => 0x2018, 0x92 => 0x2019, 0x93 => 0x201C,
            0x94 => 0x201D, 0x95 => 0x2022, 0x96 => 0x2013, 0x97 => 0x2014,
            0x98 => 0x02DC, 0x99 => 0x2122, 0x9A => 0x0161, 0x9B => 0x203A,
            0x9C => 0x0153, 0x9E => 0x017E, 0x9F => 0x0178,
        ];

        foreach ($khusus as $byte => $kode) {
            $peta[$kode] = $byte;
        }

        return self::$petaByte = $peta;
    }
}
