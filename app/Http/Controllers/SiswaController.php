<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
}