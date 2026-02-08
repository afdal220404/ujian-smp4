<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas() // Pastikan ujian punya relasi balik ke kelas
    {
        return $this->belongsTo(Kelas::class);
    }

    // === TAMBAHKAN KODE INI JUGA ===
    public function hasilUjians()
    {
        // Satu Ujian memiliki Banyak Hasil (Nilai Siswa)
        return $this->hasMany(HasilUjian::class);
    }
    
    // Opsional: Relasi ke Soal jika dibutuhkan nanti
    public function soals()
    {
        return $this->hasMany(Soal::class);
    }
}