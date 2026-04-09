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
    // Halaman Kenaikan Kelas
    Route::get('/operator/kenaikan-kelas', [SiswaController::class, 'indexKenaikan'])->name('operator.kenaikan_kelas');
    // Proses Kenaikan (AJAX/Post)
    Route::post('/operator/kenaikan-kelas/proses', [SiswaController::class, 'storeKenaikan'])->name('operator.proses_kenaikan');
    Route::post('/operator/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::get('/operator/siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');

    // Route Alumni (Baru/Restore)
    Route::get('/operator/alumni', [SiswaController::class, 'indexAlumni'])->name('operator.alumni.index');
    Route::get('/operator/alumni/{id}/detail', [SiswaController::class, 'detailAlumni'])->name('operator.alumni.detail');

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

// ROUTE KHUSUS SISWA
Route::middleware(['auth:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/nilai', [App\Http\Controllers\SiswaDashboardController::class, 'indexNilai'])->name('nilai');
    Route::get('/bank-soal', [App\Http\Controllers\SiswaDashboardController::class, 'indexBankSoal'])->name('bank_soal');
    Route::get('/ujian/{id}', [App\Http\Controllers\SiswaDashboardController::class, 'showUjian'])->name('ujian.detail');
    
    // EXAM FLOW ROUTES
    Route::get('/ujian/{id}/konfirmasi', [App\Http\Controllers\SiswaDashboardController::class, 'konfirmasiUjian'])->name('ujian.konfirmasi');
    Route::post('/ujian/{id}/mulai', [App\Http\Controllers\SiswaDashboardController::class, 'mulaiUjian'])->name('ujian.mulai');
    Route::get('/ujian/{id}/kerjakan', [App\Http\Controllers\SiswaDashboardController::class, 'kerjakanUjian'])->name('ujian.kerjakan');
    Route::post('/ujian/simpan-jawaban', [App\Http\Controllers\SiswaDashboardController::class, 'simpanJawaban'])->name('ujian.simpan_jawaban');
    Route::post('/ujian/{id}/selesai', [App\Http\Controllers\SiswaDashboardController::class, 'selesaiUjian'])->name('ujian.selesai');
    Route::get('/ujian/{id}/hasil', [App\Http\Controllers\SiswaDashboardController::class, 'hasilUjian'])->name('ujian.hasil');
});


Route::middleware(['auth'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/index', [GuruIndexController::class, 'index'])->name('index');
    Route::get('/walikelas/{kelas}', [GuruWaliKelasController::class, 'show'])->name('walikelas.dashboard');
    Route::get('/walikelas/{kelas}/siswa', [GuruWaliKelasController::class, 'showSiswa'])->name('walikelas.siswa');
    Route::get('/walikelas/siswa/{siswa}', [GuruWaliKelasController::class, 'showSiswaDetail'])->name('walikelas.siswa.detail');
    Route::get('/walikelas/{kelas}/rekap-nilai', [GuruWaliKelasController::class, 'indexRekapNilai'])->name('walikelas.rekap_nilai');
    Route::get('/walikelas/{kelas}/rekap-nilai/export', [GuruWaliKelasController::class, 'exportRekapNilai'])->name('walikelas.rekap_nilai.export');

    Route::get('/mapel/{mapel}', [GuruMapelController::class, 'show'])->name('mapel.dashboard');
    Route::get('/mapel/{mapel}/siswa', [GuruMapelController::class, 'showSiswa'])->name('mapel.siswa');

    // Halaman 1: Tampilkan Form Buat Ujian (GET)
    Route::get('/mapel/{mapel}/ujian/create', [GuruMapelController::class, 'createUjian'])->name('mapel.ujian.create');

    // Halaman 1: Handler untuk "Simpan Ujian" (Final) & "Kelola Soal" (POST)
    Route::post('/mapel/{mapel}/ujian/store', [GuruMapelController::class, 'storeUjian'])->name('mapel.ujian.store');

    // Halaman 1: Handler untuk tombol "Update Ujian" & "Kelola Soal"
    Route::post('/mapel/{mapel}/ujian/{ujian}/update', [GuruMapelController::class, 'updateUjian'])->name('mapel.ujian.update');
    
    // Halaman 1 (Review): Untuk KEMBALI ke form (MEMBACA SESI)
    Route::get('/mapel/{mapel}/ujian/review', [GuruMapelController::class, 'showCreateUjianPage'])->name('mapel.ujian.review');
    
    // Update Waktu Ujian (Modal)
    Route::post('/ujian/{ujian}/update-waktu', [GuruMapelController::class, 'updateWaktu'])->name('mapel.ujian.update_waktu');
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

    // Akses halaman Analisis Soal
    Route::get('/ujian/{ujian}/analisis-soal', [GuruMapelController::class, 'showAnalisisSoal'])->name('mapel.ujian.analisis_soal');

    // Akses halaman Detail Siswa untuk Ujian Tertentu
    Route::get('/ujian/{ujian}/siswa/{siswa}/detail', [GuruMapelController::class, 'showSiswaUjianDetail'])->name('mapel.ujian.siswa.detail');

    // --- BANK SOAL (Butir Soal untuk di-import) ---
    Route::get('/mapel/{mapel}/bank-soal', [GuruMapelController::class, 'indexBankSoal'])->name('mapel.bank_soal.index');
    Route::post('/mapel/{mapel}/bank-soal', [GuruMapelController::class, 'storeBankSoal'])->name('mapel.bank_soal.store');
    Route::put('/mapel/{mapel}/bank-soal/{bankSoal}', [GuruMapelController::class, 'updateBankSoal'])->name('mapel.bank_soal.update');
    Route::delete('/mapel/{mapel}/bank-soal/{bankSoal}', [GuruMapelController::class, 'destroyBankSoal'])->name('mapel.bank_soal.destroy');
    Route::get('/mapel/{mapel}/bank-soal-items', [GuruMapelController::class, 'getBankSoalItems'])->name('mapel.bank_soal.items');
    Route::post('/mapel/{mapel}/bank-soal/bulk-delete', [App\Http\Controllers\GuruMapelController::class, 'destroyBulkBankSoal'])->name('mapel.bank_soal.bulk_delete');

    // --- ARSIP SOAL SISWA (Upload PDF) ---
    Route::get('/mapel/{mapel}/arsip-soal-siswa', [GuruMapelController::class, 'indexArsipSoalSiswa'])->name('mapel.arsip_soal_siswa.index');
    Route::post('/mapel/{mapel}/arsip-soal-siswa', [GuruMapelController::class, 'handleArsipSoalSiswa'])->name('mapel.arsip_soal_siswa.store');
    Route::delete('/arsip-soal-siswa/{arsipSoalSiswa}', [GuruMapelController::class, 'destroyArsipSoalSiswa'])->name('mapel.arsip_soal_siswa.destroy');

    // Ujian Susulan
    Route::get('/ujian/{ujian}/susulan/create', [GuruMapelController::class, 'createSusulan'])->name('mapel.ujian.susulan.create');
    Route::post('/ujian/{ujian}/susulan/store', [GuruMapelController::class, 'storeSusulan'])->name('mapel.ujian.susulan.store');

    // Force Finish Ujian
    Route::post('/ujian/{ujian}/force-finish', [GuruMapelController::class, 'forceFinish'])->name('mapel.ujian.force_finish');
    
    // Restart ujian siswa
    Route::post('/ujian/{ujian}/siswa/{siswa}/restart', [GuruMapelController::class, 'restartUjianSiswa'])->name('mapel.ujian.siswa.restart');
});
    
// ---------------------------------------------------------------------
    // 3. ROLE: KEPALA SEKOLAH (KEPSEK) - BARU
    // ---------------------------------------------------------------------
    Route::middleware(['auth'])->prefix('kepsek')->name('kepsek.')->group(function () {
    
    Route::get('/dashboard', [KepsekController::class, 'index'])->name('index'); 
    
    Route::get('/monitor-guru', [KepsekController::class, 'monitorGuru'])->name('guru');
    Route::get('/monitor-siswa', [KepsekController::class, 'monitorSiswa'])->name('siswa');
    Route::get('/laporan-nilai', [KepsekController::class, 'laporanNilai'])->name('nilai');
    Route::get('/nilai/{id}', [KepsekController::class, 'detailNilai'])->name('nilai.detail');

    // Route Alumni untuk Kepsek
    Route::get('/alumni', [KepsekController::class, 'indexAlumni'])->name('alumni.index');
    Route::get('/alumni/{id}/detail', [KepsekController::class, 'detailAlumni'])->name('alumni.detail');
});
Route::get('/landingpage', [KepsekController::class, 'index'])
    ->middleware('auth')
    ->name('landingpage');

Route::get('/landingpage2', [GuruIndexController::class, 'index'])->name('landingpage2');

Route::get('/landingpage3', function () {
    return view('operator/landingpage');
})->name('landingpage3');

Route::get('/landingpage4', function () {
    return view('wali_kelas/landingpage');
})->name('landingpage4');

Route::get('/daftar_siswa3', function () {
    return view('wali_kelas/daftar_siswa');
})->name('daftar_siswa3');


Route::get('/tambah_guru', function () {
    return view('operator/tambah_guru');
})->name('tambah_guru');

Route::get('/debug-grading', function() {
    $h = \App\Models\HasilUjian::latest()->first();
    if(!$h) return 'No Result';
    
    return \App\Models\JawabanSiswa::where('hasil_ujian_id', $h->id)
        ->with('soal')
        ->get()
        ->map(function($j){
            return [
                'soal_id' => $j->soal_id,
                'tipe' => $j->soal->tipe,
                'kunci_raw' => $j->soal->kunci_jawaban,
                'jawab_raw' => $j->jawaban_dipilih,
                'is_correct_db' => $j->is_correct,
                'data_soal' => $j->soal->data_soal,
            ];
        });
});
