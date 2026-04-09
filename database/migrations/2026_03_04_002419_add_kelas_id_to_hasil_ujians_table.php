<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('cascade');
        });

        // Backfill data: ambil kelas_id dari tabel siswas
        $hasilUjians = \App\Models\HasilUjian::with('siswa')->get();
        foreach ($hasilUjians as $hasil) {
            if ($hasil->siswa) {
                // Gunakan query builder agar mass assignment bypass
                \Illuminate\Support\Facades\DB::table('hasil_ujians')
                    ->where('id', $hasil->id)
                    ->update(['kelas_id' => $hasil->siswa->kelas_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn('kelas_id');
        });
    }
};
