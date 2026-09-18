<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'kelas_id',
        'waktu_mulai',
        'waktu_selesai',
        'nilai',
        'jumlah_benar',
        'jumlah_salah',
        'status_penyelesaian',
        'keterangan_pelanggaran',
        'is_locked_reentry',
        'is_paused',
        'waktu_jeda',
        'last_heartbeat'
    ];

    protected $casts = [
        'is_locked_reentry' => 'boolean',
        'is_paused' => 'boolean',
        'waktu_jeda' => 'datetime',
        'last_heartbeat' => 'datetime',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Finalisasi pengerjaan ujian, hitung nilai otomatis, dan sync susulan (jika ada)
     */
    public function finalizeExam($statusPenyelesaian = 'normal', $keteranganPelanggaran = null)
    {
        $this->waktu_selesai = now();
        $this->status_penyelesaian = $statusPenyelesaian;
        $this->keterangan_pelanggaran = $keteranganPelanggaran;
        $this->is_locked_reentry = false;
        $this->is_paused = false;

        $ujian = Ujian::with('soals.bankSoal')->find($this->ujian_id);
        if (!$ujian) {
            $this->save();
            return;
        }

        $jawabanSiswa = \App\Models\JawabanSiswa::where('hasil_ujian_id', $this->id)->get()->keyBy('soal_id');
        $jumlahBenar = 0;
        $totalSoal = $ujian->soals->count();

        foreach ($ujian->soals as $soal) {
            $jawabanSiswaRecord = $jawabanSiswa[$soal->id] ?? null;
            $jawaban = $jawabanSiswaRecord ? $jawabanSiswaRecord->jawaban_dipilih : null;
            $isCorrect = false;

            // 1. Pilihan Ganda & Benar/Salah
            if ($soal->tipe == 'pilihan_ganda' || $soal->tipe == 'benar_salah') {
                $kunci = trim(strtoupper($soal->kunci_jawaban));
                $jawab = trim(strtoupper($jawaban));

                if ($soal->tipe == 'benar_salah') {
                    if ($kunci == 'COMPLEX_TF') {
                        $pernyataan = $soal->data_soal['pernyataan'] ?? [];
                        $jawabJson = json_decode($jawaban, true);
                        $allCorrect = !empty($pernyataan);

                        if (is_array($pernyataan) && $allCorrect) {
                            foreach ($pernyataan as $idx => $item) {
                                $kunciItem = $item['correct'] ?? '';
                                $jawabItem = $jawabJson[$idx] ?? '';
                                if ($kunciItem !== $jawabItem) {
                                    $allCorrect = false;
                                    break;
                                }
                            }
                        } else {
                            $allCorrect = false;
                        }

                        if ($allCorrect) $isCorrect = true;
                        goto skip_simple_check;
                    }

                    if ($jawab == 'A') $jawab = 'TRUE';
                    if ($jawab == 'B') $jawab = 'FALSE';
                    if ($kunci == 'A') $kunci = 'TRUE';
                    if ($kunci == 'B') $kunci = 'FALSE';
                }

                if ($jawab == $kunci && $jawab != '') {
                    $isCorrect = true;
                }

                skip_simple_check:
            }
            // 2. Jawaban Ganda
            elseif ($soal->tipe == 'jawaban_ganda') {
                if ($jawaban) {
                    $jawabanArr = array_map(function($val) {
                        return trim(strtoupper($val));
                    }, explode(',', $jawaban));
                    sort($jawabanArr);

                    $kunciArr = array_map(function($val) {
                        return trim(strtoupper($val));
                    }, explode(',', $soal->kunci_jawaban));
                    sort($kunciArr);

                    if ($jawabanArr == $kunciArr) {
                        $isCorrect = true;
                    }
                }
            }
            // 3. Menjodohkan
            elseif ($soal->tipe == 'menjodohkan') {
                if ($jawaban) {
                    $pairs = json_decode($jawaban, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $pairs = [];
                    }

                    if (is_array($pairs)) {
                        $matchesData = $soal->data_soal['matches'] ?? [];
                        $totalPairs = count($matchesData);

                        if ($totalPairs > 0 && count($pairs) >= $totalPairs) {
                            $allPairsCorrect = true;
                            foreach ($matchesData as $k => $matchData) {
                                $expectedKey = 'L' . $k;
                                $expectedValue = 'R' . $k;

                                if (!isset($pairs[$expectedKey]) || $pairs[$expectedKey] !== $expectedValue) {
                                    $allPairsCorrect = false;
                                    break;
                                }
                            }

                            if ($allPairsCorrect) {
                                $isCorrect = true;
                            }
                        }
                    }
                }
            }

            if ($jawabanSiswaRecord) {
                $jawabanSiswaRecord->is_correct = $isCorrect ? 1 : 0;
                $jawabanSiswaRecord->save();
            }

            if ($isCorrect) {
                $jumlahBenar++;
            }
        }

        $nilai = $totalSoal > 0 ? ($jumlahBenar / $totalSoal) * 100 : 0;
        $this->nilai = $nilai;
        $this->jumlah_benar = $jumlahBenar;
        $this->jumlah_salah = $totalSoal - $jumlahBenar;

        if (empty($this->kelas_id) && $this->siswa) {
            $this->kelas_id = $this->siswa->kelas_id;
        }

        $this->save();

        // Mirroring ke Ujian Induk jika ini Ujian Susulan
        if ($ujian->is_susulan && $ujian->ujian_induk_id) {
            try {
                $hasilInduk = \App\Models\HasilUjian::updateOrCreate(
                    [
                        'ujian_id' => $ujian->ujian_induk_id,
                        'siswa_id' => $this->siswa_id
                    ],
                    [
                        'kelas_id'               => $this->kelas_id,
                        'waktu_mulai'            => $this->waktu_mulai,
                        'waktu_selesai'          => $this->waktu_selesai,
                        'nilai'                  => $this->nilai,
                        'jumlah_benar'           => $this->jumlah_benar,
                        'jumlah_salah'           => $this->jumlah_salah,
                        'status_penyelesaian'    => $this->status_penyelesaian,
                        'keterangan_pelanggaran' => $this->keterangan_pelanggaran,
                    ]
                );

                $jawabanSiswaFresh = \App\Models\JawabanSiswa::where('hasil_ujian_id', $this->id)->get()->keyBy('soal_id');
                $parentUjian = Ujian::with('soals')->find($ujian->ujian_induk_id);
                if ($parentUjian) {
                    $parentSoalMap = $parentUjian->soals->pluck('id', 'bank_soal_id');

                    foreach ($jawabanSiswaFresh as $susulanSoalId => $jsRecord) {
                        $susulanSoalRecord = $ujian->soals->where('id', $susulanSoalId)->first();
                        $bankSoalId = $susulanSoalRecord ? $susulanSoalRecord->bank_soal_id : null;
                        $parentSoalId = $parentSoalMap[$bankSoalId] ?? null;

                        if ($parentSoalId) {
                            \App\Models\JawabanSiswa::updateOrCreate(
                                [
                                    'hasil_ujian_id' => $hasilInduk->id,
                                    'soal_id'        => $parentSoalId
                                ],
                                [
                                    'jawaban_dipilih' => $jsRecord->jawaban_dipilih,
                                    'is_correct'      => $jsRecord->is_correct,
                                ]
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Mirroring Error on finalize: ' . $e->getMessage());
            }
        }
    }
}

