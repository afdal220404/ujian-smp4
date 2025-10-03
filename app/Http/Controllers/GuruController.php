<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Akun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GuruController extends Controller
{
    // ... metode index, create, edit tidak berubah ...
    public function index()
    {
        $gurus = Guru::all();
        return view('operator.daftar_guru', compact('gurus'));
    }

    public function create()
    {
        return view('operator.tambah_guru');
    }

    public function edit($id)
    {
        $guru = Guru::findOrFail($id);
        return view('operator.tambah_guru', compact('guru')); // pakai view form yang sama
    }

    public function store(Request $request)
    {
        // Definisikan aturan validasi
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|unique:gurus,nip',
            'username' => 'required|string|unique:akuns,username',
            'password' => 'required|string|min:6',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:Operator,Kepala Sekolah,Guru',
        ];

        // ✅ Definisikan pesan error kustom untuk setiap aturan
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

        // Jalankan validasi dengan aturan dan pesan kustom
        $validatedData = $request->validate($rules, $messages);

        try {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('guru', 'public');
            }

            DB::transaction(function () use ($validatedData, $fotoPath) {
                $akun = Akun::create([
                    'username' => $validatedData['username'],
                    'password' => Hash::make($validatedData['password']),
                    'role' => $validatedData['role'],
                ]);

                Guru::create([
                    'akun_id' => $akun->akun_id,
                    'nama_lengkap' => $validatedData['nama_lengkap'],
                    'nip' => $validatedData['nip'],
                    'foto' => $fotoPath,
                ]);
            });

            return redirect()->route('daftar_guru2')->with('success', 'Data Guru Berhasil Ditambahkan ✅');
        } catch (\Exception $e) {
            Log::error('Gagal saat menyimpan guru baru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data guru. Terjadi kesalahan.');
        }
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::with('akun')->findOrFail($id);
        $akun = $guru->akun;
        
        // Definisikan aturan validasi untuk update
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|unique:gurus,nip,' . $guru->id,
            'username' => 'required|string|unique:akuns,username,' . $akun->akun_id . ',akun_id',
            'password' => 'nullable|string|min:6',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:Operator,Kepala Sekolah,Guru',
        ];
        
        // ✅ Gunakan pesan error kustom yang sama
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

        // Jalankan validasi
        $validatedData = $request->validate($rules, $messages);
        
        try {
            DB::transaction(function () use ($validatedData, $request, $guru, $akun) {
                $guru->nama_lengkap = $validatedData['nama_lengkap'];
                $guru->nip = $validatedData['nip'];

                $akun->username = $validatedData['username'];
                $akun->role = $validatedData['role'];

                if ($request->filled('password')) {
                    $akun->password = Hash::make($validatedData['password']);
                }

                if ($request->hasFile('foto')) {
                    if ($guru->foto) { Storage::disk('public')->delete($guru->foto); }
                    $guru->foto = $request->file('foto')->store('guru', 'public');
                }

                $guru->save();
                $akun->save();
            });

            return redirect()->route('daftar_guru2')->with('success', 'Data guru berhasil diupdate ✅');
        } catch (\Exception $e) {
            Log::error('Gagal saat update guru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data guru. Terjadi kesalahan.');
        }
    }

    public function destroy($id)
    {
        try {
            $guru = Guru::findOrFail($id);
            if ($guru->foto) { Storage::disk('public')->delete($guru->foto); }
            $guru->akun->delete();
            return redirect()->route('daftar_guru2')->with('success', 'Data guru berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal saat menghapus guru: ' . $e->getMessage());
            return redirect()->route('daftar_guru2')->with('error', 'Gagal menghapus data guru. Terjadi kesalahan.');
        }
    }
    
    // ... (metode filter tidak berubah) ...
    public function filter(Request $request)
    {
        $searchTerm = $request->search;
        $gurus = Guru::query()
            ->with('akun') 
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('nama_lengkap', 'like', "%{$searchTerm}%")
                             ->orWhere('nip', 'like', "%{$searchTerm}%");
            })
            ->orderBy('nama_lengkap', 'asc')
            ->get();
        return response()->json($gurus);
    }
}