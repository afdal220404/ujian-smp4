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
        if (!Schema::hasColumn('ujians', 'pengawas_id')) {
            Schema::table('ujians', function (Blueprint $table) {
                $table->foreignId('pengawas_id')
                      ->nullable()
                      ->after('guru_id')
                      ->constrained('gurus')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ujians', 'pengawas_id')) {
            Schema::table('ujians', function (Blueprint $table) {
                $table->dropForeign(['pengawas_id']);
                $table->dropColumn('pengawas_id');
            });
        }
    }
};
