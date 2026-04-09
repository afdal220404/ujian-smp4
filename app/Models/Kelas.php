<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class);
    }

    public function mapels()
    {
        return $this->hasMany(Mapel::class);
    }

    // === PERBAIKAN DI SINI ===
    public function ujians()
    {
        // SALAH (Penyebab Error): Mencari kolom 'kelas_id' di tabel 'ujians'
        // return $this->hasMany(Ujian::class);

        // BENAR: Mengambil Ujian MELALUI tabel Mapel
        // Syarat: Tabel 'mapels' punya 'kelas_id', tabel 'ujians' punya 'mapel_id'
        return $this->hasManyThrough(Ujian::class, Mapel::class);
    }
}