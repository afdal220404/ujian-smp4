<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaliKelas; // Pastikan model ini ada
use App\Models\Mapel; // Pastikan model ini ada
use App\Models\Guru; // Kita butuh ini
use App\Models\Ujian;
use Carbon\Carbon;

class GuruIndexController extends Controller
{
   /**
     * Menampilkan halaman dasbor "Pemilih Konteks" untuk guru.
     */
    public function index()
    {
        // 1. Langsung dapatkan model GURU yang sedang login
        $guru = Auth::user();

        if (! $guru instanceof Guru) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['username' => 'Gagal mengidentifikasi profil guru.']);
        }

        // 2. Ambil data Wali Kelas
        $waliKelasTugas = $guru->waliKelas()->with('kelas')->first();

        // 3. Ambil data Mata Pelajaran yang diajar
        $mapelTugas = $guru->mapels()->with('kelas')->orderBy('kelas_id')->get();

        // 4. Ambil data Ujian di mana guru ditugaskan sebagai Pengawas Ruangan
        $now = Carbon::now('Asia/Jakarta');
        $tugasPengawas = Ujian::where('pengawas_id', $guru->id)
            ->where('waktu_selesai', '>=', $now->copy()->subHours(2))
            ->with(['mapel.kelas'])
            ->orderBy('waktu_mulai', 'asc')
            ->get()
            ->map(function ($ujian) use ($now) {
                $start = Carbon::parse($ujian->waktu_mulai)->timezone('Asia/Jakarta');
                $end = Carbon::parse($ujian->waktu_selesai)->timezone('Asia/Jakarta');
                $accessibleTime = $start->copy()->subMinutes(30);

                $canEnter = ($now >= $accessibleTime && $now <= $end);
                $isUpcoming = ($now < $accessibleTime);
                $isOngoing = ($now >= $start && $now <= $end);
                $isFinished = ($now > $end);

                return (object) [
                    'ujian' => $ujian,
                    'start' => $start,
                    'end' => $end,
                    'accessible_time' => $accessibleTime,
                    'can_enter' => $canEnter,
                    'is_upcoming' => $isUpcoming,
                    'is_ongoing' => $isOngoing,
                    'is_finished' => $isFinished,
                ];
            });

        // 5. Tampilkan view dasbor dengan data yang sudah diambil
        return view('guru.index', compact('guru', 'waliKelasTugas', 'mapelTugas', 'tugasPengawas'));
    }

    // Nanti Anda bisa menambahkan fungsi lain di sini, seperti:
    // public function showWaliKelasDashboard($kelasId) { ... }
    // public function showMapelDashboard($mapelId) { ... }
}