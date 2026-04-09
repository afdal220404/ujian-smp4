<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->string('tahun_ajaran', 9)->nullable()->after('mapel_id')
                  ->comment('Contoh: 2024/2025. Diisi otomatis saat ujian dibuat.');
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn('tahun_ajaran');
        });
    }
};
