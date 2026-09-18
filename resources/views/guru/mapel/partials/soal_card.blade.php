<div class="soal-card bg-white rounded-2xl shadow-[0_2px_15px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden group hover:border-blue-200 transition-all mb-6" data-index="{{ $index }}">
    @if(isset($soal['bank_soal_id']) && $soal['bank_soal_id'])
    <input type="hidden" name="soal[{{ $index }}][bank_soal_id]" value="{{ $soal['bank_soal_id'] }}">
    @endif
    {{-- Header --}}
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-lg">No. <span class="soal-nomor">{{ $index + 1 }}</span></span>
            
            {{-- Tipe Soal Select --}}
            @php $tipe = $soal['tipe'] ?? 'pilihan_ganda'; @endphp
            <select name="soal[{{ $index }}][tipe]" class="soal-tipe-select bg-white border border-gray-200 text-gray-700 text-xs font-bold py-1 px-3 rounded-lg focus:outline-none focus:border-blue-500 uppercase">
                <option value="pilihan_ganda" {{ $tipe == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                <option value="benar_salah" {{ $tipe == 'benar_salah' ? 'selected' : '' }}>Benar / Salah</option>
                <option value="jawaban_ganda" {{ $tipe == 'jawaban_ganda' ? 'selected' : '' }}>Pilih Banyak Jawaban</option>
                <option value="menjodohkan" {{ $tipe == 'menjodohkan' ? 'selected' : '' }}>Mencocokkan</option>
            </select>
        </div>
        <button type="button" class="delete-soal-btn text-gray-400 hover:text-red-500 transition-colors" title="Hapus Soal">
            <i class="bi bi-trash3-fill text-lg"></i>
        </button>
    </div>

    <div class="p-6 space-y-6">
        {{-- 1. Baris Atas: Pertanyaan (Kiri) & Gambar Pendukung + Kunci PG (Kanan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Kiri: Pertanyaan --}}
            <div class="lg:col-span-8 pertanyaan-container">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase">Pertanyaan</label>
                    <div class="flex items-center gap-1 bg-gray-100 p-0.5 rounded-lg border border-gray-200 text-xs">
                        <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'bold')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs font-bold text-gray-700 hover:text-blue-600 transition-all cursor-pointer" title="Tebal (Ctrl+B)">
                            <b>B</b>
                        </button>
                        <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'italic')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs italic text-gray-700 hover:text-blue-600 transition-all font-serif cursor-pointer" title="Miring (Ctrl+I)">
                            <i>I</i>
                        </button>
                        <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'underline')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs underline text-gray-700 hover:text-blue-600 transition-all cursor-pointer" title="Garis Bawah (Ctrl+U)">
                            <u>U</u>
                        </button>
                    </div>
                </div>
                <div contenteditable="true" class="soal-editor w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800" data-placeholder="Tulis pertanyaan disini...">{!! $soal['pertanyaan'] ?? '' !!}</div>
                <textarea name="soal[{{ $index }}][pertanyaan]" class="soal-textarea hidden">{{ $soal['pertanyaan'] ?? '' }}</textarea>
            </div>

            {{-- Kanan: Gambar Pendukung & Kunci PG --}}
            <div class="lg:col-span-4 flex flex-col gap-3">
                {{-- Upload Gambar --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Pendukung</label>
                    <div class="soal-image-upload relative w-full h-[110px] border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden group/upload">
                        @php $gambarPath = $soal['gambar_path'] ?? null; @endphp
                        <img class="image-preview absolute inset-0 w-full h-full object-contain bg-white p-2" 
                             src="{{ $gambarPath ? asset('storage/' . $gambarPath) : '' }}" 
                             style="display: {{ $gambarPath ? 'block' : 'none' }};">
                        
                        <div class="upload-text text-center p-2 {{ $gambarPath ? 'hidden' : '' }}">
                            <i class="bi bi-cloud-arrow-up-fill text-2xl text-gray-300 group-hover/upload:text-blue-500 transition-colors"></i>
                            <p class="text-[11px] text-gray-500 mt-0.5 font-medium">Upload Gambar</p>
                        </div>
                        <input type="file" name="soal[{{ $index }}][gambar]" class="soal-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                        @if($gambarPath)
                        <input type="hidden" name="soal[{{ $index }}][gambar_old]" value="{{ $gambarPath }}" class="soal-image-hidden-old">
                        @endif
                    </div>
                </div>

                {{-- Kunci Jawaban (HANYA UNTUK PILIHAN GANDA) --}}
                <div class="key-section key-pilihan_ganda bg-blue-50 rounded-xl p-3 border border-blue-100 {{ $tipe != 'pilihan_ganda' ? 'hidden' : '' }}">
                    <label class="block text-[11px] font-bold text-blue-800 uppercase mb-1">Kunci Jawaban PG</label>
                    <div class="relative">
                        <select name="soal[{{ $index }}][kunci_jawaban]" class="w-full px-3 py-1.5 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-xs font-bold text-blue-700 appearance-none cursor-pointer">
                            <option value="" disabled {{ !isset($soal['kunci_jawaban']) ? 'selected' : '' }}>-- Pilih Kunci --</option>
                            @foreach(['A','B','C','D'] as $huruf)
                            <option value="{{ $huruf }}" {{ ($soal['kunci_jawaban'] ?? '') == $huruf ? 'selected' : '' }}>Jawaban {{ $huruf }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-500">
                            <i class="bi bi-check-circle-fill text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Baris Bawah: Container Jawaban Dinamis (FULL WIDTH) --}}
        <div class="answers-container space-y-4 w-full">
            {{-- 1. PILIHAN GANDA (Default) --}}
            <div class="type-section type-pilihan_ganda space-y-3 {{ $tipe != 'pilihan_ganda' ? 'hidden' : '' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    @foreach(['a','b','c','d'] as $opsi)
                    @php $imgOpsi = $soal['gambar_'.$opsi] ?? null; @endphp
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50/70 border border-slate-200/80 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center shrink-0 border border-blue-200 uppercase text-xs">
                            {{ $opsi }}
                        </div>
                        <input type="text" name="soal[{{ $index }}][opsi_{{ $opsi }}]" value="{{ $soal['opsi_'.$opsi] !== '-' ? ($soal['opsi_'.$opsi] ?? '') : '' }}" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                        {{-- Upload Gambar Opsi --}}
                        <div class="option-image-upload shrink-0 relative w-9 h-9 border border-dashed border-gray-300 rounded-lg bg-white hover:bg-gray-50 cursor-pointer overflow-hidden group">
                            <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $imgOpsi ? asset('storage/' . $imgOpsi) : '' }}" style="display: {{ $imgOpsi ? 'block' : 'none' }};">
                            <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500" style="display: {{ $imgOpsi ? 'none' : 'flex' }};">
                                <i class="bi bi-image text-xs"></i>
                            </div>
                            <input type="file" name="soal[{{ $index }}][gambar_{{ $opsi }}]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            @if($imgOpsi)
                            <input type="hidden" name="soal[{{ $index }}][gambar_{{ $opsi }}_old]" value="{{ $imgOpsi }}" class="opt-existing-img">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. BENAR / SALAH (MULTI STATEMENT) --}}
            <div class="type-section type-benar_salah space-y-3 {{ $tipe != 'benar_salah' ? 'hidden' : '' }}">
                <div class="bg-green-50 p-3 rounded-lg text-xs text-green-700 mb-2">
                    <i class="bi bi-info-circle mr-1"></i> Klik "Tambah Pilihan" jika diperlukan. Pilih radio button (BENAR/SALAH) untuk tiap pernyataan.
                </div>
                
                <div class="bs-options-container space-y-2">
                    @php
                        $data = is_string($soal['data_soal'] ?? '') ? json_decode($soal['data_soal'], true) : ($soal['data_soal'] ?? []);
                        $pernyataan = $data['pernyataan'] ?? ($data['options'] ?? []);
                    @endphp
                    @foreach($pernyataan as $pidx => $opt)
                    @php $pUid = $opt['id'] ?? $pidx; @endphp
                    <div class="bs-item flex items-center gap-3" data-id="{{ $pUid }}">
                        {{-- Text & Image --}}
                        <div class="flex-1 flex items-center gap-2">
                            <input type="text" name="soal[{{ $index }}][bs_pernyataan][{{ $pUid }}][text]" value="{{ $opt['text'] ?? '' }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-green-500 outline-none text-sm" placeholder="Tulis Pernyataan...">
                            
                            {{-- Option Image --}}
                            @php $optImg = $opt['gambar'] ?? null; @endphp
                            <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                                <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $optImg ? asset('storage/' . $optImg) : '' }}" style="display: {{ $optImg ? 'block' : 'none' }};">
                                <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500" style="display: {{ $optImg ? 'none' : 'flex' }};">
                                    <i class="bi bi-image text-sm"></i>
                                </div>
                                <input type="file" name="soal[{{ $index }}][bs_pernyataan][{{ $pUid }}][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            </div>
                        </div>

                        {{-- Choice: Benar / Salah --}}
                        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl border border-gray-200 shrink-0">
                            <label class="cursor-pointer px-3 py-1.5 rounded-lg transition-all text-[10px] font-bold has-[:checked]:bg-green-600 has-[:checked]:text-white text-gray-500 hover:bg-gray-200">
                                <input type="radio" name="soal[{{ $index }}][bs_pernyataan][{{ $pUid }}][correct]" value="TRUE" class="hidden" {{ ($opt['correct'] ?? '') == 'TRUE' ? 'checked' : '' }}>
                                BENAR
                            </label>
                            <div class="w-px h-4 bg-gray-300"></div>
                            <label class="cursor-pointer px-3 py-1.5 rounded-lg transition-all text-[10px] font-bold has-[:checked]:bg-red-600 has-[:checked]:text-white text-gray-500 hover:bg-gray-200">
                                <input type="radio" name="soal[{{ $index }}][bs_pernyataan][{{ $pUid }}][correct]" value="FALSE" class="hidden" {{ ($opt['correct'] ?? '') == 'FALSE' ? 'checked' : '' }}>
                                SALAH
                            </label>
                        </div>

                        <button type="button" class="remove-bs-btn text-red-300 hover:text-red-500 shrink-0">
                            <i class="bi bi-x-circle-fill text-lg"></i>
                        </button>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="add-bs-btn text-xs font-bold text-green-600 hover:text-green-800 flex items-center gap-1 mt-2">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pilihan
                </button>
            </div>

            {{-- 3. JAWABAN GANDA --}}
            <div class="type-section type-jawaban_ganda space-y-3 {{ $tipe != 'jawaban_ganda' ? 'hidden' : '' }}">
                <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700 mb-2">
                    <i class="bi bi-info-circle mr-1"></i> Klik "Tambah Opsi" untuk menambah pilihan jawaban. Centang kotak di kanan untuk menandai jawaban benar.
                </div>
                
                <div class="jg-options-container space-y-2">
                    @php
                        $dataJG = is_string($soal['data_soal'] ?? '') ? json_decode($soal['data_soal'], true) : ($soal['data_soal'] ?? []);
                        $optionsJG = $dataJG['options'] ?? [];
                        $kunciJG = explode(',', $soal['kunci_jawaban'] ?? '');
                        $alphabet = range('A', 'Z');
                    @endphp
                    @foreach($optionsJG as $oidx => $opt)
                    @php $oUid = $opt['id'] ?? $alphabet[$oidx] ?? $oidx; @endphp
                    <div class="jg-item flex items-center gap-3">
                        <div class="jg-label w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">
                            {{ $alphabet[$oidx] ?? $oUid }}
                        </div>
                        <input type="text" name="soal[{{ $index }}][jg_options][{{ $oUid }}][text]" value="{{ $opt['text'] ?? '' }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan Jawaban">
                        
                        {{-- Option Image --}}
                        @php $optImgJg = $opt['gambar'] ?? null; @endphp
                        <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                            <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $optImgJg ? asset('storage/' . $optImgJg) : '' }}" style="display: {{ $optImgJg ? 'block' : 'none' }};">
                            <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500" style="display: {{ $optImgJg ? 'none' : 'flex' }};">
                                <i class="bi bi-image text-sm"></i>
                            </div>
                            <input type="file" name="soal[{{ $index }}][jg_options][{{ $oUid }}][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            <input type="hidden" name="soal[{{ $index }}][jg_options][{{ $oUid }}][existing_gambar]" value="{{ $optImgJg }}" class="jg-existing-img">
                        </div>

                        <div class="shrink-0 flex items-center gap-3">
                            <input type="checkbox" name="soal[{{ $index }}][kunci_jawaban_jg][]" value="{{ $alphabet[$oidx] ?? $oUid }}" class="jg-checkbox w-6 h-6 text-blue-600 rounded focus:ring-blue-500 border-gray-300 cursor-pointer" title="Tandai sebagai jawaban benar" {{ in_array($alphabet[$oidx] ?? $oUid, $kunciJG) ? 'checked' : '' }}>
                            <button type="button" class="remove-jg-btn text-red-400 hover:text-red-600">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="add-jg-btn text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-2">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Opsi Jawaban
                </button>
            </div>

            {{-- 4. MENCOCOKKAN (INTERAKTIF DENGAN PAPAN GARIS PENGHUBUNG) --}}
            <div class="type-section type-menjodohkan space-y-4 {{ $tipe != 'menjodohkan' ? 'hidden' : '' }}">
                <div class="bg-blue-50 p-3.5 rounded-xl text-xs text-blue-700 border border-blue-100 flex items-start gap-2">
                    <i class="bi bi-info-circle-fill text-blue-500 mt-0.5 shrink-0"></i>
                    <div>
                        <b>Panduan Membuat Soal Mencocokkan:</b>
                        <ol class="list-decimal list-inside mt-1 space-y-0.5 text-blue-800">
                            <li>Tambahkan item pada daftar <b>Premis (Kiri)</b> dan <b>Pilihan Jawaban (Kanan)</b>.</li>
                            <li>Tentukan kunci jawaban pada <b>Papan Kunci Pasangan</b> di bawah dengan cara mengklik item kiri lalu mengklik item kanan untuk menarik garis.</li>
                            <li>Bebas menghubungkan 1-ke-1, 1-ke-banyak, maupun membiarkan item tanpa pasangan (pengecoh).</li>
                        </ol>
                    </div>
                </div>

                @php
                    $dataSoalArr = is_string($soal['data_soal'] ?? '') ? json_decode($soal['data_soal'], true) : ($soal['data_soal'] ?? []);
                    $leftList = [];
                    $rightList = [];
                    $correctPairs = [];

                    if (isset($dataSoalArr['left_items']) || isset($dataSoalArr['right_items'])) {
                        $leftList = $dataSoalArr['left_items'] ?? [];
                        $rightList = $dataSoalArr['right_items'] ?? [];
                        $correctPairs = $dataSoalArr['correct_pairs'] ?? [];
                    } elseif (isset($dataSoalArr['matches']) && is_array($dataSoalArr['matches'])) {
                        foreach ($dataSoalArr['matches'] as $midx => $m) {
                            $lId = 'L' . $midx;
                            $rId = 'R' . $midx;
                            $leftList[] = ['id' => $lId, 'text' => $m['left'] ?? '', 'gambar' => $m['gambar_left'] ?? null];
                            $rightList[] = ['id' => $rId, 'text' => $m['right'] ?? '', 'gambar' => $m['gambar_right'] ?? null];
                            $correctPairs[] = ['left' => $lId, 'right' => $rId];
                        }
                    }
                    $alphabet = range('A', 'Z');
                @endphp

                {{-- DUA KOLOM INPUT: PREMIS KIRI & PILIHAN KANAN --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- SISI KIRI (PREMIS / PERTANYAAN) --}}
                    <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bi bi-card-text text-blue-600 text-sm"></i> Premis (Sisi Kiri)
                            </span>
                            <button type="button" class="add-match-left-btn text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-blue-200 shadow-2xs">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Item Kiri
                            </button>
                        </div>
                        <div class="match-lefts-container space-y-2">
                            @foreach($leftList as $lidx => $lItem)
                            @php 
                                $lUid = $lItem['id'] ?? ('L' . $lidx);
                                $lImg = $lItem['gambar'] ?? null;
                            @endphp
                            <div class="match-left-item flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-200 shadow-2xs" data-id="{{ $lUid }}">
                                <span class="left-num-badge px-2 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs shrink-0">
                                    {{ $lidx + 1 }}
                                </span>
                                <input type="hidden" name="soal[{{ $index }}][left_items][{{ $lidx }}][id]" value="{{ $lUid }}" class="left-id-input">
                                <input type="text" name="soal[{{ $index }}][left_items][{{ $lidx }}][text]" value="{{ $lItem['text'] ?? '' }}" class="left-text-input flex-1 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:bg-white focus:border-blue-500 outline-none" placeholder="Tulis premis...">
                                
                                {{-- Option Image --}}
                                <div class="option-image-upload shrink-0 relative w-8 h-8 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                                    <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $lImg ? asset('storage/' . $lImg) : '' }}" style="display: {{ $lImg ? 'block' : 'none' }};">
                                    <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500" style="display: {{ $lImg ? 'none' : 'flex' }};">
                                        <i class="bi bi-image text-xs"></i>
                                    </div>
                                    <input type="file" name="soal[{{ $index }}][left_items][{{ $lidx }}][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                    <input type="hidden" name="soal[{{ $index }}][left_items][{{ $lidx }}][existing_gambar]" value="{{ $lImg }}" class="left-existing-img">
                                </div>

                                <button type="button" class="remove-match-left-btn text-slate-300 hover:text-rose-500 shrink-0 p-1">
                                    <i class="bi bi-trash3-fill text-sm"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- SISI KANAN (PILIHAN JAWABAN) --}}
                    <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bi bi-list-check text-emerald-600 text-sm"></i> Pilihan (Sisi Kanan)
                            </span>
                            <button type="button" class="add-match-right-btn text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Pilihan Kanan
                            </button>
                        </div>
                        <div class="match-rights-container space-y-2">
                            @foreach($rightList as $ridx => $rItem)
                            @php 
                                $rUid = $rItem['id'] ?? ('R' . $ridx);
                                $rLabel = $alphabet[$ridx] ?? $rUid;
                                $rImg = $rItem['gambar'] ?? null;
                            @endphp
                            <div class="match-right-item flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-200 shadow-2xs" data-id="{{ $rUid }}">
                                <div class="right-label-badge w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $rLabel }}
                                </div>
                                <input type="hidden" name="soal[{{ $index }}][right_items][{{ $ridx }}][id]" value="{{ $rUid }}" class="right-id-input">
                                <input type="text" name="soal[{{ $index }}][right_items][{{ $ridx }}][text]" value="{{ $rItem['text'] ?? '' }}" class="right-text-input flex-1 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:bg-white focus:border-emerald-500 outline-none" placeholder="Tulis pilihan...">
                                
                                {{-- Option Image --}}
                                <div class="option-image-upload shrink-0 relative w-8 h-8 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                                    <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $rImg ? asset('storage/' . $rImg) : '' }}" style="display: {{ $rImg ? 'block' : 'none' }};">
                                    <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-emerald-500" style="display: {{ $rImg ? 'none' : 'flex' }};">
                                        <i class="bi bi-image text-xs"></i>
                                    </div>
                                    <input type="file" name="soal[{{ $index }}][right_items][{{ $ridx }}][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                    <input type="hidden" name="soal[{{ $index }}][right_items][{{ $ridx }}][existing_gambar]" value="{{ $rImg }}" class="right-existing-img">
                                </div>

                                <button type="button" class="remove-match-right-btn text-slate-300 hover:text-rose-500 shrink-0 p-1">
                                    <i class="bi bi-trash3-fill text-sm"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- PAPAN KUNCI PASANGAN (HUBUNGKAN DENGAN GARIS) --}}
                <div class="teacher-matching-board bg-white p-4 rounded-2xl border-2 border-indigo-100 shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-indigo-50">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                                <i class="bi bi-bezier2"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-800">Papan Kunci Pasangan (Hubungkan dengan Garis)</h4>
                                <p class="text-[11px] text-gray-400">Klik item Kiri lalu klik item Kanan untuk memasangkan (atau klik ulang untuk memutus).</p>
                            </div>
                        </div>
                        <button type="button" class="teacher-reset-match-btn text-[11px] font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg border border-rose-200 transition-colors flex items-center gap-1">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Garis
                        </button>
                    </div>

                    {{-- Interactive Canvas --}}
                    <div class="teacher-match-canvas relative select-none p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 min-h-[160px]">
                        <svg class="absolute inset-0 w-full h-full pointer-events-none z-10 teacher-match-svg"></svg>
                        
                        <div class="flex flex-col sm:flex-row justify-between relative z-20 gap-6 sm:gap-10">
                            {{-- Sisi Kiri Board --}}
                            <div class="teacher-board-left flex-1 space-y-2.5">
                                {{-- Dynamically populated by JS --}}
                            </div>

                            {{-- Sisi Kanan Board --}}
                            <div class="teacher-board-right flex-1 space-y-2.5">
                                {{-- Dynamically populated by JS --}}
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="soal[{{ $index }}][correct_pairs_json]" class="match-pairs-json" value='@json($correctPairs)'>
                </div>
            </div>
        </div>
    </div>
</div>v>
