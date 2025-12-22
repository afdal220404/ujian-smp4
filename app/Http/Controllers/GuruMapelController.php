<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ujian;
use App\Models\Soal;
use Illuminate\Support\Facades\DB;
use App\Models\BankSoal;


class GuruMapelController extends Controller
{
    /**
     * Menampilkan dasbor spesifik untuk Guru Mata Pelajaran.
     */
    public function show(Mapel $mapel)
    {
        // 1. Dapatkan data guru & kelas
        $guru = Auth::user();
        $kelas = $mapel->kelas; // Asumsi relasi 'kelas' ada di model Mapel

        // 2. Verifikasi Keamanan:
        // Pastikan guru yang login adalah guru yang mengajar mapel ini
        if ($mapel->guru_id != $guru->id) {
            return redirect()->route('guru.index')->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }

        // 3. Ambil data untuk widget
        $jumlahSiswa = Siswa::where('kelas_id', $mapel->kelas_id)->count();

        $avgKuis = 82.5; // Placeholder
        $avgUTS = 78.9;  // Placeholder
        $avgUAS = 81.0;  // Placeholder

        $daftarUjian = Ujian::where('mapel_id', $mapel->id)
            ->where('waktu_mulai', '>', now()) // Hanya yang akan datang
            ->orderBy('waktu_mulai', 'asc') // Urutkan dari yang terdekat
            ->get();

        $historyUjian = Ujian::where('mapel_id', $mapel->id)
            ->where('waktu_mulai', '<', now()) // Hanya yang sudah berlalu
            ->orderBy('waktu_mulai', 'desc') // Tampilkan yang terbaru dulu
            ->get();

        // 5. Kirim semua data ke view
        return view('guru.mapel.dashboard', compact(
            'guru',
            'mapel',
            'kelas',
            'jumlahSiswa',
            'daftarUjian',
            'historyUjian',
            'avgKuis',
            'avgUTS',
            'avgUAS'
        ));
    }

    public function showSiswa(Mapel $mapel)
    {
        // 1. Ambil data guru, mapel, dan kelas
        $guru = Auth::user();
        $kelas = $mapel->kelas;

        // 2. Verifikasi Keamanan (opsional, tapi bagus)
        if ($mapel->guru_id != $guru->id) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        // 3. Ambil semua siswa di kelas ini
        $siswas = Siswa::where('kelas_id', $mapel->kelas_id)
            ->orderBy('nama_lengkap')
            ->get();

        // 4. Kirim data ke view
        return view('guru.mapel.daftar_siswa', [
            'guru' => $guru,
            'mapel' => $mapel,
            'kelas' => $kelas,
            'siswas' => $siswas,
        ]);
    }

    public function createUjian(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        session()->forget(['ujian_temp_details', 'ujian_temp_soals']);
        $ujianDetails = null;
        $jumlahSoal = 0;

        return view('guru.mapel.create_ujian', compact('mapel', 'ujianDetails', 'jumlahSoal'));
    }

    public function showCreateUjianPage(Mapel $mapel)
    {
        // 1. Get data dari session
        $ujianDetails = session('ujian_temp_details');
        $tempSoals = session('ujian_temp_soals', []);

        // 2. Jika sesi kosong (misal, user refresh), paksa kembali ke awal
        if (empty($ujianDetails)) {
            return redirect()->route('guru.mapel.ujian.create', $mapel->id)
                ->with('error', 'Sesi pembuatan ujian tidak ditemukan. Harap mulai dari awal.');
        }

        // 3. Siapkan data untuk view
        $jumlahSoal = count($tempSoals);
        $ujian = null; // Penting: $ujian = null menandakan mode CREATE

        // 4. Tampilkan view
        return view('guru.mapel.create_ujian', compact('mapel', 'ujianDetails', 'jumlahSoal', 'ujian'));
    }

    /**
     * Halaman 1: Handler untuk tombol "Kelola Soal" dan "Simpan Ujian"
     */
    public function storeUjian(Request $request, Mapel $mapel)
    {
        $validated = $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'jenis_ujian' => 'required|in:Kuis,UTS,UAS',
            'tanggal_ujian' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        ]);

        // Hitung durasi
        $start = new \DateTime($validated['waktu_mulai']);
        $end = new \DateTime($validated['waktu_selesai']);
        $diff = $start->diff($end);
        $durasi_menit = ($diff->h * 60) + $diff->i;

        $waktu_mulai_full = $validated['tanggal_ujian'] . ' ' . $validated['waktu_mulai'] . ':00';
        $waktu_selesai_full = $validated['tanggal_ujian'] . ' ' . $validated['waktu_selesai'] . ':00';

        // Simpan data ujian ke Sesi
        $ujianData = $validated + [
            'durasi_menit' => $durasi_menit,
            'mapel_id' => $mapel->id,
            'guru_id' => Auth::id(),
        ];
        session(['ujian_temp_details' => $ujianData]);

        // Cek tombol mana yang diklik
        if ($request->input('action') == 'tambah_soal') {

            // Jika klik "Soal", arahkan ke halaman tambah soal
            return redirect()->route('guru.mapel.soal.create');
        } elseif ($request->input('action') == 'simpan_ujian') {
            // Jika klik "Simpan Ujian", simpan semuanya ke database
            $tempSoals = session('ujian_temp_soals', []);

            if (empty($tempSoals)) {
                return back()->withInput()->with('error', 'Gagal menyimpan. Anda belum menambahkan soal apapun.');
            }

            try {
                DB::beginTransaction();

                $ujianDataDB = [
                    'mapel_id' => $mapel->id,
                    'guru_id' => Auth::id(),
                    'nama_ujian' => $validated['nama_ujian'],
                    'jenis_ujian' => $validated['jenis_ujian'],
                    'tanggal_ujian' => $validated['tanggal_ujian'], // <-- ✅ PERBAIKAN: TAMBAHKAN BARIS INI
                    'waktu_mulai' => $waktu_mulai_full,
                    'waktu_selesai' => $waktu_selesai_full,
                    'durasi_menit' => $durasi_menit,
                ];
                // 1. Buat Ujian di DB
                $ujian = Ujian::create($ujianDataDB);

                // 2. Loop dan simpan soal-soal
                foreach ($tempSoals as $dataSoal) {
                    $gambarPathBaru = null;
                    // Pindahkan gambar dari 'temp' ke 'public'
                    if (isset($dataSoal['gambar_path']) && Storage::disk('public')->exists($dataSoal['gambar_path'])) {
                        $tempPath = $dataSoal['gambar_path'];
                        $gambarPathBaru = str_replace('temp_soal/', 'soal/', $tempPath);
                        Storage::disk('public')->move($tempPath, $gambarPathBaru);
                    }

                    Soal::create([
                        'ujian_id' => $ujian->id,
                        'pertanyaan' => $dataSoal['pertanyaan'],
                        'gambar' => $gambarPathBaru,
                        'opsi_a' => $dataSoal['opsi_a'],
                        'opsi_b' => $dataSoal['opsi_b'],
                        'opsi_c' => $dataSoal['opsi_c'],
                        'opsi_d' => $dataSoal['opsi_d'],
                        'opsi_e' => $dataSoal['opsi_e'],
                        'kunci_jawaban' => $dataSoal['kunci_jawaban'],
                    ]);
                }
                DB::commit();

                // Hapus data dari Sesi
                session()->forget(['ujian_temp_details', 'ujian_temp_soals']);

                return redirect()->route('guru.mapel.dashboard', $mapel->id)
                    ->with('success', 'Ujian dan ' . count($tempSoals) . ' soal berhasil disimpan!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error Simpan Ujian: ' . $e->getMessage()); // Log error
                // Hapus file temp yang mungkin sudah ter-upload
                foreach (session('ujian_temp_soals', []) as $dataSoal) {
                    if (isset($dataSoal['gambar_path'])) {
                        Storage::delete($dataSoal['gambar_path']);
                    }
                }
                return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan ke database.');
            }
        }
    }

    /**
     * Halaman 2: Menampilkan form untuk menambah soal
     */
    public function createSoal()
    {
        // Cek apakah data ujian ada di sesi
        if (!session('ujian_temp_details')) {
            return redirect()->route('guru.index')->with('error', 'Sesi ujian tidak ditemukan. Harap mulai dari awal.');
        }

        // Ambil data soal yang sudah ada di Sesi untuk ditampilkan (jika Anda ingin review)
        $tempSoals = session('ujian_temp_soals', []);
        $ujian = null;


        return view('guru.mapel.tambah_soal', compact('tempSoals', 'ujian'));
    }

    /**
     * Halaman 2: Menyimpan SEMUA soal dari form dinamis ke Sesi
     */
    public function storeSoalToSession(Request $request, Ujian $ujian = null)
    {
        // 1. Ambil data ujian dari Sesi
        $ujianDetails = session('ujian_temp_details');
        if (!$ujianDetails) {
            return redirect()->route('guru.index')->with('error', 'Sesi ujian telah berakhir.');
        }

        // 2. Validasi data yang masuk sebagai array
        $validated = $request->validate([
            'soal'                 => 'required|array',
            'soal.*.pertanyaan'    => 'required|string',
            'soal.*.gambar'        => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'soal.*.opsi_a'        => 'required|string|max:255',
            'soal.*.opsi_b'        => 'required|string|max:255',
            'soal.*.opsi_c'        => 'required|string|max:255',
            'soal.*.opsi_d'        => 'required|string|max:255',
            'soal.*.opsi_e'        => 'required|string|max:255',
            'soal.*.kunci_jawaban' => 'required|in:A,B,C,D,E',
        ]);

        // 3. Hapus file gambar lama dari 'temp/'
        $soalsLama = session('ujian_temp_soals', []);
        foreach ($soalsLama as $soal) {
            if (isset($soal['gambar_path']) && str_starts_with($soal['gambar_path'], 'temp/')) {
                Storage::delete($soal['gambar_path']);
            }
        }

        // 4. Proses dan simpan soal baru ke Sesi
        $soalsBaru = [];
        foreach ($validated['soal'] as $index => $dataSoal) {
            $gambarPath = null;
            if ($request->hasFile("soal.{$index}.gambar")) {
                $gambarPath = $request->file("soal.{$index}.gambar")->store('temp_soal', 'public');
            } else {
                // Pertahankan gambar lama jika ada (dari mode edit)
                $gambarPath = $soalsLama[$index]['gambar_path'] ?? null;
            }

            unset($dataSoal['gambar']);
            $dataSoal['gambar_path'] = $gambarPath;
            $soalsBaru[] = $dataSoal;
        }

        // 5. Simpan array soal yang baru ke Sesi
        session(['ujian_temp_soals' => $soalsBaru]);

        // 6. Tentukan ke mana harus kembali
        if ($request->has('ujian') && $request->input('ujian') != null) {

            // Mode Edit: Kembali ke Halaman 1 (Edit)
            // Kita gunakan ID dari $request
            return redirect()->route('guru.mapel.ujian.edit', $request->input('ujian'))
                ->with('success', count($soalsBaru) . ' soal berhasil disimpan sementara.');
        } else {
            // Mode Create: Kembali ke Halaman 1 (Create) via route "review"
            // agar sesi tidak terhapus
            return redirect()->route('guru.mapel.ujian.review', $ujianDetails['mapel_id']) // <-- INI PERBAIKANNYA
                ->with('success', count($soalsBaru) . ' soal berhasil disimpan sementara.');
        }
    }

    public function editUjian(Ujian $ujian)
    {
        if ($ujian->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        $ujianDetails = session('ujian_temp_details');
        $tempSoals = session('ujian_temp_soals');

        if (empty($ujianDetails)) {
            $ujianDetails = [
                'nama_ujian' => $ujian->nama_ujian,
                'jenis_ujian' => $ujian->jenis_ujian,
                'tanggal_ujian' => $ujian->tanggal_ujian,
                'waktu_mulai' => \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i'),
                'waktu_selesai' => \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i'),
                'durasi_menit' => $ujian->durasi_menit,
            ];
            // Simpan ke Sesi untuk pertama kali
            session(['ujian_temp_details' => $ujianDetails + ['mapel_id' => $ujian->mapel_id]]);
        }

        // 3. Jika Soal TIDAK ADA di Sesi, baru ambil dari DB
        if (empty($tempSoals)) {
            $tempSoals = $ujian->soals->map(function ($soal) {
                return [
                    'pertanyaan' => $soal->pertanyaan,
                    'gambar_path' => $soal->gambar, // Path sudah di 'public/'
                    'opsi_a' => $soal->opsi_a,
                    'opsi_b' => $soal->opsi_b,
                    'opsi_c' => $soal->opsi_c,
                    'opsi_d' => $soal->opsi_d,
                    'opsi_e' => $soal->opsi_e,
                    'kunci_jawaban' => $soal->kunci_jawaban,
                ];
            })->toArray();
            // Simpan ke Sesi untuk pertama kali
            session(['ujian_temp_soals' => $tempSoals]);
        } // ▼▼▼ AKHIR PERBAIKAN ▼▼▼

        $jumlahSoal = count($tempSoals); // Hitung dari $tempSoals yang dari sesi
        $mapel = $ujian->mapel;

        // Gunakan view yang sama, kirim $ujianDetails yang sudah pasti dari Sesi
        return view('guru.mapel.create_ujian', compact('mapel', 'ujianDetails', 'jumlahSoal', 'ujian'));
    }

    public function updateUjian(Request $request, Mapel $mapel, ?Ujian $ujian = null)
    {
        $validated = $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'jenis_ujian' => 'required|in:Kuis,UTS,UAS',
            'tanggal_ujian' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        ]);

        $start = new \DateTime($validated['waktu_mulai']);
        $end = new \DateTime($validated['waktu_selesai']);
        $diff = $start->diff($end);
        $durasi_menit = ($diff->h * 60) + $diff->i;

        $waktu_mulai_full = $validated['tanggal_ujian'] . ' ' . $validated['waktu_mulai'] . ':00';
        $waktu_selesai_full = $validated['tanggal_ujian'] . ' ' . $validated['waktu_selesai'] . ':00';

        $ujianDataSesi = $validated + [
            'durasi_menit' => $durasi_menit,
            'mapel_id' => $mapel->id,
            'guru_id' => Auth::id(),
        ];
        session(['ujian_temp_details' => $ujianDataSesi]);

        // Tentukan Ujian (jika sedang mode edit)
        // Jika $ujian tidak null, berarti kita sedang dalam mode UPDATE
        $ujianTarget = $ujian;

        if ($request->input('action') == 'tambah_soal') {
            // Jika klik "Soal", arahkan ke halaman tambah soal
            // Jika mode edit, kirim ID ujian. Jika mode create, ID belum ada
            $routeParams = $ujianTarget ? ['ujian' => $ujianTarget->id] : [];
            return redirect()->route('guru.mapel.soal.show', $routeParams);
        } elseif ($request->input('action') == 'simpan_ujian') {
            // Jika klik "Simpan Ujian", simpan semuanya ke database
            $tempSoals = session('ujian_temp_soals', []);

            if (empty($tempSoals)) {
                return back()->withInput()->with('error', 'Gagal menyimpan. Anda belum menambahkan soal apapun.');
            }

            try {
                DB::beginTransaction();

                $ujianDataDB = [
                    'mapel_id' => $mapel->id,
                    'guru_id' => Auth::id(),
                    'nama_ujian' => $validated['nama_ujian'],
                    'jenis_ujian' => $validated['jenis_ujian'],
                    'tanggal_ujian' => $validated['tanggal_ujian'],
                    'waktu_mulai' => $waktu_mulai_full,
                    'waktu_selesai' => $waktu_selesai_full,
                    'durasi_menit' => $durasi_menit,
                ];

                // Jika $ujianTarget ada (mode edit), update. Jika tidak, create.
                if ($ujianTarget) {
                    $ujianTarget->update($ujianDataDB);
                    $ujian = $ujianTarget; // Gunakan ujian yang sudah ada
                    $ujian->soals()->delete(); // Hapus soal lama untuk diganti baru
                } else {
                    $ujian = Ujian::create($ujianDataDB); // Buat ujian baru
                }

                // Loop dan simpan soal-soal
                foreach ($tempSoals as $dataSoal) {
                    $gambarPathBaru = $dataSoal['gambar_path'] ?? null;

                    // Jika gambar ada di 'temp/', pindahkan ke 'public/'
                    if ($gambarPathBaru && str_starts_with($gambarPathBaru, 'temp/')) {
                        $tempPath = $gambarPathBaru;
                        $gambarPathBaru = str_replace('temp_soal/', 'soal/', $tempPath);
                        
                        // Gunakan Storage::disk('public') agar konsisten
                        if (Storage::disk('public')->exists($tempPath)) {
                            Storage::disk('public')->move($tempPath, $gambarPathBaru);
                        }
                    }

                    Soal::create([
                        'ujian_id' => $ujian->id,
                        'pertanyaan' => $dataSoal['pertanyaan'],
                        'gambar' => $gambarPathBaru,
                        'opsi_a' => $dataSoal['opsi_a'],
                        'opsi_b' => $dataSoal['opsi_b'],
                        'opsi_c' => $dataSoal['opsi_c'],
                        'opsi_d' => $dataSoal['opsi_d'],
                        'opsi_e' => $dataSoal['opsi_e'],
                        'kunci_jawaban' => $dataSoal['kunci_jawaban'],
                    ]);
                }

                DB::commit();
                session()->forget(['ujian_temp_details', 'ujian_temp_soals']);
                return redirect()->route('guru.mapel.dashboard', $mapel->id)
                    ->with('success', 'Ujian dan ' . count($tempSoals) . ' soal berhasil disimpan!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal Simpan Ujian: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Terjadi kesalahan. Silakan periksa log server.');
            }
        }
    }

    public function showSoalForm(Ujian $ujian = null)
    {
        // Jika $ujian ada (mode edit), data sudah dimuat ke Sesi oleh editUjian()
        // Jika $ujian null (mode create), data dimuat dari Sesi oleh storeUjian()

        if (!session('ujian_temp_details')) {
            return redirect()->route('guru.index')->with('error', 'Sesi ujian tidak ditemukan. Harap mulai dari awal.');
        }

        $tempSoals = session('ujian_temp_soals', []);
        $mapel = $ujian->mapel;
        // Kirim $ujian (bisa null) untuk menentukan URL form action
        return view('guru.mapel.tambah_soal', compact('tempSoals', 'ujian'));
    }

    public function destroyUjian(Ujian $ujian)
    {
        // 1. Verifikasi Keamanan: Pastikan guru yang login adalah pemilik ujian
        if ($ujian->guru_id != Auth::id()) {
            return redirect()->route('guru.mapel.dashboard', $ujian->mapel_id)
                             ->with('error', 'Akses ditolak. Anda bukan pemilik ujian ini.');
        }

        // Simpan info untuk redirect & pesan sukses
        $mapelId = $ujian->mapel_id;
        $namaUjian = $ujian->nama_ujian;

        try {
            DB::beginTransaction();
            
            // 1. Hapus soal-soal terkait (seperti di fungsi updateUjian)
            // Ini mengasumsikan model Ujian Anda memiliki relasi 'soals()'
            $ujian->soals()->delete(); 
            
            // 2. Hapus ujiannya
            $ujian->delete();
            
            DB::commit();

            return redirect()->route('guru.mapel.dashboard', $mapelId)
                             ->with('success', 'Ujian "' . $namaUjian . '" dan soal-soalnya telah berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Gagal Hapus Ujian: ' . $e->getMessage());
            return redirect()->route('guru.mapel.dashboard', $mapelId)
                             ->with('error', 'Terjadi kesalahan saat menghapus ujian. Silakan cek log.');
        }
    }

    public function showUjianDetail(Ujian $ujian)
    {
        // 1. Verifikasi Keamanan
        if ($ujian->guru_id != Auth::id()) {
            return redirect()->route('guru.mapel.dashboard', $ujian->mapel_id)
                             ->with('error', 'Akses ditolak.');
        }

        // 2. Ambil data terkait untuk sidebar dan judul
        $mapel = $ujian->mapel;
        $kelas = $mapel->kelas;

        // 3. Ambil daftar siswa dari kelas yang terkait dengan ujian ini
        $siswasDiKelas = Siswa::where('kelas_id', $mapel->kelas_id)
                             ->orderBy('nama_lengkap', 'asc')
                             ->get();

        // 4. === MEMBUAT DATA NILAI DUMMY ===
        //    Nanti, Anda akan mengganti ini dengan query ke tabel 'nilais'
        //    Contoh: $hasilUjian = Nilai::where('ujian_id', $ujian->id)->with('siswa')->get();
        
        $hasilUjian = $siswasDiKelas->map(function ($siswa) {
            // Beri nilai acak antara 65 dan 98
            $nilaiDummy = rand(65, 98); 
            
            // Tiru struktur data yang mungkin akan Anda gunakan nanti
            return (object) [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_lengkap,
                'nisn_siswa' => $siswa->nisn, 
                'nilai' => $nilaiDummy
            ];
        });

        // 5. Kirim data ke view baru
        return view('guru.mapel.detail_ujian', compact(
            'ujian',
            'mapel',
            'kelas',
            'hasilUjian'
        ));
    }

    public function showSiswaUjianDetail(Ujian $ujian, Siswa $siswa)
    {
        // 1. Verifikasi Keamanan
        $mapel = $ujian->mapel;
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }
        if ($siswa->kelas_id != $mapel->kelas_id) {
            return redirect()->route('guru.index')->with('error', 'Siswa tidak ditemukan di kelas ini.');
        }

        // 2. Ambil semua soal dari ujian ini
        $semuaSoal = $ujian->soals()->get(); // Mengambil relasi soals()
        $jumlahTotalSoal = $semuaSoal->count();

        // 3. === GENERASI DATA DUMMY ===
        //    Di sinilah nanti Anda akan query ke tabel 'jawaban_siswa'
        
        $detailJawaban = [];
        $jumlahBenar = 0;
        $kunciOpsi = ['A', 'B', 'C', 'D', 'E'];

        foreach ($semuaSoal as $soal) {
            // 3a. Buat jawaban dummy (acak 'A' s/d 'E')
            $jawabanSiswaDummy = $kunciOpsi[array_rand($kunciOpsi)];

            // 3b. Cek apakah jawaban dummy ini benar
            $isBenar = ($jawabanSiswaDummy == $soal->kunci_jawaban);
            if ($isBenar) {
                $jumlahBenar++;
            }

            // 3c. Simpan ke array hasil
            $detailJawaban[] = (object) [
                'soal' => $soal, // Ini adalah model Soal lengkap
                'jawaban_siswa' => $jawabanSiswaDummy,
                'is_benar' => $isBenar
            ];
        }

        // 3d. Hitung nilai dummy
        $nilaiSiswa = ($jumlahTotalSoal > 0) ? round(($jumlahBenar / $jumlahTotalSoal) * 100) : 0;
        
        // === AKHIR GENERASI DATA DUMMY ===


        // 4. Kirim semua data ke view baru
        return view('guru.mapel.detail_jawaban_siswa', compact(
            'ujian',
            'siswa',
            'mapel',
            'detailJawaban', // Array hasil dummy
            'jumlahBenar',
            'nilaiSiswa',
            'jumlahTotalSoal'
        ));
    }

    public function indexBankSoal(Mapel $mapel)
    {
        // 1. Verifikasi Keamanan
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        // 2. Ambil semua bank soal milik guru dan mapel ini
        $bankSoals = BankSoal::where('mapel_id', $mapel->id)
                             ->where('guru_id', Auth::id())
                             ->orderBy('nama', 'asc')
                             ->get();

        return view('guru.mapel.bank_soal', compact('mapel', 'bankSoals'));
    }

    /**
     * Menangani upload file baru dan update visibilitas file lama.
     */
    public function handleBankSoal(Request $request, Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }
        
        // 1. Validasi untuk file yang di-upload
        $request->validate([
            'new_files.*.file'       => 'required_with:new_files.*.nama|file|mimes:pdf|max:5120', // Maks 5MB
            'new_files.*.nama'       => 'required_with:new_files.*.file|string|max:255',
            'new_files.*.visibilitas'=> 'required_with:new_files.*.file|in:Public,Private,Draft',
            // Validasi untuk update file lama (jika ada)
            'existing.*.visibilitas' => 'required|in:Public,Private,Draft', 
            'existing.*.nama'        => 'required|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();

            // 2. Handle File Baru (Jika ada upload)
            if ($request->has('new_files')) {
                foreach ($request->file('new_files') as $index => $newFile) {
                    // Pastikan file dan nama ada
                    if (isset($newFile['file']) && $request->new_files[$index]['nama']) {
                        $file = $newFile['file'];
                        
                        // Simpan file ke folder 'bank_soal' di public disk
                        $filePath = $file->store('bank_soal/' . $mapel->id, 'public'); 

                        BankSoal::create([
                            'guru_id' => Auth::id(),
                            'mapel_id' => $mapel->id,
                            'nama' => $request->new_files[$index]['nama'],
                            'file_path' => $filePath,
                            'visibilitas' => $request->new_files[$index]['visibilitas'],
                        ]);
                    }
                }
            }

            // 3. Handle Update Visibilitas & Nama File Lama
            if ($request->has('existing')) {
                foreach ($request->existing as $fileId => $data) {
                    $fileToUpdate = BankSoal::findOrFail($fileId);
                    $fileToUpdate->update([
                        'nama' => $data['nama'],
                        'visibilitas' => $data['visibilitas'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('guru.mapel.bank_soal.index', $mapel->id)->with('success', 'Bank soal berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Bank Soal: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui bank soal: ' . $e->getMessage());
        }
    }
    
    /**
     * Menghapus file soal dari database dan storage.
     */
    public function destroyBankSoal(BankSoal $bankSoal)
    {
        if ($bankSoal->guru_id != Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        try {
            // 1. Hapus file dari storage
            if (Storage::disk('public')->exists($bankSoal->file_path)) {
                Storage::disk('public')->delete($bankSoal->file_path);
            }

            // 2. Hapus record dari database
            $bankSoal->delete();

            return redirect()->back()->with('success', 'File "' . $bankSoal->nama . '" berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Gagal hapus bank soal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus file bank soal.');
        }
    }
}
