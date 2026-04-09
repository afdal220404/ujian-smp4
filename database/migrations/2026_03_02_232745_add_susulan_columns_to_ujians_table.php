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
        Schema::table('ujians', function (Blueprint $table) {
            $table->boolean('is_susulan')->default(false)->after('durasi_menit');
            $table->foreignId('ujian_induk_id')->nullable()->constrained('ujians')->onDelete('cascade')->after('is_susulan');
            $table->json('peserta_susulan')->nullable()->after('ujian_induk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropForeign(['ujian_induk_id']);
            $table->dropColumn(['is_susulan', 'ujian_induk_id', 'peserta_susulan']);
        });
    }
};
