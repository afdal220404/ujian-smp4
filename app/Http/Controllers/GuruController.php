<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GuruController extends Controller
{
    public function index()
    {
        return view('operator.daftar_guru');
    }

    public function create()
    {
        return view('operator.tambah_guru');
    }

    public function edit($id)
    {
        $guru = Guru::findOrFail($id);
        return view('operator.tambah_guru', compact('guru'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|unique:gurus,nip',
            'username' => 'required|string|unique:gurus,username',
            'password' => 'required|string|min:6',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:Operator,Kepala Sekolah,Guru',
        ];

        // ✅ PESAN ERROR KUSTOM DIKEMBALIKAN
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar. Silakan gunakan NIP yang lain.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Silakan pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus .jpg, .jpeg, atau .png.',
            'foto.max' => 'Ukuran gambar maksimal adalah 2MB.',
            'role.required' => 'Role pengguna wajib dipilih.',
        ];

        $validatedData = $request->validate($rules, $messages);

        try {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('guru', 'public');
            }
            
            Guru::create([
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'nip' => $validatedData['nip'],
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'foto' => $fotoPath,
            ]);

            return redirect()->route('daftar_guru2')->with('success', 'Data Guru Berhasil Ditambahkan ✅');
        } catch (\Exception $e) {
            Log::error('Gagal saat menyimpan guru baru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data guru.');
        }
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|unique:gurus,nip,' . $guru->id,
            'username' => 'required|string|unique:gurus,username,' . $guru->id,
            'password' => 'nullable|string|min:6',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:Operator,Kepala Sekolah,Guru',
        ];
        
        // ✅ PESAN ERROR KUSTOM DIKEMBALIKAN
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar. Silakan gunakan NIP yang lain.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan. Silakan pilih username lain.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus .jpg, .jpeg, atau .png.',
            'foto.max' => 'Ukuran gambar maksimal adalah 2MB.',
            'role.required' => 'Role pengguna wajib dipilih.',
        ];

        $validatedData = $request->validate($rules, $messages);
        
        try {
            $updateData = [
                'nama_lengkap' => $validatedData['nama_lengkap'],
                'nip' => $validatedData['nip'],
                'username' => $validatedData['username'],
                'role' => $validatedData['role'],
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validatedData['password']);
            }

            if ($request->hasFile('foto')) {
                if ($guru->foto) { Storage::disk('public')->delete($guru->foto); }
                $updateData['foto'] = $request->file('foto')->store('guru', 'public');
            }
            
            $guru->update($updateData);

            return redirect()->route('daftar_guru2')->with('success', 'Data guru berhasil diupdate ✅');
        } catch (\Exception $e) {
            Log::error('Gagal saat update guru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data guru.');
        }
    }

    public function destroy($id)
    {
        try {
            $guru = Guru::findOrFail($id);
            if ($guru->foto) { Storage::disk('public')->delete($guru->foto); }
            $guru->delete();
            return redirect()->route('daftar_guru2')->with('success', 'Data guru berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal saat menghapus guru: ' . $e->getMessage());
            return redirect()->route('daftar_guru2')->with('error', 'Gagal menghapus data guru.');
        }
    }
    
    public function filter(Request $request)
    {
        $searchTerm = $request->search;
        $gurus = Guru::query()
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('nama_lengkap', 'like', "%{$searchTerm}%")
                             ->orWhere('nip', 'like', "%{$searchTerm}%")
                             ->orWhere('username', 'like', "%{$searchTerm}%");
            })
            ->orderBy('nama_lengkap', 'asc')
            ->get();
            
        return response()->json($gurus);
    }
}