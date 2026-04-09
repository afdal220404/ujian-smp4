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

        // Ambil Data Asli dari Database, abaikan kelas Alumni
        $dataKelas = Kelas::where('kelas', '!=', 'Alumni')->with(['ujians.hasilUjians', 'ujians.mapel', 'siswas'])->get()->map(function ($kelas) {
            
            // --- DATA GRAFIK MAPEL ASLI ---
            $groupedByMapel = $kelas->ujians->groupBy('mapel_id');
            $chartLabels = [];
            $dataKuis = []; $dataUTS = []; $dataUAS = [];

            foreach ($groupedByMapel as $mapelId => $ujians) {
                $namaMapel = $ujians->first()->mapel->nama_mapel ?? 'Mapel Lain';
                
                $avgPerMapel = function($keyword) use ($ujians, $kelas) {
                    $filtered = $ujians->filter(function ($u) use ($keyword) {
                        return stripos($u->nama_ujian, $keyword) !== false 
                            || stripos($u->jenis_ujian ?? '', $keyword) !== false;
                    });
                    return round($filtered->flatMap->hasilUjians->where('kelas_id', $kelas->id)->avg('nilai') ?? 0, 1);
                };

                $chartLabels[] = $namaMapel;
                $dataKuis[] = $avgPerMapel('Kuis');
                $dataUTS[] = $avgPerMapel('UTS');
                $dataUAS[] = $avgPerMapel('UAS');
            }

            // --- HITUNG NILAI AKHIR SISWA DENGAN RUMUS BARU ---
            $mapels = $kelas->ujians->pluck('mapel')->unique('id');
            $siswaAverages = collect();
            $gradeDistribution = [0, 0, 0, 0];
            
            $totalKuisSiswaGlobal = collect();
            $totalUtsSiswaGlobal = collect();
            $totalUasSiswaGlobal = collect();
            
            foreach ($kelas->siswas as $siswa) {
                $hasil = $kelas->ujians->flatMap(function ($ujian) use ($siswa, $kelas) {
                    return $ujian->hasilUjians->where('siswa_id', $siswa->id)->where('kelas_id', $kelas->id);
                });
                
                $totalAkhirMapel = 0;
                $jumlahMapel = 0;
                
                $totalKuisSiswa = 0; $countKuisSiswa = 0;
                $totalUtsSiswa  = 0; $countUtsSiswa  = 0;
                $totalUasSiswa  = 0; $countUasSiswa  = 0;

                if ($hasil->isNotEmpty()) {
                    foreach ($mapels as $mapel) {
                        if (!$mapel) continue;
                        $hasilSiswaMapel = $hasil->filter(fn($h) => $h->ujian->mapel_id == $mapel->id);
                        // --- KONSOLIDASI NILAI INDUK & SUSULAN ---
                        $hasilSiswaMapelConsolidated = $hasilSiswaMapel->groupBy(function($h) {
                            return $h->ujian->is_susulan ? $h->ujian->ujian_induk_id : $h->ujian_id;
                        })->map(fn($group) => $group->first());

                        if ($hasilSiswaMapelConsolidated->isNotEmpty()) {
                            $kuisColl = $hasilSiswaMapelConsolidated->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'Kuis') !== false);
                            $utsColl  = $hasilSiswaMapelConsolidated->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false);
                            $uasColl  = $hasilSiswaMapelConsolidated->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false);
                            
                            $kuis = $kuisColl->isNotEmpty() ? $kuisColl->avg('nilai') : null;
                            $uts  = $utsColl->isNotEmpty() ? $utsColl->avg('nilai') : null;
                            $uas  = $uasColl->isNotEmpty() ? $uasColl->avg('nilai') : null;
                            
                            if($kuis !== null) { $totalKuisSiswa += $kuis; $countKuisSiswa++; }
                            if($uts !== null) { $totalUtsSiswa += $uts; $countUtsSiswa++; }
                            if($uas !== null) { $totalUasSiswa += $uas; $countUasSiswa++; }
                            
                            $komponen = array_filter([$kuis, $uts, $uas], fn($v) => $v !== null);
                            $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;
                            
                            if ($akhir !== null) {
                                $totalAkhirMapel += $akhir;
                                $jumlahMapel++;
                            }
                        }
                    }
                }
                
                $nilaiSiswa = $jumlahMapel > 0 ? $totalAkhirMapel / $jumlahMapel : null;
                
                if ($countKuisSiswa > 0) $totalKuisSiswaGlobal->push($totalKuisSiswa / $countKuisSiswa);
                if ($countUtsSiswa > 0) $totalUtsSiswaGlobal->push($totalUtsSiswa / $countUtsSiswa);
                if ($countUasSiswa > 0) $totalUasSiswaGlobal->push($totalUasSiswa / $countUasSiswa);
                
                if ($nilaiSiswa !== null) {
                    $siswaAverages->push(['label' => $siswa->nama_lengkap, 'value' => round($nilaiSiswa, 1)]);
                    if ($nilaiSiswa >= 85) $gradeDistribution[0]++;
                    elseif ($nilaiSiswa >= 75) $gradeDistribution[1]++;
                    elseif ($nilaiSiswa >= 70) $gradeDistribution[2]++;
                    else $gradeDistribution[3]++;
                }
            }
            
            $rataAkhir = $siswaAverages->isNotEmpty() ? number_format($siswaAverages->avg('value'), 2) : 0;
            $nilaiKuis = $totalKuisSiswaGlobal->isNotEmpty() ? number_format($totalKuisSiswaGlobal->avg(), 2) : 0;
            $nilaiUTS  = $totalUtsSiswaGlobal->isNotEmpty() ? number_format($totalUtsSiswaGlobal->avg(), 2) : 0;
            $nilaiUAS  = $totalUasSiswaGlobal->isNotEmpty() ? number_format($totalUasSiswaGlobal->avg(), 2) : 0;
            
            $chartSiswa = $siswaAverages->sortByDesc('value')->take(5)->values();

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

        // Ambil semua mapel dan group by kelas_id
        $semuaMapel = Mapel::all()->groupBy('kelas_id');

        // 3. Ambil Data & Proses Hitung Nilai Rata-rata
        $siswas = $query->get()->map(function($siswa) use ($semuaMapel) {
            
            $hasil = $siswa->hasilUjians;
            $mapels = $semuaMapel->get($siswa->kelas_id) ?? collect();
            
            $totalAkhirMapel = 0; $jumlahMapel = 0;
            $totalKuisSiswa = 0; $countKuisSiswa = 0;
            $totalUtsSiswa  = 0; $countUtsSiswa  = 0;
            $totalUasSiswa  = 0; $countUasSiswa  = 0;

            if ($hasil->isNotEmpty()) {
                foreach ($mapels as $mapel) {
                    $hasilSiswaMapel = $hasil->filter(fn($h) => $h->ujian->mapel_id == $mapel->id && $h->kelas_id == $siswa->kelas_id);
                    if ($hasilSiswaMapel->isNotEmpty()) {
                        // --- KONSOLIDASI NILAI INDUK & SUSULAN ---
                        $hasilSiswaMapelConsolidated = $hasilSiswaMapel->groupBy(function($h) {
                            return $h->ujian->is_susulan ? $h->ujian->ujian_induk_id : $h->ujian_id;
                        })->map(fn($group) => $group->first());

                        $kuisColl = $hasilSiswaMapelConsolidated->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'Kuis') !== false);
                        $utsColl  = $hasilSiswaMapelConsolidated->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false);
                        $uasColl  = $hasilSiswaMapelConsolidated->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false);
                        
                        $kuis = $kuisColl->isNotEmpty() ? $kuisColl->avg('nilai') : null;
                        $uts  = $utsColl->isNotEmpty() ? $utsColl->avg('nilai') : null;
                        $uas  = $uasColl->isNotEmpty() ? $uasColl->avg('nilai') : null;
                        
                        if($kuis !== null) { $totalKuisSiswa += $kuis; $countKuisSiswa++; }
                        if($uts  !== null) { $totalUtsSiswa += $uts; $countUtsSiswa++; }
                        if($uas  !== null) { $totalUasSiswa += $uas; $countUasSiswa++; }
                        
                        $komponen = array_filter([$kuis, $uts, $uas], fn($v) => $v !== null);
                        $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;
                        
                        if ($akhir !== null) {
                            $totalAkhirMapel += $akhir;
                            $jumlahMapel++;
                        }
                    }
                }
            }

            // Global display stats — null jika belum ada ujian
            $rataKuis = $countKuisSiswa > 0 ? $totalKuisSiswa / $countKuisSiswa : null;
            $rataUTS  = $countUtsSiswa  > 0 ? $totalUtsSiswa  / $countUtsSiswa  : null;
            $rataUAS  = $countUasSiswa  > 0 ? $totalUasSiswa  / $countUasSiswa  : null;

            $nilaiAkhir = $jumlahMapel > 0 ? $totalAkhirMapel / $jumlahMapel : null;

            $show = fn($val) => $val === null ? '-' : number_format($val, 1);

            // RETURN SEBAGAI OBJECT (Pastikan 'id' ada!)
            return (object) [
                'id'          => $siswa->id,
                'nama'        => $siswa->nama_lengkap,
                'nisn'        => $siswa->nisn,
                'kelas'       => $siswa->kelas->kelas ?? 'Belum Masuk',
                'rata_kuis'   => $show($rataKuis),
                'rata_uts'    => $show($rataUTS),
                'rata_uas'    => $show($rataUAS),
                'nilai_akhir' => $show($nilaiAkhir),
                'grade_raw'   => $nilaiAkhir ?? 0,
            ];
        });

        return view('kepsek.daftar_nilai', compact('siswas', 'kelasList'));
    }

    public function detailNilai($id)
    {
        $siswa = Siswa::with('kelas')->findOrFail($id);

        // Tentukan kelas mana yang mau dilihat (Filter Historis)
        $availableKelasIds = HasilUjian::where('siswa_id', $siswa->id)
                                ->whereNotNull('kelas_id')
                                ->distinct()
                                ->pluck('kelas_id')
                                ->toArray();
        
        // Selalu masukkan kelas saat ini ke daftar filter
        if (!in_array($siswa->kelas_id, $availableKelasIds)) {
            $availableKelasIds[] = $siswa->kelas_id;
        }

        $activeKelasId = request('kelas_id', $siswa->kelas_id);
        
        // Ambil data kelas aktif (bisa jadi kelas lama)
        $kelas = Kelas::find($activeKelasId) ?? $siswa->kelas;

        // 1. AMBIL MAPEL YANG TERDAFTAR DI KELAS SISWA (Filter Kelas Aktif)
        $mapelKelas = Mapel::where('kelas_id', $activeKelasId)->get();
        
        $maxKuis = 0; 
        $maxUts = 0;
        $maxUas = 0;

        $transkrip = $mapelKelas->map(function ($mapel) use ($siswa, $activeKelasId, &$maxKuis, &$maxUts, &$maxUas) {
            
            // A. AMBIL MASTER (Hanya Ujian Induk, Bukan Susulan)
            $masterKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis')
                            ->where('is_susulan', false)
                            ->orderBy('created_at', 'asc')
                            ->get();

            $masterUts = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'UTS')
                            ->where('is_susulan', false)
                            ->orderBy('created_at', 'asc')
                            ->get();
                            
            $masterUas = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'UAS')
                            ->where('is_susulan', false)
                            ->orderBy('created_at', 'asc')
                            ->get();

            // B. AMBIL HASIL UJIAN SISWA (Filter Kelas Aktif)
            $hasilSiswa = HasilUjian::where('siswa_id', $siswa->id)
                            ->where('kelas_id', $activeKelasId)
                            ->whereHas('ujian', function($q) use ($mapel) {
                                $q->where('mapel_id', $mapel->id);
                            })
                            ->with('ujian')
                            ->get();

            // C. LOOPING MATRIX (Ambil nilai dari Induk ATAU Susulannya)
            $listKuis = [];
            foreach($masterKuis as $kuis) {
                $dataNilai = $hasilSiswa->filter(fn($h) => $h->ujian_id == $kuis->id || $h->ujian->ujian_induk_id == $kuis->id)->first();
                $listKuis[] = $dataNilai ? $dataNilai->nilai : '-';
            }

            $listUts = [];
            foreach($masterUts as $utsMod) {
                $dataNilai = $hasilSiswa->filter(fn($h) => $h->ujian_id == $utsMod->id || $h->ujian->ujian_induk_id == $utsMod->id)->first();
                $listUts[] = $dataNilai ? $dataNilai->nilai : '-';
            }

            $listUas = [];
            foreach($masterUas as $uasMod) {
                $dataNilai = $hasilSiswa->filter(fn($h) => $h->ujian_id == $uasMod->id || $h->ujian->ujian_induk_id == $uasMod->id)->first();
                $listUas[] = $dataNilai ? $dataNilai->nilai : '-';
            }

            if (count($listKuis) > $maxKuis) $maxKuis = count($listKuis);
            if (count($listUts) > $maxUts) $maxUts = count($listUts);
            if (count($listUas) > $maxUas) $maxUas = count($listUas);

            $nilaiValid = array_filter($listKuis, fn($v) => is_numeric($v));
            $rataKuis = count($nilaiValid) > 0 ? array_sum($nilaiValid) / count($nilaiValid) : null;

            $utsValid = array_filter($listUts, fn($v) => is_numeric($v));
            $utsVal = count($utsValid) > 0 ? array_sum($utsValid) / count($utsValid) : null;

            $uasValid = array_filter($listUas, fn($v) => is_numeric($v));
            $uasVal = count($uasValid) > 0 ? array_sum($uasValid) / count($uasValid) : null;

            $komponen = array_filter([$rataKuis, $utsVal, $uasVal], fn($v) => $v !== null);
            $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;

            $grade = '-';
            if ($akhir !== null) {
                if($akhir >= 85) $grade = 'A';
                elseif($akhir >= 75) $grade = 'B';
                elseif($akhir >= 70) $grade = 'C';
                else $grade = 'D';
            }

            $show = fn($val) => $val === null ? '-' : number_format($val, 1);

            return (object) [
                'mapel'      => $mapel->nama_mapel,
                'list_kuis'  => $listKuis,
                'list_uts'   => $listUts,
                'list_uas'   => $listUas,
                'rata_kuis'  => $show($rataKuis),
                'uts'        => $show($utsVal),
                'uas'        => $show($uasVal),
                'akhir'      => $akhir === null ? '-' : number_format($akhir, 1),
                'grade_val'  => $akhir ?? 0,
                'predikat'   => $grade
            ];
        });

        if ($maxKuis == 0) $maxKuis = 1;
        if ($maxUts == 0) $maxUts = 1;
        if ($maxUas == 0) $maxUas = 1;

        return view('kepsek.detail_nilai', compact('siswa', 'transkrip', 'maxKuis', 'maxUts', 'maxUas', 'availableKelasIds', 'activeKelasId', 'kelas'));
    }

    public function indexAlumni(Request $request)
    {
        // Cari kelas Alumni
        $alumniKelas = Kelas::where('kelas', 'Alumni')->first();
        $alumniId = $alumniKelas ? $alumniKelas->id : -1;

        // Query Siswa yang Alumni atau kelas_id nya null (legacy alumni)
        $query = Siswa::with(['hasilUjians.ujian'])->where(function($q) use ($alumniId) {
            $q->where('kelas_id', $alumniId)
              ->orWhereNull('kelas_id');
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswas = $query->orderBy('nama_lengkap', 'asc')->get();
        return view('kepsek.daftar_alumni', compact('siswas'));
    }

    public function detailAlumni($id)
    {
        $siswa = Siswa::with('kelas')->findOrFail($id);

        $availableKelasIds = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                                ->whereNotNull('kelas_id')
                                ->distinct()
                                ->pluck('kelas_id')
                                ->toArray();
        
        if ($siswa->kelas_id && !in_array($siswa->kelas_id, $availableKelasIds)) {
            $availableKelasIds[] = $siswa->kelas_id;
        }

        $allTranskrips = [];
        $kelasToProcess = [1, 2, 3]; // Kelas SMP VII, VIII, IX

        foreach($kelasToProcess as $kId) {
            // Hanya proses jika siswa pernah di kelas ini
            if (!in_array($kId, $availableKelasIds)) {
                continue;
            }

            $kelas = \App\Models\Kelas::find($kId);
            if (!$kelas) continue;

            $mapelKelas = \App\Models\Mapel::where('kelas_id', $kId)->with('guru')->get();

            $maxKuis = 0; $maxUts = 0; $maxUas = 0;

            $transkrip = $mapelKelas->map(function ($mapel) use ($siswa, $kId, &$maxKuis, &$maxUts, &$maxUas) {
                
                // A. AMBIL MASTER (Hanya Ujian Induk, Bukan Susulan)
                $masterKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'Kuis')->where('is_susulan', false)->orderBy('created_at', 'asc')->get();
                $masterUts = \App\Models\Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'UTS')->where('is_susulan', false)->orderBy('created_at', 'asc')->get();
                $masterUas = \App\Models\Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'UAS')->where('is_susulan', false)->orderBy('created_at', 'asc')->get();

                // B. AMBIL HASIL UJIAN SISWA
                $hasilSiswa = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                                ->where('kelas_id', $kId)
                                ->whereHas('ujian', function($q) use ($mapel) {
                                    $q->where('mapel_id', $mapel->id);
                                })
                                ->with('ujian')
                                ->get();

                $listKuis = [];
                foreach ($masterKuis as $ujian) {
                    $score = $hasilSiswa->filter(fn($h) => $h->ujian_id == $ujian->id || $h->ujian->ujian_induk_id == $ujian->id)->first();
                    $listKuis[] = $score ? $score->nilai : '-';
                }

                $listUts = [];
                foreach ($masterUts as $ujian) {
                    $score = $hasilSiswa->filter(fn($h) => $h->ujian_id == $ujian->id || $h->ujian->ujian_induk_id == $ujian->id)->first();
                    $listUts[] = $score ? $score->nilai : '-';
                }

                $listUas = [];
                foreach ($masterUas as $ujian) {
                    $score = $hasilSiswa->filter(fn($h) => $h->ujian_id == $ujian->id || $h->ujian->ujian_induk_id == $ujian->id)->first();
                    $listUas[] = $score ? $score->nilai : '-';
                }

                if (count($masterKuis) > $maxKuis) $maxKuis = count($masterKuis);
                if (count($masterUts) > $maxUts) $maxUts = count($masterUts);
                if (count($masterUas) > $maxUas) $maxUas = count($masterUas);

                $validKuis = array_filter($listKuis, fn($v) => is_numeric($v));
                $rataKuis = count($validKuis) > 0 ? array_sum($validKuis) / count($validKuis) : null;

                $validUts = array_filter($listUts, fn($v) => is_numeric($v));
                $rataUts = count($validUts) > 0 ? array_sum($validUts) / count($validUts) : null;

                $validUas = array_filter($listUas, fn($v) => is_numeric($v));
                $rataUas = count($validUas) > 0 ? array_sum($validUas) / count($validUas) : null;

                $komponen = array_filter([$rataKuis, $rataUts, $rataUas], fn($v) => $v !== null);
                $nilaiAkhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;

                return [
                    'mapel'      => $mapel,
                    'detailKuis' => $listKuis,
                    'detailUts'  => $listUts,
                    'detailUas'  => $listUas,
                    'nilaiAkhir' => $nilaiAkhir
                ];
            });

            if ($maxKuis == 0) $maxKuis = 1;
            if ($maxUts == 0) $maxUts = 1;
            if ($maxUas == 0) $maxUas = 1;

            $allTranskrips[] = [
                'kelas' => $kelas,
                'transkrip' => $transkrip,
                'maxKuis' => $maxKuis,
                'maxUts' => $maxUts,
                'maxUas' => $maxUas
            ];
        }

        return view('kepsek.detail_alumni', compact('siswa', 'allTranskrips'));
    }
}