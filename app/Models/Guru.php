<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Ubah ini
use Illuminate\Notifications\Notifiable;

class Guru extends Authenticatable 
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama_lengkap',
        'nip',
        'foto',
        'username', // Tambahkan
        'password', // Tambahkan
        'role',     // Tambahkan
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Relasi ke tabel 'wali_kelas'.
     * Seorang guru bisa menjadi wali kelas (hasOne).
     */
    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class, 'guru_id');
    }

    /**
     * Relasi ke tabel 'mapels'.
     * Seorang guru bisa mengajar banyak mata pelajaran (hasMany).
     */
    public function mapels()
    {
        return $this->hasMany(Mapel::class, 'guru_id');
    }
}