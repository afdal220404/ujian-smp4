<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\MapelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login/process', [AuthController::class, 'loginProcess'])->name('login.process');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/operator/landingpage', function () {
        return view('operator.landingpage');
    })->name('operator.landingpage');

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
});


Route::get('/tes-sesi', [App\Http\Controllers\AuthController::class, 'tesSesi']);

Route::middleware('auth')->group(function () {
    Route::get('/mapel', [MapelController::class, 'index'])->name('mapel');
    Route::post('/mapel/store', [MapelController::class, 'store'])->name('mapel.store');
    Route::put('/mapel/{id}', [MapelController::class, 'update'])->name('mapel.update');
    Route::get('/mapel/kelas/{kelas}', [MapelController::class, 'getByKelas'])->name('mapel.getByKelas');
    Route::delete('/mapel/{id}', [MapelController::class, 'destroy'])->name('mapel.destroy');
});



Route::get('/landingpage2', function () {
    return view('guru_mapel/landingpage');
})->name('landingpage2');

Route::get('/landingpage3', function () {
    return view('operator/landingpage');
})->name('landingpage3');

Route::get('/landingpage4', function () {
    return view('wali_kelas/landingpage');
})->name('landingpage4');

Route::get('/landingpage', function () {
    return view('kepsek/landingpage');
})->name('landingpage');

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


Route::get('/wali_kelas', function () {
    return view('operator/wali_kelas');
})->name('wali_kelas');

Route::get('/detail', function () {
    return view('kepsek/detail_nilai');
})->name('detail');
