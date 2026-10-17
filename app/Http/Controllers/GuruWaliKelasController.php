<?php

namespace App\Http\Controllers;

use App\Models\HasilUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Exports\RekapNilaiExport;
use Maatwebsite\Excel\Facades\Excel;

class GuruWaliKelasController extends Controller
{
    public function show(Kelas $kelas)
    {
        $guru = Auth::user();
        $siswaIds = Siswa::where('kelas_id', $kelas->id)->pluck('id');
        $totalSiswa = $siswaIds->count();

        // 1. AMBIL DATA REAL (DATABASE ONLY) - Filter by Current Class
        $allHasil = HasilUjian::whereIn('siswa_id', $siswaIds)
                        ->where('kelas_id', $kelas->id)
                        ->with('ujian')
                        ->get();
        $hasRealData = $allHasil->isNotEmpty();

        // 2. HITUNG NILAI AKHIR SISWA DAN MAPEL BERMASALAH
        $mapels = Mapel::where('kelas_id', $kelas->id)->get();
        $siswas = Siswa::where('kelas_id', $kelas->id)->with('hasilUjians.ujian')->get();
        
        $siswas->each(function($s) use ($mapels, $kelas) {
            $totalAkhirMapel = 0;
            $jumlahMapel = 0;
            $mapelBermasalah = [];
            
            $totalKuisSiswa = 0; $countKuisSiswa = 0;
            $totalUtsSiswa  = 0; $countUtsSiswa  = 0;
            $totalUasSiswa  = 0; $countUasSiswa  = 0;
            
            if ($s->hasilUjians->isNotEmpty()) {
                foreach ($mapels as $mapel) {
                    $hasilSiswaMapel = $s->hasilUjians->filter(fn($h) => $h->ujian->mapel_id == $mapel->id && $h->kelas_id == $kelas->id);
                    
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
                            if ($akhir < 70) {
                                $mapelBermasalah[] = (object) [
                                    'nama_mapel' => $mapel->nama_mapel,
                                    'nilai' => number_format($akhir, 1)
                                ];
                            }
                        }
                    }
                }
            }
            
            $s->mapel_bermasalah = $mapelBermasalah;
            $s->rata_rata_akhir = $jumlahMapel > 0 ? $totalAkhirMapel / $jumlahMapel : null;
            $s->rata_kuis_global = $countKuisSiswa > 0 ? $totalKuisSiswa / $countKuisSiswa : null;
            $s->rata_uts_global  = $countUtsSiswa > 0 ? $totalUtsSiswa / $countUtsSiswa : null;
            $s->rata_uas_global  = $countUasSiswa > 0 ? $totalUasSiswa / $countUasSiswa : null;
            
            return $s;
        });

        $siswaBermasalah = $siswas->filter(fn($s) => count($s->mapel_bermasalah) > 0)->sortBy('nama_lengkap');

        $rataRataKelasCollection = $siswas->filter(fn($s) => $s->rata_rata_akhir !== null);
        $rataRataKelas = $rataRataKelasCollection->isNotEmpty() ? $rataRataKelasCollection->avg('rata_rata_akhir') : 0;
        
        // 3. HITUNG RATA-RATA GLOBAL KELAS (Secara Keseluruhan) DENGAN CARA YANG SAMA
        $rataKuis = $siswas->filter(fn($s) => $s->rata_kuis_global !== null)->avg('rata_kuis_global') ?? 0;
        $rataUTS  = $siswas->filter(fn($s) => $s->rata_uts_global !== null)->avg('rata_uts_global') ?? 0;
        $rataUAS  = $siswas->filter(fn($s) => $s->rata_uas_global !== null)->avg('rata_uas_global') ?? 0;

        // 4. CHART PERFORMANSA PER MAPEL
        $dataMapel = $mapels->map(function($mapel) use ($allHasil) {
            $hasil = $allHasil->filter(fn($h) => $h->ujian->mapel_id == $mapel->id);
            
            $kuis = $hasil->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'Kuis') !== false)->avg('nilai') ?? 0;
            $uts  = $hasil->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false)->avg('nilai') ?? 0;
            $uas  = $hasil->filter(fn($h) => stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false)->avg('nilai') ?? 0;

            return [
                'label' => $mapel->nama_mapel, 
                'kuis' => round($kuis, 1), 
                'uts' => round($uts, 1), 
                'uas' => round($uas, 1)
            ];
        })->values();

        // 5. SEBARAN NILAI & TOP SISWA
        $sebaran = ['Sangat Baik (85-100)' => 0, 'Baik (75-84)' => 0, 'Cukup (70-74)' => 0, 'Kurang (<70)' => 0];
        $siswas->each(function($s) use (&$sebaran) {
            if ($s->rata_rata_akhir === null) return;
            
            $val = $s->rata_rata_akhir;
            
            if($val >= 85) $sebaran['Sangat Baik (85-100)']++;
            elseif($val >= 75) $sebaran['Baik (75-84)']++;
            elseif($val >= 70) $sebaran['Cukup (70-74)']++;
            else $sebaran['Kurang (<70)']++;
        });

        // Top 5 Siswa
        $topSiswas = $siswas->filter(fn($s) => $s->rata_rata_akhir !== null)
                            ->sortByDesc('rata_rata_akhir')
                            ->take(5);

        return view('guru.walikelas.dashboard', compact(
            'guru', 'kelas', 'totalSiswa', 'rataRataKelas', 
            'rataKuis', 'rataUTS', 'rataUAS', 
            'siswaBermasalah', 'dataMapel', 'sebaran', 'topSiswas', 'hasRealData'
        ));
    }

    public function showSiswa(Kelas $kelas)
    {
        $guru = Auth::user();

        // Ambil Siswa + Hasil Ujiannya
        $siswasRaw = Siswa::where('kelas_id', $kelas->id)
                        ->with(['hasilUjians' => function($query) use ($kelas) {
                            $query->where('kelas_id', $kelas->id)->with('ujian');
                        }])
                        ->orderBy('nama_lengkap')
                        ->get();

        $mapels = Mapel::where('kelas_id', $kelas->id)->get();

        // Proses setiap siswa (MURNI DATA DB)
        $siswas = $siswasRaw->map(function($siswa) use ($mapels) {
            
            $totalAkhirMapel = 0; $jumlahMapel = 0;
            $totalKuisSiswa = 0; $countKuisSiswa = 0;
            $totalUtsSiswa  = 0; $countUtsSiswa  = 0;
            $totalUasSiswa  = 0; $countUasSiswa  = 0;
            
            if ($siswa->hasilUjians->isNotEmpty()) {
                foreach ($mapels as $mapel) {
                    $hasilSiswaMapel = $siswa->hasilUjians->filter(fn($h) => $h->ujian->mapel_id == $mapel->id);
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

            // Hitung rata-rata per kategori global — null jika belum ada ujian sama sekali
            $rataKuisGlobal = $countKuisSiswa > 0 ? $totalKuisSiswa / $countKuisSiswa : null;
            $rataUTSGlobal  = $countUtsSiswa  > 0 ? $totalUtsSiswa  / $countUtsSiswa  : null;
            $rataUASGlobal  = $countUasSiswa  > 0 ? $totalUasSiswa  / $countUasSiswa  : null;

            $akhirKeseluruhan = $jumlahMapel > 0 ? $totalAkhirMapel / $jumlahMapel : null;

            return (object) [
                'id'           => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn'         => $siswa->nisn,
                'foto'         => $siswa->foto,
                'rata_kuis'    => $rataKuisGlobal !== null ? number_format($rataKuisGlobal, 1) : '-',
                'rata_uts'     => $rataUTSGlobal  !== null ? number_format($rataUTSGlobal,  1) : '-',
                'rata_uas'     => $rataUASGlobal   !== null ? number_format($rataUASGlobal,  1) : '-',
                'nilai_akhir'  => $akhirKeseluruhan !== null ? number_format($akhirKeseluruhan, 1) : '-',
                'grade_raw'    => $akhirKeseluruhan ?? 0
            ];
        });

        return view('guru.walikelas.daftar_siswa', compact('guru', 'kelas', 'siswas'));
    }
    
    public function showSiswaDetail(Siswa $siswa)
    {
        $guru = Auth::user();

        // 1. Validasi Akses Wali Kelas
        $waliKelas = $guru->waliKelas;
        if (!$waliKelas || $waliKelas->kelas_id != $siswa->kelas_id) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

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

        // 2. AMBIL MATA PELAJARAN (Sesuai kelas aktif)
        $mapels = Mapel::where('kelas_id', $activeKelasId)->get();

        $maxKuis = 0; 
        $maxUts = 0;
        $maxUas = 0;

        // 3. Loop Transkrip Nilai
        $transkrip = $mapels->map(function($mapel) use ($siswa, $activeKelasId, &$maxKuis, &$maxUts, &$maxUas) {
            
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

            // B. Ambil Hasil Ujian Siswa (Hanya untuk kelas yang aktif difilter)
            $hasilSiswa = HasilUjian::where('siswa_id', $siswa->id)
                            ->where('kelas_id', $activeKelasId)
                            ->whereHas('ujian', fn($q) => $q->where('mapel_id', $mapel->id))
                            ->get();

            $listKuis = [];
            
            // C. LOOP MASTER KUIS (LOGIKA MATRIX - Ambil dari Induk atau Susulannya)
            $listKuis = [];
            foreach($masterKuis as $kuis) {
                $dataNilai = $hasilSiswa->filter(fn($h) => $h->ujian_id == $kuis->id || $h->ujian->ujian_induk_id == $kuis->id)->first();
                $listKuis[] = $dataNilai ? $dataNilai->nilai : '-';
            }

            // LOOP MASTER UTS
            $listUts = [];
            foreach($masterUts as $utsMod) {
                $dataNilai = $hasilSiswa->filter(fn($h) => $h->ujian_id == $utsMod->id || $h->ujian->ujian_induk_id == $utsMod->id)->first();
                $listUts[] = $dataNilai ? $dataNilai->nilai : '-';
            }

            // LOOP MASTER UAS
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
            $akhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : 0;

            if($akhir >= 85) $grade = 'A';
            elseif($akhir >= 75) $grade = 'B';
            elseif($akhir >= 70) $grade = 'C';
            else $grade = 'D';

            return (object) [
                'mapel'     => $mapel->nama_mapel,
                'list_kuis' => $listKuis, 
                'list_uts'  => $listUts,
                'list_uas'  => $listUas,
                'rata_kuis' => $rataKuis !== null ? number_format($rataKuis, 1) : '-',
                'uts'       => $utsVal !== null ? number_format($utsVal, 1) : '-',
                'uas'       => $uasVal !== null ? number_format($uasVal, 1) : '-',
                'akhir'     => count($komponen) > 0 ? number_format($akhir, 1) : '-',
                'predikat'  => count($komponen) > 0 ? $grade : '-'
            ];
        });

        if ($maxKuis == 0) $maxKuis = 1;
        if ($maxUts == 0) $maxUts = 1;
        if ($maxUas == 0) $maxUas = 1;

        return view('guru.walikelas.detail_siswa', compact('guru', 'siswa', 'transkrip', 'maxKuis', 'maxUts', 'maxUas', 'kelas', 'availableKelasIds', 'activeKelasId'));
    }

    public function indexRekapNilai(Kelas $kelas)
    {
        $guru = Auth::user();
        
        // 1. Validasi Akses
        $waliKelas = $guru->waliKelas;
        if (!$waliKelas || $waliKelas->kelas_id != $kelas->id) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        // 2. Bangun matriks nilai lewat service bersama.
        //    Service yang sama dipakai oleh ekspor Excel (RekapNilaiExport)
        //    supaya angka dan struktur kolom tidak pernah berbeda lagi.
        $rekap = app(\App\Services\RekapNilaiService::class)->build($kelas);

        return view('guru.walikelas.rekap_nilai', [
            'guru'       => $guru,
            'kelas'      => $rekap['kelas'],
            'mapels'     => $rekap['mapels'],
            'siswas'     => $rekap['siswas'],
            'rekapNilai' => $rekap['rekapNilai'],
        ]);
    }

    public function exportRekapNilai(Kelas $kelas)
    {
        // Validasi Akses (Sama seperti sebelumnya)
        $guru = Auth::user();
        $waliKelas = $guru->waliKelas;
        if (!$waliKelas || $waliKelas->kelas_id != $kelas->id) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        $namaFile = 'Leger_Nilai_Kelas_' . $kelas->kelas . '_' . date('Y-m-d_H-i') . '.xlsx';
        
        return Excel::download(new RekapNilaiExport($kelas->id), $namaFile);
    }
}