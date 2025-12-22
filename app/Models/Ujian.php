<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;
    protected $fillable = [
        'mapel_id',
        'guru_id',
        'nama_ujian',
        'jenis_ujian',
        'tanggal_ujian',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit'
    ];

    public function getBadgeClassAttribute()
    {
        switch ($this->jenis_ujian) {
            case 'Kuis':
                return 'badge-kuis';
            case 'UTS':
                return 'badge-uts';
            case 'UAS':
                return 'badge-uas';
            default:
                return 'badge-default';
        }
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
    public function soals()
    {
        return $this->hasMany(Soal::class);
    }
}
