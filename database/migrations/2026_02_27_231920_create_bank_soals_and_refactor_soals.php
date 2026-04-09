<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Langkah migrasi lengkap:
     * 1. Buat tabel bank_soal_items (dengan kolom gambar opsi)
     * 2. Migrasi semua data soal dari tabel soals ke bank_soal_items
     * 3. Rebuild tabel soals menjadi pure pivot (ujian_id + bank_soal_id)
     * 4. Buat ulang jawaban_siswas jika diperlukan
     */
    public function up(): void
    {
        // ══════════════════════════════════════════════════
        // LANGKAH 1: Buat tabel bank_soal_items
        // ══════════════════════════════════════════════════
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bank_soal_items');

        Schema::create('bank_soal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained('mapels')->onDelete('cascade');
            $table->enum('tipe', ['pilihan_ganda', 'benar_salah', 'jawaban_ganda', 'menjodohkan'])->default('pilihan_ganda');
            $table->text('pertanyaan');
            $table->string('gambar')->nullable();
            // Opsi jawaban + gambar opsi
            $table->text('opsi_a')->nullable();
            $table->string('gambar_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->string('gambar_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->string('gambar_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->string('gambar_d')->nullable();
            $table->string('kunci_jawaban')->nullable();
            $table->json('data_soal')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();

        // ══════════════════════════════════════════════════
        // LANGKAH 2: Ambil semua soal dari tabel soals (masih punya data lama)
        //            dan pindahkan ke bank_soal_items
        // ══════════════════════════════════════════════════
        if (Schema::hasTable('soals')) {
            $soals = DB::table('soals')
                ->join('ujians', 'soals.ujian_id', '=', 'ujians.id')
                ->select('soals.*', 'ujians.mapel_id')
                ->get();

            // Map soal_id → bank_soal_id untuk restore jawaban_siswas nanti
            $soalToBankMap = [];

            foreach ($soals as $soal) {
                $bankSoalId = DB::table('bank_soal_items')->insertGetId([
                    'mapel_id'      => $soal->mapel_id,
                    'tipe'          => $soal->tipe          ?? 'pilihan_ganda',
                    'pertanyaan'    => $soal->pertanyaan    ?? '',
                    'gambar'        => $soal->gambar        ?? null,
                    'opsi_a'        => $soal->opsi_a        ?? null,
                    'gambar_a'      => $soal->gambar_a      ?? null,
                    'opsi_b'        => $soal->opsi_b        ?? null,
                    'gambar_b'      => $soal->gambar_b      ?? null,
                    'opsi_c'        => $soal->opsi_c        ?? null,
                    'gambar_c'      => $soal->gambar_c      ?? null,
                    'opsi_d'        => $soal->opsi_d        ?? null,
                    'gambar_d'      => $soal->gambar_d      ?? null,
                    'kunci_jawaban' => $soal->kunci_jawaban ?? null,
                    'data_soal'     => $soal->data_soal     ?? null,
                    'created_at'    => $soal->created_at,
                    'updated_at'    => $soal->updated_at,
                ]);

                $soalToBankMap[$soal->id] = $bankSoalId;
            }

            // ══════════════════════════════════════════════
            // LANGKAH 3: Simpan pivot data sebelum drop tabel soals
            // ══════════════════════════════════════════════
            $pivots = DB::table('soals')
                ->select('id', 'ujian_id', 'created_at', 'updated_at')
                ->get();

            // Simpan jawaban_siswas soal_id sebelum tabel di-drop
            $jawabanSiswas = [];
            if (Schema::hasTable('jawaban_siswas')) {
                $jawabanSiswas = DB::table('jawaban_siswas')->get()->toArray();
                Schema::disableForeignKeyConstraints();
                DB::statement('DELETE FROM jawaban_siswas');
                Schema::enableForeignKeyConstraints();
            }

            // ══════════════════════════════════════════════
            // LANGKAH 4: Rebuild tabel soals sebagai pure pivot
            // ══════════════════════════════════════════════
            Schema::disableForeignKeyConstraints();
            Schema::dropIfExists('soals');

            Schema::create('soals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
                $table->foreignId('bank_soal_id')->constrained('bank_soal_items')->onDelete('cascade');
                $table->timestamps();
            });

            // Restore pivot data
            foreach ($pivots as $pivot) {
                $bankSoalId = $soalToBankMap[$pivot->id] ?? null;
                if ($bankSoalId) {
                    DB::table('soals')->insert([
                        'id'           => $pivot->id,
                        'ujian_id'     => $pivot->ujian_id,
                        'bank_soal_id' => $bankSoalId,
                        'created_at'   => $pivot->created_at,
                        'updated_at'   => $pivot->updated_at,
                    ]);
                }
            }

            // ══════════════════════════════════════════════
            // LANGKAH 5: Rebuild jawaban_siswas dan restore data
            // ══════════════════════════════════════════════
            if (!empty($jawabanSiswas) || Schema::hasTable('jawaban_siswas')) {
                if (!Schema::hasTable('jawaban_siswas')) {
                    Schema::create('jawaban_siswas', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('hasil_ujian_id')->constrained('hasil_ujians')->onDelete('cascade');
                        $table->foreignId('soal_id')->constrained('soals')->onDelete('cascade');
                        $table->text('jawaban_dipilih')->nullable();
                        $table->boolean('is_correct')->default(false);
                        $table->timestamps();
                    });
                }

                // Restore jawaban_siswas — soal_id masih sama karena kita preserve ID
                foreach ($jawabanSiswas as $jawaban) {
                    // Hanya restore jika soal_id masih ada (pivot sudah di-restore dengan ID sama)
                    if (DB::table('soals')->where('id', $jawaban->soal_id)->exists()) {
                        DB::table('jawaban_siswas')->insert([
                            'id'             => $jawaban->id,
                            'hasil_ujian_id' => $jawaban->hasil_ujian_id,
                            'soal_id'        => $jawaban->soal_id,
                            'jawaban_dipilih'=> $jawaban->jawaban_dipilih,
                            'is_correct'     => $jawaban->is_correct,
                            'created_at'     => $jawaban->created_at,
                            'updated_at'     => $jawaban->updated_at,
                        ]);
                    }
                }
            }

            Schema::enableForeignKeyConstraints();
        } else {
            // Jika soals belum ada (fresh install), buat dari awal
            Schema::create('soals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
                $table->foreignId('bank_soal_id')->constrained('bank_soal_items')->onDelete('cascade');
                $table->timestamps();
            });

            if (!Schema::hasTable('jawaban_siswas')) {
                Schema::create('jawaban_siswas', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('hasil_ujian_id')->constrained('hasil_ujians')->onDelete('cascade');
                    $table->foreignId('soal_id')->constrained('soals')->onDelete('cascade');
                    $table->text('jawaban_dipilih')->nullable();
                    $table->boolean('is_correct')->default(false);
                    $table->timestamps();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('jawaban_siswas');
        Schema::dropIfExists('soals');
        Schema::dropIfExists('bank_soal_items');
        Schema::enableForeignKeyConstraints();
    }
};
