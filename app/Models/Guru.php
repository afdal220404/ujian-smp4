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
}