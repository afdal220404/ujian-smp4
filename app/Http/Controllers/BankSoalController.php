<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Mapel;
use App\Models\BankSoal;

class BankSoalController extends Controller
{
    /**
     * Tampilkan daftar soal di bank soal untuk mapel tertentu.
     */
    public function index(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        $soals = BankSoal::where('mapel_id', $mapel->id)
            ->withCount('soals')   // eager load jumlah ujian pemakai
            ->orderBy('created_at', 'desc')
            ->get();

        return view('guru.mapel.bank_soal_items', compact('mapel', 'soals'));
    }

    /**
     * Form tambah soal baru ke bank soal (tanpa assign ujian).
     */
    public function create(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);
        return view('guru.mapel.bank_soal_create', compact('mapel'));
    }

    /**
     * Simpan soal baru ke bank soal.
     */
    public function store(Request $request, Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        $request->validate([
            'tipe'       => 'required|in:pilihan_ganda,benar_salah,jawaban_ganda,menjodohkan',
            'pertanyaan' => 'required|string',
            'gambar'     => 'nullable|image|max:2048',
        ]);

        $tipe = $request->tipe;

        try {
            DB::beginTransaction();

            // Handle gambar soal
            $gambarPath = null;
            if ($request->hasFile('gambar')) {
                $gambarPath = $request->file('gambar')->store('soal', 'public');
            }

            $data = [
                'mapel_id'   => $mapel->id,
                'tipe'       => $tipe,
                'pertanyaan' => $request->pertanyaan,
                'gambar'     => $gambarPath,
                'data_soal'  => null,
            ];

            if ($tipe === 'pilihan_ganda') {
                $data['opsi_a'] = $request->opsi_a ?? '-';
                $data['opsi_b'] = $request->opsi_b ?? '-';
                $data['opsi_c'] = $request->opsi_c ?? '-';
                $data['opsi_d'] = $request->opsi_d ?? '-';
                $data['kunci_jawaban'] = $request->kunci_jawaban;

                foreach (['a', 'b', 'c', 'd'] as $opsi) {
                    $key = "gambar_{$opsi}";
                    $data[$key] = null;
                    if ($request->hasFile($key)) {
                        $data[$key] = $request->file($key)->store('soal', 'public');
                    }
                }
            } elseif ($tipe === 'benar_salah') {
                $data['opsi_a'] = 'Benar';
                $data['opsi_b'] = 'Salah';
                $statements = [];
                if ($request->has('pernyataan') && is_array($request->pernyataan)) {
                    foreach ($request->pernyataan as $idx => $stmt) {
                        $stmtGambarPath = null;
                        if ($request->hasFile("pernyataan.{$idx}.gambar")) {
                            $stmtGambarPath = $request->file("pernyataan.{$idx}.gambar")->store('soal', 'public');
                        }
                        $statements[] = [
                            'text'    => $stmt['text'] ?? '',
                            'correct' => $stmt['correct'] ?? 'FALSE',
                            'gambar'  => $stmtGambarPath,
                        ];
                    }
                }
                $data['data_soal']     = ['pernyataan' => $statements];
                $data['kunci_jawaban'] = 'COMPLEX_TF';
            } elseif ($tipe === 'jawaban_ganda') {
                $data['opsi_a'] = null;
                $data['opsi_b'] = null;
                $data['opsi_c'] = null;
                $data['opsi_d'] = null;
                
                $jgOptionsList = [];
                $kunciArr = [];
                
                if ($request->has('jg_options') && is_array($request->jg_options)) {
                    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $optIndex = 0;
                    
                    foreach ($request->jg_options as $jidx => $opt) {
                        $optId = substr($alphabet, $optIndex, 1) ?: 'O' . $optIndex;
                        $gambarPath = null;
                        
                        if ($request->hasFile("jg_options.{$jidx}.gambar")) {
                            $gambarPath = $request->file("jg_options.{$jidx}.gambar")->store('soal', 'public');
                        }

                        $jgOptionsList[] = [
                            'id' => $optId,
                            'text' => $opt['text'] ?? '',
                            'gambar' => $gambarPath
                        ];
                        
                        if (isset($opt['correct']) && $opt['correct']) {
                            $kunciArr[] = $optId;
                        }
                        $optIndex++;
                    }
                }
                
                $data['data_soal']     = ['options' => $jgOptionsList];
                $data['kunci_jawaban'] = implode(',', $kunciArr);
            } elseif ($tipe === 'menjodohkan') {
                $matches = [];
                if ($request->has('matches') && is_array($request->matches)) {
                    foreach ($request->matches as $idx => $match) {
                        $leftGambar  = null;
                        $rightGambar = null;
                        if ($request->hasFile("matches.{$idx}.gambar_left")) {
                            $leftGambar = $request->file("matches.{$idx}.gambar_left")->store('soal', 'public');
                        }
                        if ($request->hasFile("matches.{$idx}.gambar_right")) {
                            $rightGambar = $request->file("matches.{$idx}.gambar_right")->store('soal', 'public');
                        }
                        $matches[] = [
                            'left'         => $match['left'] ?? '',
                            'right'        => $match['right'] ?? '',
                            'gambar_left'  => $leftGambar,
                            'gambar_right' => $rightGambar,
                        ];
                    }
                }
                $data['data_soal']     = ['matches' => $matches];
                $data['kunci_jawaban'] = 'MATCHING';
            }

            BankSoal::create($data);
            DB::commit();

            return redirect()->route('guru.mapel.bank_soal.index', $mapel->id)
                ->with('success', 'Soal berhasil ditambahkan ke bank soal!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan soal: ' . $e->getMessage());
        }
    }

    /**
     * Update soal yang sudah ada di bank soal.
     */
    public function update(Request $request, Mapel $mapel, BankSoal $item)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        // KUNCI KEAMANAN: Jangan edit jika sudah digunakan
        if ($item->soals()->count() > 0) {
            return back()->with('error', 'Gagal diperbarui: soal ini sudah digunakan di dalam ujian dan tidak boleh diubah untuk menjaga integritas nilai.');
        }

        $request->validate([
            'tipe'       => 'required|in:pilihan_ganda,benar_salah,jawaban_ganda,menjodohkan',
            'pertanyaan' => 'required|string',
            'gambar'     => 'nullable|image|max:2048',
        ]);

        $tipe = $request->tipe;

        try {
            DB::beginTransaction();

            $oldImages = [];
            // Ambil semua gambar lama dari data_soal untuk cleanup nanti
            if (is_array($item->data_soal)) {
                if (isset($item->data_soal['pernyataan'])) {
                    foreach ($item->data_soal['pernyataan'] as $s) if (!empty($s['gambar'])) $oldImages[] = $s['gambar'];
                }
                if (isset($item->data_soal['matches'])) {
                    foreach ($item->data_soal['matches'] as $m) {
                        if (!empty($m['gambar_left'])) $oldImages[] = $m['gambar_left'];
                        if (!empty($m['gambar_right'])) $oldImages[] = $m['gambar_right'];
                    }
                }
                if (isset($item->data_soal['options'])) {
                    foreach ($item->data_soal['options'] as $o) {
                        if (!empty($o['gambar'])) $oldImages[] = $o['gambar'];
                    }
                }
            }
            $newImages = [];

            // Handle gambar soal: ganti jika ada upload baru
            $gambarPath = $item->gambar;
            if ($request->hasFile('gambar')) {
                if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                    Storage::disk('public')->delete($gambarPath);
                }
                $gambarPath = $request->file('gambar')->store('soal', 'public');
            }

            $data = [
                'tipe'       => $tipe,
                'pertanyaan' => $request->pertanyaan,
                'gambar'     => $gambarPath,
                'data_soal'  => null,
                // Reset kolom opsi agar tidak ada data lama
                'opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null,
                'gambar_a' => $item->gambar_a, 'gambar_b' => $item->gambar_b, 'gambar_c' => $item->gambar_c, 'gambar_d' => $item->gambar_d,
                'kunci_jawaban' => null,
            ];

            if ($tipe === 'pilihan_ganda') {
                $data['opsi_a'] = $request->opsi_a ?? '-';
                $data['opsi_b'] = $request->opsi_b ?? '-';
                $data['opsi_c'] = $request->opsi_c ?? '-';
                $data['opsi_d'] = $request->opsi_d ?? '-';
                $data['kunci_jawaban'] = $request->kunci_jawaban;
                foreach (['a', 'b', 'c', 'd'] as $opsi) {
                    $key = "gambar_{$opsi}";
                    if ($request->hasFile($key)) {
                        if ($item->$key && Storage::disk('public')->exists($item->$key)) {
                            Storage::disk('public')->delete($item->$key);
                        }
                        $data[$key] = $request->file($key)->store('soal', 'public');
                    }
                }
            } elseif ($tipe === 'benar_salah') {
                $data['opsi_a'] = 'Benar';
                $data['opsi_b'] = 'Salah';
                $statements = [];
                if ($request->has('pernyataan') && is_array($request->pernyataan)) {
                    foreach ($request->pernyataan as $idx => $stmt) {
                        $stmtGambarPath = $stmt['existing_gambar'] ?? null;
                        if ($request->hasFile("pernyataan.{$idx}.gambar")) {
                            $stmtGambarPath = $request->file("pernyataan.{$idx}.gambar")->store('soal', 'public');
                        }
                        if ($stmtGambarPath) $newImages[] = $stmtGambarPath;
                        $statements[] = [
                            'text'    => $stmt['text'] ?? '',
                            'correct' => $stmt['correct'] ?? 'FALSE',
                            'gambar'  => $stmtGambarPath,
                        ];
                    }
                }
                $data['data_soal']     = ['pernyataan' => $statements];
                $data['kunci_jawaban'] = 'COMPLEX_TF';
            } elseif ($tipe === 'jawaban_ganda') {
                $data['opsi_a'] = null;
                $data['opsi_b'] = null;
                $data['opsi_c'] = null;
                $data['opsi_d'] = null;
                
                $jgOptionsList = [];
                $kunciArr = [];
                
                if ($request->has('jg_options') && is_array($request->jg_options)) {
                    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $optIndex = 0;
                    
                    foreach ($request->jg_options as $jidx => $opt) {
                        $optId = substr($alphabet, $optIndex, 1) ?: 'O' . $optIndex;
                        $gambarPath = $opt['existing_gambar'] ?? null;
                        
                        if ($request->hasFile("jg_options.{$jidx}.gambar")) {
                            $gambarPath = $request->file("jg_options.{$jidx}.gambar")->store('soal', 'public');
                        }
                        
                        if ($gambarPath) $newImages[] = $gambarPath;

                        $jgOptionsList[] = [
                            'id' => $optId,
                            'text' => $opt['text'] ?? '',
                            'gambar' => $gambarPath
                        ];
                        
                        if (isset($opt['correct']) && $opt['correct']) {
                            $kunciArr[] = $optId;
                        }
                        $optIndex++;
                    }
                }
                
                $data['data_soal']     = ['options' => $jgOptionsList];
                $data['kunci_jawaban'] = implode(',', $kunciArr);
            } elseif ($tipe === 'menjodohkan') {
                $matches = [];
                if ($request->has('matches') && is_array($request->matches)) {
                    foreach ($request->matches as $idx => $match) {
                        $leftGambar  = $match['existing_gambar_left'] ?? null;
                        $rightGambar = $match['existing_gambar_right'] ?? null;
                        if ($request->hasFile("matches.{$idx}.gambar_left")) {
                            $leftGambar = $request->file("matches.{$idx}.gambar_left")->store('soal', 'public');
                        }
                        if ($request->hasFile("matches.{$idx}.gambar_right")) {
                            $rightGambar = $request->file("matches.{$idx}.gambar_right")->store('soal', 'public');
                        }
                        if ($leftGambar) $newImages[] = $leftGambar;
                        if ($rightGambar) $newImages[] = $rightGambar;
                        $matches[] = [
                            'left'         => $match['left'] ?? '',
                            'right'        => $match['right'] ?? '',
                            'gambar_left'  => $leftGambar,
                            'gambar_right' => $rightGambar,
                        ];
                    }
                }
                $data['data_soal']     = ['matches' => $matches];
                $data['kunci_jawaban'] = 'MATCHING';
            }

            // Cleanup gambar lama yang tidak dipakai lagi di data_soal
            $toDelete = array_diff($oldImages, $newImages);
            foreach ($toDelete as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $item->update($data);
            DB::commit();

            return redirect()->route('guru.mapel.bank_soal.index', $mapel->id)
                ->with('success', 'Soal berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui soal: ' . $e->getMessage());
        }
    }

    /**
     * Hapus soal dari bank soal.
     */
    public function destroy(Mapel $mapel, BankSoal $item)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        // Keamanan: cegah hapus jika soal sedang digunakan
        if ($item->soals()->count() > 0) {
            return back()->with('error', 'Gagal dihapus: soal masih digunakan di dalam ujian.');
        }

        // Hapus gambar utama jika ada
        if ($item->gambar && Storage::disk('public')->exists($item->gambar)) {
            Storage::disk('public')->delete($item->gambar);
        }
        // Hapus gambar opsi a-d
        foreach (['a', 'b', 'c', 'd'] as $opsi) {
            $field = "gambar_{$opsi}";
            if ($item->$field && Storage::disk('public')->exists($item->$field)) {
                Storage::disk('public')->delete($item->$field);
            }
        }
        
        // Hapus gambar di dalam data_soal (TF/Matching)
        if (is_array($item->data_soal)) {
            if (isset($item->data_soal['pernyataan'])) {
                foreach ($item->data_soal['pernyataan'] as $s) {
                    if (!empty($s['gambar']) && Storage::disk('public')->exists($s['gambar'])) {
                        Storage::disk('public')->delete($s['gambar']);
                    }
                }
            }
            if (isset($item->data_soal['matches'])) {
                foreach ($item->data_soal['matches'] as $m) {
                    if (!empty($m['gambar_left']) && Storage::disk('public')->exists($m['gambar_left'])) {
                        Storage::disk('public')->delete($m['gambar_left']);
                    }
                    if (!empty($m['gambar_right']) && Storage::disk('public')->exists($m['gambar_right'])) {
                        Storage::disk('public')->delete($m['gambar_right']);
                    }
                }
            }
            if (isset($item->data_soal['options'])) {
                foreach ($item->data_soal['options'] as $o) {
                    if (!empty($o['gambar']) && Storage::disk('public')->exists($o['gambar'])) {
                        Storage::disk('public')->delete($o['gambar']);
                    }
                }
            }
        }

        $item->delete();

        return back()->with('success', 'Soal dihapus dari bank soal.');
    }

    /**
     * API endpoint: return JSON daftar soal untuk modal impor di halaman tambah soal.
     */
    public function api(Mapel $mapel)
    {
        if ($mapel->guru_id != Auth::id()) abort(403);

        $soals = BankSoal::where('mapel_id', $mapel->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                    $dataSoal = $s->data_soal;
                    if (is_array($dataSoal)) {
                        if (isset($dataSoal['pernyataan'])) {
                            foreach ($dataSoal['pernyataan'] as &$stmt) {
                                if (!empty($stmt['gambar'])) {
                                    $stmt['gambar'] = asset('storage/' . $stmt['gambar']);
                                }
                            }
                        }
                        if (isset($dataSoal['matches'])) {
                            foreach ($dataSoal['matches'] as &$match) {
                                if (!empty($match['gambar_left'])) {
                                    $match['gambar_left'] = asset('storage/' . $match['gambar_left']);
                                }
                                if (!empty($match['gambar_right'])) {
                                    $match['gambar_right'] = asset('storage/' . $match['gambar_right']);
                                }
                            }
                        }
                        if (isset($dataSoal['options'])) {
                            foreach ($dataSoal['options'] as &$opt) {
                                if (!empty($opt['gambar'])) {
                                    $opt['gambar'] = asset('storage/' . $opt['gambar']);
                                }
                            }
                        }
                    }

                    return [
                        'id'          => $s->id,
                        'tipe'        => $s->tipe,
                        'pertanyaan'  => $s->pertanyaan,
                        'gambar'      => $s->gambar ? asset('storage/' . $s->gambar) : null,
                        'opsi_a'      => $s->opsi_a, 'gambar_a' => $s->gambar_a ? asset('storage/' . $s->gambar_a) : null,
                        'opsi_b'      => $s->opsi_b, 'gambar_b' => $s->gambar_b ? asset('storage/' . $s->gambar_b) : null,
                        'opsi_c'      => $s->opsi_c, 'gambar_c' => $s->gambar_c ? asset('storage/' . $s->gambar_c) : null,
                        'opsi_d'      => $s->opsi_d, 'gambar_d' => $s->gambar_d ? asset('storage/' . $s->gambar_d) : null,
                        'kunci_jawaban' => $s->kunci_jawaban,
                        'data_soal'   => $dataSoal,
                    ];
            });

        return response()->json($soals);
    }
}
