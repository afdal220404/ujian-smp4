<?php

namespace App\Console\Commands;

use App\Support\Mojibake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Memperbaiki teks soal yang rusak karena salah encoding (mojibake).
 *
 * Contoh: "cmÂ³" menjadi "cm³", "30Â°C" menjadi "30°C", "Ã¢â‚¬Â¦" menjadi "…".
 *
 * Pemakaian:
 *   php artisan soal:perbaiki-encoding --dry-run   (lihat saja, tidak mengubah)
 *   php artisan soal:perbaiki-encoding             (lihat lalu minta konfirmasi)
 *   php artisan soal:perbaiki-encoding --force      (langsung perbaiki)
 */
class PerbaikiEncodingTeks extends Command
{
    protected $signature = 'soal:perbaiki-encoding
                            {--dry-run : Hanya menampilkan temuan, tidak mengubah database}
                            {--force : Langsung perbaiki tanpa konfirmasi}';

    protected $description = 'Memperbaiki teks soal yang rusak akibat salah encoding (mis. "cmÂ³" -> "cm³").';

    /**
     * Kolom yang diperiksa per tabel.
     *
     * Kolom jawaban siswa (jawaban_siswas.jawaban_dipilih) SENGAJA tidak ikut
     * diperiksa: nilainya berupa huruf pilihan / indeks pasangan, dan mengubahnya
     * berisiko mempengaruhi hasil penilaian.
     */
    private array $sasaran = [
        'bank_soal_items' => [
            'teks' => [
                'pertanyaan',
                'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d',
                'gambar', 'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d',
                'pembahasan',
                'kunci_jawaban',
            ],
            'json' => ['data_soal'],
        ],
        'arsip_soal_siswas' => [
            'teks' => ['nama', 'keterangan'],
            'json' => [],
        ],
        'ujians' => [
            'teks' => ['nama_ujian'],
            'json' => [],
        ],
        'mapels' => [
            'teks' => ['nama_mapel'],
            'json' => [],
        ],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');

        $temuan = $this->scan();

        if ($temuan['baris'] === 0) {
            $this->info('Tidak ada teks yang perlu diperbaiki. Semua sudah bersih.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("Ditemukan {$temuan['baris']} baris pada {$temuan['perubahan']} kolom yang perlu diperbaiki:");
        $this->table(['Tabel', 'Kolom', 'Sebelum', 'Sesudah'], $temuan['contoh']);

        if ($dryRun) {
            $this->comment('Mode --dry-run: tidak ada perubahan yang disimpan.');

            return self::SUCCESS;
        }

        if (!$force && !$this->confirm('Terapkan perbaikan sekarang?', true)) {
            $this->warn('Dibatalkan. Tidak ada perubahan.');

            return self::SUCCESS;
        }

        $berkas = 'backup-encoding-' . date('Ymd-His') . '.json';
        Storage::disk('local')->put($berkas, json_encode($temuan['cadangan'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->line('Cadangan nilai lama disimpan di: storage/app/' . $berkas);

        DB::transaction(function () use ($temuan) {
            foreach ($temuan['cadangan'] as $tabel => $baris) {
                foreach ($baris as $id => $perubahan) {
                    DB::table($tabel)->where('id', $id)->update($perubahan);
                }
            }
        });

        $this->info("Selesai! {$temuan['baris']} baris berhasil diperbaiki.");

        return self::SUCCESS;
    }

    /**
     * Telusuri semua tabel sasaran dan kumpulkan perubahan yang diperlukan.
     *
     * @return array{baris:int, perubahan:int, contoh:array, cadangan:array}
     */
    private function scan(): array
    {
        $jumlahBaris = 0;
        $jumlahKolom = 0;
        $contoh = [];
        $cadangan = [];

        foreach ($this->sasaran as $tabel => $spesifikasi) {
            $kolom = $this->kolomYangAda($tabel, array_merge(
                $spesifikasi['teks'],
                $spesifikasi['json']
            ));

            if ($kolom === []) {
                continue;
            }

            $kolomTeks = array_values(array_intersect($spesifikasi['teks'], $kolom));
            $kolomJson = array_values(array_intersect($spesifikasi['json'], $kolom));

            DB::table($tabel)
                ->select(array_merge(['id'], $kolomTeks, $kolomJson))
                ->orderBy('id')
                ->chunkById(200, function ($rows) use (
                    $tabel, $kolomTeks, $kolomJson, &$jumlahBaris, &$jumlahKolom, &$contoh, &$cadangan
                ) {
                    foreach ($rows as $baris) {
                        $perubahan = [];

                        foreach ($kolomTeks as $nama) {
                            $nilai = $baris->{$nama};
                            $baru = Mojibake::perbaiki($nilai);

                            if ($baru !== null && $baru !== $nilai) {
                                $perubahan[$nama] = $baru;
                            }
                        }

                        foreach ($kolomJson as $nama) {
                            $nilai = $baris->{$nama};
                            $baru = Mojibake::perbaikiJson($nilai);

                            if ($baru !== null && $baru !== $nilai) {
                                $perubahan[$nama] = $baru;
                            }
                        }

                        if ($perubahan === []) {
                            continue;
                        }

                        $jumlahBaris++;
                        $jumlahKolom += count($perubahan);
                        $cadangan[$tabel][$baris->id] = $perubahan;

                        foreach ($perubahan as $nama => $baru) {
                            if (count($contoh) < 25) {
                                $contoh[] = [
                                    $tabel,
                                    $nama,
                                    $this->potong((string) $baris->{$nama}),
                                    $this->potong((string) $baru),
                                ];
                            }
                        }
                    }
                });
        }

        return [
            'baris'     => $jumlahBaris,
            'perubahan' => $jumlahKolom,
            'contoh'    => $contoh,
            'cadangan'  => $cadangan,
        ];
    }

    /**
     * @param array<int,string> $diminta
     * @return array<int,string>
     */
    private function kolomYangAda(string $tabel, array $diminta): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($tabel)) {
            return [];
        }

        $ada = \Illuminate\Support\Facades\Schema::getColumnListing($tabel);

        return array_values(array_intersect($diminta, $ada));
    }

    private function potong(string $teks, int $batas = 70): string
    {
        $teks = preg_replace('/\s+/u', ' ', $teks) ?? $teks;

        return mb_strlen($teks) > $batas ? mb_substr($teks, 0, $batas) . '…' : $teks;
    }
}
