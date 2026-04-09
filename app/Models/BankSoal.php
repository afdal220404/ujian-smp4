<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'bank_soal_items';

    protected $fillable = [
        'mapel_id', 'tipe', 'pertanyaan', 'gambar',
        'opsi_a', 'gambar_a', 'opsi_b', 'gambar_b',
        'opsi_c', 'gambar_c', 'opsi_d', 'gambar_d',
        'kunci_jawaban', 'data_soal',
    ];

    protected $casts = [
        'data_soal' => 'array',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'bank_soal_id');
    }
}
