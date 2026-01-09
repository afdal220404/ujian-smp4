<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\WaliKelasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruIndexController;
use App\Http\Controllers\GuruWaliKelasController;
use App\Http\Controllers\GuruMapelController;
use App\Http\Controllers\KepsekController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login/process', [AuthController::class, 'loginProcess'])->name('login.process');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/operator/landingpage', [DashboardController::class, 'index'])->name('operator.landingpage');
    Route::get('/operator/daftar_siswa', [SiswaController::class, 'index'])->name('operator.daftar_siswa');
    Route::get('/tambah_siswa', [SiswaController::class, 'create'])->name('tambah_siswa');
    Route::post('/tambah_siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::delete('/operator/siswa/{id}', [SiswaController::class, 'destroy'])->name('hapus_siswa');
    Route::get('/operator/filter-siswa', [SiswaController::class, 'filterByKelas'])->name('operator.filter_siswa');
    Route::get('/operator/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::put('/operator/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::post('/guru/store', [GuruController::class, 'store'])->name('guru.store');

    // Halaman daftar guru
    Route::get('/daftar-guru', [GuruController::class, 'index'])->name('daftar_guru2');
    // Form tambah guru
    Route::get('/tambah-guru', [GuruController::class, 'create'])->name('guru.create');
    Route::post('/guru/store', [GuruController::class, 'store'])->name('guru.store');

    // Form edit guru
    Route::get('/guru/{id}/edit', [GuruController::class, 'edit'])->name('guru.edit');
    Route::put('/guru/{id}', [GuruController::class, 'update'])->name('guru.update');

    // Hapus guru
    Route::delete('/guru/{id}', [GuruController::class, 'destroy'])->name('guru.destroy');
    Route::get('/guru/filter', [App\Http\Controllers\GuruController::class, 'filter'])->name('guru.filter');

    Route::post('/wali-kelas', [WaliKelasController::class, 'store'])->name('walikelas.store');
    Route::get('/wali-kelas', [WaliKelasController::class, 'index'])->name('walikelas.index');
});


Route::get('/tes-sesi', [App\Http\Controllers\AuthController::class, 'tesSesi']);

Route::middleware('auth')->group(function () {
    Route::get('/mapel', [MapelController::class, 'index'])->name('mapel');
    Route::post('/mapel/store', [MapelController::class, 'store'])->name('mapel.store');
    Route::put('/mapel/{id}', [MapelController::class, 'update'])->name('mapel.update');
    Route::get('/mapel/kelas/{kelas}', [MapelController::class, 'getByKelas'])->name('mapel.getByKelas');
    Route::delete('/mapel/{id}', [MapelController::class, 'destroy'])->name('mapel.destroy');
});

Route::middleware(['auth'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/index', [GuruIndexController::class, 'index'])->name('index');
    Route::get('/walikelas/{kelas}', [GuruWaliKelasController::class, 'show'])->name('walikelas.dashboard');
    Route::get('/walikelas/{kelas}/siswa', [GuruWaliKelasController::class, 'showSiswa'])->name('walikelas.siswa');
    Route::get('/walikelas/siswa/{siswa}', [GuruWaliKelasController::class, 'showSiswaDetail'])->name('walikelas.siswa.detail');
    Route::get('/mapel/{mapel}', [GuruMapelController::class, 'show'])->name('mapel.dashboard');
    Route::get('/mapel/{mapel}/siswa', [GuruMapelController::class, 'showSiswa'])->name('mapel.siswa');

    // Halaman 1: Tampilkan Form Buat Ujian (GET)
    Route::get('/mapel/{mapel}/ujian/create', [GuruMapelController::class, 'createUjian'])->name('mapel.ujian.create');

    // Halaman 1: Handler untuk "Simpan Ujian" (Final) & "Kelola Soal" (POST)
    Route::post('/mapel/{mapel}/ujian/store', [GuruMapelController::class, 'storeUjian'])->name('mapel.ujian.store');

    // Halaman 1: Handler untuk tombol "Update Ujian" & "Kelola Soal"
    Route::post('/mapel/{mapel}/ujian/{ujian}/update', [GuruMapelController::class, 'updateUjian'])->name('mapel.ujian.update');
    
    // ▼▼▼ TAMBAHKAN RUTE BARU INI ▼▼▼
    // Halaman 1 (Review): Untuk KEMBALI ke form (MEMBACA SESI)
    Route::get('/mapel/{mapel}/ujian/review', [GuruMapelController::class, 'showCreateUjianPage'])->name('mapel.ujian.review');
    // ▲▲▲ AKHIR RUTE BARU ▲▲▲

    // Halaman 2: Tampilkan Form Tambah Soal (GET)
    Route::get('/ujian/soal/create', [GuruMapelController::class, 'createSoal'])->name('mapel.soal.create');

    // Halaman 2: Handler "Simpan Soal & Kembali" (Menyimpan ke Sesi) (POST)
    Route::post('/ujian/soal/store-temp', [GuruMapelController::class, 'storeSoalToSession'])->name('mapel.soal.store-temp');

    // Halaman 2: Tampilkan Form Tambah/Edit Soal
    Route::get('/ujian/{ujian}/soal', [GuruMapelController::class, 'showSoalForm'])->name('mapel.soal.show');

    Route::get('/ujian/{ujian}/edit', [GuruMapelController::class, 'editUjian'])->name('mapel.ujian.edit');

    Route::delete('/ujian/{ujian}/destroy', [GuruMapelController::class, 'destroyUjian'])->name('mapel.ujian.destroy');
    
    // Akses halaman Detail Ujian
    Route::get('/ujian/{ujian}/detail', [GuruMapelController::class, 'showUjianDetail'])->name('mapel.ujian.detail');

    // Akses halaman Detail Siswa untuk Ujian Tertentu
    Route::get('/ujian/{ujian}/siswa/{siswa}/detail', [GuruMapelController::class, 'showSiswaUjianDetail'])->name('mapel.ujian.siswa.detail');

    // GET: Menampilkan daftar bank soal
    Route::get('/mapel/{mapel}/bank-soal', [GuruMapelController::class, 'indexBankSoal'])->name('mapel.bank_soal.index');
    // POST: Menangani penambahan file baru dan update visibilitas
    Route::post('/mapel/{mapel}/bank-soal', [GuruMapelController::class, 'handleBankSoal'])->name('mapel.bank_soal.store');
    // DELETE: Menghapus file soal
    Route::delete('/bank-soal/{bankSoal}', [GuruMapelController::class, 'destroyBankSoal'])->name('mapel.bank_soal.destroy');
});
    
// ---------------------------------------------------------------------
    // 3. ROLE: KEPALA SEKOLAH (KEPSEK) - BARU
    // ---------------------------------------------------------------------
    Route::prefix('kepsek')->name('kepsek.')->group(function () {
        Route::get('/dashboard', [KepsekController::class, 'index'])->name('index');
        
        // Fitur Monitoring (Placeholder untuk pengembangan selanjutnya)
        Route::get('/monitor-guru', [KepsekController::class, 'monitorGuru'])->name('guru');
        Route::get('/monitor-siswa', [KepsekController::class, 'monitorSiswa'])->name('siswa');
        Route::get('/laporan-nilai', [KepsekController::class, 'laporanNilai'])->name('nilai');
    });


Route::get('/landingpage2', [GuruIndexController::class, 'index'])->name('landingpage2');

Route::get('/landingpage3', function () {
    return view('operator/landingpage');
})->name('landingpage3');

Route::get('/landingpage4', function () {
    return view('wali_kelas/landingpage');
})->name('landingpage4');

Route::get('/daftar_nilai', function () {
    return view('kepsek/daftar_nilai');
})->name('daftar_nilai');

Route::get('/daftar_siswa', function () {
    return view('kepsek/daftar_siswa');
})->name('daftar_siswa');

Route::get('/daftar_guru', function () {
    return view('kepsek/daftar_guru');
})->name('daftar_guru');

Route::get('/daftar_siswa3', function () {
    return view('wali_kelas/daftar_siswa');
})->name('daftar_siswa3');


Route::get('/tambah_guru', function () {
    return view('operator/tambah_guru');
})->name('tambah_guru');

Route::get('/detail', function () {
    return view('kepsek/detail_nilai');
})->name('detail');
