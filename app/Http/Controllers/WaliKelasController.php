<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\WaliKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Tambahkan Log

class WaliKelasController extends Controller
{
    public function index()
    {
        $kelasList = Kelas::orderBy('kelas')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();
        $waliKelasData = WaliKelas::pluck('guru_id', 'kelas_id');

        return view('operator.wali_kelas', compact('kelasList', 'gurus', 'waliKelasData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wali_kelas' => 'required|array',
            'wali_kelas.*' => 'nullable|exists:gurus,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Gunakan Eloquent untuk efisiensi
                WaliKelas::query()->delete();

                foreach ($validated['wali_kelas'] as $kelasId => $guruId) {
                    if ($guruId) {
                        WaliKelas::create([
                            'kelas_id' => $kelasId,
                            'guru_id' => $guruId,
                        ]);
                    }
                }
            });

            // ✅ PERBAIKAN: Set session secara manual lalu return redirect
            session()->flash('success', 'Data Wali Kelas berhasil diperbarui!');
            
            return redirect()->route('walikelas.index');

        } catch (\Exception $e) {
            // Log error untuk debugging di masa depan
            Log::error('Gagal menyimpan Wali Kelas: ' . $e->getMessage());

            // Set session error secara manual
            session()->flash('error', 'Gagal menyimpan data. Terjadi kesalahan server.');

            return redirect()->route('walikelas.index');
        }
    }
}