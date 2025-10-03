<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('akuns', function (Blueprint $table) {
            $table->id('akun_id');
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->enum('role', ['Operator', 'Kepala Sekolah', 'Guru', 'Siswa']);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akuns');
    }
};
