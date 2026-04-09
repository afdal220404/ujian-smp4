<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    /**
     * Soal sekarang adalah pivot antara Ujian dan BankSoal.
     * Data soal sepenuhnya ada di bank_soal_items.
     */
    protected $fillable = [
        'ujian_id',
        'bank_soal_id',
    ];

    // ── Relasi ──────────────────────────────────────────────
    public function ujian()    { return $this->belongsTo(Ujian::class); }
    public function bankSoal() { return $this->belongsTo(BankSoal::class, 'bank_soal_id'); }

    // ── Accessor: forward property ke BankSoal ───────────────
    // Agar semua kode yang memanggil $soal->pertanyaan, $soal->tipe, dll
    // tetap berjalan tanpa perlu mengubah view.
    public function __get($key)
    {
        // Daftar properti yang harus diambil dari bankSoal
        $bankSoalProps = [
            'tipe', 'pertanyaan', 'gambar',
            'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d',
            'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d',
            'kunci_jawaban', 'data_soal',
            'mapel_id',
        ];

        if (in_array($key, $bankSoalProps)) {
            // Eager load bankSoal jika belum di-load
            if (!$this->relationLoaded('bankSoal')) {
                $this->load('bankSoal');
            }
            return $this->bankSoal?->{$key};
        }

        return parent::__get($key);
    }

    // Helper agar data_soal otomatis di-cast meski lewat accessor
    public function getDataSoalAttribute()
    {
        if (!$this->relationLoaded('bankSoal')) {
            $this->load('bankSoal');
        }
        $raw = $this->bankSoal?->data_soal;
        if (is_string($raw)) {
            return json_decode($raw, true);
        }
        return $raw;
    }
}