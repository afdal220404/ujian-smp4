<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;

class GuruWaliKelasController extends Controller
{
    /**
     * Menampilkan dasbor spesifik untuk Wali Kelas.
     */
    public function show(Kelas $kelas)
    {
        // 1. Ambil data guru yang sedang login
        $guru = Auth::user();

        // 2. Ambil data untuk widget
        // (Kita bisa ambil jumlah siswa di kelas ini)
        $jumlahSiswa = Siswa::where('kelas_id', $kelas->id)->count();

        // 3. Ambil data untuk chart
        // !! PENTING: Database Anda (ujian.sql) belum memiliki tabel 'ujians' atau 'nilais'.
        // Untuk saat ini, saya akan mengirimkan data placeholder untuk chart.
        // Nanti, kita bisa ganti ini dengan query nilai yang sebenarnya.
        $chartLabels = ['Siswa Tuntas', 'Siswa Belum Tuntas'];
        $chartData = [($jumlahSiswa - 3), 3]; // Contoh data statis

        // 4. Kirim semua data ke view
        return view('guru.walikelas.dashboard', [
            'guru' => $guru,
            'kelas' => $kelas,
            'jumlahSiswa' => $jumlahSiswa,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }

    public function showSiswa(Kelas $kelas)
    {
        // 1. Ambil data guru yang login
        $guru = Auth::user();

        // 2. Ambil semua siswa di kelas ini
        $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();

        // 3. Kirim data ke view
        // (Kita tidak perlu lagi mengirim $mapels ke view ini)
        return view('guru.walikelas.daftar_siswa', [
            'guru' => $guru,
            'kelas' => $kelas,
            'siswas' => $siswas,
        ]);
    }
    
    public function showSiswaDetail(Siswa $siswa)
    {
        // 1. Ambil guru yang sedang login
        $guru = Auth::user();

        // 2. Verifikasi: Pastikan guru ini adalah wali kelas dari siswa ini
        $waliKelas = $guru->waliKelas;
        if (!$waliKelas || $waliKelas->kelas_id != $siswa->kelas_id) {
            // Jika bukan, kembalikan ke halaman index guru dengan error
            return redirect()->route('guru.index')->with('error', 'Akses ditolak. Anda bukan wali kelas siswa tersebut.');
        }

        // 3. Ambil semua mata pelajaran di kelas siswa ini
        $mapels = Mapel::where('kelas_id', $siswa->kelas_id)->orderBy('nama_mapel')->get();

        // 4. Kirim data ke view
        return view('guru.walikelas.detail_siswa', [
            'guru' => $guru,
            'siswa' => $siswa,
            'kelas' => $siswa->kelas, // Asumsi relasi 'kelas' ada di model Siswa
            'mapels' => $mapels,
        ]);
    }
}
