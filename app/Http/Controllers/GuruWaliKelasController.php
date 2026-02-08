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

        // 1. AMBIL DATA REAL (DATABASE ONLY)
        $allHasil = HasilUjian::whereIn('siswa_id', $siswaIds)->with('ujian')->get();
        $hasRealData = $allHasil->isNotEmpty(); // Flag untuk View jika dibutuhkan

        // 2. HITUNG RATA-RATA KELAS (DEFAULT 0 JIKA KOSONG)
        // Menggunakan null coalescing operator (?? 0) agar tidak error saat data kosong
        $rataKuis = $allHasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'Kuis') !== false || stripos($h->ujian->jenis_ujian ?? '', 'Kuis') !== false)->avg('nilai') ?? 0;
        $rataUTS  = $allHasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UTS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false)->avg('nilai') ?? 0;
        $rataUAS  = $allHasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UAS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false)->avg('nilai') ?? 0;
        
        $rataRataKelas = $allHasil->avg('nilai') ?? 0;

        // 3. CARI SISWA PERLU BIMBINGAN (Rata-rata < 70)
        $siswaBermasalah = Siswa::where('kelas_id', $kelas->id)
            ->with('hasilUjians')
            ->get()
            ->filter(function($s) {
                if ($s->hasilUjians->isEmpty()) return false; // Lewati jika belum ada nilai
                $avg = $s->hasilUjians->avg('nilai');
                return $avg !== null && $avg < 70; 
            })
            ->sortBy('nama_lengkap');

        // 4. CHART PERFORMANSA PER MAPEL
        $mapels = Mapel::where('kelas_id', $kelas->id)->get();

        $dataMapel = $mapels->map(function($mapel) use ($siswaIds) {
            // Ambil nilai khusus mapel ini
            $hasil = HasilUjian::whereIn('siswa_id', $siswaIds)
                        ->whereHas('ujian', fn($q) => $q->where('mapel_id', $mapel->id))
                        ->with('ujian')->get();
            
            // Hitung rata-rata per kategori ujian
            $kuis = $hasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'Kuis') !== false)->avg('nilai') ?? 0;
            $uts  = $hasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UTS') !== false)->avg('nilai') ?? 0;
            $uas  = $hasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UAS') !== false)->avg('nilai') ?? 0;

            return [
                'label' => $mapel->nama_mapel, 
                'kuis' => round($kuis, 1), 
                'uts' => round($uts, 1), 
                'uas' => round($uas, 1)
            ];
        })->values();

        // 5. SEBARAN NILAI & TOP SISWA
        $sebaran = ['Sangat Baik (>90)' => 0, 'Baik (80-90)' => 0, 'Cukup (70-79)' => 0, 'Kurang (<70)' => 0];
        $siswas = Siswa::where('kelas_id', $kelas->id)->with('hasilUjians')->get();
        
        $siswas->each(function($s) use (&$sebaran) {
            if ($s->hasilUjians->isEmpty()) return; // Skip siswa tanpa nilai

            $val = $s->hasilUjians->avg('nilai') ?? 0;
            
            if($val >= 90) $sebaran['Sangat Baik (>90)']++;
            elseif($val >= 80) $sebaran['Baik (80-90)']++;
            elseif($val >= 70) $sebaran['Cukup (70-79)']++;
            else $sebaran['Kurang (<70)']++;
        });

        // Top 5 Siswa
        $topSiswas = $siswas->filter(fn($s) => $s->hasilUjians->isNotEmpty())
                            ->sortByDesc(fn($s) => $s->hasilUjians->avg('nilai') ?? 0)
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
                        ->with(['hasilUjians.ujian'])
                        ->orderBy('nama_lengkap')
                        ->get();

        // Proses setiap siswa (MURNI DATA DB)
        $siswas = $siswasRaw->map(function($siswa) {
            
            $hasil = $siswa->hasilUjians;
            
            // Hitung rata-rata per kategori (default 0)
            $rataKuis = $hasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'Kuis') !== false || stripos($h->ujian->jenis_ujian ?? '', 'Kuis') !== false)->avg('nilai') ?? 0;
            $rataUTS  = $hasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UTS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false)->avg('nilai') ?? 0;
            $rataUAS  = $hasil->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UAS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false)->avg('nilai') ?? 0;

            // Rumus Nilai Akhir
            $akhir = ($rataKuis * 0.4) + ($rataUTS * 0.3) + ($rataUAS * 0.3);

            return (object) [
                'id'           => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn'         => $siswa->nisn,
                'foto'         => $siswa->foto,
                'rata_kuis'    => number_format($rataKuis, 1),
                'rata_uts'     => number_format($rataUTS, 1),
                'rata_uas'     => number_format($rataUAS, 1),
                'nilai_akhir'  => number_format($akhir, 1),
                'grade_raw'    => $akhir 
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

        $kelas = $siswa->kelas; 

        // 2. AMBIL MATA PELAJARAN
        $mapels = Mapel::where('kelas_id', $siswa->kelas_id)->get();

        $maxKuis = 0; 

        // 3. Loop Transkrip Nilai
        $transkrip = $mapels->map(function($mapel) use ($siswa, &$maxKuis) {
            
            // A. AMBIL MASTER KUIS (Agar kolom konsisten dengan Guru Mapel)
            // Kita cari semua ujian bertipe Kuis di mapel ini
            $masterKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis') // Sesuaikan dengan filter di GuruMapel
                            ->orderBy('created_at', 'asc') // Urutan harus sama dengan GuruMapel
                            ->get();

            // B. Ambil Hasil Ujian Siswa (Untuk pencocokan)
            $hasilSiswa = HasilUjian::where('siswa_id', $siswa->id)
                            ->whereHas('ujian', fn($q) => $q->where('mapel_id', $mapel->id))
                            ->get();

            $listKuis = [];
            
            // C. LOOP MASTER KUIS (LOGIKA MATRIX)
            // Cek satu per satu: Apakah siswa punya nilai di Kuis Master ini?
            foreach($masterKuis as $kuis) {
                // Cari nilai siswa yang ujian_id nya cocok dengan kuis master
                $nilai = $hasilSiswa->firstWhere('ujian_id', $kuis->id);
                
                // Jika ada simpan nilai, jika tidak simpan '-'
                $listKuis[] = $nilai ? $nilai->nilai : '-';
            }

            // Ambil UTS & UAS (Ambil nilai pertama yang ketemu)
            $uts = $hasilSiswa->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UTS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UTS') !== false)->first()->nilai ?? 0;
            $uas = $hasilSiswa->filter(fn($h) => stripos($h->ujian->nama_ujian ?? '', 'UAS') !== false || stripos($h->ujian->jenis_ujian ?? '', 'UAS') !== false)->first()->nilai ?? 0;

            // Update Max Column untuk lebar tabel di View
            if (count($listKuis) > $maxKuis) {
                $maxKuis = count($listKuis);
            }

            // Hitung Rata-rata (Hanya dari nilai yang valid/angka)
            $nilaiValid = array_filter($listKuis, fn($v) => is_numeric($v));
            $rataKuis = count($nilaiValid) > 0 ? array_sum($nilaiValid) / count($nilaiValid) : 0;
            
            $akhir = ($rataKuis * 0.4) + ($uts * 0.3) + ($uas * 0.3);

            // Tentukan Grade
            if($akhir >= 90) $grade = 'A';
            elseif($akhir >= 80) $grade = 'B';
            elseif($akhir >= 70) $grade = 'C';
            else $grade = 'D';

            return (object) [
                'mapel'     => $mapel->nama_mapel,
                'list_kuis' => $listKuis, 
                'rata_kuis' => number_format($rataKuis, 1),
                'uts'       => $uts == 0 ? '-' : $uts,
                'uas'       => $uas == 0 ? '-' : $uas,
                'akhir'     => number_format($akhir, 1),
                'predikat'  => $grade
            ];
        });

        if ($maxKuis == 0) $maxKuis = 1;

        return view('guru.walikelas.detail_siswa', compact('guru', 'siswa', 'transkrip', 'maxKuis', 'kelas'));
    }

    public function indexRekapNilai(Kelas $kelas)
    {
        $guru = Auth::user();
        
        // 1. Validasi Akses
        $waliKelas = $guru->waliKelas;
        if (!$waliKelas || $waliKelas->kelas_id != $kelas->id) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        // 2. Ambil Data Master & Hitung Jumlah Kuis Per Mapel
        $mapels = Mapel::where('kelas_id', $kelas->id)->orderBy('nama_mapel')->get();
        
        // Siapkan struktur kolom tabel (Dynamic Headers)
        foreach ($mapels as $mapel) {
            $jumlahKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis')
                            ->count();
            // Minimal 1 kolom kuis agar tabel tidak rusak
            $mapel->jumlah_kuis = $jumlahKuis > 0 ? $jumlahKuis : 1;
            
            // Ambil ID ujian kuis untuk mapping nilai nanti (sorted)
            $mapel->ujian_kuis_ids = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis')
                            ->orderBy('created_at', 'asc')
                            ->pluck('id')
                            ->toArray();
        }

        $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        
        // 3. Ambil Semua Nilai (Eager Loading)
        $siswaIds = $siswas->pluck('id');
        $allHasil = HasilUjian::whereIn('siswa_id', $siswaIds)
                        ->with('ujian') 
                        ->get();

        // 4. Susun Matriks Nilai
        $rekapNilai = [];

        foreach ($siswas as $siswa) {
            $hasilSiswa = $allHasil->where('siswa_id', $siswa->id);
            
            $totalNilaiAkhirSemuaMapel = 0;
            $jumlahMapelDiambil = 0;

            foreach ($mapels as $mapel) {
                // Filter nilai mapel ini
                $hasilMapel = $hasilSiswa->filter(fn($h) => $h->ujian->mapel_id == $mapel->id);

                // A. KUMPULKAN NILAI KUIS (Sesuai Urutan Master Ujian)
                $nilaiKuisArr = [];
                if (!empty($mapel->ujian_kuis_ids)) {
                    foreach ($mapel->ujian_kuis_ids as $ujianId) {
                        $score = $hasilMapel->firstWhere('ujian_id', $ujianId);
                        $nilaiKuisArr[] = $score ? $score->nilai : '-';
                    }
                } else {
                    $nilaiKuisArr[] = '-'; // Placeholder jika belum ada kuis
                }

                // Hitung Rata Kuis (Untuk Rumus Akhir)
                $nilaiKuisValid = array_filter($nilaiKuisArr, fn($v) => is_numeric($v));
                $rataKuis = count($nilaiKuisValid) > 0 ? array_sum($nilaiKuisValid) / count($nilaiKuisValid) : 0;

                // B. UTS & UAS
                $uts = $hasilMapel->filter(fn($h) => stripos($h->ujian->jenis_ujian, 'UTS') !== false)->first()->nilai ?? 0;
                $uas = $hasilMapel->filter(fn($h) => stripos($h->ujian->jenis_ujian, 'UAS') !== false)->first()->nilai ?? 0;

                // C. Hitung Nilai Akhir Mapel (Hanya untuk perhitungan rata-rata siswa)
                $akhirMapel = ($rataKuis * 0.4) + ($uts * 0.3) + ($uas * 0.3);

                // Akumulasi
                if ($akhirMapel > 0) {
                    $totalNilaiAkhirSemuaMapel += $akhirMapel;
                    $jumlahMapelDiambil++;
                }

                // Simpan Data Tampilan
                $rekapNilai[$siswa->id]['mapel'][$mapel->id] = [
                    'detail_kuis' => $nilaiKuisArr, // Array [80, 90, ...]
                    'uts'  => $uts == 0 ? '-' : number_format($uts, 0),
                    'uas'  => $uas == 0 ? '-' : number_format($uas, 0),
                ];
            }

            // 5. Rata-Rata Akhir Siswa (Total Mapel / Jumlah Mapel)
            $rataRataSiswa = $jumlahMapelDiambil > 0 ? ($totalNilaiAkhirSemuaMapel / $jumlahMapelDiambil) : 0;
            
            $rekapNilai[$siswa->id]['rata_akhir'] = number_format($rataRataSiswa, 1);
            $rekapNilai[$siswa->id]['grade_akhir'] = $rataRataSiswa;
        }

        return view('guru.walikelas.rekap_nilai', compact('guru', 'kelas', 'mapels', 'siswas', 'rekapNilai'));
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