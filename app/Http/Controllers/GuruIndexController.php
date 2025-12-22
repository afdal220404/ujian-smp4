<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaliKelas; // Pastikan model ini ada
use App\Models\Mapel; // Pastikan model ini ada
use App\Models\Guru; // Kita butuh ini

class GuruIndexController extends Controller
{
   /**
     * Menampilkan halaman dasbor "Pemilih Konteks" untuk guru.
     */
    public function index()
    {
        // 1. Langsung dapatkan model GURU yang sedang login
        // Auth::user() sekarang adalah instance dari model Guru
        $guru = Auth::user();

        // 2. Jika karena alasan apapun yang login bukan guru
        // (Misal, Anda salah mengkonfigurasi AuthController),
        // kita lakukan pengecekan sederhana.
        // Kita asumsikan jika modelnya adalah Guru, maka profilnya ada.
        if (! $guru instanceof Guru) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['username' => 'Gagal mengidentifikasi profil guru.']);
        }

        // 3. Ambil data Wali Kelas
        // Kita gunakan relasi 'waliKelas' yang akan kita buat di Model Guru
        $waliKelasTugas = $guru->waliKelas()->with('kelas')->first();

        // 4. Ambil data Mata Pelajaran yang diajar
        // Kita gunakan relasi 'mapels' yang akan kita buat di Model Guru
        $mapelTugas = $guru->mapels()->with('kelas')->orderBy('kelas_id')->get();

        // 5. Tampilkan view dasbor dengan data yang sudah diambil
        return view('guru.index', compact('guru', 'waliKelasTugas', 'mapelTugas'));
    }

    // Nanti Anda bisa menambahkan fungsi lain di sini, seperti:
    // public function showWaliKelasDashboard($kelasId) { ... }
    // public function showMapelDashboard($mapelId) { ... }
}