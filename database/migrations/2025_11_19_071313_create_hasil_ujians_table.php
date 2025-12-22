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
        Schema::create('hasil_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');

            $table->decimal('nilai', 5, 2)->nullable(); // Nilai akhir (cth: 85.50)
            $table->unsignedSmallInteger('jumlah_benar')->nullable(); // Jumlah soal benar
            $table->dateTime('waktu_mulai'); // Waktu siswa mulai mengerjakan
            $table->dateTime('waktu_selesai')->nullable(); // Waktu siswa selesai/submit

            $table->timestamps();

            // Pastikan tidak ada siswa yang bisa mengerjakan ujian yang sama dua kali
            $table->unique(['ujian_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ujians');
    }
};
