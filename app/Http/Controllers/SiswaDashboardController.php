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
        
        // Hitung Statistik Nilai
        $nilaiSemua = \App\Models\HasilUjian::where('siswa_id', $siswa->id)->get();
        $rataRata = $nilaiSemua->avg('nilai');
        $totalUjian = $nilaiSemua->count();
        $ujianTerakhir = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
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
            ->with(['mapel.guru']) // Fix: mapel.guru
            ->get();

        // 2. Akan Datang (Waktu mulai > sekarang)
        $akanDatang = Ujian::whereIn('mapel_id', $siswa->kelas->mapels->pluck('id')) // Fix: whereIn
            ->where('waktu_mulai', '>', now())
            ->with(['mapel.guru']) // Fix: mapel.guru
            ->get();

        // 3. Telah Berlalu (Waktu selesai < sekarang ATAU sudah dikerjakan)
        $telahBerlalu = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                            ->with(['ujian.mapel.guru']) // Fix: chain eager load
                            ->latest()
                            ->get();

        return view('siswa.dashboard', compact('siswa', 'rataRata', 'totalUjian', 'ujianTerakhir', 
            'sedangBerlangsung', 'akanDatang', 'telahBerlalu'));
    }

    public function indexNilai(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $keyword = $request->input('search');
        
        // 1. Hitung Rata-Rata Keseluruhan (Global Stats)
        // Revisi: Gunakan basis UJIAN yang unik, bukan HasilUjian (untuk menghindari duplikasi nilai jika ada remedial/multiple attempt yang belum dihandle)
        // Ini memastikan konsistensi dengan tampilan per-mapel yang meloop Ujian.
        $allUjianFinished = Ujian::whereHas('hasilUjians', function($q) use ($siswa) {
                                $q->where('siswa_id', $siswa->id);
                            })
                            ->with(['hasilUjians' => function($q) use ($siswa) {
                                $q->where('siswa_id', $siswa->id);
                            }])
                            ->get();

        $totalNilaiGlobal = 0;
        $countUjianGlobal = 0;

        foreach($allUjianFinished as $ujian) {
            // Gunakan logika yang sama dengan per-mapel: ambil satu nilai representatif per ujian
            // Kita ambil yang pertama (default) atau bisa dimodifikasi ambil max()
            $hasil = $ujian->hasilUjians->first(); 
            if($hasil) {
                $totalNilaiGlobal += $hasil->nilai;
                $countUjianGlobal++;
            }
        }

        $rataRataKeseluruhan = $countUjianGlobal > 0 ? ($totalNilaiGlobal / $countUjianGlobal) : 0;
        
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

        // 3. Load Ujian & Hitung Rata-Rata Per Mapel
        foreach($mapels as $mapel) {
            // Load ujian yang sudah selesai dikerjakan siswa
            // Kita load SEMUA ujian selesai untuk mapel ini agar rata-rata mapel AKURAT
            // (Meskipun valid jika kita mau filter ujian yang tampil nanti di view, tapi datanya harus ada)
            $mapel->ujian_selesai = Ujian::where('mapel_id', $mapel->id)
                ->whereHas('hasilUjians', function($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                })
                ->with(['hasilUjians' => function($q) use ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                }])
                ->latest() // Urutkan terbaru
                ->get();
                
            // Hitung Rata-Rata Mapel
            $totalNilaiMapel = 0;
            $countUjianMapel = 0;
            
            foreach($mapel->ujian_selesai as $ujian) {
                 $hasil = $ujian->hasilUjians->first();
                 if($hasil) {
                     $totalNilaiMapel += $hasil->nilai;
                     $countUjianMapel++;
                 }
            }
            
            $mapel->rata_rata = $countUjianMapel > 0 ? ($totalNilaiMapel / $countUjianMapel) : 0;
        }

        return view('siswa.nilai', compact('siswa', 'mapels', 'rataRataKeseluruhan', 'keyword'));
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

        // 3. Ambil Soal
        $semuaSoal = $ujian->soals()->get();
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

        // Query Bank Soal
        $query = \App\Models\BankSoal::whereIn('mapel_id', $mapelIds)
                    ->where('visibilitas', 'Public') // Hanya tampilkan yang Public
                    ->with(['mapel', 'guru']);

        if ($keyword) {
            $query->where('nama', 'like', "%{$keyword}%");
        }

        if ($selectedMapelId) {
            $query->where('mapel_id', $selectedMapelId);
        }

        $bankSoals = $query->latest()->get();

        return view('siswa.bank_soal', compact('siswa', 'bankSoals', 'keyword', 'mapels', 'selectedMapelId'));
    }

    // --- FITUR PENGERJAAN UJIAN ---

    public function konfirmasiUjian($id)
    {
        $siswa = Auth::guard('siswa')->user();
        $ujian = Ujian::with(['mapel.guru', 'soals'])->findOrFail($id);

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
        $ujian = Ujian::with('soals')->findOrFail($id); // Eager load soals

        // Cek Record Hasil Ujian (Session Ujian)
        $hasilUjian = \App\Models\HasilUjian::firstOrCreate(
            [
                'ujian_id' => $id,
                'siswa_id' => $siswa->id
            ],
            [
                'waktu_mulai' => now(), // Set waktu mulai saat pertama kali klik MULAI
                'nilai' => 0
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
                            ->with('soal') // Eager load soal
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
        $ujian = Ujian::with('soals')->findOrFail($id);
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
                    if ($jawab == 'A') $jawab = 'TRUE';
                    if ($jawab == 'B') $jawab = 'FALSE';
                    if ($kunci == 'A') $kunci = 'TRUE';
                    if ($kunci == 'B') $kunci = 'FALSE';
                }

                if ($jawab == $kunci && $jawab != '') {
                    $isCorrect = true;
                }
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
        // User specifically asked to only store correct count, and DB reflects that.
        $hasilUjian->save();

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
