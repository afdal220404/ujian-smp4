<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'kelas_id',
        'waktu_mulai',
        'waktu_selesai',
        'nilai',
        'jumlah_benar',
        'jumlah_salah',
        'status_penyelesaian',
        'keterangan_pelanggaran',
        'is_locked_reentry',
        'is_paused',
        'waktu_jeda',
        'last_heartbeat'
    ];

    protected $casts = [
        'is_locked_reentry' => 'boolean',
        'is_paused' => 'boolean',
        'waktu_jeda' => 'datetime',
        'last_heartbeat' => 'datetime',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

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

