<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename tabel bank_soal → arsip_soal_siswas
     * (Bank soal siswa sebelumnya bernama bank_soal,
     *  sekarang digunakan sebagai arsip soal siswa)
     */
    public function up(): void
    {
        if (Schema::hasTable('bank_soal') && !Schema::hasTable('arsip_soal_siswas')) {
            Schema::rename('bank_soal', 'arsip_soal_siswas');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('arsip_soal_siswas') && !Schema::hasTable('bank_soal')) {
            Schema::rename('arsip_soal_siswas', 'bank_soal');
        }
    }
};
