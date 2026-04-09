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
        // 1. Statistik Utama
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahKelas = Kelas::count(); 
        
        $jumlahPenggunaAktif = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(15)->getTimestamp())
            ->count();

        // 2. Grafik Kiri: Distribusi Siswa (Bar Chart)
        $siswaPerKelas = Siswa::select('kelas.kelas', DB::raw('count(siswas.id) as jumlah'))
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->groupBy('kelas.kelas')
            ->orderBy('kelas.kelas')
            ->get();

        $chartSiswaLabels = $siswaPerKelas->pluck('kelas');
        $chartSiswaData = $siswaPerKelas->pluck('jumlah');

        // 3. (BARU) Grafik Kanan: Komposisi Pegawai (Doughnut Chart)
        // Kita hitung jumlah user berdasarkan role di tabel gurus
        $komposisiRole = Guru::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();

        $chartRoleLabels = $komposisiRole->pluck('role');
        $chartRoleData = $komposisiRole->pluck('total');

        $siswaTanpaNISN = Siswa::whereNull('nisn')->orWhere('nisn', '')->count();
        $guruTanpaNIP = Guru::whereNull('nip')->orWhere('nip', '')->count();
        $kelasTanpaWali = Kelas::where('id', '!=', 4)->doesntHave('waliKelas')->count();

        return view('operator.landingpage', compact(
            'jumlahSiswa',
            'jumlahGuru',
            'jumlahKelas',
            'jumlahPenggunaAktif',
            'chartSiswaLabels',
            'chartSiswaData',
            'chartRoleLabels', // Data Baru
            'chartRoleData' ,   // Data Baru
            'siswaTanpaNISN',
            'guruTanpaNIP',
            'kelasTanpaWali'
        ));
    }
}