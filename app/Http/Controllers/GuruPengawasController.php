<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\Siswa;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruPengawasController extends Controller
{
    /**
     * Tampilkan Halaman Ruang Pengawas Ujian
     */
    public function ruangPengawas(Ujian $ujian)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        // 1. Validasi: Hanya guru yang ditugaskan sebagai pengawas yang boleh masuk
        if ($ujian->pengawas_id != $guruId) {
            abort(403, 'Akses ditolak. Anda bukan guru pengawas untuk ujian ini.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $start = Carbon::parse($ujian->waktu_mulai)->timezone('Asia/Jakarta');
        $end = Carbon::parse($ujian->waktu_selesai)->timezone('Asia/Jakarta');
        $accessibleTime = $start->copy()->subMinutes(30);

        // 2. Validasi Batas Waktu: Hanya bisa diakses mulai 30 menit sebelum ujian
        if ($now < $accessibleTime) {
            return redirect()->route('guru.index')
                ->with('error', 'Ruang pengawas baru dapat diakses 30 menit sebelum ujian dimulai (pukul ' . $accessibleTime->format('H:i') . ' WIB).');
        }

        $mapel = $ujian->mapel;
        $kelas = $mapel->kelas;

        // 3. Ambil Daftar Siswa (Peserta Susulan atau Seluruh Kelas)
        if ($ujian->is_susulan && !empty($ujian->peserta_susulan)) {
            $siswas = Siswa::whereIn('id', $ujian->peserta_susulan)->orderBy('nama_lengkap')->get();
        } else {
            $siswas = Siswa::where('kelas_id', $mapel->kelas_id)->orderBy('nama_lengkap')->get();
        }

        // 4. Ambil Hasil Ujian Siswa (Hanya Status & Waktu, TANPA NILAI & KUNCI JAWABAN)
        $hasilUjians = HasilUjian::where('ujian_id', $ujian->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $jumlahSelesai = 0;
        $jumlahSedang = 0;
        $jumlahBelum = 0;

        $daftarMonitoring = $siswas->map(function ($siswa) use ($ujian, $hasilUjians, &$jumlahSelesai, &$jumlahSedang, &$jumlahBelum) {
            $hasil = $hasilUjians->get($siswa->id);
            $statusPelanggaran = null;
            $isLockedReentry = (bool) ($hasil->is_locked_reentry ?? false);
            $isPaused = (bool) ($hasil->is_paused ?? false);

            if ($hasil && $hasil->waktu_selesai) {
                if ($hasil->status_penyelesaian === 'pelanggaran') {
                    $status = 'Pelanggaran';
                    $statusColor = 'red';
                    $statusPelanggaran = $hasil->keterangan_pelanggaran ?? 'Keluar dari aplikasi saat ujian';
                } elseif ($hasil->status_penyelesaian === 'waktu_habis') {
                    $status = 'Waktu Habis';
                    $statusColor = 'amber';
                } else {
                    $status = 'Selesai';
                    $statusColor = 'green';
                }
                $jumlahSelesai++;
            } elseif ($hasil && $hasil->waktu_mulai) {
                if ($isPaused) {
                    $status = 'Ujian Dijeda';
                    $statusColor = 'amber';
                } elseif ($isLockedReentry) {
                    $status = 'Sesi Terkunci';
                    $statusColor = 'orange';
                } else {
                    $status = 'Sedang Mengerjakan';
                    $statusColor = 'blue';
                }
                $jumlahSedang++;
            } else {
                $status = 'Belum Mulai';
                $statusColor = 'gray';
                $jumlahBelum++;
            }

            return (object) [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'status' => $status,
                'status_color' => $statusColor,
                'status_penyelesaian' => $hasil->status_penyelesaian ?? 'belum_selesai',
                'keterangan_pelanggaran' => $statusPelanggaran,
                'is_locked_reentry' => $isLockedReentry,
                'is_paused' => $isPaused,
                'waktu_mulai' => ($hasil && $hasil->waktu_mulai) ? Carbon::parse($hasil->waktu_mulai)->format('H:i:s') : '-',
                'waktu_selesai' => ($hasil && $hasil->waktu_selesai) ? Carbon::parse($hasil->waktu_selesai)->format('H:i:s') : '-',
                'can_restart' => ($hasil !== null && $hasil->waktu_mulai !== null),
                'allow_web' => $ujian->isSiswaAllowedWeb($siswa->id),
            ];
        });

        $totalSiswa = $siswas->count();
        $isPreExam = ($now < $start);
        $detikKeMulai = $isPreExam ? $now->diffInSeconds($start, false) : 0;
        $isFinished = ($now > $end);
        $detikSisaUjian = (!$isPreExam && !$isFinished) ? $now->diffInSeconds($end, false) : 0;

        return view('guru.pengawas.ruang', compact(
            'ujian',
            'mapel',
            'kelas',
            'daftarMonitoring',
            'totalSiswa',
            'jumlahSelesai',
            'jumlahSedang',
            'jumlahBelum',
            'isPreExam',
            'detikKeMulai',
            'isFinished',
            'detikSisaUjian',
            'start',
            'end'
        ));
    }

    /**
     * Endpoint JSON Live Data untuk Real-time Polling Siswa
     */
    public function liveData(Ujian $ujian)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $now = Carbon::now('Asia/Jakarta');
        $start = Carbon::parse($ujian->waktu_mulai)->timezone('Asia/Jakarta');
        $end = Carbon::parse($ujian->waktu_selesai)->timezone('Asia/Jakarta');

        $mapel = $ujian->mapel;

        if ($ujian->is_susulan && !empty($ujian->peserta_susulan)) {
            $siswas = Siswa::whereIn('id', $ujian->peserta_susulan)->orderBy('nama_lengkap')->get();
        } else {
            $siswas = Siswa::where('kelas_id', $mapel->kelas_id)->orderBy('nama_lengkap')->get();
        }

        $hasilUjians = HasilUjian::where('ujian_id', $ujian->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $jumlahSelesai = 0;
        $jumlahSedang = 0;
        $jumlahBelum = 0;

        $daftarMonitoring = $siswas->map(function ($siswa) use ($ujian, $hasilUjians, &$jumlahSelesai, &$jumlahSedang, &$jumlahBelum) {
            $hasil = $hasilUjians->get($siswa->id);
            $statusPelanggaran = null;
            $isLockedReentry = (bool) ($hasil->is_locked_reentry ?? false);
            $isPaused = (bool) ($hasil->is_paused ?? false);

            if ($hasil && $hasil->waktu_selesai) {
                if ($hasil->status_penyelesaian === 'pelanggaran') {
                    $status = 'Pelanggaran';
                    $statusPelanggaran = $hasil->keterangan_pelanggaran ?? 'Keluar dari aplikasi saat ujian';
                } elseif ($hasil->status_penyelesaian === 'waktu_habis') {
                    $status = 'Waktu Habis';
                } else {
                    $status = 'Selesai';
                }
                $jumlahSelesai++;
            } elseif ($hasil && $hasil->waktu_mulai) {
                if ($isPaused) {
                    $status = 'Ujian Dijeda';
                } elseif ($isLockedReentry) {
                    $status = 'Sesi Terkunci';
                } else {
                    $status = 'Sedang Mengerjakan';
                }
                $jumlahSedang++;
            } else {
                $status = 'Belum Mulai';
                $jumlahBelum++;
            }

            return [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'status' => $status,
                'status_penyelesaian' => $hasil->status_penyelesaian ?? 'belum_selesai',
                'keterangan_pelanggaran' => $statusPelanggaran,
                'is_locked_reentry' => $isLockedReentry,
                'is_paused' => $isPaused,
                'waktu_mulai' => ($hasil && $hasil->waktu_mulai) ? Carbon::parse($hasil->waktu_mulai)->format('H:i:s') . ' WIB' : '-',
                'waktu_selesai' => ($hasil && $hasil->waktu_selesai) ? Carbon::parse($hasil->waktu_selesai)->format('H:i:s') . ' WIB' : '-',
                'can_restart' => ($hasil !== null && $hasil->waktu_mulai !== null),
                'allow_web' => $ujian->isSiswaAllowedWeb($siswa->id),
            ];
        });

        $totalSiswa = $siswas->count();
        $isPreExam = ($now < $start);
        $detikKeMulai = $isPreExam ? $now->diffInSeconds($start, false) : 0;
        $isFinished = ($now > $end);
        $detikSisaUjian = (!$isPreExam && !$isFinished) ? $now->diffInSeconds($end, false) : 0;

        return response()->json([
            'success' => true,
            'totalSiswa' => $totalSiswa,
            'jumlahSelesai' => $jumlahSelesai,
            'jumlahSedang' => $jumlahSedang,
            'jumlahBelum' => $jumlahBelum,
            'isPreExam' => $isPreExam,
            'detikKeMulai' => $detikKeMulai,
            'isFinished' => $isFinished,
            'detikSisaUjian' => $detikSisaUjian,
            'waktuSelesaiFormatted' => $end->format('H:i') . ' WIB',
            'durasiMenit' => $ujian->durasi_menit,
            'data' => $daftarMonitoring,
        ]);
    }

    /**
     * Restart Ujian Siswa (Reset pengerjaan siswa yang mengalami kendala teknis)
     */
    public function restartUjianSiswa(Ujian $ujian, Siswa $siswa)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            abort(403, 'Akses ditolak. Anda bukan pengawas untuk ujian ini.');
        }

        $semuaIdUjian = Ujian::where('id', $ujian->id)
            ->orWhere('ujian_induk_id', $ujian->id)
            ->pluck('id')
            ->toArray();

        $hasil = HasilUjian::whereIn('ujian_id', $semuaIdUjian)
            ->where('siswa_id', $siswa->id)
            ->first();

        if ($hasil) {
            try {
                DB::beginTransaction();

                // Reset status sesi ujian siswa agar kembali ke 'Belum Mulai'
                // Waktu mulai/selesai dikosongkan dan status jeda dinonaktifkan
                // Jawaban yang sudah diisi tetap tersimpan aman di database
                $hasil->update([
                    'waktu_mulai' => null,
                    'waktu_selesai' => null,
                    'nilai' => 0,
                    'jumlah_benar' => null,
                    'jumlah_salah' => null,
                    'is_paused' => false,
                    'waktu_jeda' => null,
                    'is_locked_reentry' => false,
                    'status_penyelesaian' => null,
                    'keterangan_pelanggaran' => null,
                ]);

                DB::commit();
                return back()->with('success', 'Ujian untuk ' . $siswa->nama_lengkap . ' berhasil diulang. Status pengerjaan kembali ke "Belum Mulai" dan menunggu siswa memulai kembali ujian.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal membuka kembali akses ujian: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Data pengerjaan siswa tidak ditemukan.');
    }

    /**
     * Update / Tambah Waktu Ujian oleh Guru Pengawas
     */
    public function updateWaktu(Request $request, Ujian $ujian)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            abort(403, 'Akses ditolak. Anda bukan pengawas untuk ujian ini.');
        }

        $request->validate([
            'tambahan_menit' => 'required|integer',
        ]);

        $tambahan = (int) $request->tambahan_menit;
        $currentEnd = Carbon::parse($ujian->waktu_selesai);
        $newEnd = $currentEnd->copy()->addMinutes($tambahan);
        $start = Carbon::parse($ujian->waktu_mulai);

        if ($newEnd <= $start) {
            return back()->with('error', 'Waktu selesai tidak boleh kurang dari waktu mulai.');
        }

        $ujian->waktu_selesai = $newEnd->format('Y-m-d H:i:s');
        $diff = $start->diff($newEnd);
        $ujian->durasi_menit = ($diff->h * 60) + $diff->i;
        $ujian->save();

        return back()->with('success', 'Waktu ujian berhasil disesuaikan (+ ' . $tambahan . ' menit). Waktu selesai baru: ' . $newEnd->format('H:i') . ' WIB.');
    }

    /**
     * Selesaikan Ujian oleh Guru Pengawas Ruangan
     */
    public function forceFinish(Ujian $ujian)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            abort(403, 'Akses ditolak. Anda bukan pengawas untuk ujian ini.');
        }

        $now = Carbon::now('Asia/Jakarta');

        try {
            DB::beginTransaction();

            $ujian->update([
                'waktu_selesai' => $now->toDateTimeString(),
            ]);

            $start = Carbon::parse($ujian->waktu_mulai);
            $diff = $start->diff($now);
            $newDurasi = ($diff->h * 60) + $diff->i;

            $ujian->update([
                'durasi_menit' => $newDurasi > 0 ? $newDurasi : 1,
            ]);

            DB::commit();
            return back()->with('success', 'Pelaksanaan ujian di ruangan telah berhasil diselesaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyelesaikan ujian: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Izin Akses Browser Web untuk Siswa
     */
    public function toggleWebAccess(Request $request, Ujian $ujian)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda bukan guru pengawas untuk ujian ini.'
            ], 403);
        }

        try {
            $newStatus = $request->has('allow_web_access') 
                ? $request->boolean('allow_web_access') 
                : !$ujian->allow_web_access;

            $ujian->update([
                'allow_web_access' => $newStatus
            ]);

            $statusText = $newStatus ? 'Web Browser & Mobile Diizinkan' : 'Khusus Aplikasi Mobile Ujian Digital';

            return response()->json([
                'success' => true,
                'allow_web_access' => $newStatus,
                'message' => 'Status izin akses berhasil diubah: ' . $statusText
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah izin akses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle Izin Akses Browser Web untuk Siswa Tertentu
     */
    public function toggleSiswaWebAccess(Request $request, Ujian $ujian, Siswa $siswa)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda bukan guru pengawas untuk ujian ini.'
            ], 403);
        }

        try {
            $allowedList = $ujian->siswa_izin_web ?? [];
            $allowedList = array_map('intval', $allowedList);
            $siswaId = (int) $siswa->id;

            if (in_array($siswaId, $allowedList)) {
                // Hapus dari daftar izin (kembalikan ke Khusus Mobile)
                $allowedList = array_values(array_diff($allowedList, [$siswaId]));
                $newStatus = false;
                $message = "Izin web untuk {$siswa->nama_lengkap} dinonaktifkan (Khusus Mobile).";
            } else {
                // Tambahkan ke daftar izin (izinkan Web Komputer)
                $allowedList[] = $siswaId;
                $allowedList = array_values(array_unique($allowedList));
                $newStatus = true;
                $message = "Izin web untuk {$siswa->nama_lengkap} diaktifkan (Bisa Web Komputer).";
            }

            $ujian->update([
                'siswa_izin_web' => $allowedList
            ]);

            return response()->json([
                'success' => true,
                'allow_web' => $newStatus,
                'siswa_id' => $siswaId,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah izin akses siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buka Kunci Masuk Ulang (Re-Entry Unlock) Siswa oleh Pengawas
     */
    public function unlockSiswaReentry(Ujian $ujian, Siswa $siswa)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda bukan pengawas untuk ujian ini.'
            ], 403);
        }

        $semuaIdUjian = Ujian::where('id', $ujian->id)
            ->orWhere('ujian_induk_id', $ujian->id)
            ->pluck('id')
            ->toArray();

        $hasil = HasilUjian::whereIn('ujian_id', $semuaIdUjian)
            ->where('siswa_id', $siswa->id)
            ->first();

        if ($hasil) {
            $hasil->is_locked_reentry = false;
            $hasil->last_heartbeat = now();
            $hasil->save();

            return response()->json([
                'success' => true,
                'message' => "Izin masuk ulang untuk {$siswa->nama_lengkap} berhasil dibuka. Siswa dapat melanjutkan ujian.",
                'siswa_id' => $siswa->id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data sesi ujian siswa tidak ditemukan.'
        ], 404);
    }

    /**
     * Jeda / Lanjutkan Ujian (Pause & Resume) Siswa Tertentu oleh Pengawas Ruangan
     */
    public function togglePauseSiswa(Request $request, Ujian $ujian, Siswa $siswa)
    {
        $user = Auth::user();
        $guruId = $user->guru ? $user->guru->id : $user->id;

        if ($ujian->pengawas_id != $guruId) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda bukan pengawas untuk ujian ini.'
            ], 403);
        }

        $semuaIdUjian = Ujian::where('id', $ujian->id)
            ->orWhere('ujian_induk_id', $ujian->id)
            ->pluck('id')
            ->toArray();

        $hasil = HasilUjian::whereIn('ujian_id', $semuaIdUjian)
            ->where('siswa_id', $siswa->id)
            ->first();

        if ($hasil) {
            if ($hasil->waktu_selesai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian siswa sudah selesai dan tidak dapat dijeda.'
                ], 422);
            }

            $newPauseState = !$hasil->is_paused;
            $hasil->is_paused = $newPauseState;
            
            if ($newPauseState) {
                $hasil->waktu_jeda = now();
            }

            $hasil->save();

            $message = $newPauseState
                ? "Ujian untuk {$siswa->nama_lengkap} berhasil dijeda (Izin keluar ruangan)."
                : "Ujian untuk {$siswa->nama_lengkap} berhasil dilanjutkan kembali.";

            return response()->json([
                'success' => true,
                'is_paused' => $newPauseState,
                'siswa_id' => $siswa->id,
                'message' => $message
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data sesi pengerjaan siswa belum dimulai.'
        ], 404);
    }
}
