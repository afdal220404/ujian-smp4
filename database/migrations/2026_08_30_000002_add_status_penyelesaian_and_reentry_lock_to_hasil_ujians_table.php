<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('hasil_ujians', 'status_penyelesaian')) {
                $table->string('status_penyelesaian')->default('belum_selesai')->nullable()->after('waktu_selesai');
            }
            if (!Schema::hasColumn('hasil_ujians', 'keterangan_pelanggaran')) {
                $table->string('keterangan_pelanggaran')->nullable()->after('status_penyelesaian');
            }
            if (!Schema::hasColumn('hasil_ujians', 'is_locked_reentry')) {
                $table->boolean('is_locked_reentry')->default(false)->after('keterangan_pelanggaran');
            }
            if (!Schema::hasColumn('hasil_ujians', 'last_heartbeat')) {
                $table->timestamp('last_heartbeat')->nullable()->after('is_locked_reentry');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->dropColumn(['status_penyelesaian', 'keterangan_pelanggaran', 'is_locked_reentry', 'last_heartbeat']);
        });
    }
};
