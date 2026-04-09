<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\HasilUjian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapNilaiExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $kelasId;

    public function __construct($kelasId)
    {
        $this->kelasId = $kelasId;
    }

    public function view(): View
    {
        $kelas = Kelas::findOrFail($this->kelasId);
        $mapels = Mapel::where('kelas_id', $this->kelasId)->orderBy('nama_mapel')->get();
        $siswas = Siswa::where('kelas_id', $this->kelasId)->orderBy('nama_lengkap')->get();
        
        // --- LOGIKA HITUNG NILAI (SAMA SEPERTI DI CONTROLLER) ---
        foreach ($mapels as $mapel) {
            $jumlahKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis')
                            ->count();
            $mapel->jumlah_kuis = $jumlahKuis > 0 ? $jumlahKuis : 1;
            
            $mapel->ujian_kuis_ids = \App\Models\Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis')
                            ->orderBy('created_at', 'asc')
                            ->pluck('id')
                            ->toArray();
        }

        $siswaIds = $siswas->pluck('id');
        $allHasil = HasilUjian::whereIn('siswa_id', $siswaIds)
                        ->where('kelas_id', $this->kelasId)
                        ->with('ujian')
                        ->get();
        $rekapNilai = [];

        foreach ($siswas as $siswa) {
            $hasilSiswa = $allHasil->where('siswa_id', $siswa->id);
            $totalNilaiAkhirSemuaMapel = 0;
            $jumlahMapelDiambil = 0;

            foreach ($mapels as $mapel) {
                $hasilMapel = $hasilSiswa->filter(fn($h) => $h->ujian->mapel_id == $mapel->id);

                // KUIS
                $nilaiKuisArr = [];
                if (!empty($mapel->ujian_kuis_ids)) {
                    foreach ($mapel->ujian_kuis_ids as $ujianId) {
                        $score = $hasilMapel->firstWhere('ujian_id', $ujianId);
                        $nilaiKuisArr[] = $score ? $score->nilai : '-';
                    }
                } else {
                    $nilaiKuisArr[] = '-';
                }

                $nilaiKuisValid = array_filter($nilaiKuisArr, fn($v) => is_numeric($v));
                $rataKuis = count($nilaiKuisValid) > 0 ? array_sum($nilaiKuisValid) / count($nilaiKuisValid) : 0;

                // UTS & UAS
                $uts = $hasilMapel->filter(fn($h) => stripos($h->ujian->jenis_ujian, 'UTS') !== false)->first()->nilai ?? 0;
                $uas = $hasilMapel->filter(fn($h) => stripos($h->ujian->jenis_ujian, 'UAS') !== false)->first()->nilai ?? 0;

                // AKHIR
                $akhirMapel = ($rataKuis * 0.4) + ($uts * 0.3) + ($uas * 0.3);

                if ($akhirMapel > 0) {
                    $totalNilaiAkhirSemuaMapel += $akhirMapel;
                    $jumlahMapelDiambil++;
                }

                $rekapNilai[$siswa->id]['mapel'][$mapel->id] = [
                    'detail_kuis' => $nilaiKuisArr,
                    'uts' => $uts == 0 ? '-' : $uts,
                    'uas' => $uas == 0 ? '-' : $uas,
                ];
            }

            $rataRataSiswa = $jumlahMapelDiambil > 0 ? ($totalNilaiAkhirSemuaMapel / $jumlahMapelDiambil) : 0;
            $rekapNilai[$siswa->id]['rata_akhir'] = number_format($rataRataSiswa, 1);
        }

        // Kita gunakan view khusus untuk Excel agar formatnya tabel polos
        return view('guru.walikelas.export_excel', compact('kelas', 'mapels', 'siswas', 'rekapNilai'));
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style Header Baris 1 (Judul Mapel)
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF00415A']]],
            // Style Header Baris 2 (K1, U, A)
            2 => ['font' => ['bold' => true, 'size' => 10], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFEEEEEE']]],
        ];
    }
}