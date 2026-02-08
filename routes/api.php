<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaController; // Asumsi Anda punya controller ini
use App\Http\Controllers\UjianController; // Anda mungkin perlu membuat ini

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Login (Public Route)
// Kita buat method baru 'loginApi' di AuthController nanti
Route::post('/login', [AuthController::class, 'loginApi']);

// Group Route yang butuh Login (Optional: pakai middleware 'auth:sanctum' jika sudah setup)
// Untuk sementara kita buka dulu (tanpa middleware) agar mudah dites
Route::group([], function () {

    // 2. Daftar Ujian
    Route::get('/ujian', [UjianController::class, 'indexApi']);
    
    // 3. Soal Ujian
    Route::get('/ujian/soal', [UjianController::class, 'getSoalApi']);
    
    // 4. Mulai Ujian
    Route::post('/ujian/start', [UjianController::class, 'startUjianApi']);
    
    // 5. Submit Jawaban
    Route::post('/ujian/submit', [UjianController::class, 'submitUjianApi']);
    
    // 6. Nilai Siswa
    Route::get('/nilai', [SiswaController::class, 'getNilaiApi']);
    
    // 7. Detail Hasil
    Route::get('/ujian/hasil-detail', [UjianController::class, 'getDetailHasilApi']);
    
    // 8. Profil Siswa
    Route::get('/profil', [SiswaController::class, 'getProfilApi']);
    
    // 9. Ganti Password
    Route::post('/profil/change-password', [SiswaController::class, 'changePasswordApi']);
    
    // 10. Bank Soal
    Route::get('/bank-soal', [UjianController::class, 'getBankSoalApi']);
});