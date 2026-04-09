<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $fillable = ['ujian_id', 'siswa_id', 'kelas_id', 'waktu_mulai', 'waktu_selesai', 'nilai'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

