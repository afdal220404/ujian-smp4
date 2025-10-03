<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Akun extends Authenticatable
{
    use HasFactory;

    protected $table = 'akuns';
    protected $primaryKey = 'akun_id';

    protected $fillable = ['username', 'password', 'role', 'last_login'];

    protected $hidden = ['password'];

    // Relasi: Satu Akun hanya memiliki satu profil Guru
    public function guru()
    {
        return $this->hasOne(Guru::class, 'akun_id', 'akun_id');
    }

    // Relasi: Satu Akun hanya memiliki satu profil Siswa
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'akun_id', 'akun_id');
    }
}
