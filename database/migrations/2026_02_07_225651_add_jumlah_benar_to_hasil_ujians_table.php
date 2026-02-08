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
            $table->integer('jumlah_benar')->nullable()->after('waktu_selesai');
            $table->integer('jumlah_salah')->nullable()->after('jumlah_benar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->dropColumn(['jumlah_benar', 'jumlah_salah']);
        });
    }
};
