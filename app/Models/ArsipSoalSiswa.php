<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArsipSoalSiswa extends Model
{
    use HasFactory;
    protected $table = 'arsip_soal_siswas';
    protected $fillable = [
        'guru_id',
        'mapel_id',
        'nama',
        'file_path',
        'visibilitas', // Public, Private, Draft
    ];

    
    protected $casts = [
        'visibilitas' => 'string',
    ];

    /**
     * Relasi ke Guru (Pemilik file).
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Relasi ke Mata Pelajaran (Mapel).
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }
    
    // Anda bisa menambahkan helper untuk mendapatkan URL file
    public function getFileUrlAttribute()
    {
        // Menggunakan asset() untuk URL yang sudah terbukti bekerja dengan storage:link
        return asset('storage/' . $this->file_path);
    }
}