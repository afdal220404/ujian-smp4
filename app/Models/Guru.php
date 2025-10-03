<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
class Guru extends Model
{
    use Notifiable;
    protected $fillable = [
        'akun_id',
        'nama_lengkap',
        'nip',
        'foto',
    ];

    // Relasi balik ke Akun
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'akun_id', 'akun_id');
    }
}