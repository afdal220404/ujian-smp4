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
    Schema::table('gurus', function (Blueprint $table) {
        // Hapus foreign key lama
        $table->dropForeign(['akun_id']);
        $table->dropColumn('akun_id');

        // Tambahkan kolom login baru
        $table->string('username')->unique()->after('nip');
        $table->string('password')->after('username');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            //
        });
    }
};
