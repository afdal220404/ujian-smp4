<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->text('data_soal')->nullable()->change();
        });
    }

 
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->string('data_soal')->nullable()->change();
        });
    }
};
