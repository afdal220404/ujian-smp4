<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Akun;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // ✅ Tambahkan ini untuk logging

class SiswaController extends Controller
{
    // ... metode index, filterByKelas, create, dan edit tidak berubah ...
    public function index()
    {
        return view('operator.daftar_siswa');
    }

    public function filterByKelas(Request $request)
    {
        $query = Siswa::with(['akun', 'kelas']);
        if ($request->filled('kelas')) {
            $query->where('kelas_id', $request->kelas);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('akun', function ($subq) use ($search) {
                        $subq->where('username', 'like', "%{$search}%");
                    });
            });
        }
        $siswa = $query->orderBy('nama_lengkap', 'asc')->get();
        return response()->json($siswa);
    }
    
    public function create()
    {
        $kelasList = Kelas::all();
        return view('operator.tambah_siswa', compact('kelasList'));
    }
    
    public function edit($id)
    {
        $siswa = Siswa::with('akun')->findOrFail($id);
        $kelasList = Kelas::all();
        return view('operator.tambah_siswa', compact('siswa', 'kelasList'));
    }

    public function store(Request $request)
    {
        // Definisikan aturan validasi
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswas,nisn',
            'kelas_id' => 'required|exists:kelas,id',
            'username' => 'required|string|unique:akuns,username',
            'password' => 'required|string|min:6',
        ];

        // ✅ Definisikan pesan error kustom
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

        // Jalankan validasi
        $validatedData = $request->validate($rules, $messages);

        try {
            DB::transaction(function () use ($validatedData) {
                // Buat Akun
                $akun = Akun::create([
                    'username' => $validatedData['username'],
                    'password' => Hash::make($validatedData['password']),
                    'role' => 'Siswa', 
                ]);

                // Buat data Siswa
                Siswa::create([
                    'akun_id' => $akun->akun_id,
                    'nama_lengkap' => $validatedData['nama_lengkap'],
                    'nisn' => $validatedData['nisn'],
                    'kelas_id' => $validatedData['kelas_id'],
                ]);
            });

            return redirect()->route('operator.daftar_siswa')->with('success', 'Data Siswa Berhasil Ditambahkan ✅');

        } catch (\Exception $e) {
            Log::error('Gagal saat menyimpan siswa baru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data siswa. Terjadi kesalahan.');
        }
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::with('akun')->findOrFail($id);
        $akun = $siswa->akun;

        // Definisikan aturan validasi untuk update
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswas,nisn,' . $siswa->id, // PERBAIKAN: Primary key adalah 'id'
            'jenis_kelamin' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
            'username' => 'required|string|unique:akuns,username,' . $akun->akun_id . ',akun_id',
            'password' => 'nullable|string|min:6',
        ];

        // ✅ Gunakan pesan error kustom yang sama
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN yang lain.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Pilih username lain.',
            'password.min' => 'Password minimal harus 6 karakter.',
        ];

        // Jalankan validasi
        $validatedData = $request->validate($rules, $messages);
        
        try {
            DB::transaction(function () use ($validatedData, $request, $siswa, $akun) {
                // Update data profil
                $siswa->update([
                    'nama_lengkap' => $validatedData['nama_lengkap'],
                    'nisn' => $validatedData['nisn'],
                    'jenis_kelamin' => $validatedData['jenis_kelamin'],
                    'kelas_id' => $validatedData['kelas_id'],
                ]);

                // Update data login
                $akunData = ['username' => $validatedData['username']];
                if ($request->filled('password')) {
                    $akunData['password'] = Hash::make($validatedData['password']);
                }
                $akun->update($akunData);
            });

            return redirect()->route('operator.daftar_siswa')->with('success', 'Data siswa berhasil diupdate ✅');

        } catch (\Exception $e) {
            Log::error('Gagal saat update siswa: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data siswa. Terjadi kesalahan.');
        }
    }

    public function destroy($id)
    {
        try {
            $siswa = Siswa::findOrFail($id);
            // Hapus akun, data siswa akan ikut terhapus karena cascade
            $siswa->akun->delete();
            return redirect()->route('operator.daftar_siswa')->with('success', 'Data siswa berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Gagal saat menghapus siswa: ' . $e->getMessage());
            return redirect()->route('operator.daftar_siswa')->with('error', 'Gagal menghapus data siswa. Terjadi kesalahan.');
        }
    }
}