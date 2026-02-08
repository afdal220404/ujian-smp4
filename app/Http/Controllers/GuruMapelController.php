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
use App\Models\HasilUjian;
use App\Models\JawabanSiswa; // Pastikan model ini ada
use Carbon\Carbon;

class GuruMapelController extends Controller
{
    /**
     * Menampilkan dasbor spesifik untuk Guru Mata Pelajaran.
     * UPDATE: Menghitung statistik real dari database.
     */
    public function show(Mapel $mapel)
    {
        $guru = Auth::user();
        $kelas = $mapel->kelas; 

        // 1. Validasi Akses
        if ($mapel->guru_id != $guru->id) {
            return redirect()->route('guru.index')->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }

        // 2. Data Siswa
        $jumlahSiswa = Siswa::where('kelas_id', $mapel->kelas_id)->count();

        // 3. LOGIKA STATISTIK REAL (Berdasarkan Jenis Ujian)
        // Ambil semua ID ujian milik mapel ini
        $ujianIds = Ujian::where('mapel_id', $mapel->id)->pluck('id');

        // Ambil semua hasil ujian yang terkait dengan ujian-ujian tersebut
        $allHasil = HasilUjian::whereIn('ujian_id', $ujianIds)->with('ujian')->get();

        // Hitung rata-rata berdasarkan jenis_ujian (Kuis, UTS, UAS)
        $avgKuis = $allHasil->filter(fn($h) => $h->ujian->jenis_ujian == 'Kuis')->avg('nilai') ?? 0;
        $avgUTS  = $allHasil->filter(fn($h) => $h->ujian->jenis_ujian == 'UTS')->avg('nilai') ?? 0;
        $avgUAS  = $allHasil->filter(fn($h) => $h->ujian->jenis_ujian == 'UAS')->avg('nilai') ?? 0;

        // 4. Kategori Waktu Ujian
        $now = Carbon::now('Asia/Jakarta');

        // A. SEDANG BERLANGSUNG
        $ongoingUjian = Ujian::where('mapel_id', $mapel->id)
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->orderBy('waktu_selesai', 'asc')
            ->get();

        // B. AKAN DATANG
        $upcomingUjian = Ujian::where('mapel_id', $mapel->id)
            ->where('waktu_mulai', '>', $now)
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // C. RIWAYAT
        $historyUjian = Ujian::where('mapel_id', $mapel->id)
            ->where('waktu_selesai', '<', $now)
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        return view('guru.mapel.dashboard', compact(
            'guru', 'mapel', 'kelas', 'jumlahSiswa',
            'ongoingUjian', 'upcomingUjian', 'historyUjian',
            'avgKuis', 'avgUTS', 'avgUAS'
        ));
    }

    /**
     * Menampilkan daftar siswa dan nilai rekap per mapel.
     * Sudah menggunakan data real di iterasi sebelumnya.
     */
    public function showSiswa(Mapel $mapel)
    {
        $user = Auth::user();

        // 1. Validasi Akses
        $guruId = $user->guru ? $user->guru->id : $user->id;
        if ($mapel->guru_id != $guruId) {
             abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }

        // 2. Ambil Daftar Kuis (Master)
        $daftarKuisMaster = Ujian::where('mapel_id', $mapel->id)
                            ->where('jenis_ujian', 'Kuis') 
                            ->orderBy('created_at', 'asc')
                            ->get();

        $maxKuis = $daftarKuisMaster->count();

        // 3. Ambil Siswa
        $siswasRaw = Siswa::where('kelas_id', $mapel->kelas_id)
                        ->orderBy('nama_lengkap')
                        ->get();

        // 4. Olah Data Siswa
        $siswas = $siswasRaw->map(function($siswa) use ($mapel, $daftarKuisMaster) {
            
            $listKuis = [];
            
            foreach($daftarKuisMaster as $kuisMaster) {
                $nilai = HasilUjian::where('siswa_id', $siswa->id)
                                   ->where('ujian_id', $kuisMaster->id)
                                   ->value('nilai');
                $listKuis[] = $nilai !== null ? $nilai : '-';
            }

            $nilaiKuisValid = array_filter($listKuis, fn($v) => is_numeric($v));
            $rataKuis = count($nilaiKuisValid) > 0 ? array_sum($nilaiKuisValid) / count($nilaiKuisValid) : 0;

            // Ambil UTS & UAS
            $uts = HasilUjian::where('siswa_id', $siswa->id)
                    ->whereHas('ujian', fn($q) => $q->where('mapel_id', $mapel->id)->where('jenis_ujian', 'UTS'))
                    ->value('nilai') ?? 0;

            $uas = HasilUjian::where('siswa_id', $siswa->id)
                    ->whereHas('ujian', fn($q) => $q->where('mapel_id', $mapel->id)->where('jenis_ujian', 'UAS'))
                    ->value('nilai') ?? 0;

            // Hitung Akhir
            $akhir = ($rataKuis * 0.4) + ($uts * 0.3) + ($uas * 0.3);

            return (object) [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap, 
                'nisn' => $siswa->nisn,
                'list_kuis' => $listKuis,
                'rata_kuis' => number_format($rataKuis, 1),
                
                // --- PERBAIKAN DI SINI (Ganti 'rata_uts' jadi 'uts') ---
                'uts' => $uts == 0 ? '-' : number_format($uts, 1),
                'uas' => $uas == 0 ? '-' : number_format($uas, 1),
                // -------------------------------------------------------

                'nilai_akhir' => number_format($akhir, 1),
                'grade_raw' => $akhir
            ];
        });

        if ($maxKuis == 0) $maxKuis = 1;

        return view('guru.mapel.daftar_siswa', compact('mapel', 'siswas', 'maxKuis'));
    }

    /**
     * Menampilkan detail hasil ujian satu kelas.
     * UPDATE: Menghapus data dummy total. Hanya menampilkan data real.
     */
    public function showUjianDetail(Ujian $ujian)
    {
        $user = Auth::user();
        $mapel = $ujian->mapel;
        $kelas = $mapel->kelas;
        
        // 1. Validasi Akses
        $guruId = $user->guru ? $user->guru->id : $user->id;
        if ($mapel->guru_id != $guruId) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Data Master Siswa
        $semuaSiswa = Siswa::where('kelas_id', $mapel->kelas_id)
                        ->orderBy('nama_lengkap', 'asc')
                        ->get();
        
        // Hitung Total Soal untuk referensi statistik
        $totalSoalUjian = $ujian->soals()->count();
        if ($totalSoalUjian == 0) $totalSoalUjian = 1; 

        // 3. Ambil Hasil Ujian Real
        $hasilUjianReal = HasilUjian::where('ujian_id', $ujian->id)
                        ->with('siswa')
                        ->get();

        // 4. Map Data Real (Tanpa Dummy Fallback)
        $hasilUjian = $hasilUjianReal->map(function($hasil) use ($totalSoalUjian) {
            
            // Cek apakah kolom jumlah_benar di DB terisi (sesuai SQL dump)
            if (!is_null($hasil->jumlah_benar)) {
                $benar = $hasil->jumlah_benar;
                $salah = $totalSoalUjian - $benar;
            } else {
                // Kalkulasi manual jika kolom DB masih kosong (backward compatibility)
                $benar = round(($hasil->nilai / 100) * $totalSoalUjian);
                $salah = $totalSoalUjian - $benar;
            }
            
            // Inject atribut untuk view
            $hasil->jumlah_benar = $benar;
            $hasil->jumlah_salah = $salah;
            $hasil->total_soal = $totalSoalUjian;
            
            return $hasil;
        });
        
        // 5. Pisahkan siswa yang belum mengerjakan
        $idSiswaSudah = $hasilUjian->pluck('siswa_id')->toArray();
        $siswaBelum = $semuaSiswa->filter(fn($s) => !in_array($s->id, $idSiswaSudah));
        
        $sudahMengerjakan = $hasilUjian->count();
        $belumMengerjakan = $siswaBelum->count();
        $totalSiswa = $semuaSiswa->count();

        return view('guru.mapel.detail_ujian', compact(
            'ujian', 'mapel', 'kelas',
            'hasilUjian', 'siswaBelum', 
            'totalSiswa', 'sudahMengerjakan', 'belumMengerjakan'
        ));
    }

    /**
     * Menampilkan detail jawaban spesifik per siswa.
     * UPDATE: Menggunakan relasi tabel hasil_ujians -> jawaban_siswas.
     */
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

        // 2. Ambil soal
        $semuaSoal = $ujian->soals()->get();
        $jumlahTotalSoal = $semuaSoal->count();

        // 3. AMBIL DATA HASIL & JAWABAN (SESUAI STRUKTUR DB)
        
        // A. Cari HasilUjian dulu untuk mendapatkan ID-nya
        $hasilUjian = HasilUjian::where('ujian_id', $ujian->id)
                        ->where('siswa_id', $siswa->id)
                        ->first();

        // B. Ambil Jawaban berdasarkan hasil_ujian_id
        $listJawabanSiswa = collect(); 
        
        if ($hasilUjian) {
            // Ambil dari tabel jawaban_siswas menggunakan hasil_ujian_id
            $listJawabanSiswa = JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
                                    ->get()
                                    ->keyBy('soal_id');
        }

        // 4. Proses Data untuk View
        $daftarSoal = []; 
        $jumlahBenar = 0;

        foreach ($semuaSoal as $soal) {
            // Cari jawaban untuk soal ini
            $jawabanDb = $listJawabanSiswa->get($soal->id);
            
            // Nama kolom di tabel jawaban_siswas adalah 'jawaban_dipilih' (sesuai SQL)
            $jawabanSiswa = $jawabanDb ? $jawabanDb->jawaban_dipilih : null; 

            // Cek kebenaran (Case insensitive)
            $isBenar = false;
            if ($jawabanSiswa) {
                $isBenar = (strtoupper($jawabanSiswa) == strtoupper($soal->kunci_jawaban));
            }

            if ($isBenar) $jumlahBenar++;

            // Inject data ke object soal
            $soal->jawaban_siswa = $jawabanSiswa; 
            $soal->status_jawaban = $isBenar;     

            $daftarSoal[] = $soal;
        }

        // 5. Ambil Nilai Akhir (Konsisten dengan DB)
        $nilai = $hasilUjian ? $hasilUjian->nilai : 0;

        return view('guru.mapel.detail_jawaban_siswa', compact(
            'ujian', 'siswa', 'mapel',
            'daftarSoal', 'jumlahBenar', 'nilai', 'jumlahTotalSoal','hasilUjian'
        ));
    }

    // --- FUNGSI CRUD UJIAN & BANK SOAL (TIDAK PERLU DUMMY, SUDAH REAL) ---

    public function createUjian(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }
        session()->forget(['ujian_temp_details', 'ujian_temp_soals']);
        return view('guru.mapel.create_ujian', ['mapel' => $mapel, 'ujianDetails' => null, 'jumlahSoal' => 0]);
    }

    public function showCreateUjianPage(Mapel $mapel)
    {
        $ujianDetails = session('ujian_temp_details');
        $tempSoals = session('ujian_temp_soals', []);

        if (empty($ujianDetails)) {
            return redirect()->route('guru.mapel.ujian.create', $mapel->id)
                ->with('error', 'Sesi pembuatan ujian tidak ditemukan.');
        }

        $jumlahSoal = count($tempSoals);
        $ujian = null; 

        return view('guru.mapel.create_ujian', compact('mapel', 'ujianDetails', 'jumlahSoal', 'ujian'));
    }

    public function storeUjian(Request $request, Mapel $mapel)
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

        $ujianData = $validated + [
            'durasi_menit' => $durasi_menit,
            'mapel_id' => $mapel->id,
            'guru_id' => Auth::id(),
        ];
        session(['ujian_temp_details' => $ujianData]);

        if ($request->input('action') == 'tambah_soal') {
            return redirect()->route('guru.mapel.soal.create');
        } elseif ($request->input('action') == 'simpan_ujian') {
            $tempSoals = session('ujian_temp_soals', []);

            if (empty($tempSoals)) {
                return back()->withInput()->with('error', 'Gagal menyimpan. Soal masih kosong.');
            }

            try {
                DB::beginTransaction();

                $ujian = Ujian::create([
                    'mapel_id' => $mapel->id,
                    'guru_id' => Auth::id(),
                    'nama_ujian' => $validated['nama_ujian'],
                    'jenis_ujian' => $validated['jenis_ujian'],
                    'tanggal_ujian' => $validated['tanggal_ujian'],
                    'waktu_mulai' => $waktu_mulai_full,
                    'waktu_selesai' => $waktu_selesai_full,
                    'durasi_menit' => $durasi_menit,
                ]);

                foreach ($tempSoals as $dataSoal) {
                    $gambarPathBaru = null;
                    if (isset($dataSoal['gambar_path']) && Storage::disk('public')->exists($dataSoal['gambar_path'])) {
                        $tempPath = $dataSoal['gambar_path'];
                        $gambarPathBaru = str_replace('temp_soal/', 'soal/', $tempPath);
                        Storage::disk('public')->move($tempPath, $gambarPathBaru);
                    }

                    Soal::create([
                        'ujian_id' => $ujian->id,
                        'tipe' => $dataSoal['tipe'] ?? 'pilihan_ganda',
                        'pertanyaan' => $dataSoal['pertanyaan'],
                        'gambar' => $gambarPathBaru,
                        'opsi_a' => $dataSoal['opsi_a'],
                        'opsi_b' => $dataSoal['opsi_b'],
                        'opsi_c' => $dataSoal['opsi_c'],
                        'opsi_d' => $dataSoal['opsi_d'],
                        'kunci_jawaban' => $dataSoal['kunci_jawaban'],
                        'data_soal' => $dataSoal['data_soal'] ?? null,
                    ]);
                }
                DB::commit();
                session()->forget(['ujian_temp_details', 'ujian_temp_soals']);

                return redirect()->route('guru.mapel.dashboard', $mapel->id)->with('success', 'Ujian berhasil disimpan!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error Simpan Ujian: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Terjadi kesalahan sistem.');
            }
        }
    }

    public function createSoal()
{
    $ujianDetails = session('ujian_temp_details');
    if (!$ujianDetails) {
        return redirect()->route('guru.index')->with('error', 'Sesi ujian berakhir.');
    }

    // AMBIL DATA MAPEL BERDASARKAN ID DI SESSION
    $mapel = Mapel::findOrFail($ujianDetails['mapel_id']);
    
    $tempSoals = session('ujian_temp_soals', []);
    $ujian = null;

    // Kirim $mapel ke view
    return view('guru.mapel.tambah_soal', compact('tempSoals', 'ujian', 'mapel'));
}

    public function storeSoalToSession(Request $request, Ujian $ujian = null)
    {
        $ujianDetails = session('ujian_temp_details');
        if (!$ujianDetails) return redirect()->route('guru.index');

        $validated = $request->validate([
            'soal' => 'required|array',
            'soal.*.tipe' => 'required|in:pilihan_ganda,benar_salah,jawaban_ganda,menjodohkan',
            'soal.*.pertanyaan' => 'required',
            'soal.*.gambar' => 'nullable|image|max:2048',
        ]);

        $soalsLama = session('ujian_temp_soals', []);
        $soalsBaru = [];
        $inputs = $request->input('soal');

        foreach ($inputs as $index => $dataSoal) {
            $tipe = $dataSoal['tipe'];
            
            // 1. Handle Gambar
            $gambarPath = null;
            if ($request->hasFile("soal.{$index}.gambar")) {
                $gambarPath = $request->file("soal.{$index}.gambar")->store('temp_soal', 'public');
            } else {
                // Pertahankan gambar lama jika ada (dari session)
                // Note: Index form mungkin beda dengan index session kalau ada delete. 
                // Tapi asumsi index urut dari 0..N krn reindex di JS. 
                // Better approach: use hidden input for old path or assume index match.
                // Disini kita asumsi index match atau replace all.
                $gambarPath = $soalsLama[$index]['gambar_path'] ?? null;
            }
            $dataSoal['gambar_path'] = $gambarPath;

            // 2. Format Data Berdasarkan Tipe
            $formatted = [
                'tipe' => $tipe,
                'pertanyaan' => $dataSoal['pertanyaan'],
                'gambar_path' => $gambarPath,
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null,
                'kunci_jawaban' => null,
                'data_soal' => null
            ];

            if ($tipe == 'pilihan_ganda') {
                $formatted['opsi_a'] = $dataSoal['opsi_a'] ?? '-';
                $formatted['opsi_b'] = $dataSoal['opsi_b'] ?? '-';
                $formatted['opsi_c'] = $dataSoal['opsi_c'] ?? '-';
                $formatted['opsi_d'] = $dataSoal['opsi_d'] ?? '-';
                $formatted['kunci_jawaban'] = $dataSoal['kunci_jawaban'] ?? '';
            } 
            elseif ($tipe == 'benar_salah') {
                $formatted['opsi_a'] = 'Benar';
                $formatted['opsi_b'] = 'Salah';
                $formatted['kunci_jawaban'] = $dataSoal['kunci_jawaban_bs'] ?? 'TRUE';
            }
            elseif ($tipe == 'jawaban_ganda') {
                $formatted['opsi_a'] = $dataSoal['opsi_a_jg'] ?? '-';
                $formatted['opsi_b'] = $dataSoal['opsi_b_jg'] ?? '-';
                $formatted['opsi_c'] = $dataSoal['opsi_c_jg'] ?? '-';
                $formatted['opsi_d'] = $dataSoal['opsi_d_jg'] ?? '-';
                // Kunci Jawaban Array -> String
                $kunci = $dataSoal['kunci_jawaban_jg'] ?? [];
                $formatted['kunci_jawaban'] = implode(',', $kunci);
            }
            elseif ($tipe == 'menjodohkan') {
                // Simpan pasangan di data_soal JSON
                $matches = $dataSoal['matches'] ?? [];
                // Bersihkan array keys
                $matches = array_values($matches);
                $formatted['data_soal'] = ['matches' => $matches];
                $formatted['kunci_jawaban'] = 'MATCHING'; // Placeholder
            }

            $soalsBaru[] = $formatted;
        }

        session(['ujian_temp_soals' => $soalsBaru]);

        if ($request->has('ujian') && $request->input('ujian') != null) {
            return redirect()->route('guru.mapel.ujian.edit', $request->input('ujian'))->with('success', 'Soal tersimpan sementara.');
        } else {
            return redirect()->route('guru.mapel.ujian.review', $ujianDetails['mapel_id'])->with('success', 'Soal tersimpan sementara.');
        }
    }

    public function editUjian(Ujian $ujian)
    {
        if ($ujian->guru_id != Auth::id()) return redirect()->back();

        $now = Carbon::now('Asia/Jakarta');
        $isOngoing = ($ujian->waktu_mulai <= $now && $ujian->waktu_selesai >= $now);
        $isFinished = ($ujian->waktu_selesai < $now);

        if (!session('ujian_temp_details')) {
            $ujianDetails = [
                'nama_ujian' => $ujian->nama_ujian,
                'jenis_ujian' => $ujian->jenis_ujian,
                'tanggal_ujian' => $ujian->tanggal_ujian,
                'waktu_mulai' => Carbon::parse($ujian->waktu_mulai)->format('H:i'),
                'waktu_selesai' => Carbon::parse($ujian->waktu_selesai)->format('H:i'),
                'durasi_menit' => $ujian->durasi_menit,
                'mapel_id' => $ujian->mapel_id
            ];
            session(['ujian_temp_details' => $ujianDetails]);
        }
        
        if (!session('ujian_temp_soals')) {
             $tempSoals = $ujian->soals->map(function ($soal) {
                return [
                    'tipe' => $soal->tipe,
                    'pertanyaan' => $soal->pertanyaan,
                    'gambar_path' => $soal->gambar,
                    'opsi_a' => $soal->opsi_a, 
                    'opsi_b' => $soal->opsi_b,
                    'opsi_c' => $soal->opsi_c, 
                    'opsi_d' => $soal->opsi_d, 
                    'kunci_jawaban' => $soal->kunci_jawaban,
                    'data_soal' => $soal->data_soal,
                ];
            })->toArray();
            session(['ujian_temp_soals' => $tempSoals]);
        }

        $tempSoals = session('ujian_temp_soals');
        $jumlahSoal = count($tempSoals);
        $mapel = $ujian->mapel;
        $ujianDetails = session('ujian_temp_details');

        return view('guru.mapel.create_ujian', compact('mapel', 'ujianDetails', 'jumlahSoal', 'ujian', 'isOngoing', 'isFinished'));
    }

    public function updateUjian(Request $request, Mapel $mapel, ?Ujian $ujian = null)
    {
        $now = Carbon::now('Asia/Jakarta');
        $isOngoing = ($ujian && $ujian->waktu_mulai <= $now && $ujian->waktu_selesai >= $now);
        $isFinished = ($ujian && $ujian->waktu_selesai < $now);

        if ($isFinished) return back()->with('error', 'Ujian selesai, tidak bisa diedit.');

        $rules = ['waktu_selesai' => 'required|date_format:H:i'];
        if (!$isOngoing) {
            $rules += [
                'nama_ujian' => 'required|string',
                'jenis_ujian' => 'required',
                'tanggal_ujian' => 'required|date',
                'waktu_mulai' => 'required|date_format:H:i',
            ];
        }
        $validated = $request->validate($rules);

        if ($isOngoing) {
            if ($request->input('action') == 'tambah_soal') return back()->with('error', 'Tidak bisa edit soal saat ujian berlangsung.');
            
            $start = new \DateTime(Carbon::parse($ujian->waktu_mulai)->format('H:i'));
            $end = new \DateTime($validated['waktu_selesai']);
            $diff = $start->diff($end);
            $durasi = ($diff->h * 60) + $diff->i;

            $ujianDataDB = [
                'waktu_selesai' => $ujian->tanggal_ujian . ' ' . $validated['waktu_selesai'] . ':00',
                'durasi_menit' => $durasi,
            ];
        } else {
            $start = new \DateTime($validated['waktu_mulai']);
            $end = new \DateTime($validated['waktu_selesai']);
            $diff = $start->diff($end);
            $durasi = ($diff->h * 60) + $diff->i;

            $ujianDataDB = [
                'nama_ujian' => $validated['nama_ujian'],
                'jenis_ujian' => $validated['jenis_ujian'],
                'tanggal_ujian' => $validated['tanggal_ujian'],
                'waktu_mulai' => $validated['tanggal_ujian'] . ' ' . $validated['waktu_mulai'] . ':00',
                'waktu_selesai' => $validated['tanggal_ujian'] . ' ' . $validated['waktu_selesai'] . ':00',
                'durasi_menit' => $durasi,
            ];
        }

        if ($request->input('action') == 'tambah_soal') {
            return redirect()->route('guru.mapel.soal.show', ['ujian' => $ujian->id]);
        } elseif ($request->input('action') == 'simpan_ujian') {
            try {
                DB::beginTransaction();
                $ujian->update($ujianDataDB);

                if (!$isOngoing) {
                    $ujian->soals()->delete(); 
                    $tempSoals = session('ujian_temp_soals', []);
                    foreach ($tempSoals as $dataSoal) {
                        $gambarPath = $dataSoal['gambar_path'] ?? null;
                        if ($gambarPath && str_starts_with($gambarPath, 'temp/')) {
                            $newPath = str_replace('temp_soal/', 'soal/', $gambarPath);
                            Storage::disk('public')->move($gambarPath, $newPath);
                            $gambarPath = $newPath;
                        }
                        Soal::create(array_merge($dataSoal, ['ujian_id' => $ujian->id, 'gambar' => $gambarPath]));
                    }
                }
                DB::commit();
                session()->forget(['ujian_temp_details', 'ujian_temp_soals']);
                return redirect()->route('guru.mapel.dashboard', $mapel->id)->with('success', 'Perubahan disimpan.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Update Waktu Ujian (Modal)
     * Menambah atau mengurangi waktu selesai ujian yang sedang berlangsung.
     */
    public function updateWaktu(Request $request, Ujian $ujian)
    {
        // 1. Validasi
        if ($ujian->guru_id != Auth::id()) return back()->with('error', 'Akses ditolak.');

        $request->validate([
            'tambahan_menit' => 'required|integer', // Bisa negatif untuk mengurangi
        ]);

        $tambahan = (int) $request->tambahan_menit;

        // 2. Hitung Waktu Baru
        $currentEnd = Carbon::parse($ujian->waktu_selesai);
        $newEnd = $currentEnd->copy()->addMinutes($tambahan);

        // Jangan sampai waktu selesai kurang dari waktu mulai
        $start = Carbon::parse($ujian->waktu_mulai);
        if ($newEnd <= $start) {
            return back()->with('error', 'Waktu selesai tidak boleh kurang dari waktu mulai.');
        }

        // 3. Update DB
        $ujian->waktu_selesai = $newEnd->format('Y-m-d H:i:s');
        
        // Update Durasi Total
        $diff = $start->diff($newEnd);
        $ujian->durasi_menit = ($diff->h * 60) + $diff->i;
        
        $ujian->save();

        return back()->with('success', 'Waktu ujian berhasil diperbarui.');
    }

    public function showSoalForm(Ujian $ujian = null)
{
    $ujianDetails = session('ujian_temp_details');
    if (!$ujianDetails) return redirect()->route('guru.index');

    // AMBIL DATA MAPEL BERDASARKAN ID DI SESSION
    $mapel = Mapel::findOrFail($ujianDetails['mapel_id']);

    $tempSoals = session('ujian_temp_soals', []);

    // Kirim $mapel ke view
    return view('guru.mapel.tambah_soal', compact('tempSoals', 'ujian', 'mapel'));
}

    public function destroyUjian(Ujian $ujian)
    {
        if ($ujian->guru_id != Auth::id()) return back()->with('error', 'Akses ditolak.');
        $mapelId = $ujian->mapel_id;
        $ujian->soals()->delete();
        $ujian->delete();
        return redirect()->route('guru.mapel.dashboard', $mapelId)->with('success', 'Ujian dihapus.');
    }

    public function indexBankSoal(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) return back()->with('error', 'Akses ditolak.');
        $bankSoals = BankSoal::where('mapel_id', $mapel->id)->where('guru_id', Auth::id())->get();
        return view('guru.mapel.bank_soal', compact('mapel', 'bankSoals'));
    }

    public function handleBankSoal(Request $request, Mapel $mapel)
    {
        $request->validate(['new_files.*.file' => 'required|mimes:pdf|max:5120']);
        
        try {
            DB::beginTransaction();
            if ($request->has('new_files')) {
                foreach ($request->file('new_files') as $idx => $newFile) {
                    $path = $newFile['file']->store('bank_soal/' . $mapel->id, 'public');
                    BankSoal::create([
                        'guru_id' => Auth::id(),
                        'mapel_id' => $mapel->id,
                        'nama' => $request->new_files[$idx]['nama'],
                        'file_path' => $path,
                        'visibilitas' => $request->new_files[$idx]['visibilitas'],
                    ]);
                }
            }
            if ($request->has('existing')) {
                foreach ($request->existing as $id => $data) {
                    BankSoal::findOrFail($id)->update($data);
                }
            }
            DB::commit();
            return back()->with('success', 'Bank soal diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update bank soal.');
        }
    }

    public function destroyBankSoal(BankSoal $bankSoal)
    {
        if ($bankSoal->guru_id != Auth::id()) return back();
        if (Storage::disk('public')->exists($bankSoal->file_path)) Storage::disk('public')->delete($bankSoal->file_path);
        $bankSoal->delete();
        return back()->with('success', 'File dihapus.');
    }
}