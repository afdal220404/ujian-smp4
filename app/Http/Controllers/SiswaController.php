<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
Use App\Exports\TemplateSiswaExport;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
class SiswaController extends Controller
{
    public function index()
    {
        $kelasList = Kelas::orderBy('kelas')->get();
        return view('operator.daftar_siswa', compact('kelasList'));
    }

    public function create()
    {
        $kelasList = Kelas::where('id', '!=', 4)->orderBy('kelas')->get();
        return view('operator.tambah_siswa', compact('kelasList'));
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelasList = Kelas::where('id', '!=', 4)->orderBy('kelas')->get();
        return view('operator.tambah_siswa', compact('siswa', 'kelasList'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|numeric|digits_between:10,20|unique:siswas,nisn',
            'kelas_id' => 'required|exists:kelas,id',
            'username' => 'required|string|unique:siswas,username',
            'password' => 'required|string|min:6|regex:/[a-zA-Z]/|regex:/[0-9]/',
        ];

        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.numeric' => 'NISN wajib berupa angka.',
            'nisn.digits_between' => 'NISN minimal harus 10 karakter.',
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN yang lain.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.regex' => 'Password harus mengandung kombinasi huruf dan angka.',
        ];

        $validatedData = $request->validate($rules, $messages);

        try {
            Siswa::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'nisn' => $validatedData['nisn'],
                'kelas_id' => $validatedData['kelas_id'],
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
            ]);

            return redirect()->route('operator.daftar_siswa')->with('success', 'Data Siswa Berhasil Ditambahkan ✅');
        } catch (\Exception $e) {
            Log::error('Gagal saat menyimpan siswa baru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data siswa.');
        }
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|numeric|digits_between:10,20|unique:siswas,nisn,' . $siswa->id,
            'kelas_id' => 'required|exists:kelas,id',
            'username' => 'required|string|unique:siswas,username,' . $siswa->id,
            'password' => 'nullable|string|min:6|regex:/[a-zA-Z]/|regex:/[0-9]/',
        ];

        // ✅ PESAN ERROR KUSTOM DIKEMBALIKAN
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.numeric' => 'NISN wajib berupa angka.',
            'nisn.digits_between' => 'NISN minimal harus 10 karakter.',
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN yang lain.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Pilih username lain.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.regex' => 'Password baru harus mengandung kombinasi huruf dan angka.',
        ];
        
        $validatedData = $request->validate($rules, $messages);
        
        try {
            $updateData = [
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'nisn' => $validatedData['nisn'],
                'kelas_id' => $validatedData['kelas_id'],
                'username' => $validatedData['username'],
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validatedData['password']);
            }
            
            $siswa->update($updateData);

            return redirect()->route('operator.daftar_siswa')->with('success', 'Data siswa berhasil diupdate ✅');
        } catch (\Exception $e) {
            Log::error('Gagal saat update siswa: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data siswa.');
        }
    }

    public function destroy($id)
    {
        try {
            Siswa::findOrFail($id)->delete();
            return redirect()->route('operator.daftar_siswa')->with('success', 'Data siswa berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Gagal saat menghapus siswa: ' . $e->getMessage());
            return redirect()->route('operator.daftar_siswa')->with('error', 'Gagal menghapus data siswa.');
        }
    }

    public function filterByKelas(Request $request)
    {
        $query = Siswa::with('kelas');

        if ($request->filled('kelas')) {
            $query->where('kelas_id', $request->kelas);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $siswa = $query->orderBy('nama_lengkap', 'asc')->get();
        return response()->json($siswa);
    }

    public function indexKenaikan()
    {
        $kelasList = Kelas::where('id', '!=', 4)->orderBy('kelas')->get();
        return view('operator.kenaikan_kelas', compact('kelasList'));
    }

    // Proses Kenaikan Kelas Massal
    public function storeKenaikan(Request $request)
    {
        $request->validate([
            'kelas_asal_id' => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required', // Bisa ID kelas atau string "LULUS"
            'siswa_ids' => 'required|array', // Array ID siswa yang DIPILIH untuk naik
            'siswa_ids.*' => 'exists:siswas,id',
        ]);

        $ids = $request->siswa_ids;
        $tujuan = $request->kelas_tujuan_id;

        try {
            if ($tujuan === 'LULUS') {
                // Update status siswa menjadi Lulus (masuk kelas Alumni)
                // Menggunakan kelas_id = 4 khusus untuk Alumni
                // Sekaligus hapus username & password agar siswa tidak bisa login lagi
                Siswa::whereIn('id', $ids)->update([
                    'kelas_id' => 4,
                    'username'  => null,
                    'password'  => null,
                ]);
                $pesan = count($ids) . " Siswa berhasil diluluskan (dipindah ke kelas Alumni)!";
            } else {
                // Update kelas_id ke kelas baru
                Siswa::whereIn('id', $ids)->update([
                    'kelas_id' => $tujuan
                ]);
                
                $kelasBaru = Kelas::find($tujuan);
                $pesan = count($ids) . " Siswa berhasil naik ke kelas " . $kelasBaru->kelas;
            }

            return response()->json(['success' => true, 'message' => $pesan]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request) 
    {
        // 1. Validasi File Upload
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            // 2. Coba Proses Import
            Excel::import(new SiswaImport, $request->file('file'));
            
            // Jika berhasil (tidak ada error), lari ke sini
            return redirect()->back()->with('success', 'Data siswa berhasil diimport! ✅');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            
            // --- BAGIAN INI MENANGKAP ERROR VALIDASI EXCEL ---
            
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $baris = $failure->row();
                $kolom = $failure->attribute();
                // Ambil pesan error pertama dari array error
                $pesan = $failure->errors()[0]; 
                
                // Masukkan ke daftar error
                $errorMessages[] = "Baris {$baris} (Kolom: {$kolom}): {$pesan}";
            }

            // Kembalikan ke halaman sebelumnya dengan membawa daftar error
            return redirect()->back()
                ->with('import_errors', $errorMessages);

        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    
    // Fitur Download Template (Opsional tapi sangat membantu)
    public function downloadTemplate()
    {
        // Anda bisa membuat file excel kosong manual lalu taruh di folder public
        // Atau return response stream csv sederhana
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_siswa.csv"',
        ];

        $columns = ['nama_lengkap', 'nisn', 'kelas', 'username', 'password'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            // Contoh Data Dummy
            fputcsv($file, ['Budi Santoso', '0012345678', '7A', 'budi.santoso', '123456']);
            
            fclose($file);
        };

        return Excel::download(new TemplateSiswaExport, 'template_siswa.xlsx');
    }

    public function indexAlumni(Request $request)
    {
        $alumniKelas = Kelas::where('kelas', 'Alumni')->first();
        $alumniId = $alumniKelas ? $alumniKelas->id : -1;

        $query = Siswa::with(['hasilUjians.ujian'])->where(function($q) use ($alumniId) {
            $q->where('kelas_id', $alumniId)
              ->orWhereNull('kelas_id');
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswas = $query->orderBy('nama_lengkap', 'asc')->get();

        return view('operator.daftar_alumni', compact('siswas'));
    }

    public function detailAlumni($id)
    {
        $siswa = Siswa::with('kelas')->findOrFail($id);

        $availableKelasIds = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                                ->whereNotNull('kelas_id')
                                ->distinct()
                                ->pluck('kelas_id')
                                ->toArray();
        
        if ($siswa->kelas_id && !in_array($siswa->kelas_id, $availableKelasIds)) {
            $availableKelasIds[] = $siswa->kelas_id;
        }

        $allTranskrips = [];
        $kelasToProcess = [1, 2, 3]; // Kelas SMP VII, VIII, IX

        foreach($kelasToProcess as $kId) {
            // Hanya proses jika siswa pernah di kelas ini
            if (!in_array($kId, $availableKelasIds)) {
                continue;
            }

            $kelas = \App\Models\Kelas::find($kId);
            if (!$kelas) continue;

            $mapelKelas = \App\Models\Mapel::where('kelas_id', $kId)->with('guru')->get();

            $maxKuis = 0; $maxUts = 0; $maxUas = 0;

            $transkrip = $mapelKelas->map(function ($mapel) use ($siswa, $kId, &$maxKuis, &$maxUts, &$maxUas) {
                
                // A. AMBIL MASTER
                $masterKuis = \App\Models\Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'Kuis')->orderBy('created_at', 'asc')->get();
                $masterUts = \App\Models\Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'UTS')->orderBy('created_at', 'asc')->get();
                $masterUas = \App\Models\Ujian::where('mapel_id', $mapel->id)->where('jenis_ujian', 'UAS')->orderBy('created_at', 'asc')->get();

                // B. AMBIL HASIL UJIAN SISWA
                $hasilSiswa = \App\Models\HasilUjian::where('siswa_id', $siswa->id)
                                ->where('kelas_id', $kId)
                                ->whereHas('ujian', function($q) use ($mapel) {
                                    $q->where('mapel_id', $mapel->id);
                                })
                                ->with('ujian')
                                ->get();

                $listKuis = [];
                foreach ($masterKuis as $ujian) {
                    $score = $hasilSiswa->firstWhere('ujian_id', $ujian->id);
                    $listKuis[] = $score ? $score->nilai : '-';
                }

                $listUts = [];
                foreach ($masterUts as $ujian) {
                    $score = $hasilSiswa->firstWhere('ujian_id', $ujian->id);
                    $listUts[] = $score ? $score->nilai : '-';
                }

                $listUas = [];
                foreach ($masterUas as $ujian) {
                    $score = $hasilSiswa->firstWhere('ujian_id', $ujian->id);
                    $listUas[] = $score ? $score->nilai : '-';
                }

                if (count($masterKuis) > $maxKuis) $maxKuis = count($masterKuis);
                if (count($masterUts) > $maxUts) $maxUts = count($masterUts);
                if (count($masterUas) > $maxUas) $maxUas = count($masterUas);

                $validKuis = array_filter($listKuis, fn($v) => is_numeric($v));
                $rataKuis = count($validKuis) > 0 ? array_sum($validKuis) / count($validKuis) : null;

                $validUts = array_filter($listUts, fn($v) => is_numeric($v));
                $rataUts = count($validUts) > 0 ? array_sum($validUts) / count($validUts) : null;

                $validUas = array_filter($listUas, fn($v) => is_numeric($v));
                $rataUas = count($validUas) > 0 ? array_sum($validUas) / count($validUas) : null;

                $komponen = array_filter([$rataKuis, $rataUts, $rataUas], fn($v) => $v !== null);
                $nilaiAkhir = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;

                return [
                    'mapel'      => $mapel,
                    'detailKuis' => $listKuis,
                    'detailUts'  => $listUts,
                    'detailUas'  => $listUas,
                    'nilaiAkhir' => $nilaiAkhir
                ];
            });

            if ($maxKuis == 0) $maxKuis = 1;
            if ($maxUts == 0) $maxUts = 1;
            if ($maxUas == 0) $maxUas = 1;

            $allTranskrips[] = [
                'kelas' => $kelas,
                'transkrip' => $transkrip,
                'maxKuis' => $maxKuis,
                'maxUts' => $maxUts,
                'maxUas' => $maxUas
            ];
        }

        return view('operator.detail_alumni', compact('siswa', 'allTranskrips'));
    }
}