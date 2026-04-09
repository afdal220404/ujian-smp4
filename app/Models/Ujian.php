<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($ujian) {
            if (!$ujian->tahun_ajaran) {
                // Gunakan Asia/Jakarta agar konsisten dengan scheduler
                $date = $ujian->waktu_mulai 
                    ? \Carbon\Carbon::parse($ujian->waktu_mulai)->timezone('Asia/Jakarta') 
                    : \Carbon\Carbon::now('Asia/Jakarta');
                
                $ujian->tahun_ajaran = $date->month >= 7 
                    ? $date->year . '/' . ($date->year + 1) 
                    : ($date->year - 1) . '/' . $date->year;
            }
        });
    }

    protected $casts = [
        'peserta_susulan' => 'array',
    ];

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

    // === RELASI UJIAN SUSULAN ===
    public function ujianInduk()
    {
        return $this->belongsTo(Ujian::class, 'ujian_induk_id');
    }

    public function ujianSusulans()
    {
        return $this->hasMany(Ujian::class, 'ujian_induk_id');
    }
}