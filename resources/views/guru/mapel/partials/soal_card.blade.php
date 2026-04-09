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

    <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kiri: Pertanyaan & Konten --}}
        <div class="lg:col-span-8 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pertanyaan</label>
                <textarea name="soal[{{ $index }}][pertanyaan]" class="soal-textarea w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800" rows="3" placeholder="Tulis pertanyaan disini...">{{ $soal['pertanyaan'] ?? '' }}</textarea>
            </div>

            {{-- Container Jawaban Dinamis --}}
            <div class="answers-container space-y-4">
                {{-- 1. PILIHAN GANDA (Default) --}}
                <div class="type-section type-pilihan_ganda space-y-3 {{ $tipe != 'pilihan_ganda' ? 'hidden' : '' }}">
                    @foreach(['a','b','c','d'] as $opsi)
                    @php $imgOpsi = $soal['gambar_'.$opsi] ?? null; @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">
                            {{ $opsi }}
                        </div>
                        <input type="text" name="soal[{{ $index }}][opsi_{{ $opsi }}]" value="{{ $soal['opsi_'.$opsi] !== '-' ? ($soal['opsi_'.$opsi] ?? '') : '' }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                        {{-- Upload Gambar Opsi — struktur SAMA dengan template JS --}}
                        <label class="shrink-0 cursor-pointer w-10 h-10 rounded-lg bg-gray-50 hover:bg-gray-100 border {{ $imgOpsi ? 'border-blue-400 p-0' : 'border-gray-200' }} flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar Opsi">
                            <i class="bi bi-image text-gray-400 opsi-icon {{ $imgOpsi ? 'hidden' : '' }}"></i>
                            <img src="{{ $imgOpsi ? asset('storage/' . $imgOpsi) : '' }}" class="opsi-image-preview {{ $imgOpsi ? '' : 'hidden' }} w-full h-full object-cover rounded-lg">
                            <input type="file" name="soal[{{ $index }}][gambar_{{ $opsi }}]" class="hidden opsi-image-input" accept="image/*" onchange="previewOpsiImage(this)">
                            @if($imgOpsi)
                            <input type="hidden" name="soal[{{ $index }}][gambar_{{ $opsi }}_old]" value="{{ $imgOpsi }}" class="opsi-image-hidden-old">
                            @endif
                        </label>
                    </div>
                    @endforeach
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

                {{-- 4. MENCOCOKKAN --}}
                <div class="type-section type-menjodohkan space-y-4 {{ $tipe != 'menjodohkan' ? 'hidden' : '' }}">
                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                        <i class="bi bi-info-circle mr-1"></i> Buat pasangan pertanyaan (kiri) dan jawaban (kanan) yang sesuai.
                    </div>
                    
                    <div class="matches-container space-y-2">
                        @if(isset($soal['data_soal']['matches']) && is_array($soal['data_soal']['matches']))
                            @foreach($soal['data_soal']['matches'] as $midx => $match)
                                <div class="match-item flex flex-col gap-2 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        {{-- Left Side --}}
                                        <div class="flex-1 flex items-center gap-2">
                                            <input type="text" name="soal[{{ $index }}][matches][{{ $midx }}][left]" value="{{ $match['left'] ?? '' }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none" placeholder="Pernyataan Kiri">
                                            @php $imgL = $match['gambar_left'] ?? null; @endphp
                                            <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                                                <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $imgL ? asset('storage/' . $imgL) : '' }}" style="display: {{ $imgL ? 'block' : 'none' }};">
                                                <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500" style="display: {{ $imgL ? 'none' : 'flex' }};">
                                                    <i class="bi bi-image text-sm"></i>
                                                </div>
                                                <input type="file" name="soal[{{ $index }}][matches][{{ $midx }}][gambar_left]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                            </div>
                                        </div>

                                        <div class="text-gray-400"><i class="bi bi-arrow-right-circle-fill"></i></div>

                                        {{-- Right Side --}}
                                        <div class="flex-1 flex items-center gap-2">
                                            <input type="text" name="soal[{{ $index }}][matches][{{ $midx }}][right]" value="{{ $match['right'] ?? '' }}" class="w-full px-3 py-2 bg-white border border-green-200 rounded-lg text-sm focus:border-green-500 outline-none" placeholder="Jawaban Kanan">
                                            @php $imgR = $match['gambar_right'] ?? null; @endphp
                                            <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                                                <img class="opt-preview absolute inset-0 w-full h-full object-cover" src="{{ $imgR ? asset('storage/' . $imgR) : '' }}" style="display: {{ $imgR ? 'block' : 'none' }};">
                                                <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500" style="display: {{ $imgR ? 'none' : 'flex' }};">
                                                    <i class="bi bi-image text-sm"></i>
                                                </div>
                                                <input type="file" name="soal[{{ $index }}][matches][{{ $midx }}][gambar_right]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                            </div>
                                        </div>
                                        <button type="button" class="remove-match-btn text-red-300 hover:text-red-500 shrink-0">
                                            <i class="bi bi-x-circle-fill text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <button type="button" class="add-match-btn text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-2">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Pasangan
                    </button>
                </div>
            </div>
        </div>

        {{-- Kanan: Gambar & Kunci (Sidebar) --}}
        <div class="lg:col-span-4 space-y-4">
            
            {{-- Upload Gambar --}}
            <div class="bg-white">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Pendukung</label>
                <div class="soal-image-upload relative w-full h-40 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden group/upload">
                    @php $gambarPath = $soal['gambar_path'] ?? null; @endphp
                    <img class="image-preview absolute inset-0 w-full h-full object-contain bg-white p-2" 
                         src="{{ $gambarPath ? asset('storage/' . $gambarPath) : '' }}" 
                         style="display: {{ $gambarPath ? 'block' : 'none' }};">
                    
                    <div class="upload-text text-center p-4 {{ $gambarPath ? 'hidden' : '' }}">
                        <i class="bi bi-cloud-arrow-up-fill text-3xl text-gray-300 group-hover/upload:text-blue-500 transition-colors"></i>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Upload Gambar</p>
                    </div>
                    <input type="file" name="soal[{{ $index }}][gambar]" class="soal-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                    @if($gambarPath)
                    <input type="hidden" name="soal[{{ $index }}][gambar_old]" value="{{ $gambarPath }}" class="soal-image-hidden-old">
                    @endif
                </div>
            </div>

            {{-- Kunci Jawaban (HANYA UNTUK PILIHAN GANDA) --}}
            <div class="key-section key-pilihan_ganda bg-blue-50 rounded-xl p-4 border border-blue-100 {{ $tipe != 'pilihan_ganda' ? 'hidden' : '' }}">
                <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Kunci Jawaban</label>
                <div class="relative">
                    <select name="soal[{{ $index }}][kunci_jawaban]" class="w-full px-3 py-2 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-sm font-bold text-blue-700 appearance-none cursor-pointer">
                        <option value="" disabled {{ !isset($soal['kunci_jawaban']) ? 'selected' : '' }}>-- Pilih Kunci --</option>
                        @foreach(['A','B','C','D'] as $huruf)
                        <option value="{{ $huruf }}" {{ ($soal['kunci_jawaban'] ?? '') == $huruf ? 'selected' : '' }}>Jawaban {{ $huruf }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-500">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
