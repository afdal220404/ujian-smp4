<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom token untuk autentikasi endpoint API (public/api_*.php).
 *
 * Sebelumnya semua endpoint API tidak memeriksa identitas sama sekali sehingga
 * siapa pun yang menebak ID siswa / ID hasil ujian dapat membaca nilai, kunci
 * jawaban, bahkan mengganti password siswa. Token ini dikirim oleh klien
 * (aplikasi Android) melalui header X-Api-Token atau Authorization: Bearer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'api_token')) {
                $table->string('api_token', 64)->nullable()->unique()->after('password');
            }
            if (!Schema::hasColumn('siswas', 'api_token_expires_at')) {
                $table->timestamp('api_token_expires_at')->nullable()->after('api_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'api_token_expires_at')) {
                $table->dropColumn('api_token_expires_at');
            }
            if (Schema::hasColumn('siswas', 'api_token')) {
                $table->dropColumn('api_token');
            }
        });
    }
};
