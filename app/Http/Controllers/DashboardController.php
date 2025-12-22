<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil data untuk widget statistik
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        
        // Menghitung pengguna yang aktif dalam 15 menit terakhir
        $jumlahPenggunaAktif = DB::table('sessions')
                                ->where('last_activity', '>', now()->subMinutes(15)->getTimestamp())
                                ->count();

        // 2. Ambil data untuk bar chart perbandingan siswa per kelas
        $siswaPerKelas = Siswa::select('kelas.kelas', DB::raw('count(siswas.id) as jumlah'))
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->groupBy('kelas.kelas')
            ->orderBy('kelas.kelas')
            ->get();

        // Siapkan data untuk dikirim ke view
        $chartLabels = $siswaPerKelas->pluck('kelas');
        $chartData = $siswaPerKelas->pluck('jumlah');

        // 3. Kirim semua data ke view
        return view('operator.landingpage', compact(
            'jumlahSiswa',
            'jumlahGuru',
            'jumlahPenggunaAktif',
            'chartLabels',
            'chartData'
        ));
    }
}