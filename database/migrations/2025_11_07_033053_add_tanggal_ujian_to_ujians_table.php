<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            // Tambahkan kolom tanggal_ujian setelah jenis_ujian
            $table->date('tanggal_ujian')->after('jenis_ujian');
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn('tanggal_ujian');
        });
    }
};