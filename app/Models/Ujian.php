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
        'allow_web_access' => 'boolean',
        'siswa_izin_web' => 'array',
    ];

    /**
     * Cek apakah siswa tertentu diizinkan mengakses ujian dari web browser
     */
    public function isSiswaAllowedWeb($siswaId): bool
    {
        // Jika ujian secara global diizinkan web, semua siswa boleh
        if ($this->allow_web_access) {
            return true;
        }

        // Cek daftar izin perorangan
        $allowedList = $this->siswa_izin_web ?? [];
        return in_array($siswaId, $allowedList) || in_array((string)$siswaId, $allowedList) || in_array((int)$siswaId, $allowedList);
    }

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

    public function pengawas()
    {
        return $this->belongsTo(Guru::class, 'pengawas_id');
    }
}