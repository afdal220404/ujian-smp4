<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

class Kelas extends Authenticatable 
{
    use Notifiable;
    protected $fillable = [
        'id', 
        'kelas', 
    ];
}