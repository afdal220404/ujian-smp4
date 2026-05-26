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
use App\Models\ArsipSoalSiswa;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa; 
use Carbon\Carbon;

function getTahunAjaran() {
    $now = Carbon::now('Asia/Jakarta');
    $month = $now->month;
    $year = $now->year;
    if ($month >= 7) {
        return $year . '/' . ($year + 1);
    } else {
        return ($year - 1) . '/' . $year;
    }
}

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
        $currentTahunAjaran = $now->month >= 7 
            ? $now->year . '/' . ($now->year + 1) 
            : ($now->year - 1) . '/' . $now->year;

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
            ->where('is_susulan', false)
            ->orderBy('waktu_mulai', 'desc')
            ->get()
            ->groupBy('tahun_ajaran');

        return view('guru.mapel.dashboard', compact(
            'guru', 'mapel', 'kelas', 'jumlahSiswa',
            'ongoingUjian', 'upcomingUjian', 'historyUjian',
            'avgKuis', 'avgUTS', 'avgUAS', 'currentTahunAjaran'
        ));
    }

    /**
     * Menampilkan daftar siswa dan nilai rekap per mapel.
     * Sudah menggunakan data real di iterasi sebelumnya.
     */
    public function showSiswa(Mapel $mapel)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;
        if ($mapel->guru_id != $guruId) abort(403, 'Akses ditolak.');

        // AMBIL SEMUA UJIAN INDUK SECARA DINAMIS
        $daftarKuisMaster = Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'Kuis')->where('is_susulan', false)->orderBy('created_at', 'asc')->get();
        $daftarUtsMaster  = Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'UTS')->where('is_susulan', false)->orderBy('created_at', 'asc')->get();
        $daftarUasMaster  = Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'UAS')->where('is_susulan', false)->orderBy('created_at', 'asc')->get();

        $maxKuis = $daftarKuisMaster->count() ?: 1;
        $maxUts  = $daftarUtsMaster->count() ?: 1;
        $maxUas  = $daftarUasMaster->count() ?: 1;

        $siswasRaw = Siswa::where('kelas_id', $mapel->kelas_id)->orderBy('nama_lengkap')->get();

        $siswas = $siswasRaw->map(function($siswa) use ($mapel, $daftarKuisMaster, $daftarUtsMaster, $daftarUasMaster) {
            
            // 1. PROSES KUIS
            $listKuis = [];
            foreach($daftarKuisMaster as $kuisMaster) {
                $semuaIdUjianTerkait = Ujian::where('id', $kuisMaster->id)->orWhere('ujian_induk_id', $kuisMaster->id)->pluck('id')->toArray();
                $nilai = HasilUjian::where('siswa_id', $siswa->id)->whereIn('ujian_id', $semuaIdUjianTerkait)->orderBy('created_at', 'desc')->value('nilai');
                $listKuis[] = $nilai !== null ? $nilai : '-';
            }
            $nilaiKuisValid = array_filter($listKuis, fn($v) => is_numeric($v));
            $rataKuis = count($nilaiKuisValid) > 0 ? array_sum($nilaiKuisValid) / count($nilaiKuisValid) : 0;

            // 2. PROSES UTS (Dibuat Dinamis)
            $listUts = [];
            foreach($daftarUtsMaster as $utsMaster) {
                $semuaIdUjianTerkait = Ujian::where('id', $utsMaster->id)->orWhere('ujian_induk_id', $utsMaster->id)->pluck('id')->toArray();
                $nilai = HasilUjian::where('siswa_id', $siswa->id)->whereIn('ujian_id', $semuaIdUjianTerkait)->orderBy('created_at', 'desc')->value('nilai');
                $listUts[] = $nilai !== null ? $nilai : '-';
            }
            $nilaiUtsValid = array_filter($listUts, fn($v) => is_numeric($v));
            $rataUts = count($nilaiUtsValid) > 0 ? array_sum($nilaiUtsValid) / count($nilaiUtsValid) : 0;

            // 3. PROSES UAS (Dibuat Dinamis)
            $listUas = [];
            foreach($daftarUasMaster as $uasMaster) {
                $semuaIdUjianTerkait = Ujian::where('id', $uasMaster->id)->orWhere('ujian_induk_id', $uasMaster->id)->pluck('id')->toArray();
                $nilai = HasilUjian::where('siswa_id', $siswa->id)->whereIn('ujian_id', $semuaIdUjianTerkait)->orderBy('created_at', 'desc')->value('nilai');
                $listUas[] = $nilai !== null ? $nilai : '-';
            }
            $nilaiUasValid = array_filter($listUas, fn($v) => is_numeric($v));
            $rataUas = count($nilaiUasValid) > 0 ? array_sum($nilaiUasValid) / count($nilaiUasValid) : 0;

            // 4. HITUNG NILAI AKHIR (Dinamis berdasarkan jenis ujian yang sudah ada)
            $totalKomponen = 0;
            $totalNilai = 0;

            // Jika guru sudah pernah membuat Kuis, masukkan ke perhitungan
            if ($daftarKuisMaster->count() > 0) {
                $totalKomponen++;
                $totalNilai += $rataKuis;
            }

            // Jika guru sudah pernah membuat UTS, masukkan ke perhitungan
            if ($daftarUtsMaster->count() > 0) {
                $totalKomponen++;
                $totalNilai += $rataUts;
            }

            // Jika guru sudah pernah membuat UAS, masukkan ke perhitungan
            if ($daftarUasMaster->count() > 0) {
                $totalKomponen++;
                $totalNilai += $rataUas;
            }

            // Hitung nilai akhir: Total nilai dibagi jumlah komponen yang aktif
            $akhir = $totalKomponen > 0 ? ($totalNilai / $totalKomponen) : 0;

            return (object) [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap, 
                'nisn' => $siswa->nisn,
                'list_kuis' => $listKuis,
                'list_uts'  => $listUts,
                'list_uas'  => $listUas,
                'rata_kuis' => number_format($rataKuis, 1),
                'rata_uts'  => number_format($rataUts, 1),
                'rata_uas'  => number_format($rataUas, 1),
                'nilai_akhir' => number_format($akhir, 1),
                'grade_raw' => $akhir
            ];
        });

        // Pastikan view dikirimi variabel maxUts dan maxUas
        return view('guru.mapel.daftar_siswa', compact('mapel', 'siswas', 'maxKuis', 'maxUts', 'maxUas'));
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
        $querySiswa = Siswa::where('kelas_id', $mapel->kelas_id);

        // Jika ini ujian susulan, maka peserta hanya yang terdaftar di kolom peserta_susulan saja
        if ($ujian->is_susulan && !empty($ujian->peserta_susulan)) {
            $querySiswa->whereIn('id', $ujian->peserta_susulan);
        }

        $semuaSiswa = $querySiswa->orderBy('nama_lengkap', 'asc')->get();
        
        // Hitung Total Soal untuk referensi statistik
        $totalSoalUjian = $ujian->soals()->count();
        if ($totalSoalUjian == 0) $totalSoalUjian = 1; 

        // 3. Ambil Hasil Ujian Real (Merge dengan susulan jika ini induk)
        $allUjianIds = [$ujian->id];
        if (!$ujian->is_susulan) {
            $susulanIds = Ujian::where('ujian_induk_id', $ujian->id)->pluck('id')->toArray();
            $allUjianIds = array_merge($allUjianIds, $susulanIds);
        }

        $hasilUjianReal = HasilUjian::whereIn('ujian_id', $allUjianIds)
                        ->with('siswa')
                        ->get()
                        // Prioritaskan hasil susulan jika ada duplikasi sebelum di-unique
                        ->sortByDesc(function($hasil) use ($ujian) {
                            return $hasil->ujian_id != $ujian->id;
                        })
                        ->unique('siswa_id')
                        // Urutkan alfabet nama siswa
                        ->sortBy(function($hasil) {
                            return $hasil->siswa->nama_lengkap;
                        })
                        ->values(); // Reset key agar penomoran $index di Blade berurutan 1, 2, 3...

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

        // 6. Validasi Kelayakan Susulan
        $now = Carbon::now('Asia/Jakarta');
        $isIndukOngoing = ($ujian->waktu_mulai <= $now && $ujian->waktu_selesai >= $now);
        $hasActiveSusulan = $ujian->ujianSusulans()->where('waktu_selesai', '>', $now)->exists();

        return view('guru.mapel.detail_ujian', compact(
            'ujian', 'mapel', 'kelas',
            'hasilUjian', 'siswaBelum', 
            'totalSiswa', 'sudahMengerjakan', 'belumMengerjakan',
            'isIndukOngoing', 'hasActiveSusulan'
        ));
    }

    /**
     * Menampilkan detail analisis soal dan perincian siapa saja yang menjawab benar atau salah.
     */
    public function showAnalisisSoal(Ujian $ujian)
    {
        $user = Auth::user();
        $mapel = $ujian->mapel;
        $kelas = $mapel->kelas;
        
        $guruId = $user->guru ? $user->guru->id : $user->id;
        if ($mapel->guru_id != $guruId) {
            abort(403, 'Akses ditolak.');
        }

        // 1. Gabungkan ID Ujian Induk dan Susulan
        $allUjianIds = [$ujian->id];
        if (!$ujian->is_susulan) {
            $susulanIds = Ujian::where('ujian_induk_id', $ujian->id)->pluck('id')->toArray();
            $allUjianIds = array_merge($allUjianIds, $susulanIds);
        }

        // 2. Ambil soal dari SEMUA ujian terkait, deduplikasi berdasarkan bank_soal_id.
        //    Ini memastikan soal dari susulan (yang punya ID berbeda tapi bank_soal_id sama)
        //    tetap terdeteksi dengan benar saat mencocokkan jawaban.
        $semuaSoalRaw = DB::table('soals')
            ->join('bank_soal_items', 'soals.bank_soal_id', '=', 'bank_soal_items.id')
            ->whereIn('soals.ujian_id', $allUjianIds)
            ->select('soals.id as pivot_id', 'soals.bank_soal_id', 'bank_soal_items.*')
            ->get();

        // Deduplikasi: ambil satu soal per bank_soal_id (prioritaskan dari ujian induk)
        $semuaSoal = $semuaSoalRaw
            ->sortBy(function($s) use ($ujian) {
                // Prioritaskan soal dari ujian induk (ujian->id)
                return $s->pivot_id; // soal induk dibuat lebih dulu (ID lebih kecil)
            })
            ->unique('bank_soal_id')
            ->values();

        // 3. Ambil semua HasilUjian (dari induk dan susulan), prioritaskan susulan jika duplikasi
        $hasilUjians = HasilUjian::whereIn('ujian_id', $allUjianIds)
                        ->with('siswa')
                        ->get()
                        ->sortByDesc(function($h) use ($ujian) {
                            // Prioritaskan susulan agar hasil susulan meng-override induk saat unique
                            return $h->ujian_id != $ujian->id;
                        })
                        ->unique('siswa_id')
                        ->sortBy(function($h) {
                            return $h->siswa->nama_lengkap ?? '';
                        })
                        ->values();
        
        $hasilUjianIds = $hasilUjians->pluck('id')->toArray();

        // 4. Tarik semua jawaban siswa, JOIN dengan soals untuk mendapat bank_soal_id
        $semuaJawaban = DB::table('jawaban_siswas')
            ->join('soals', 'jawaban_siswas.soal_id', '=', 'soals.id')
            ->whereIn('jawaban_siswas.hasil_ujian_id', $hasilUjianIds)
            ->select('jawaban_siswas.*', 'soals.bank_soal_id')
            ->get();

        $analisis = [];
        foreach ($semuaSoal as $soal) {
            
            // Cocokkan menggunakan bank_soal_id agar induk dan susulan bisa saling cocok
            $jawabanSoalDb = $semuaJawaban->where('bank_soal_id', $soal->bank_soal_id);
            
            $benar = [];
            $salah = [];
            $hasilSudahDiproses = []; // ID HasilUjian yang sudah diproses untuk soal ini

            foreach ($jawabanSoalDb as $jawabanDb) {
                $hasil = $hasilUjians->where('id', $jawabanDb->hasil_ujian_id)->first();
                if ($hasil && $hasil->siswa) {
                    $isBenar = ($jawabanDb->is_correct == 1 || $jawabanDb->is_correct == true);
                    
                    if ($isBenar) {
                        $benar[] = $hasil->siswa;
                    } else {
                        $salah[] = $hasil->siswa;
                    }
                    $hasilSudahDiproses[] = $hasil->id;
                }
            }
            
            // Siswa yang tidak punya jawaban untuk soal ini dihitung sebagai salah
            foreach ($hasilUjians as $hasil) {
                if ($hasil->siswa && !in_array($hasil->id, $hasilSudahDiproses)) {
                    $salah[] = $hasil->siswa;
                }
            }

            $analisis[] = [
                'soal' => $soal,
                'jumlah_benar' => count($benar),
                'jumlah_salah' => count($salah),
                'siswa_benar' => collect($benar),
                'siswa_salah' => collect($salah)
            ];
        }

        return view('guru.mapel.analisis_soal', compact('ujian', 'mapel', 'kelas', 'analisis'));
    }


    /**
     * Menampilkan detail jawaban spesifik per siswa.
     * UPDATE: Menggunakan relasi tabel hasil_ujians -> jawaban_siswas.
     */
    public function showSiswaUjianDetail(Ujian $ujian, Siswa $siswa)
    {
        $mapel = $ujian->mapel;
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }

        // === LANGKAH 1: Cari HasilUjian LEBIH DULU ===
        // Cakup kemungkinan siswa mengerjakan di ujian induk ATAU susulan
        $allUjianIds = [$ujian->id];
        if (!$ujian->is_susulan) {
            $susulanIds = Ujian::where('ujian_induk_id', $ujian->id)->pluck('id')->toArray();
            $allUjianIds = array_merge($allUjianIds, $susulanIds);
        } else {
            if ($ujian->ujian_induk_id) {
                $allUjianIds[] = $ujian->ujian_induk_id;
            }
        }

        $hasilUjian = HasilUjian::whereIn('ujian_id', $allUjianIds)
                        ->where('siswa_id', $siswa->id)
                        ->first();

        // === LANGKAH 2: Tentukan ujian mana yang digunakan siswa ===
        // Jika siswa mengerjakan susulan, hasilUjian->ujian_id = ID susulan.
        // Soal harus diambil dari ujian yang sama agar soal_id cocok dengan jawaban_siswas.soal_id.
        $ujianIdUntukSoal = $hasilUjian ? $hasilUjian->ujian_id : $ujian->id;

        // === LANGKAH 3: Ambil soal dari ujian yang TEPAT ===
        $semuaSoal = DB::table('soals')
            ->join('bank_soal_items', 'soals.bank_soal_id', '=', 'bank_soal_items.id')
            ->where('soals.ujian_id', $ujianIdUntukSoal)
            ->select('soals.id as pivot_id', 'soals.bank_soal_id', 'bank_soal_items.*')
            ->get();

        $jumlahTotalSoal = $semuaSoal->count();

        // === LANGKAH 4: Ambil jawaban siswa dan match by soal_id langsung (lebih reliable) ===
        $listJawabanSiswa = collect();
        if ($hasilUjian) {
            $listJawabanSiswa = DB::table('jawaban_siswas')
                ->where('hasil_ujian_id', $hasilUjian->id)
                ->get()
                ->keyBy('soal_id'); // Key by soal_id langsung (karena soal dari ujian yang sama)
        }

        $daftarSoal = [];
        $jumlahBenarLoop = 0;

        foreach ($semuaSoal as $soal) {
            // Cocokkan menggunakan pivot_id (soals.id) langsung — lebih reliable dari bank_soal_id
            $jawabanDb = $listJawabanSiswa->get($soal->pivot_id);

            $jawabanSiswa = $jawabanDb ? $jawabanDb->jawaban_dipilih : null;

            $isBenar = false;
            if ($jawabanDb) {
                $isBenar = ($jawabanDb->is_correct == 1 || $jawabanDb->is_correct == true);
            }

            if ($isBenar) $jumlahBenarLoop++;

            $soal->jawaban_siswa = $jawabanSiswa;
            $soal->status_jawaban = $isBenar;

            $daftarSoal[] = $soal;
        }

        // === LANGKAH 5: Gunakan jumlah_benar dari DB sebagai sumber otoritatif ===
        // Ini menjamin konsistensi dengan halaman Detail Ujian yang juga pakai kolom DB.
        // Fallback ke hasil loop jika kolom DB null (data lama).
        $jumlahBenar = ($hasilUjian && !is_null($hasilUjian->jumlah_benar))
            ? (int) $hasilUjian->jumlah_benar
            : $jumlahBenarLoop;

        $nilai = $hasilUjian ? $hasilUjian->nilai : 0;

        return view('guru.mapel.detail_jawaban_siswa', compact(
            'ujian', 'siswa', 'mapel',
            'daftarSoal', 'jumlahBenar', 'nilai', 'jumlahTotalSoal', 'hasilUjian'
        ));
    }

    // --- FUNGSI CRUD UJIAN & BANK SOAL (TIDAK PERLU DUMMY, SUDAH REAL) ---

    public function createUjian(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) {
            return redirect()->route('guru.index')->with('error', 'Akses ditolak.');
        }
        session()->forget(['ujian_temp_details', 'ujian_temp_soals', 'editing_ujian_id']);
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
                    // Smart Archiving - findOrCreateBankSoal handles all image moving
                    $bankSoalId = $this->findOrCreateBankSoal($dataSoal, $mapel->id);

                    // Simpan ke Pivot Soal
                    Soal::create([
                        'ujian_id' => $ujian->id,
                        'bank_soal_id' => $bankSoalId,
                    ]);
                }
                DB::commit();
                session()->forget(['ujian_temp_details', 'ujian_temp_soals', 'editing_ujian_id']);

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
            
            // 1. Handle Main Soal Image
            $gambarPath = null;
            if ($request->hasFile("soal.{$index}.gambar")) {
                $gambarPath = $request->file("soal.{$index}.gambar")->store('temp_soal', 'public');
            } else {
                $existingMain = $dataSoal['existing_gambar'] ?? null;
                // Jika input hidden ada isinya, pakai itu. Jika tidak, ambil dari session lama.
                $gambarPath = !empty($existingMain) ? $existingMain : ($soalsLama[$index]['gambar_path'] ?? null);
            }
            $dataSoal['gambar_path'] = $gambarPath;

            // 2. Format Data Berdasarkan Tipe
            $formatted = [
                'tipe' => $tipe,
                'pertanyaan' => $dataSoal['pertanyaan'],
                'gambar_path' => $gambarPath,
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null,
                'gambar_a_path' => null, 'gambar_b_path' => null, 'gambar_c_path' => null, 'gambar_d_path' => null,
                'kunci_jawaban' => null,
                'data_soal' => null,
                'bank_soal_id' => $dataSoal['bank_soal_id'] ?? ($soalsLama[$index]['bank_soal_id'] ?? null),
            ];

            if ($tipe == 'pilihan_ganda') {
                foreach(['a','b','c','d'] as $o) {
                    $formatted["opsi_$o"] = $dataSoal["opsi_$o"] ?? '-';
                    
                    // Option Images
                    if ($request->hasFile("soal.{$index}.gambar_$o")) {
                        // Jika ada gambar baru yang diupload
                        $path = $request->file("soal.{$index}.gambar_$o")->store('temp_soal', 'public');
                        $formatted["gambar_{$o}_path"] = $path;
                        $formatted["gambar_$o"] = $path; // Duplikat key
                    } else {
                        // Jika tidak ada upload, ambil data lama & hindari bug string kosong ("")
                        $existing = $dataSoal["existing_gambar_$o"] ?? null;
                        
                        if (empty($existing)) {
                            // Jika hidden input kosong, paksa tarik dari session lama
                            $existing = $soalsLama[$index]["gambar_{$o}_path"] ?? ($soalsLama[$index]["gambar_$o"] ?? null);
                        }
                        
                        $formatted["gambar_{$o}_path"] = $existing;
                        $formatted["gambar_$o"] = $existing; // Duplikat key
                    }
                }
                $formatted['kunci_jawaban'] = $dataSoal['kunci_jawaban'] ?? '';
            }
            elseif ($tipe == 'benar_salah') {
                $pernyataan = [];
                foreach ($dataSoal['bs_pernyataan'] ?? [] as $jidx => $opt) {
                    $optPath = null;
                    if ($request->hasFile("soal.{$index}.bs_pernyataan.{$jidx}.gambar")) {
                        $optPath = $request->file("soal.{$index}.bs_pernyataan.{$jidx}.gambar")->store('temp_soal', 'public');
                    } else {
                        // Priority 1: From form (imported or hidden field)
                        if (isset($opt['existing_gambar'])) {
                            $optPath = $opt['existing_gambar'];
                        } else {
                            // Priority 2: From session (older save)
                            $oldBS = $soalsLama[$index]['data_soal']['pernyataan'] ?? ($soalsLama[$index]['data_soal']['options'] ?? []);
                            foreach($oldBS as $oldP) {
                                if(($oldP['id'] ?? null) == $jidx || ($oldP['text'] ?? '') == ($opt['text'] ?? '')) 
                                    $optPath = $oldP['gambar'] ?? null;
                            }
                        }
                    }
                    $pernyataan[] = [
                        'id' => is_numeric($jidx) ? (int)$jidx : $jidx,
                        'text' => $opt['text'] ?? '',
                        'gambar' => $optPath,
                        'correct' => $opt['correct'] ?? 'TRUE'
                    ];
                }
                $formatted['data_soal'] = ['pernyataan' => $pernyataan];
                $formatted['kunci_jawaban'] = 'COMPLEX_TF';
            }
            elseif ($tipe == 'jawaban_ganda') {
                $jgOptions = [];
                $labels = range('A', 'Z');
                $count = 0;
                foreach ($dataSoal['jg_options'] ?? [] as $jidx => $opt) {
                    $oLabel = $labels[$count++] ?? "X$count";
                    $optPath = null;
                    if ($request->hasFile("soal.{$index}.jg_options.{$jidx}.gambar")) {
                        $optPath = $request->file("soal.{$index}.jg_options.{$jidx}.gambar")->store('temp_soal', 'public');
                    } else {
                        if (isset($opt['existing_gambar'])) {
                            $optPath = $opt['existing_gambar'];
                        } else {
                            $oldOptions = $soalsLama[$index]['data_soal']['options'] ?? [];
                            foreach($oldOptions as $oldOpt) {
                                if(($oldOpt['id'] ?? null) == $jidx || ($oldOpt['text'] ?? '') == ($opt['text'] ?? '')) 
                                    $optPath = $oldOpt['gambar'] ?? null;
                            }
                        }
                    }
                    $jgOptions[] = [
                        'id' => $oLabel,
                        'text' => $opt['text'] ?? '',
                        'gambar' => $optPath
                    ];
                }
                $formatted['data_soal'] = ['options' => $jgOptions];
                $formatted['kunci_jawaban'] = isset($dataSoal['kunci_jawaban_jg']) ? implode(',', $dataSoal['kunci_jawaban_jg']) : '';
            }
            elseif ($tipe == 'menjodohkan') {
                $matches = [];
                foreach ($dataSoal['matches'] ?? [] as $midx => $m) {
                    $imgL = null; $imgR = null;
                    // Left Image
                    if ($request->hasFile("soal.{$index}.matches.{$midx}.gambar_left")) {
                        $imgL = $request->file("soal.{$index}.matches.{$midx}.gambar_left")->store('temp_soal', 'public');
                    } else {
                        if (isset($m['existing_gambar_left'])) {
                            $imgL = $m['existing_gambar_left'];
                        } else {
                            $oldM = $soalsLama[$index]['data_soal']['matches'] ?? [];
                            foreach($oldM as $om) if(($om['left'] ?? '') == ($m['left'] ?? '')) $imgL = $om['gambar_left'] ?? null;
                        }
                    }
                    // Right Image
                    if ($request->hasFile("soal.{$index}.matches.{$midx}.gambar_right")) {
                        $imgR = $request->file("soal.{$index}.matches.{$midx}.gambar_right")->store('temp_soal', 'public');
                    } else {
                        if (isset($m['existing_gambar_right'])) {
                            $imgR = $m['existing_gambar_right'];
                        } else {
                            $oldM = $soalsLama[$index]['data_soal']['matches'] ?? [];
                            foreach($oldM as $om) if(($om['right'] ?? '') == ($m['right'] ?? '')) $imgR = $om['gambar_right'] ?? null;
                        }
                    }
                    $matches[] = [
                        'left' => $m['left'] ?? '',
                        'right' => $m['right'] ?? '',
                        'gambar_left' => $imgL,
                        'gambar_right' => $imgR
                    ];
                }
                $formatted['data_soal'] = ['matches' => $matches];
                $formatted['kunci_jawaban'] = 'MATCHING';
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

        // === FIX BUG: Cek apakah sesi saat ini milik ujian yang sama ===
        // Jika guru berpindah dari edit ujian A ke ujian B, sesi lama harus di-reset
        // agar tidak menampilkan data soal/detail dari ujian yang salah.
        $sessionUjianId = session('editing_ujian_id');
        if ($sessionUjianId !== $ujian->id) {
            // Ujian berbeda atau sesi baru — reset semua sesi terkait
            session()->forget(['ujian_temp_details', 'ujian_temp_soals', 'editing_ujian_id']);
        }

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
                    'bank_soal_id' => $soal->bank_soal_id,
                    'tipe' => $soal->tipe,
                    'pertanyaan' => $soal->pertanyaan,
                    'gambar_path' => $soal->gambar,
                    'opsi_a' => $soal->opsi_a, 
                    'opsi_b' => $soal->opsi_b,
                    'opsi_c' => $soal->opsi_c, 
                    'opsi_d' => $soal->opsi_d, 
                    'gambar_a_path' => $soal->gambar_a,
                    'gambar_b_path' => $soal->gambar_b,
                    'gambar_c_path' => $soal->gambar_c,
                    'gambar_d_path' => $soal->gambar_d,
                    'gambar_a' => $soal->gambar_a,
                    'gambar_b' => $soal->gambar_b,
                    'gambar_c' => $soal->gambar_c,
                    'gambar_d' => $soal->gambar_d,
                    'kunci_jawaban' => $soal->kunci_jawaban,
                    'data_soal' => $soal->data_soal,
                ];
            })->toArray();
            session(['ujian_temp_soals' => $tempSoals]);
        }

        // Tandai ujian mana yang sedang diedit di sesi
        session(['editing_ujian_id' => $ujian->id]);

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
                        // Update Main Image
                        if (isset($dataSoal['gambar_path']) && str_starts_with($dataSoal['gambar_path'], 'temp_soal/')) {
                            $newPath = str_replace('temp_soal/', 'soal/', $dataSoal['gambar_path']);
                            if (Storage::disk('public')->exists($dataSoal['gambar_path'])) {
                                Storage::disk('public')->move($dataSoal['gambar_path'], $newPath);
                            }
                            $dataSoal['gambar_path'] = $newPath;
                        }
                        
                        // Update Option Images
                        foreach(['a','b','c','d'] as $o) {
                            $pathKey = "gambar_{$o}_path";
                            if (isset($dataSoal[$pathKey]) && str_starts_with($dataSoal[$pathKey], 'temp_soal/')) {
                                $newPath = str_replace('temp_soal/', 'soal/', $dataSoal[$pathKey]);
                                if (Storage::disk('public')->exists($dataSoal[$pathKey])) {
                                    Storage::disk('public')->move($dataSoal[$pathKey], $newPath);
                                }
                                $dataSoal[$pathKey] = $newPath;
                            }
                        }

                        // Update Dynamic Option Images
                        if (isset($dataSoal['data_soal']['options'])) {
                            foreach ($dataSoal['data_soal']['options'] as &$opt) {
                                if (isset($opt['gambar']) && str_starts_with($opt['gambar'], 'temp_soal/')) {
                                    $newPath = str_replace('temp_soal/', 'soal/', $opt['gambar']);
                                    if (Storage::disk('public')->exists($opt['gambar'])) {
                                        Storage::disk('public')->move($opt['gambar'], $newPath);
                                    }
                                    $opt['gambar'] = $newPath;
                                }
                            }
                        }
                        
                        // Smart Archiving
                        $bankSoalId = $this->findOrCreateBankSoal($dataSoal, $mapel->id);

                        // Simpan ke Pivot Soal
                        Soal::create([
                            'ujian_id' => $ujian->id,
                            'bank_soal_id' => $bankSoalId,
                        ]);
                    }
                }
                DB::commit();
                session()->forget(['ujian_temp_details', 'ujian_temp_soals', 'editing_ujian_id']);
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

        // === FIX BUG: Verifikasi sesi cocok dengan ujian yang sedang dibuka ===
        // Jika guru langsung mengakses /ujian/{ujian}/soal tanpa melewati editUjian(),
        // sesi bisa berisi soal dari ujian lain (kelas berbeda pada guru yang sama).
        if ($ujian && session('editing_ujian_id') !== $ujian->id) {
            // Sesi tidak cocok — reload soal dari DB untuk ujian yang benar
            session()->forget(['ujian_temp_details', 'ujian_temp_soals', 'editing_ujian_id']);

            $ujianDetails = [
                'nama_ujian'    => $ujian->nama_ujian,
                'jenis_ujian'   => $ujian->jenis_ujian,
                'tanggal_ujian' => $ujian->tanggal_ujian,
                'waktu_mulai'   => Carbon::parse($ujian->waktu_mulai)->format('H:i'),
                'waktu_selesai' => Carbon::parse($ujian->waktu_selesai)->format('H:i'),
                'durasi_menit'  => $ujian->durasi_menit,
                'mapel_id'      => $ujian->mapel_id,
            ];

            $tempSoals = $ujian->soals->map(function ($soal) {
                return [
                    'bank_soal_id'  => $soal->bank_soal_id,
                    'tipe'          => $soal->tipe,
                    'pertanyaan'    => $soal->pertanyaan,
                    'gambar_path'   => $soal->gambar,
                    'opsi_a'        => $soal->opsi_a,
                    'opsi_b'        => $soal->opsi_b,
                    'opsi_c'        => $soal->opsi_c,
                    'opsi_d'        => $soal->opsi_d,
                    'gambar_a_path' => $soal->gambar_a,
                    'gambar_b_path' => $soal->gambar_b,
                    'gambar_c_path' => $soal->gambar_c,
                    'gambar_d_path' => $soal->gambar_d,
                    'gambar_a'      => $soal->gambar_a,
                    'gambar_b'      => $soal->gambar_b,
                    'gambar_c'      => $soal->gambar_c,
                    'gambar_d'      => $soal->gambar_d,
                    'kunci_jawaban' => $soal->kunci_jawaban,
                    'data_soal'     => $soal->data_soal,
                ];
            })->toArray();

            session([
                'ujian_temp_details'  => $ujianDetails,
                'ujian_temp_soals'    => $tempSoals,
                'editing_ujian_id'    => $ujian->id,
            ]);
        }

        // Ambil data mapel dari sesi (sudah dipastikan benar di atas)
        $ujianDetails = session('ujian_temp_details');
        $mapel = Mapel::findOrFail($ujianDetails['mapel_id']);
        $tempSoals = session('ujian_temp_soals', []);

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

    /**
     * TAMPILAN BANK SOAL (Items / Butir Soal)
     */
    public function indexBankSoal(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) return back()->with('error', 'Akses ditolak.');
        
        // Ambil butir soal (items) dari database bank_soal_items
        $soals = BankSoal::where('mapel_id', $mapel->id)
                    ->withCount('soals')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return view('guru.mapel.bank_soal_items', compact('mapel', 'soals'));
    }

    /**
     * API untuk ambil data bank soal (AJAX Import)
     */
    public function getBankSoalItems(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) return response()->json(['error' => 'Unauthorized'], 403);
        
        $items = BankSoal::where('mapel_id', $mapel->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return response()->json($items);
    }

    /**
     * SIMPAN SOAL KE BANK SOAL
     */
    public function storeBankSoal(Request $request, Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        $request->validate([
            'tipe' => 'required|string',
            'pertanyaan' => 'required|string',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban']);
        $data['mapel_id'] = $mapel->id;

        // Handle Soal Image
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('soal', 'public');
        }

        // Handle Option Images
        foreach (['a', 'b', 'c', 'd'] as $opsi) {
            if ($request->hasFile("gambar_$opsi")) {
                $data["gambar_$opsi"] = $request->file("gambar_$opsi")->store('soal', 'public');
            }
        }

        // Handle complex types in data_soal
        if (in_array($request->tipe, ['benar_salah', 'jawaban_ganda', 'menjodohkan'])) {
            $data['data_soal'] = $this->prepareDataSoal($request, $mapel->id);
            
            // Force legacy keys for kunci_jawaban
            if ($request->tipe === 'benar_salah') $data['kunci_jawaban'] = 'COMPLEX_TF';
            if ($request->tipe === 'menjodohkan') $data['kunci_jawaban'] = 'MATCHING';
        }

        BankSoal::create($data);

        return back()->with('success', 'Soal berhasil ditambahkan ke Bank Soal.');
    }

    /**
     * UPDATE SOAL DI BANK SOAL
     */
    public function updateBankSoal(Request $request, Mapel $mapel, BankSoal $bankSoal)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        $request->validate([
            'tipe' => 'required|string',
            'pertanyaan' => 'required|string',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban']);

        // Handle Soal Image
        if ($request->hasFile('gambar')) {
            if ($bankSoal->gambar) Storage::disk('public')->delete($bankSoal->gambar);
            $data['gambar'] = $request->file('gambar')->store('soal', 'public');
        }

        // Handle Option Images
        foreach (['a', 'b', 'c', 'd'] as $opsi) {
            if ($request->hasFile("gambar_$opsi")) {
                if ($bankSoal->{"gambar_$opsi"}) Storage::disk('public')->delete($bankSoal->{"gambar_$opsi"});
                $data["gambar_$opsi"] = $request->file("gambar_$opsi")->store('soal', 'public');
            }
        }

        // Handle complex types in data_soal
        if (in_array($request->tipe, ['benar_salah', 'jawaban_ganda', 'menjodohkan'])) {
            $data['data_soal'] = $this->prepareDataSoal($request, $mapel->id, $bankSoal->data_soal);
            
            // Force legacy keys for kunci_jawaban
            if ($request->tipe === 'benar_salah') $data['kunci_jawaban'] = 'COMPLEX_TF';
            if ($request->tipe === 'menjodohkan') $data['kunci_jawaban'] = 'MATCHING';
        } else {
            $data['data_soal'] = null;
        }

        $bankSoal->update($data);

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * HAPUS SOAL DARI BANK SOAL
     */
    public function destroyBankSoal(Mapel $mapel, BankSoal $bankSoal)
    {
        // 1. Otorisasi
        if ($mapel->guru_id != Auth::id()) abort(403);

        // 2. CEK KEAMANAN: Pastikan soal tidak sedang dipakai di ujian
        $sedangDipakai = \App\Models\Soal::where('bank_soal_id', $bankSoal->id)->exists();
        
        if ($sedangDipakai) {
            return back()->with('error', 'Gagal: Soal ini tidak dapat dihapus karena sedang digunakan di dalam sebuah Ujian.');
        }

        // 3. Hapus gambar utama & opsi (A, B, C, D)
        $images = ['gambar', 'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d'];
        foreach ($images as $img) {
            if ($bankSoal->$img) {
                Storage::disk('public')->delete($bankSoal->$img);
            }
        }

        // 4. Hapus gambar di dalam JSON (data_soal)
        if ($bankSoal->data_soal) {
            $data = is_string($bankSoal->data_soal) ? json_decode($bankSoal->data_soal, true) : $bankSoal->data_soal;
            
            if (is_array($data)) {
                // Bersihkan gambar di tipe Benar/Salah & Jawaban Ganda
                $items = $data['pernyataan'] ?? ($data['options'] ?? []);
                foreach ($items as $item) {
                    if (!empty($item['gambar'])) {
                        Storage::disk('public')->delete($item['gambar']);
                    }
                }

                // Bersihkan gambar di tipe Menjodohkan
                $matches = $data['matches'] ?? [];
                foreach ($matches as $match) {
                    if (!empty($match['gambar_left'])) {
                        Storage::disk('public')->delete($match['gambar_left']);
                    }
                    if (!empty($match['gambar_right'])) {
                        Storage::disk('public')->delete($match['gambar_right']);
                    }
                }
            }
        }

        // 5. Eksekusi Hapus Data
        $bankSoal->delete();

        return back()->with('success', 'Soal beserta file gambarnya berhasil dihapus dari Bank Soal.');
    }

    /**
     * FITUR BARU: Menghapus banyak soal sekaligus secara aman
     */

    public function destroyBulkBankSoal(Request $request, Mapel $mapel)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $idsRequested = $request->ids;

        // 1. Cari ID mana saja yang SEDANG DIPAKAI di ujian
        $terpakaiIds = \App\Models\Soal::whereIn('bank_soal_id', $idsRequested)
            ->pluck('bank_soal_id')
            ->toArray();
        
        // 2. Filter ID yang AMAN untuk dihapus (yang tidak ada di array terpakai)
        $amanUntukDihapus = array_diff($idsRequested, $terpakaiIds);

        // 3. Eksekusi Hapus untuk yang aman
        if (count($amanUntukDihapus) > 0) {
            \App\Models\BankSoal::whereIn('id', $amanUntukDihapus)->delete(); // Sesuaikan Model Anda
        }

        // 4. Buat pesan respons informatif
        $pesan = count($amanUntukDihapus) . ' soal yang tidak terpakai berhasil dihapus.';
        if (count($terpakaiIds) > 0) {
            $pesan .= ' Namun, ' . count($terpakaiIds) . ' soal dilewati karena sedang digunakan dalam ujian.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $pesan,
            'deleted_count' => count($amanUntukDihapus),
            'skipped_count' => count($terpakaiIds)
        ]);
    }

    /**
     * Helper to prepare data_soal
     */
    private function prepareDataSoal(Request $request, $mapelId, $oldDataSoal = null)
    {
        $tipe = $request->tipe;
        $dataSoal = [];

        if ($tipe === 'benar_salah') {
            $pernyataans = $request->pernyataan ?? [];
            foreach ($pernyataans as $idx => $val) {
                $p = ['text' => $val['text'] ?? '', 'correct' => $val['correct'] ?? 'TRUE'];
                if ($request->hasFile("pernyataan.$idx.gambar")) {
                    $p['gambar'] = $request->file("pernyataan.$idx.gambar")->store('soal', 'public');
                } elseif (isset($val['existing_gambar'])) {
                    $p['gambar'] = $val['existing_gambar'];
                }
                $dataSoal['pernyataan'][] = $p;
            }
        } elseif ($tipe === 'jawaban_ganda') {
            $options = $request->jg_options ?? [];
            foreach ($options as $idx => $val) {
                $o = ['id' => $val['id'] ?? '-', 'text' => $val['text'] ?? '', 'correct' => isset($val['correct']) && ($val['correct'] === 'on' || $val['correct'] == 1)];
                if ($request->hasFile("jg_options.$idx.gambar")) {
                    $o['gambar'] = $request->file("jg_options.$idx.gambar")->store('soal', 'public');
                } elseif (isset($val['existing_gambar'])) {
                    $o['gambar'] = $val['existing_gambar'];
                }
                $dataSoal['options'][] = $o;
            }
        } elseif ($tipe === 'menjodohkan') {
            $matches = $request->matches ?? [];
            foreach ($matches as $idx => $val) {
                $m = ['left' => $val['left'] ?? '', 'right' => $val['right'] ?? ''];
                if ($request->hasFile("matches.$idx.gambar_left")) {
                    $m['gambar_left'] = $request->file("matches.$idx.gambar_left")->store('soal', 'public');
                } elseif (isset($val['existing_gambar_left'])) {
                    $m['gambar_left'] = $val['existing_gambar_left'];
                }

                if ($request->hasFile("matches.$idx.gambar_right")) {
                    $m['gambar_right'] = $request->file("matches.$idx.gambar_right")->store('soal', 'public');
                } elseif (isset($val['existing_gambar_right'])) {
                    $m['gambar_right'] = $val['existing_gambar_right'];
                }
                $dataSoal['matches'][] = $m;
            }
        }

        return $dataSoal;
    }

    /**
     * TAMPILAN ARSIP SOAL SISWA (PDF / File)
     */
    public function indexArsipSoalSiswa(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) return back()->with('error', 'Akses ditolak.');
        
        $arsipSoalSiswas = ArsipSoalSiswa::where('mapel_id', $mapel->id)
                            ->where('guru_id', Auth::id())
                            ->orderBy('created_at', 'desc')
                            ->get();
                            
        return view('guru.mapel.arsip_soal_siswa', compact('mapel', 'arsipSoalSiswas'));
    }

    /**
     * HANDLE ARSIP SOAL SISWA (Upload PDF)
     */
    public function handleArsipSoalSiswa(Request $request, Mapel $mapel)
    {
        $request->validate([
            'new_files.*.file' => 'required|mimes:pdf|max:10240',
            'new_files.*.nama' => 'required|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();

            // 1. Simpan File Baru
            if ($request->has('new_files')) {
                foreach ($request->file('new_files') as $idx => $fileData) {
                    $file = $fileData['file'];
                    $nama = $request->new_files[$idx]['nama'];
                    $visibilitas = $request->new_files[$idx]['visibilitas'] ?? 'Private';

                    $path = $file->store('arsip_soal/' . $mapel->id, 'public');

                    ArsipSoalSiswa::create([
                        'guru_id' => Auth::id(),
                        'mapel_id' => $mapel->id,
                        'nama' => $nama,
                        'file_path' => $path,
                        'visibilitas' => $visibilitas,
                    ]);
                }
            }

            // 2. Update File Existing
            if ($request->has('existing')) {
                foreach ($request->existing as $id => $data) {
                    ArsipSoalSiswa::where('id', $id)
                        ->where('guru_id', Auth::id())
                        ->update([
                            'nama' => $data['nama'],
                            'visibilitas' => $data['visibilitas'],
                        ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Arsip soal siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Arsip Soal: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui arsip soal.');
        }
    }

    /**
     * HAPUS ARSIP SOAL SISWA
     */
    public function destroyArsipSoalSiswa(ArsipSoalSiswa $arsipSoalSiswa)
    {
        if ($arsipSoalSiswa->guru_id != Auth::id()) abort(403);

        if (Storage::disk('public')->exists($arsipSoalSiswa->file_path)) {
            Storage::disk('public')->delete($arsipSoalSiswa->file_path);
        }

        $arsipSoalSiswa->delete();
        return back()->with('success', 'File berhasil dihapus.');
    }

    /**
     * CREATE UJIAN SUSULAN
     */
    public function createSusulan(Ujian $ujian)
    {
        $mapel = $ujian->mapel;
        if ($mapel->guru_id != Auth::id()) abort(403);

        // Ambil siswa yang belum mengerjakan ujian induk
        $idSiswaSudah = HasilUjian::where('ujian_id', $ujian->id)->pluck('siswa_id')->toArray();
        $siswaBelum = Siswa::where('kelas_id', $mapel->kelas_id)
                        ->whereNotIn('id', $idSiswaSudah)
                        ->get();

        return view('guru.mapel.create_susulan', compact('ujian', 'mapel', 'siswaBelum'));
    }

    /**
     * STORE UJIAN SUSULAN
     */
    public function storeSusulan(Request $request, Ujian $ujian)
    {
        $mapel = $ujian->mapel;
        if ($mapel->guru_id != Auth::id()) abort(403);

        $now = Carbon::now('Asia/Jakarta');
        
        // Cek apakah ujian induk sedang berlangsung
        if ($ujian->waktu_mulai <= $now && $ujian->waktu_selesai >= $now) {
            return back()->with('error', 'Gagal: Ujian utama masih sedang berlangsung.');
        }

        // Cek apakah ada susulan lain yang masih aktif
        $activeSusulan = $ujian->ujianSusulans()->where('waktu_selesai', '>', $now)->first();
        if ($activeSusulan) {
            return back()->with('error', 'Gagal: Masih ada Ujian Susulan lain yang sedang berjalan (Selesai pada: ' . Carbon::parse($activeSusulan->waktu_selesai)->format('H:i') . ').');
        }

        $validated = $request->validate([
            'nama_ujian' => 'required|string|max:255',
            'tanggal_ujian' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'peserta_ids' => 'required|array',
            'peserta_ids.*' => 'exists:siswas,id',
        ]);

        $start = new \DateTime($validated['waktu_mulai']);
        $end = new \DateTime($validated['waktu_selesai']);
        $durasi = ($start->diff($end)->h * 60) + $start->diff($end)->i;

        try {
            DB::beginTransaction();

            $susulan = Ujian::create([
                'mapel_id' => $mapel->id,
                'guru_id' => Auth::id(),
                'nama_ujian' => $validated['nama_ujian'],
                'jenis_ujian' => $ujian->jenis_ujian,
                'tanggal_ujian' => $validated['tanggal_ujian'],
                'waktu_mulai' => $validated['tanggal_ujian'] . ' ' . $validated['waktu_mulai'] . ':00',
                'waktu_selesai' => $validated['tanggal_ujian'] . ' ' . $validated['waktu_selesai'] . ':00',
                'durasi_menit' => $durasi,
                'is_susulan' => true,
                'ujian_induk_id' => $ujian->id,
                'peserta_susulan' => $validated['peserta_ids'],
                'tahun_ajaran' => $ujian->tahun_ajaran ?? getTahunAjaran(),
            ]);

            // Copy semua soal dari ujian induk
            $soalsInduk = Soal::where('ujian_id', $ujian->id)->get();
            foreach ($soalsInduk as $soal) {
                $newSoal = $soal->replicate();
                $newSoal->ujian_id = $susulan->id;
                $newSoal->save();
            }

            DB::commit();
            return redirect()->route('guru.mapel.dashboard', $mapel->id)->with('success', 'Ujian susulan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat ujian susulan: ' . $e->getMessage());
        }
    }

    /**
     * SELESAIKAN UJIAN SECARA PAKSA (FORCE FINISH)
     */
    public function forceFinish(Ujian $ujian)
    {
        if ($ujian->guru_id != Auth::id()) abort(403);

        $now = Carbon::now('Asia/Jakarta');

        try {
            DB::beginTransaction();
            
            // Set waktu selesai jadi sekarang
            $ujian->update([
                'waktu_selesai' => $now->toDateTimeString(),
            ]);

            // Hitung ulang durasi (selisih antara mulai dan sekarang)
            $start = Carbon::parse($ujian->waktu_mulai);
            $diff = $start->diff($now);
            $newDurasi = ($diff->h * 60) + $diff->i;
            
            $ujian->update([
                'durasi_menit' => $newDurasi > 0 ? $newDurasi : 1,
            ]);

            DB::commit();
            return back()->with('success', 'Ujian berhasil diselesaikan secara paksa.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyelesaikan ujian: ' . $e->getMessage());
        }
    }

    /**
     * SMART ARCHIVING LOGIC
     */
    private function findOrCreateBankSoal($data, $mapelId)
    {
        // Data butir soal yang akan disimpan/dibandingkan
        $relevantData = [
            'tipe' => $data['tipe'],
            'pertanyaan' => $data['pertanyaan'],
            'opsi_a' => $data['opsi_a'] ?? null,
            'opsi_b' => $data['opsi_b'] ?? null,
            'opsi_c' => $data['opsi_c'] ?? null,
            'opsi_d' => $data['opsi_d'] ?? null,
            'kunci_jawaban' => $data['kunci_jawaban'],
            'data_soal' => is_array($data['data_soal']) ? $data['data_soal'] : json_decode($data['data_soal'] ?? '{}', true),
        ];

        $bankSoalId = $data['bank_soal_id'] ?? null;

        // Fallback: If no ID, check if exactly same question exists in DB
        if (!$bankSoalId) {
            $existing = BankSoal::where('mapel_id', $mapelId)
                ->where('tipe', $relevantData['tipe'])
                ->where('pertanyaan', $relevantData['pertanyaan'])
                ->first();
            if ($existing) $bankSoalId = $existing->id;
        }
        
        // 1. Handle Main Image
        $mainImg = $data['gambar_path'] ?? null;
        if ($mainImg && str_starts_with($mainImg, 'temp_soal/')) {
            $newPath = str_replace('temp_soal/', 'soal/', $mainImg);
            if (Storage::disk('public')->exists($mainImg)) {
                Storage::disk('public')->move($mainImg, $newPath);
            }
            $mainImg = $newPath;
        }
        $relevantData['gambar'] = $mainImg;

        // 2. Handle standard option images (gambar_a..d)
        foreach(['a','b','c','d'] as $o) {
            $pathKey = "gambar_{$o}_path";
            $imgPath = $data[$pathKey] ?? null;
            if ($imgPath && str_starts_with($imgPath, 'temp_soal/')) {
                $newPath = str_replace('temp_soal/', 'soal/', $imgPath);
                if (Storage::disk('public')->exists($imgPath)) {
                    Storage::disk('public')->move($imgPath, $newPath);
                }
                $imgPath = $newPath;
            }
            $relevantData["gambar_$o"] = $imgPath;
        }

        // 3. Handle nested images in data_soal (pernyataan, options, matches)
        $dataSoal = $relevantData['data_soal'];
        $moveImg = function(&$path) {
            if ($path && str_starts_with($path, 'temp_soal/')) {
                $newPath = str_replace('temp_soal/', 'soal/', $path);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->move($path, $newPath);
                }
                $path = $newPath;
            }
        };

        if (isset($dataSoal['options'])) {
            foreach ($dataSoal['options'] as &$opt) $moveImg($opt['gambar']);
        }
        if (isset($dataSoal['pernyataan'])) {
            foreach ($dataSoal['pernyataan'] as &$p) $moveImg($p['gambar']);
        }
        if (isset($dataSoal['matches'])) {
            foreach ($dataSoal['matches'] as &$m) {
                $moveImg($m['gambar_left']);
                $moveImg($m['gambar_right']);
            }
        }
        $relevantData['data_soal'] = $dataSoal;

        // 4. Smart Comparison & Archiving
        if ($bankSoalId) {
            $original = BankSoal::find($bankSoalId);
            if ($original) {
                // Canonicalize for comparison
                $normalize = function($val) {
                    if (is_null($val) || $val === '') return null;
                    return trim((string)$val);
                };

                // Recursive normalize for arrays
                $deepNormalize = function($arr) use (&$deepNormalize) {
                    if (!is_array($arr)) {
                        if (is_null($arr) || $arr === '') return null;
                        if (is_numeric($arr)) return (string)$arr; // Unify as string for safety
                        return trim((string)$arr);
                    }
                    ksort($arr);
                    
                    // 👇 UBAH BAGIAN INI UNTUK MENGABAIKAN NILAI NULL
                    $result = [];
                    foreach($arr as $k => $v) {
                        $normValue = $deepNormalize($v);
                        if (!is_null($normValue)) {
                            $result[$k] = $normValue;
                        }
                    }
                    return $result;
                };

                // Compare main fields
                $isChanged = (
                    $original->tipe != $relevantData['tipe'] ||
                    $normalize($original->pertanyaan) != $normalize($relevantData['pertanyaan']) ||
                    $normalize($original->kunci_jawaban) != $normalize($relevantData['kunci_jawaban']) ||
                    $normalize($original->gambar) != $normalize($relevantData['gambar']) ||
                    $normalize($original->opsi_a) != $normalize($relevantData['opsi_a']) ||
                    $normalize($original->opsi_b) != $normalize($relevantData['opsi_b']) ||
                    $normalize($original->opsi_c) != $normalize($relevantData['opsi_c']) ||
                    $normalize($original->opsi_d) != $normalize($relevantData['opsi_d']) ||
                    $normalize($original->gambar_a) != $normalize($relevantData['gambar_a']) ||
                    $normalize($original->gambar_b) != $normalize($relevantData['gambar_b']) ||
                    $normalize($original->gambar_c) != $normalize($relevantData['gambar_c']) ||
                    $normalize($original->gambar_d) != $normalize($relevantData['gambar_d'])
                );

                if (!$isChanged) {
                    // Deep compare data_soal
                    $originalData = is_array($original->data_soal) ? $original->data_soal : json_decode($original->data_soal ?? '[]', true);
                    $relevantDataArr = $relevantData['data_soal'];
                    
                    $normA = $deepNormalize($originalData);
                    $normB = $deepNormalize($relevantDataArr);
                    
                    if (json_encode($normA) === json_encode($normB)) {
                        return $bankSoalId;
                    }
                }
            }
        }

        // Simpan sebagai record BARU di Bank Soal
        $newBankSoal = BankSoal::create($relevantData + ['mapel_id' => $mapelId]);
        return $newBankSoal->id;
    }

    public function restartUjianSiswa(Ujian $ujian, Siswa $siswa)
{
    // 1. Validasi Akses Guru
    if ($ujian->mapel->guru_id != Auth::id()) abort(403);

    // 2. Identifikasi semua ID ujian terkait (Master + Susulan) 
    // karena siswa bisa saja mengerjakan di salah satunya
    $semuaIdUjian = Ujian::where('id', $ujian->id)
                         ->orWhere('ujian_induk_id', $ujian->id)
                         ->pluck('id')->toArray();
    
    // 3. Cari Record Hasil Ujian
    $hasil = HasilUjian::whereIn('ujian_id', $semuaIdUjian)
                       ->where('siswa_id', $siswa->id)
                       ->first();

    if ($hasil) {
        try {
            DB::beginTransaction();
            
            // Hapus record jawaban detail siswa terlebih dahulu
            JawabanSiswa::where('hasil_ujian_id', $hasil->id)->delete();
            
            // Hapus record hasil skor akhir
            $hasil->delete();
            
            DB::commit();
            return back()->with('success', 'Ujian ' . $siswa->nama_lengkap . ' berhasil di-restart. Siswa dapat mengerjakan ulang sekarang.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal me-restart ujian: ' . $e->getMessage());
        }
    }

    return back()->with('error', 'Data pengerjaan siswa tidak ditemukan.');
}
}