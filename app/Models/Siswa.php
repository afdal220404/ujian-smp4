<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Ubah ini
use Illuminate\Notifications\Notifiable;

class Siswa extends Authenticatable // Ubah ini
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama_lengkap',
        'nisn',
        'kelas_id',
        'username', 
        'password', 
    ];

    protected $hidden = [
        'password',
    ];

    // Tambahkan relasi ke kelas jika belum ada
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}