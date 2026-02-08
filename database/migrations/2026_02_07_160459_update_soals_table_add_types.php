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
        Schema::table('soals', function (Blueprint $table) {
            if (!Schema::hasColumn('soals', 'tipe')) {
                $table->enum('tipe', ['pilihan_ganda', 'benar_salah', 'jawaban_ganda', 'menjodohkan'])->default('pilihan_ganda')->after('ujian_id');
            }
            $table->text('kunci_jawaban')->change();
            $table->string('opsi_a')->nullable()->change();
            $table->string('opsi_b')->nullable()->change();
            $table->string('opsi_c')->nullable()->change();
            $table->string('opsi_d')->nullable()->change();
            
            if (!Schema::hasColumn('soals', 'data_soal')) {
                $table->json('data_soal')->nullable()->after('kunci_jawaban');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'data_soal']);
            $table->char('kunci_jawaban', 1)->change();
            $table->string('opsi_a')->nullable(false)->change();
            $table->string('opsi_b')->nullable(false)->change();
            $table->string('opsi_c')->nullable(false)->change();
            $table->string('opsi_d')->nullable(false)->change();
        });
    }
};
