<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateSiswaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * Data contoh (Dummy) agar operator paham cara isinya
     */
    public function collection()
    {
        return collect([
            [
                'Budi Santoso',    // nama_lengkap
                '0056789012',      // nisn
                '7A',              // kelas (Harus sesuai nama di aplikasi)
                'budi.santoso',    // username
                '123456'           // password
            ]
        ]);
    }

    /**
     * Judul Kolom (Header)
     */
    public function headings(): array
    {
        return [
            'nama_lengkap',
            'nisn',
            'kelas',
            'username',
            'password',
        ];
    }

    /**
     * Mempercantik tampilan: Bold Header
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Header) dibuat Bold
            1    => ['font' => ['bold' => true]],
        ];
    }
}