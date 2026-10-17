<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Services\RekapNilaiService;
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

        // Perhitungan nilai memakai service yang sama dengan tampilan
        // rekap nilai di layar, sehingga:
        //  - kolom detail_kuis / detail_uts / detail_uas selalu ada,
        //  - jumlah kolom UTS/UAS sesuai jumlah_uts / jumlah_uas,
        //  - nilai RATA AKHIR sama dengan yang tampil di aplikasi.
        $rekap = app(RekapNilaiService::class)->build($kelas);

        return view('guru.walikelas.export_excel', [
            'kelas'      => $rekap['kelas'],
            'mapels'     => $rekap['mapels'],
            'siswas'     => $rekap['siswas'],
            'rekapNilai' => $rekap['rekapNilai'],
        ]);
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
