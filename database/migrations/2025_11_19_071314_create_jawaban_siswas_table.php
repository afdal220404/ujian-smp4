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
        Schema::create('jawaban_siswas', function (Blueprint $table) {
            $table->id();
            // FK ke tabel hasil_ujians (summary pengerjaan)
            $table->foreignId('hasil_ujian_id')->constrained('hasil_ujians')->onDelete('cascade');

            $table->foreignId('soal_id')->constrained('soals')->onDelete('cascade');

            $table->char('jawaban_dipilih', 1)->nullable(); // Jawaban yang dipilih (A, B, C, D, E)
            $table->boolean('is_correct')->nullable(); // Status jawaban: Benar/Salah

            $table->timestamps();

            // Pastikan setiap soal hanya dijawab sekali dalam satu sesi pengerjaan
            $table->unique(['hasil_ujian_id', 'soal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswas');
    }
};
