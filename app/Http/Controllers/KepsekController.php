<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ujian;
use App\Models\Mapel;
use App\Models\HasilUjian;

class KepsekController extends Controller
{
   public function index()
    {
        // 1. Data Statistik Utama
        $totalGuru = Guru::count();
        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();
        $totalUjian = Ujian::count();
        
        // Cek apakah sudah ada nilai ujian yang masuk di sistem?
        $totalNilaiMasuk = HasilUjian::count();

        // Ambil Data Asli dari Database
        $dataKelas = Kelas::with(['ujians.hasilUjians', 'ujians.mapel', 'siswas'])->get()->map(function ($kelas) {
            
            // --- LOGIKA HITUNG NILAI ASLI ---
            $hitungRataGlobal = function ($keyword) use ($kelas) {
                $ujians = $kelas->ujians->filter(function ($ujian) use ($keyword) {
                    return stripos($ujian->nama_ujian, $keyword) !== false 
                        || stripos($ujian->jenis_ujian ?? '', $keyword) !== false;
                });
                $rata = $ujians->flatMap->hasilUjians->avg('nilai');
                return number_format($rata ?? 0, 2);
            };

            $nilaiKuis = $hitungRataGlobal('Kuis');
            $nilaiUTS  = $hitungRataGlobal('UTS');
            $nilaiUAS  = $hitungRataGlobal('UAS');
            $rataAkhir = number_format($kelas->ujians->flatMap->hasilUjians->avg('nilai') ?? 0, 2);

            // --- DATA GRAFIK ASLI ---
            $groupedByMapel = $kelas->ujians->groupBy('mapel_id');
            $chartLabels = [];
            $dataKuis = []; $dataUTS = []; $dataUAS = [];

            foreach ($groupedByMapel as $mapelId => $ujians) {
                $namaMapel = $ujians->first()->mapel->nama_mapel ?? 'Mapel Lain';
                
                $avgPerMapel = function($keyword) use ($ujians) {
                    $filtered = $ujians->filter(function ($u) use ($keyword) {
                        return stripos($u->nama_ujian, $keyword) !== false 
                            || stripos($u->jenis_ujian ?? '', $keyword) !== false;
                    });
                    return round($filtered->flatMap->hasilUjians->avg('nilai') ?? 0, 1);
                };

                $chartLabels[] = $namaMapel;
                $dataKuis[] = $avgPerMapel('Kuis');
                $dataUTS[] = $avgPerMapel('UTS');
                $dataUAS[] = $avgPerMapel('UAS');
            }

            // Data Siswa Asli
            $chartSiswa = $kelas->siswas->map(function ($siswa) use ($kelas) {
                $nilaiSiswa = $kelas->ujians->flatMap(function ($ujian) use ($siswa) {
                    return $ujian->hasilUjians->where('siswa_id', $siswa->id);
                })->avg('nilai');
                return ['label' => $siswa->nama_lengkap, 'value' => round($nilaiSiswa ?? 0, 1)];
            })->sortByDesc('value')->take(5)->values();

            // Data Sebaran Asli
            $gradeDistribution = [0, 0, 0, 0];
            $kelas->siswas->each(function ($siswa) use ($kelas, &$gradeDistribution) {
                $nilai = $kelas->ujians->flatMap(function ($ujian) use ($siswa) {
                    return $ujian->hasilUjians->where('siswa_id', $siswa->id);
                })->avg('nilai') ?? 0;
                if ($nilai > 85) $gradeDistribution[0]++;
                elseif ($nilai >= 70) $gradeDistribution[1]++;
                elseif ($nilai >= 55) $gradeDistribution[2]++;
                else $gradeDistribution[3]++;
            });

            return (object) [
                'id'         => $kelas->id,
                'nama_kelas' => $kelas->kelas,
                'kuis'       => $nilaiKuis,
                'uts'        => $nilaiUTS,
                'uas'        => $nilaiUAS,
                'akhir'      => $rataAkhir,
                'chart_labels' => $chartLabels,
                'data_kuis'    => $dataKuis,
                'data_uts'     => $dataUTS,
                'data_uas'     => $dataUAS,
                'siswa_labels' => $chartSiswa->pluck('label'),
                'siswa_values' => $chartSiswa->pluck('value'),
                'sebaran_data' => $gradeDistribution
            ];
        });

        // ============================================================
        // PERBAIKAN LOGIKA DUMMY DATA
        // ============================================================
        // Jika tidak ada kelas ATAU belum ada satupun nilai ujian yang masuk -> PAKAI DUMMY
        if ($dataKelas->isEmpty() || $totalNilaiMasuk == 0) {
            
            // Kita TIMPA variabel $dataKelas dengan data palsu
            $dummyClasses = ['VII', 'VIII', 'IX'];
            
            $dataKelas = collect($dummyClasses)->map(function($namaKelas, $index) {
                
                // Mata Pelajaran Lengkap untuk Demo
                $mapels = ['Pendidikan Agama', 'PPKn', 'B. Indonesia', 'Matematika', 'IPA', 'IPS', 'B. Inggris', 'Seni Budaya', 'PJOK', 'Prakarya'];
                
                // Generate Nilai Acak (70-95)
                $dataKuis = []; $dataUTS = []; $dataUAS = [];
                foreach($mapels as $m) {
                    $dataKuis[] = rand(75, 90);
                    $dataUTS[] = rand(70, 88);
                    $dataUAS[] = rand(70, 95);
                }

                // Siswa Dummy
                $siswaLabels = ['Ahmad Santoso', 'Budi Pratama', 'Citra Lestari', 'Dewi Anggraini', 'Eko Saputra'];
                $siswaValues = [92.5, 89.0, 88.5, 87.0, 86.5];

                return (object) [
                    'id'         => $index + 100, // ID dummy agar unik
                    'nama_kelas' => $namaKelas,
                    'kuis'       => number_format(collect($dataKuis)->avg(), 2),
                    'uts'        => number_format(collect($dataUTS)->avg(), 2),
                    'uas'        => number_format(collect($dataUAS)->avg(), 2),
                    'akhir'      => number_format(rand(80, 88), 2),
                    
                    // Grafik Mapel Lengkap
                    'chart_labels' => $mapels,
                    'data_kuis'    => $dataKuis,
                    'data_uts'     => $dataUTS,
                    'data_uas'     => $dataUAS,
                    
                    // Grafik Siswa
                    'siswa_labels' => $siswaLabels,
                    'siswa_values' => $siswaValues,
                    
                    // Grafik Sebaran
                    'sebaran_data' => [10, 15, 5, 2] // A, B, C, D
                ];
            });
            
            // Jika data benar-benar kosong, isi statistik header juga dengan angka palsu
            if ($totalKelas == 0) {
                $totalGuru = 15;
                $totalSiswa = 96;
                $totalKelas = 3;
                $totalUjian = 24;
            }
        }

        $kepsek = Auth::user();

        return view('kepsek.landingpage', compact(
            'totalGuru', 'totalSiswa', 'totalKelas', 'totalUjian',
            'dataKelas', 
            'kepsek'
        ));
    }

   public function monitorGuru(Request $request)
    {
        // 1. Query Dasar & Search
        $query = Guru::with(['mapels.kelas', 'user', 'waliKelas.kelas']);
        
        // Urutkan nama dulu agar rapi di dalam grup yang sama
        $query->orderBy('nama_lengkap', 'asc'); 

        if ($request->has('search') && $request->search != null) {
            $keyword = $request->search;
            $query->where(function($q) use ($keyword) {
                $q->where('nama_lengkap', 'LIKE', "%{$keyword}%")
                  ->orWhere('nip', 'LIKE', "%{$keyword}%")
                  ->orWhereHas('mapels', function($qMapel) use ($keyword) {
                      $qMapel->where('nama_mapel', 'LIKE', "%{$keyword}%");
                  });
            });
        }

        // 2. Ambil Semua Data
        $gurus = $query->get();

        // 3. LOGIKA CUSTOM SORTING (KEPSEK -> WALI KELAS -> GURU -> OPERATOR)
        $gurus = $gurus->sortBy(function($guru) {
            // Ambil role (Prioritas: tabel guru -> tabel user -> default Guru)
            $roleRaw = $guru->role ?? optional($guru->user)->role ?? 'Guru';
            $role = ucwords(strtolower($roleRaw));
            
            // Cek status Wali Kelas
            $isWaliKelas = $guru->waliKelas && $guru->waliKelas->kelas;

            // --- SKOR PRIORITAS ---
            if (stripos($role, 'Kepala Sekolah') !== false) {
                return 1; // Paling Atas
            }
            
            if (stripos($role, 'Operator') !== false) {
                return 4; // Paling Bawah
            }

            if ($isWaliKelas) {
                return 2; // Wali Kelas (di bawah Kepsek)
            }

            return 3; // Guru Biasa
            
        })->values(); // Reset index collection agar rapi

        return view('kepsek.daftar_guru', compact('gurus'));
    }

    public function monitorSiswa(Request $request)
    {
        // 1. Ambil daftar kelas untuk Dropdown Filter
        $kelasList = Kelas::all();

        // 2. Mulai Query Siswa
        $query = Siswa::with('kelas');

        // Logika Pencarian (Nama / NISN)
        if ($request->has('search') && $request->search != null) {
            $keyword = $request->search;
            $query->where(function($q) use ($keyword) {
                $q->where('nama_lengkap', 'LIKE', "%{$keyword}%")
                  // ->orWhere('nis', 'LIKE', "%{$keyword}%")  <-- INI PENYEBAB ERROR (HAPUS)
                  ->orWhere('nisn', 'LIKE', "%{$keyword}%");
            });
        }

        // Logika Filter Kelas
        if ($request->has('kelas_id') && $request->kelas_id != null) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Urutkan: Kelas dulu, baru Nama
        $query->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
              ->orderBy('kelas.kelas', 'asc')
              ->orderBy('siswas.nama_lengkap', 'asc')
              ->select('siswas.*');

        // Ambil Data
        $siswas = $query->get();

        return view('kepsek.daftar_siswa', compact('siswas', 'kelasList'));
    }
    
    public function laporanNilai(Request $request)
    {
        // 1. Data Kelas untuk Filter
        $kelasList = Kelas::all();

        // 2. Query Siswa (Eager Load hasil ujian & mapel agar performa cepat)
        $query = Siswa::with(['kelas', 'hasilUjians.ujian']);

        // --- Filter Kelas ---
        if ($request->has('kelas_id') && $request->kelas_id != null) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // --- Filter Pencarian ---
        if ($request->has('search') && $request->search != null) {
            $keyword = $request->search;
            $query->where('nama_lengkap', 'LIKE', "%{$keyword}%");
        }

        // 3. Ambil Data & Proses Hitung Nilai Rata-rata
        $siswas = $query->get()->map(function($siswa) {
            
            $hasil = $siswa->hasilUjians;

            // Helper untuk hitung rata-rata berdasarkan kata kunci nama ujian
            $hitungRata = function($keyword) use ($hasil) {
                $filtered = $hasil->filter(function($h) use ($keyword) {
                    // Cek di nama ujian atau jenis ujian
                    return stripos($h->ujian->nama_ujian, $keyword) !== false 
                        || stripos($h->ujian->jenis_ujian ?? '', $keyword) !== false;
                });
                return $filtered->avg('nilai') ?? 0;
            };

            $rataKuis = $hitungRata('Kuis');
            $rataUTS  = $hitungRata('UTS');
            $rataUAS  = $hitungRata('UAS');

            // Rumus Nilai Akhir (Contoh: 30% Kuis + 30% UTS + 40% UAS)
            // Anda bisa sesuaikan bobotnya
            $nilaiAkhir = ($rataKuis * 0.3) + ($rataUTS * 0.3) + ($rataUAS * 0.4);

            // RETURN SEBAGAI OBJECT (Pastikan 'id' ada!)
            return (object) [
                'id'          => $siswa->id, // <--- INI PENTING (Perbaikan Error)
                'nama'        => $siswa->nama_lengkap,
                'nisn'        => $siswa->nisn,
                'kelas'       => $siswa->kelas->kelas ?? 'Belum Masuk',
                'rata_kuis'   => number_format($rataKuis, 1),
                'rata_uts'    => number_format($rataUTS, 1),
                'rata_uas'    => number_format($rataUAS, 1),
                'nilai_akhir' => number_format($nilaiAkhir, 1),
            ];
        });

        return view('kepsek.daftar_nilai', compact('siswas', 'kelasList'));
    }

    public function detailNilai($id)
    {
        $siswa = Siswa::with('kelas')->findOrFail($id);

        // 1. AMBIL MAPEL YANG TERDAFTAR DI KELAS SISWA
        $mapelKelas = Mapel::where('kelas_id', $siswa->kelas_id)->get();
        
        $maxKuis = 0; 

        $transkrip = $mapelKelas->map(function ($mapel) use ($siswa, &$maxKuis) {
            
            // A. AMBIL MASTER KUIS (Agar kolom konsisten)
            // Kita ambil daftar ujian tipe 'Kuis' yang dibuat guru untuk mapel ini
            $masterKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis')
                            ->orderBy('created_at', 'asc') // Urutkan berdasarkan waktu buat
                            ->get();

            // B. AMBIL HASIL UJIAN SISWA
            $hasilSiswa = HasilUjian::where('siswa_id', $siswa->id)
                            ->whereHas('ujian', function($q) use ($mapel) {
                                $q->where('mapel_id', $mapel->id);
                            })
                            ->with('ujian')
                            ->get();

            // C. LOOPING MATRIX (Kuis 1, Kuis 2, dst)
            $listKuis = [];
            foreach($masterKuis as $kuis) {
                // Cek apakah siswa punya nilai di ujian ini
                $dataNilai = $hasilSiswa->firstWhere('ujian_id', $kuis->id);
                
                // Jika ada nilainya ambil, jika tidak beri tanda '-'
                $listKuis[] = $dataNilai ? $dataNilai->nilai : '-';
            }

            // Update Max Kuis untuk Header Tabel View
            if (count($listKuis) > $maxKuis) {
                $maxKuis = count($listKuis);
            }

            // Hitung Rata-rata Kuis (Hanya hitung yang ada angkanya/valid)
            $nilaiValid = array_filter($listKuis, fn($v) => is_numeric($v));
            $rataKuis = count($nilaiValid) > 0 ? array_sum($nilaiValid) / count($nilaiValid) : 0;

            // --- AMBIL UTS & UAS ---
            $nilaiUTS = $hasilSiswa->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UTS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false)->first()->nilai ?? 0;
            $nilaiUAS = $hasilSiswa->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UAS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false)->first()->nilai ?? 0;

            // Hitung Nilai Akhir
            $akhir = ($rataKuis * 0.4) + ($nilaiUTS * 0.3) + ($nilaiUAS * 0.3);

            // Tentukan Predikat (Opsional, untuk tampilan)
            if($akhir >= 90) $grade = 'A';
            elseif($akhir >= 80) $grade = 'B';
            elseif($akhir >= 70) $grade = 'C';
            else $grade = 'D';

            // Helper Tampilan
            $show = fn($val) => $val == 0 ? '-' : number_format($val, 0);

            return (object) [
                'mapel'      => $mapel->nama_mapel,
                'list_kuis'  => $listKuis, // Array yang urut sesuai master ujian
                'rata_kuis'  => $show($rataKuis),
                'uts'        => $show($nilaiUTS),
                'uas'        => $show($nilaiUAS),
                'akhir'      => $akhir == 0 ? '-' : number_format($akhir, 1),
                'grade_val'  => $akhir,
                'predikat'   => $grade
            ];
        });

        // Default minimal 1 kolom kuis
        if ($maxKuis == 0) $maxKuis = 1;

        return view('kepsek.detail_nilai', compact('siswa', 'transkrip', 'maxKuis'));
    }
}