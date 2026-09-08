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
            if (!Schema::hasColumn('hasil_ujians', 'is_paused')) {
                $table->boolean('is_paused')->default(false)->after('is_locked_reentry');
            }
            if (!Schema::hasColumn('hasil_ujians', 'waktu_jeda')) {
                $table->timestamp('waktu_jeda')->nullable()->after('is_paused');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            if (Schema::hasColumn('hasil_ujians', 'waktu_jeda')) {
                $table->dropColumn('waktu_jeda');
            }
            if (Schema::hasColumn('hasil_ujians', 'is_paused')) {
                $table->dropColumn('is_paused');
            }
        });
    }
};
