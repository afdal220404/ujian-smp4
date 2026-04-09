<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ujian;

class BackfillTahunAjaran extends Command
{
    protected $signature   = 'ujian:backfill-tahun-ajaran';
    protected $description = 'Mengisi kolom tahun_ajaran pada semua ujian yang belum memiliki nilainya, '
                           . 'dihitung otomatis dari waktu_mulai ujian tersebut.';

    public function handle(): int
    {
        $ujians = Ujian::whereNull('tahun_ajaran')->get();

        if ($ujians->isEmpty()) {
            $this->info('Semua ujian sudah memiliki tahun_ajaran. Tidak ada yang perlu diproses.');
            return self::SUCCESS;
        }

        $this->info("Memproses {$ujians->count()} ujian...");
        $bar = $this->output->createProgressBar($ujians->count());
        $bar->start();

        foreach ($ujians as $ujian) {
            $date = \Carbon\Carbon::parse($ujian->waktu_mulai);

            // Kalender sekolah: tahun ajaran baru dimulai bulan Juli
            $tahun = $date->month >= 7
                ? $date->year . '/' . ($date->year + 1)
                : ($date->year - 1) . '/' . $date->year;

            $ujian->update(['tahun_ajaran' => $tahun]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai! Kolom tahun_ajaran berhasil diisi.');

        return self::SUCCESS;
    }
}
