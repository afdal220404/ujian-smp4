<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mapel;
use App\Models\Ujian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;

class SiswaDashboardController extends Controller
{
    public function index()
    {
        $siswa = Auth::guard('siswa')->user()->load('kelas');
        
        // Hitung Statistik Nilai (filter ke kelas aktif saja)
        $mapelIdsKelas = Mapel::where('kelas_id', $siswa->kelas_id)->pluck('id');
        $nilaiSemua = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
            ->whereHas('ujian', function($q) use ($mapelIdsKelas) {
                $q->whereIn('mapel_id', $mapelIdsKelas);
            })->get();
        $totalUjian = $nilaiSemua->count();
        
        // Kalkulasi Rata-Rata (Konsisten dengan halaman Nilai)
        $mapels = Mapel::where('kelas_id', $siswa->kelas_id)->get();
        $totalNilaiGlobal = 0;
        $countMapelGlobal = 0;

        foreach($mapels as $mapel) {
            $ujian_selesai = Ujian::where('mapel_id', $mapel->id)
                ->whereHas('hasilUjians', function($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                })
                ->with(['hasilUjians' => function($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                }])
                ->get();
                
            $kuisColl = collect();
            $utsColl = collect();
            $uasColl = collect();
            
            foreach($ujian_selesai as $ujian) {
                 $hasil = $ujian->hasilUjians->first();
                 if($hasil) {
                     if (stripos($ujian->jenis_ujian ?? '', 'Kuis') !== false) {
                         $kuisColl->push($hasil->nilai);
                     } elseif (stripos($ujian->jenis_ujian ?? '', 'UTS') !== false) {
                         $utsColl->push($hasil->nilai);
                     } elseif (stripos($ujian->jenis_ujian ?? '', 'UAS') !== false) {
                         $uasColl->push($hasil->nilai);
                     }
                 }
            }
            
            $kuis = $kuisColl->isNotEmpty() ? $kuisColl->avg() : null;
            $uts = $utsColl->isNotEmpty() ? $utsColl->avg() : null;
            $uas = $uasColl->isNotEmpty() ? $uasColl->avg() : null;
            
            $komponen = array_filter([$kuis, $uts, $uas], fn($v) => $v !== null);
            $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;
            
            if ($akhir !== null) {
                $totalNilaiGlobal += $akhir;
                $countMapelGlobal++;
            }
        }
        
        $rataRata = $countMapelGlobal > 0 ? ($totalNilaiGlobal / $countMapelGlobal) : 0;

        // Kalkulasi Rata-Rata Kuis saja (untuk ditampilkan di dashboard)
        $totalKuisGlobal = 0;
        $countKuisMapel = 0;
        foreach($mapels as $mapel) {
            $kuisNilais = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                ->whereHas('ujian', function($q) use ($mapel) {
                    $q->where('mapel_id', $mapel->id)
                      ->where(function($q2) { $q2->whereRaw("LOWER(jenis_ujian) LIKE '%kuis%'"); });
                })->pluck('nilai');
            if ($kuisNilais->isNotEmpty()) {
                $totalKuisGlobal += $kuisNilais->avg();
                $countKuisMapel++;
            }
        }
        $rataRataKuis = $countKuisMapel > 0 ? ($totalKuisGlobal / $countKuisMapel) : 0;

        $ujianTerakhir = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                            ->whereHas('ujian', function($q) use ($mapelIdsKelas) {
                                $q->whereIn('mapel_id', $mapelIdsKelas);
                            })
                            ->latest()
                            ->with('ujian')
                            ->first();

        // Ambil Daftar Ujian Berdasarkan Kategori
        // 1. Sedang Berlangsung (Sekarang ada di antara waktu_mulai dan selesai, BELUM diselesaikan siswa)
        $sedangBerlangsung = Ujian::whereIn('mapel_id', $siswa->kelas->mapels->pluck('id')) // Fix: whereIn
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>', now())
            ->whereDoesntHave('hasilUjians', function($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            })
            ->where(function($q) use ($siswa) {
                $q->where('is_susulan', false)
                  ->orWhereNull('is_susulan')
                  ->orWhereJsonContains('peserta_susulan', (string)$siswa->id)
                  ->orWhereJsonContains('peserta_susulan', $siswa->id);
            })
            ->with(['mapel.guru']) // Fix: mapel.guru
            ->get();

        // 2. Akan Datang (Waktu mulai > sekarang)
        $akanDatang = Ujian::whereIn('mapel_id', $siswa->kelas->mapels->pluck('id')) // Fix: whereIn
            ->where('waktu_mulai', '>', now())
            ->where(function($q) use ($siswa) {
                $q->where('is_susulan', false)
                  ->orWhereNull('is_susulan')
                  ->orWhereJsonContains('peserta_susulan', (string)$siswa->id)
                  ->orWhereJsonContains('peserta_susulan', $siswa->id);
            })
            ->with(['mapel.guru']) // Fix: mapel.guru
            ->get();

        // 3. Telah Berlalu — hanya ujian dari kelas aktif
        $telahBerlalu = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                            ->whereHas('ujian', function($q) use ($mapelIdsKelas) {
                                $q->whereIn('mapel_id', $mapelIdsKelas);
                            })
                            ->with(['ujian.mapel.guru'])
                            ->latest()
                            ->get();

        return view('siswa.dashboard', compact('siswa', 'rataRata', 'rataRataKuis', 'totalUjian', 'ujianTerakhir', 
            'sedangBerlangsung', 'akanDatang', 'telahBerlalu'));
    }

    public function indexNilai(Request $request)
    {
        $siswa = Auth::guard('siswa')->user()->load('kelas');
        $keyword = $request->input('search');
        
        // 2. Query Mapel dengan Pencarian
        $mapelQuery = Mapel::where('kelas_id', $siswa->kelas_id)->with('guru');

        if ($keyword) {
            $mapelQuery->where(function($q) use ($keyword) {
                $q->where('nama_mapel', 'like', "%{$keyword}%")
                  ->orWhereHas('ujians', function($q2) use ($keyword) {
                      $q2->where('nama_ujian', 'like', "%{$keyword}%");
                  });
            });
        }

        $mapels = $mapelQuery->get();

        // Seluruh mapel kelas (tanpa filter pencarian) — dipakai oleh mode tabel
        $allMapels = Mapel::where('kelas_id', $siswa->kelas_id)->with('guru')->get();
        
        $totalNilaiGlobal = 0;
        $countMapelGlobal = 0;

        // Helper: load ujian_selesai dan hitung rata-rata per mapel
        // 3. Load Ujian & Hitung Rata-Rata Per Mapel (untuk $mapels — terfilter pencarian)
        foreach($mapels as $mapel) {
            // Load ujian yang sudah selesai dikerjakan siswa
            $mapel->ujian_selesai = Ujian::where('mapel_id', $mapel->id)
                ->whereHas('hasilUjians', function($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                })
                ->with(['hasilUjians' => function($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                }])
                ->latest() // Urutkan terbaru
                ->get();
                
            $kuisColl = collect();
            $utsColl = collect();
            $uasColl = collect();
            
            foreach($mapel->ujian_selesai as $ujian) {
                 $hasil = $ujian->hasilUjians->first();
                 if($hasil) {
                     if (stripos($ujian->jenis_ujian ?? '', 'Kuis') !== false) {
                         $kuisColl->push($hasil->nilai);
                     } elseif (stripos($ujian->jenis_ujian ?? '', 'UTS') !== false) {
                         $utsColl->push($hasil->nilai);
                     } elseif (stripos($ujian->jenis_ujian ?? '', 'UAS') !== false) {
                         $uasColl->push($hasil->nilai);
                     }
                 }
            }
            
            $kuis = $kuisColl->isNotEmpty() ? $kuisColl->avg() : null;
            $uts = $utsColl->isNotEmpty() ? $utsColl->avg() : null;
            $uas = $uasColl->isNotEmpty() ? $uasColl->avg() : null;
            
            $komponen = array_filter([$kuis, $uts, $uas], fn($v) => $v !== null);
            $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;
            
            $mapel->rata_rata = $akhir ?? 0;
            
            if ($akhir !== null) {
                $totalNilaiGlobal += $akhir;
                $countMapelGlobal++;
            }
        }
        
        $rataRataKeseluruhan = $countMapelGlobal > 0 ? ($totalNilaiGlobal / $countMapelGlobal) : 0;

        // Load ujian_selesai & rata_rata juga untuk $allMapels (semua mapel, tanpa filter)
        foreach ($allMapels as $mp) {
            if ($mapels->contains('id', $mp->id)) {
                // Sudah dihitung di atas, salin reference-nya
                $existing = $mapels->find($mp->id);
                $mp->ujian_selesai = $existing->ujian_selesai ?? collect();
                $mp->rata_rata     = $existing->rata_rata ?? 0;
            } else {
                // Mapel tidak termasuk dalam hasil pencarian — hitung sendiri
                $ujianSelesai = Ujian::where('mapel_id', $mp->id)
                    ->whereHas('hasilUjians', fn($q) => $q->where('siswa_id', $siswa->id))
                    ->with(['hasilUjians' => fn($q) => $q->where('siswa_id', $siswa->id)])
                    ->latest()->get();
                $kC = collect(); $uC = collect(); $aC = collect();
                foreach ($ujianSelesai as $uj) {
                    $h = $uj->hasilUjians->first();
                    if ($h) {
                        if (stripos($uj->jenis_ujian ?? '', 'Kuis') !== false) $kC->push($h->nilai);
                        elseif (stripos($uj->jenis_ujian ?? '', 'UTS') !== false) $uC->push($h->nilai);
                        elseif (stripos($uj->jenis_ujian ?? '', 'UAS') !== false) $aC->push($h->nilai);
                    }
                }
                $komp = array_filter([$kC->avg() ?: null, $uC->avg() ?: null, $aC->avg() ?: null], fn($v) => $v !== null);
                $mp->ujian_selesai = $ujianSelesai;
                $mp->rata_rata     = count($komp) > 0 ? array_sum($komp) / count($komp) : 0;
            }
        }

        // --- Semua Tingkat Kelas (VII, VIII, IX) ---
        // $siswa->kelas adalah relasi ke model Kelas
        // Kolom tingkat di tabel kelas bernama 'kelas' (VII/VIII/IX)
        $tingkatList = ['VII', 'VIII', 'IX'];
        $kelasAktif  = $siswa->kelas->kelas ?? null; // e.g. 'VIII'

        $riwayatKelas = collect();
        foreach ($tingkatList as $tingkat) {
            // Skip tingkat yang sama dengan kelas aktif siswa
            if ($tingkat === $kelasAktif) continue;

            // Ambil semua kelas rows untuk tingkat ini (e.g. semua kelas IX: 9A, 9B, ...)
            $kelasIds = \App\Models\Kelas::where('kelas', $tingkat)->pluck('id');

            // Ambil mapels yang kelas_id-nya termasuk tingkat ini
            $mapelsTingkat = Mapel::whereIn('kelas_id', $kelasIds)->with('guru')->get();

            $totalKelas = 0;
            $countKelas = 0;

            foreach ($mapelsTingkat as $mapel) {
                $ujianSelesai = Ujian::where('mapel_id', $mapel->id)
                    ->whereHas('hasilUjians', function($q) use ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    })
                    ->with(['hasilUjians' => function($q) use ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    }])
                    ->get();

                $kuisColl = collect(); $utsColl = collect(); $uasColl = collect();
                foreach ($ujianSelesai as $ujian) {
                    $hasil = $ujian->hasilUjians->first();
                    if ($hasil) {
                        if (stripos($ujian->jenis_ujian ?? '', 'Kuis') !== false) $kuisColl->push($hasil->nilai);
                        elseif (stripos($ujian->jenis_ujian ?? '', 'UTS') !== false) $utsColl->push($hasil->nilai);
                        elseif (stripos($ujian->jenis_ujian ?? '', 'UAS') !== false) $uasColl->push($hasil->nilai);
                    }
                }
                $k = $kuisColl->isNotEmpty() ? $kuisColl->avg() : null;
                $u = $utsColl->isNotEmpty() ? $utsColl->avg() : null;
                $a = $uasColl->isNotEmpty() ? $uasColl->avg() : null;
                $komponen = array_filter([$k, $u, $a], fn($v) => $v !== null);
                $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;

                $mapel->rata_rata     = $akhir ?? 0;
                $mapel->ujian_selesai = $ujianSelesai;
                if ($akhir !== null) { $totalKelas += $akhir; $countKelas++; }
            }

            $riwayatKelas->push([
                'tingkat'    => $tingkat,
                'kelas'      => (object)['nama_kelas' => 'Kelas ' . $tingkat],
                'mapels'     => $mapelsTingkat,
                'rata_rata'  => $countKelas > 0 ? ($totalKelas / $countKelas) : 0,
                'ada_nilai'  => $countKelas > 0,
                'is_current' => false,
            ]);
        }

        // Nama kelas aktif untuk label tab (e.g. "Kelas VIII")
        $namaKelasAktif = $kelasAktif ? 'Kelas ' . $kelasAktif : 'Kelas Aktif';

        return view('siswa.nilai', compact('siswa', 'mapels', 'allMapels', 'rataRataKeseluruhan', 'keyword', 'riwayatKelas', 'namaKelasAktif', 'kelasAktif'));
    }

    public function showUjian($id)
    {
        $siswa = Auth::guard('siswa')->user();
        $ujian = Ujian::with('mapel')->findOrFail($id);

        // 1. Ambil Hasil Ujian
        $hasilUjian = \App\Models\HasilUjian::where('ujian_id', $id)
                        ->where('siswa_id', $siswa->id)
                        ->first();

        if (!$hasilUjian) {
            return redirect()->route('siswa.nilai')->with('error', 'Data ujian tidak ditemukan.');
        }

        // PERBAIKAN TIMEZONE & LOCALE (Sesuai Request)
        // Set locale ke Indonesia
        \Carbon\Carbon::setLocale('id');
        
        // Asumsi: Database menyimpan waktu dalam format UTC (standar server).
        // Kita perlu memberitahu Carbon bahwa string dari DB adalah UTC, lalu convert ke Asia/Jakarta (WIB).
        
        // Fix Waktu Ujian (Jadwal)
        // Revert: Gunakan default parsing (sama seperti Guru), karena DB kemungkinan sudah WIB atau App Config sudah handle
        $ujian->waktu_mulai = \Carbon\Carbon::parse($ujian->getRawOriginal('waktu_mulai'));
        $ujian->waktu_selesai = \Carbon\Carbon::parse($ujian->getRawOriginal('waktu_selesai'));

        // Fix Waktu Hasil Ujian (Siswa Mengerjakan)
        $hasilUjian->waktu_mulai = \Carbon\Carbon::parse($hasilUjian->getRawOriginal('waktu_mulai'));
        $hasilUjian->waktu_selesai = \Carbon\Carbon::parse($hasilUjian->getRawOriginal('waktu_selesai'));

        // 2. Ambil Jawaban Siswa
        $listJawabanSiswa = \App\Models\JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
                                ->get()
                                ->keyBy('soal_id');

        // 3. Ambil Soal (dengan eager load bankSoal)
        $semuaSoal = $ujian->soals()->with('bankSoal')->get();
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $daftarSoal = [];

        foreach ($semuaSoal as $soal) {
            $jawabanDb = $listJawabanSiswa->get($soal->id);
            $jawabanSiswa = $jawabanDb ? $jawabanDb->jawaban_dipilih : null;

            // Gunakan status is_correct yang sudah disimpan di database saat submit
            // Ini menjamin konsistensi antara nilai akhir dan tampilan detail per soal
            $isBenar = $jawabanDb ? (bool)$jawabanDb->is_correct : false;

            if ($isBenar) $jumlahBenar++;
            else $jumlahSalah++;

            $soal->jawaban_siswa = $jawabanSiswa;
            $soal->status_jawaban = $isBenar;
            $daftarSoal[] = $soal;
        }

        // Override perhitungan manual dengan data dari Tabel HasilUjian jika ada (untuk konsistensi total)
        // Namun loop diatas tetap diperlukan untuk flagging per soal (background merah/hijau)
        if ($hasilUjian->jumlah_benar !== null) {
             // Opsional: bisa pakai nilai DB langsung untuk summary, tapi loop diatas tetap butuh untuk per-item
             // $jumlahBenar = $hasilUjian->jumlah_benar;
             // $jumlahSalah = count($semuaSoal) - $jumlahBenar;
        }


        return view('siswa.detail_ujian', compact('siswa', 'ujian', 'hasilUjian', 'daftarSoal', 'jumlahBenar', 'jumlahSalah'));
    }

    public function indexBankSoal(Request $request)
    {
        $siswa = Auth::guard('siswa')->user()->load('kelas.mapels');
        $keyword = $request->input('search');
        $selectedMapelId = $request->input('mapel_id');

        // Ambil Daftar Mapel di kelas siswa untuk Dropdown Filter
        $mapels = $siswa->kelas->mapels;
        
        // Ambil ID Mapel yang ada di kelas siswa (untuk security scope request)
        $mapelIds = $mapels->pluck('id');

        // Query Arsip Soal Siswa
        $query = \App\Models\ArsipSoalSiswa::whereIn('mapel_id', $mapelIds)
                    ->where('visibilitas', 'Public') // Hanya tampilkan yang Public
                    ->with(['mapel', 'guru']);

        if ($keyword) {
            $query->where('nama', 'like', "%{$keyword}%");
        }

        if ($selectedMapelId) {
            $query->where('mapel_id', $selectedMapelId);
        }

        $arsipSoalSiswas = $query->latest()->get();

        return view('siswa.bank_soal', compact('siswa', 'arsipSoalSiswas', 'keyword', 'mapels', 'selectedMapelId'));
    }

    // --- FITUR PENGERJAAN UJIAN ---

    public function konfirmasiUjian($id)
    {
        $siswa = Auth::guard('siswa')->user();
        $ujian = Ujian::with(['mapel.guru', 'soals'])->findOrFail($id);

        // Cek Akses Ujian Susulan
        if ($ujian->is_susulan) {
            $peserta = $ujian->peserta_susulan ?? [];
            if (!in_array($siswa->id, $peserta) && !in_array((string)$siswa->id, $peserta)) {
                return redirect()->route('siswa.dashboard')->with('error', 'Anda tidak terdaftar untuk ujian susulan ini.');
            }
        }

        // Cek apakah siswa sudah mengerjakan?
        $sudahMengerjakan = \App\Models\HasilUjian::where('ujian_id', $id)
                            ->where('siswa_id', $siswa->id)
                            ->whereNotNull('waktu_selesai') // Sudah selesai
                            ->exists();

        if ($sudahMengerjakan) {
            return redirect()->route('siswa.ujian.detail', $id)->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        // Cek apakah ujian sedang berlangsung secara waktu
        $now = now();
        if ($now < $ujian->waktu_mulai) {
            return back()->with('error', 'Ujian belum dimulai.');
        }
        if ($now > $ujian->waktu_selesai) {
             return redirect()->route('siswa.dashboard')->with('error', 'Waktu ujian telah berakhir.');
        }

        return view('siswa.ujian.konfirmasi', compact('siswa', 'ujian'));
    }

    public function mulaiUjian($id)
    {
        $siswa = Auth::guard('siswa')->user();
        $ujian = Ujian::with('soals.bankSoal')->findOrFail($id); // Eager load soals dengan bankSoal

        // Cek Akses Ujian Susulan
        if ($ujian->is_susulan) {
            $peserta = $ujian->peserta_susulan ?? [];
            if (!in_array($siswa->id, $peserta) && !in_array((string)$siswa->id, $peserta)) {
                return redirect()->route('siswa.dashboard')->with('error', 'Anda tidak terdaftar untuk ujian susulan ini.');
            }
        }

        // Cek Record Hasil Ujian (Session Ujian)
        $hasilUjian = \App\Models\HasilUjian::firstOrCreate(
            [
                'ujian_id' => $id,
                'siswa_id' => $siswa->id
            ],
            [
                'kelas_id'    => $siswa->kelas_id, // Simpan kelas saat ujian dimulai (historis)
                'waktu_mulai' => now(), // Set waktu mulai saat pertama kali klik MULAI
                'nilai'       => 0
            ]
        );

        // --- RANDOMISASI SOAL ---
        // Cek jika belum ada jawaban tersimpan (artinya baru mulai), generate urutan acak
        $existingJawabanCount = \App\Models\JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)->count();
        
        if ($existingJawabanCount == 0) {
            $soalIds = $ujian->soals->pluck('id')->shuffle(); // Acak urutan soal
            
            $insertData = [];
            foreach ($soalIds as $soalId) {
                $insertData[] = [
                    'hasil_ujian_id' => $hasilUjian->id,
                    'soal_id' => $soalId,
                    'jawaban_dipilih' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($insertData)) {
                \App\Models\JawabanSiswa::insert($insertData);
            }
        }

        return redirect()->route('siswa.ujian.kerjakan', $id);
    }

    public function kerjakanUjian($id)
    {
        $siswa = Auth::guard('siswa')->user();
        
        // Validasi Akses & Ambil Hasil Ujian
        $hasilUjian = \App\Models\HasilUjian::where('ujian_id', $id)
                        ->where('siswa_id', $siswa->id)
                        ->first();

        if (!$hasilUjian) {
            return redirect()->route('siswa.ujian.konfirmasi', $id);
        }

        if ($hasilUjian->waktu_selesai) {
             return redirect()->route('siswa.ujian.detail', $id);
        }

        // --- LOAD SOAL BERDASARKAN URUTAN JAWABAN SISWA (Step 2 Randomized) ---
        // Kita ambil JawabanSiswa yang sudah dibuat di mulaiUjian (yang sudah diacak), lalu load relasi Soalnya
        // Urutan JawabanSiswa = Urutan Acak yang persisten
        $jawabanSiswas = \App\Models\JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
                            ->with('soal.bankSoal') // Eager load soal beserta bankSoal
                            ->get(); // Default order by ID (creation time), which matches our shuffle order

        // Reconstruct $ujian object structure expected by view, but with custom sorted questions
        $ujian = Ujian::findOrFail($id);
        
        // Override relation 'soals' with our sorted collection
        // Note: View uses $ujian->soals loop. We can manually set it.
        $sortedSoals = $jawabanSiswas->map(function($js) {
            return $js->soal;
        });

        // Use this sorted collection for the view
        $ujian->setRelation('soals', $sortedSoals);
        
        // Mapping Jawaban Tersimpan
        $jawabanTersimpan = $jawabanSiswas->pluck('jawaban_dipilih', 'soal_id');

        return view('siswa.ujian.kerjakan', compact('siswa', 'ujian', 'hasilUjian', 'jawabanTersimpan'));
    }

    public function simpanJawaban(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        
        // Validasi Input
        $request->validate([
            'ujian_id' => 'required|exists:ujians,id',
            'soal_id'  => 'required|exists:soals,id',
            'jawaban'  => 'nullable|string'
        ]);

        // Cek Hasil Ujian yang sedang aktif
        $hasilUjian = \App\Models\HasilUjian::where('ujian_id', $request->ujian_id)
                        ->where('siswa_id', $siswa->id)
                        ->whereNull('waktu_selesai') // Pastikan belum selesai
                        ->first();

        if (!$hasilUjian) {
            return response()->json(['status' => 'error', 'message' => 'Sesi ujian tidak valid or sudah selesai.'], 400);
        }

        // Simpan/Update Jawaban
        \App\Models\JawabanSiswa::updateOrCreate(
            [
                'hasil_ujian_id' => $hasilUjian->id,
                'soal_id' => $request->soal_id
            ],
            [
                'jawaban_dipilih' => $request->jawaban
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function selesaiUjian(Request $request, $id)
    {
        $siswa = Auth::guard('siswa')->user();
        
        $hasilUjian = \App\Models\HasilUjian::where('ujian_id', $id)
                        ->where('siswa_id', $siswa->id)
                        ->firstOrFail();

        // 1. Set Waktu Selesai
        $hasilUjian->waktu_selesai = now();
        
        // 2. Hitung Nilai Otomatis
        $ujian = Ujian::with('soals.bankSoal')->findOrFail($id);
        $jawabanSiswa = \App\Models\JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)->get()->keyBy('soal_id');
        
        $jumlahBenar = 0;
        $totalSoal = $ujian->soals->count();

        foreach ($ujian->soals as $soal) {
            $jawabanSiswaRecord = $jawabanSiswa[$soal->id] ?? null;
            $jawaban = $jawabanSiswaRecord ? $jawabanSiswaRecord->jawaban_dipilih : null;
            
            $isCorrect = false;

            // --- 1. PILIHAN GANDA & BENAR/SALAH ---
            if ($soal->tipe == 'pilihan_ganda' || $soal->tipe == 'benar_salah') {
                $kunci = trim(strtoupper($soal->kunci_jawaban));
                $jawab = trim(strtoupper($jawaban));
                
                // Normalisasi Benar/Salah
                if ($soal->tipe == 'benar_salah') {
                    if ($kunci == 'COMPLEX_TF') {
                        // LOGIK COMPLEX (All or Nothing)
                        $pernyataan = $soal->data_soal['pernyataan'] ?? [];
                        $jawabJson = json_decode($jawaban, true);
                        
                        // Strict Check: Count must match (to ensure all answered? not necessarily, but all existing must be correct)
                        // Actually, if student skips one, it's WRONG.
                        
                        $allCorrect = true;
                        // Avoid crash if pernyataan empty
                        if(empty($pernyataan)) $allCorrect = false;

                        if (is_array($pernyataan)) {
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

                        // Skip logic bawah
                        goto skip_simple_check;
                    }

                    // Normalisasi Old Simple TF
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
            
            // --- 2. JAWABAN GANDA ---
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

            // --- 3. MENJODOHKAN ---
            elseif ($soal->tipe == 'menjodohkan') {
                if ($jawaban) {
                    $pairs = json_decode($jawaban, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $pairs = [];
                    }

                    if (is_array($pairs)) {
                        $matchesData = $soal->data_soal['matches'] ?? [];
                        $totalPairs = count($matchesData);
                        
                        if ($totalPairs > 0) {
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
            
            // Update Database with is_correct
            if ($jawabanSiswaRecord) {
                $jawabanSiswaRecord->is_correct = $isCorrect ? 1 : 0;
                $jawabanSiswaRecord->save();
            }

            if ($isCorrect) {
                $jumlahBenar++;
            }
        }
        
        $jumlahSalah = $totalSoal - $jumlahBenar;

        // Rumus Nilai: (Benar / Total) * 100
        $nilai = $totalSoal > 0 ? ($jumlahBenar / $totalSoal) * 100 : 0;
        
        $hasilUjian->nilai = $nilai;
        $hasilUjian->jumlah_benar = $jumlahBenar; // Simpan ke DB

        // Pastikan kelas_id tetap ada, update jika misalnya dulu waktu_mulai belum terekam kelas_id (untuk data lama)
        if (empty($hasilUjian->kelas_id)) {
            $hasilUjian->kelas_id = $siswa->kelas_id;
        }

        // User specifically asked to only store correct count, and DB reflects that.
        $hasilUjian->save();

        // --- 4. MIRRORING KE UJIAN INDUK (Jika ini Ujian Susulan) ---
        if ($ujian->is_susulan && $ujian->ujian_induk_id) {
            try {
                $hasilInduk = \App\Models\HasilUjian::updateOrCreate(
                    [
                        'ujian_id' => $ujian->ujian_induk_id,
                        'siswa_id' => $siswa->id
                    ],
                    [
                        'kelas_id'      => $siswa->kelas_id,
                        'waktu_mulai'   => $hasilUjian->waktu_mulai,
                        'waktu_selesai' => $hasilUjian->waktu_selesai,
                        'nilai'         => $hasilUjian->nilai,
                        'jumlah_benar'  => $hasilUjian->jumlah_benar,
                    ]
                );

                // Mirror JawabanSiswa
                // PENTING: Refresh dari DB agar is_correct yang sudah di-update terbaca dengan benar.
                // Tanpa refresh, $jawabanSiswa masih memegang objek lama dari memori (is_correct belum ter-update).
                $jawabanSiswaFresh = \App\Models\JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
                                        ->get()
                                        ->keyBy('soal_id');

                $parentUjian = Ujian::with('soals')->find($ujian->ujian_induk_id);
                if ($parentUjian) {
                    $parentSoalMap = $parentUjian->soals->pluck('id', 'bank_soal_id');

                    foreach ($jawabanSiswaFresh as $susulanSoalId => $jsRecord) {
                        // Ambil bank_soal_id dari soal susulan
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
                                    'is_correct'      => $jsRecord->is_correct, // Sekarang sudah benar
                                ]
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Mirroring Error: ' . $e->getMessage());
                // Tetap lanjut redirect agar siswa tidak error, guru bisa lapor jika nilai tidak sinkron
            }
        }

        return redirect()->route('siswa.ujian.hasil', $id)->with('success', 'Ujian telah selesai dikerjakan.');
    }

    public function hasilUjian($id)
    {
        $siswa = Auth::guard('siswa')->user();
        $ujian = Ujian::with('mapel')->findOrFail($id);

        $hasilUjian = \App\Models\HasilUjian::where('ujian_id', $id)
                        ->where('siswa_id', $siswa->id)
                        ->first();

        if (!$hasilUjian) {
            return redirect()->route('siswa.nilai')->with('error', 'Data ujian tidak ditemukan.');
        }

        // Hitung total soal
        $totalSoal = $ujian->soals()->count();
        // Jumlah benar sudah ada di hasilUjian (disimpan saat selesaiUjian)
        // Hitung jumlah salah
        $jumlahSalah = $totalSoal - $hasilUjian->jumlah_benar;

        return view('siswa.ujian.hasil', compact('siswa', 'ujian', 'hasilUjian', 'jumlahSalah', 'totalSoal'));
    }
}
