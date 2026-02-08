<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    protected $fillable = ['hasil_ujian_id', 'soal_id', 'jawaban_dipilih'];
    
    public function soal()
    {
        return $this->belongsTo(Soal::class);
    }
    //
}
