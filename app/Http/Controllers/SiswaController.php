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
        $kelasList = Kelas::all();
        return view('operator.tambah_siswa', compact('kelasList'));
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelasList = Kelas::all();
        return view('operator.tambah_siswa', compact('siswa', 'kelasList'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswas,nisn',
            'kelas_id' => 'required|exists:kelas,id',
            'username' => 'required|string|unique:siswas,username',
            'password' => 'required|string|min:6',
        ];

        // ✅ PESAN ERROR KUSTOM DIKEMBALIKAN
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN yang lain.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
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
            'nisn' => 'required|string|unique:siswas,nisn,' . $siswa->id,
            'kelas_id' => 'required|exists:kelas,id',
            'username' => 'required|string|unique:siswas,username,' . $siswa->id,
            'password' => 'nullable|string|min:6',
        ];

        // ✅ PESAN ERROR KUSTOM DIKEMBALIKAN
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN yang lain.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Pilih username lain.',
            'password.min' => 'Password minimal harus 6 karakter.',
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
        $kelasList = Kelas::orderBy('kelas')->get();
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
                // Update status siswa menjadi Lulus (non-aktif) & hapus kelasnya
                // Pastikan Anda punya kolom 'status' di tabel siswas, atau pindahkan ke tabel alumni
                Siswa::whereIn('id', $ids)->update([
                    'kelas_id' => null, 
                    // 'status' => 'Lulus' // Jika ada kolom status
                ]);
                $pesan = count($ids) . " Siswa berhasil diluluskan!";
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
}