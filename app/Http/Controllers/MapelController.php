<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Kelas;

use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $gurus = Guru::all();
        $kelasList = Kelas::all();
        return view('operator.mapel', compact('gurus', 'kelasList'));
    }

    public function store(Request $request)
    {
        // Ubah aturan validasi agar sesuai dengan data yang dikirim
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id', // Cek apakah kelas_id ada di tabel kelas
            'nama_mapel' => 'required|string|max:255',
            'guru_id' => 'required|exists:gurus,id',
        ]);

        // Buat data mapel menggunakan field yang benar
        Mapel::create([
            'kelas_id' => $request->kelas_id, // Gunakan kelas_id dari request
            'nama_mapel' => $request->nama_mapel,
            'guru_id' => $request->guru_id,
        ]);

        return response()->json(['success' => true]);
    }

    // Di dalam class MapelController
    public function update(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);
        $request->validate([
            'nama_mapel' => 'sometimes|required|string|max:255',
            'guru_id' => 'sometimes|required|exists:gurus,id',
        ]);

        // Gunakan request->all() atau request->only() agar lebih aman
        $mapel->update($request->all());

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        // Cari data mapel berdasarkan ID, jika tidak ketemu akan error
        $mapel = Mapel::findOrFail($id);

        // Hapus data dari database
        $mapel->delete();

        // Kembalikan respons sukses dalam format JSON
         return redirect()->back()->with('success', 'Mata pelajaran berhasil dihapus!');
    }


    public function getByKelas($kelasId) // Ganti nama parameter agar lebih jelas
    {
        // Hapus baris yang tidak perlu karena kita sudah punya ID-nya
        // $kelasId = Kelas::where('kelas', $kelas)->value('id');

        $mapels = Mapel::with('guru', 'kelas')
            ->where('kelas_id', $kelasId) // Langsung gunakan ID yang diterima
            ->get();

        return response()->json($mapels);
    }
}
