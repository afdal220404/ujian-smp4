{{-- MODAL PALET SIMBOL MATEMATIKA & PANGKAT --}}
<style>
.math-frac {
    display: inline-flex !important;
    flex-direction: column !important;
    vertical-align: middle !important;
    text-align: center !important;
    padding: 0 0.15em !important;
    font-size: 0.92em !important;
    line-height: 1 !important;
    font-weight: inherit !important;
    margin: 0 0.15em !important;
    user-select: text;
}
.math-frac .frac-top {
    display: block !important;
    padding: 0 0.25em 0.1em 0.25em !important;
    border-bottom: 1.5px solid currentColor !important;
    font-size: 0.82em !important;
    line-height: 1.15 !important;
    text-align: center !important;
}
.math-frac .frac-bottom {
    display: block !important;
    padding: 0.1em 0.25em 0 0.25em !important;
    font-size: 0.82em !important;
    line-height: 1.15 !important;
    text-align: center !important;
}
</style>
<div id="math-symbol-modal" class="fixed inset-0 z-[9999] hidden select-none overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeSymbolPicker()"></div>

    <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
            
            {{-- Header --}}
            <div class="symbol-header-gradient px-4 sm:px-5 py-3.5 text-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-base shadow-inner">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-bold font-[Poppins-Bold] leading-tight break-words" id="modal-title">Palet Simbol &amp; Format Matematika</h3>
                        <p class="text-[11px] text-white/80 font-medium leading-tight break-words">Klik simbol di bawah untuk menyisipkan ke kolom teks yang aktif</p>
                    </div>
                </div>
                <button type="button" onclick="closeSymbolPicker()" class="w-8 h-8 shrink-0 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-lg cursor-pointer" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Target Info & Quick Math Bar --}}
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-col gap-2">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5 font-medium truncate">
                        <i class="bi bi-cursor-fill text-blue-600 text-xs"></i> 
                        Menyisipkan ke: <span id="symbol-target-name" class="font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">Kolom Jawaban</span>
                    </span>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 hidden sm:inline-flex items-center gap-1">
                        <i class="bi bi-lightning-charge-fill"></i> 1-Klik Langsung Masuk
                    </span>
                </div>

                {{-- Live Preview (BISA DIEDIT): guru dapat menyeleksi / mengetik ulang di sini,
                     lalu menekan tombol Pangkat Panjang / Indeks Bawah atau simbol lain. --}}
                <div id="symbol-preview-wrap" style="display:none" class="items-start gap-2 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0 flex items-center gap-1 mt-1.5">
                        <i class="bi bi-pencil-square text-blue-500"></i> Pratinjau
                    </span>
                    <div id="symbol-preview" contenteditable="true" spellcheck="false" data-placeholder="Ketik atau tempel di sini, lalu klik tombol simbol…"
                         class="text-sm text-slate-800 break-words min-w-0 max-h-24 overflow-y-auto flex-1 rounded-md px-2 py-1 min-h-[32px] outline-none focus:ring-2 focus:ring-blue-300 focus:bg-blue-50/40 cursor-text transition-shadow"></div>
                </div>

                {{-- Quick Access Bar (Paling Sering Digunakan) --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-0.5 no-scrollbar">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Cepat:</span>
                    @php
                        $quickSymbols = [
                            ['s' => '²', 't' => 'Pangkat 2 (x²)'],
                            ['s' => '³', 't' => 'Pangkat 3 (x³)'],
                            ['s' => '⁴', 't' => 'Pangkat 4'],
                            ['s' => 'ⁿ', 't' => 'Pangkat n'],
                            ['s' => '×', 't' => 'Kali (×)'],
                            ['s' => '÷', 't' => 'Bagi (÷)'],
                            ['s' => '±', 't' => 'Plus-Minus (±)'],
                            ['s' => '√', 't' => 'Akar Kuadrat (√)'],
                            ['s' => 'π', 't' => 'Pi (π)'],
                            ['s' => '°', 't' => 'Derajat (°)'],
                            ['s' => '½', 't' => 'Pecahan Setengah'],
                            ['s' => '¼', 't' => 'Pecahan Seperempat'],
                            ['s' => '¾', 't' => 'Pecahan Tiga Perempat'],
                            ['s' => '≠', 't' => 'Tidak Sama Dengan (≠)'],
                            ['s' => '≤', 't' => 'Kurang Dari atau Sama (≤)'],
                            ['s' => '≥', 't' => 'Lebih Dari atau Sama (≥)'],
                            ['s' => '∞', 't' => 'Tak Hingga (∞)'],
                        ];
                    @endphp
                    @foreach($quickSymbols as $qs)
                        <button type="button" onclick="insertSymbol('{{ $qs['s'] }}')" class="h-8 min-w-[32px] px-2 rounded-lg bg-white hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-800 text-xs font-bold transition-all shadow-2xs hover:shadow hover:scale-105 active:scale-95 shrink-0 flex items-center justify-center cursor-pointer" title="{{ $qs['t'] }}">
                            {{ $qs['s'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="symbol-tab-bar flex flex-nowrap items-center gap-[0.2em] px-3 pt-1.5 border-b border-slate-200 bg-white overflow-x-auto no-scrollbar text-[12px]">
                <button type="button" onclick="switchSymbolTab('pangkat')" class="symbol-tab-btn active px-[0.6em] py-[0.7em] font-bold border-b-2 border-blue-600 text-blue-600 flex items-center gap-[0.35em] whitespace-nowrap shrink-0 cursor-pointer" data-tab="pangkat">
                    <i class="bi bi-superscript"></i> Pangkat & Indeks
                </button>
                <button type="button" onclick="switchSymbolTab('aritmatika')" class="symbol-tab-btn px-[0.6em] py-[0.7em] font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-[0.35em] whitespace-nowrap shrink-0 cursor-pointer" data-tab="aritmatika">
                    <i class="bi bi-plus-slash-minus"></i> Aritmatika & Aljabar
                </button>
                <button type="button" onclick="switchSymbolTab('pecahan')" class="symbol-tab-btn px-[0.6em] py-[0.7em] font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-[0.35em] whitespace-nowrap shrink-0 cursor-pointer" data-tab="pecahan">
                    <i class="bi bi-pie-chart"></i> Pecahan
                </button>
                <button type="button" onclick="switchSymbolTab('geometri')" class="symbol-tab-btn px-[0.6em] py-[0.7em] font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-[0.35em] whitespace-nowrap shrink-0 cursor-pointer" data-tab="geometri">
                    <i class="bi bi-triangle"></i> Geometri & Sains
                </button>
                <button type="button" onclick="switchSymbolTab('logika')" class="symbol-tab-btn px-[0.6em] py-[0.7em] font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-[0.35em] whitespace-nowrap shrink-0 cursor-pointer" data-tab="logika">
                    <i class="bi bi-arrow-left-right"></i> Logika & Panah
                </button>
            </div>

            {{-- Tab Content Panes --}}
            <div class="p-4 max-h-[360px] overflow-y-auto">
                
                {{-- 1. TAB: PANGKAT & INDEKS --}}
                <div id="tab-pangkat" class="symbol-pane space-y-4">
                    {{-- Pangkat Atas (Superscripts) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Pangkat Angka (Superscript)
                            </span>
                            <span class="text-[10px] text-slate-400">Contoh: x², 10⁵, cm³</span>
                        </div>
                        <div class="grid grid-cols-5 sm:grid-cols-10 gap-1.5">
                            @foreach(['⁰'=>'0', '¹'=>'1', '²'=>'2', '³'=>'3', '⁴'=>'4', '⁵'=>'5', '⁶'=>'6', '⁷'=>'7', '⁸'=>'8', '⁹'=>'9'] as $sym => $label)
                                <button type="button" onclick="insertSymbol('{{ $sym }}')" class="h-10 rounded-xl bg-slate-50 hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-800 font-bold text-base transition-all hover:scale-105 active:scale-95 flex flex-col items-center justify-center cursor-pointer shadow-2xs" title="Pangkat {{ $label }}">
                                    <span>x{{ $sym }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pangkat Variabel & Tanda --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Pangkat Huruf & Tanda Operasi
                            </span>
                            <span class="text-[10px] text-slate-400">Contoh: xⁿ, y⁻¹, a⁺</span>
                        </div>
                        <div class="grid grid-cols-4 sm:grid-cols-8 gap-1.5">
                            @foreach(['ⁿ'=>'n', 'ᵐ'=>'m', 'ˣ'=>'x', 'ʸ'=>'y', 'ᵃ'=>'a', 'ᵇ'=>'b', '⁺'=>'+', '⁻'=>'-', '⁼'=>'=', '⁽'=>'(', '⁾'=>')'] as $sym => $label)
                                <button type="button" onclick="insertSymbol('{{ $sym }}')" class="h-10 rounded-xl bg-slate-50 hover:bg-indigo-600 hover:text-white border border-slate-200 text-slate-800 font-bold text-sm transition-all hover:scale-105 active:scale-95 flex items-center justify-center cursor-pointer shadow-2xs" title="Pangkat {{ $label }}">
                                    x{{ $sym }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Indeks Bawah (Subscripts) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Indeks Bawah (Subscript)
                            </span>
                            <span class="text-[10px] text-slate-400">Contoh: x₁, H₂O, CO₂</span>
                        </div>
                        <div class="grid grid-cols-5 sm:grid-cols-10 gap-1.5">
                            @foreach(['₀'=>'0', '₁'=>'1', '₂'=>'2', '₃'=>'3', '₄'=>'4', '₅'=>'5', '₆'=>'6', '₇'=>'7', '₈'=>'8', '₉'=>'9'] as $sym => $label)
                                <button type="button" onclick="insertSymbol('{{ $sym }}')" class="h-10 rounded-xl bg-slate-50 hover:bg-emerald-600 hover:text-white border border-slate-200 text-slate-800 font-bold text-base transition-all hover:scale-105 active:scale-95 flex flex-col items-center justify-center cursor-pointer shadow-2xs" title="Indeks {{ $label }}">
                                    <span>x{{ $sym }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Format Pangkat Bebas (HTML Helper) --}}
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-blue-900 flex items-center gap-1">
                                <i class="bi bi-code-slash"></i> Pangkat Panjang / Rumus Kompleks
                            </p>
                            <p class="text-[11px] text-blue-700">Blok (seleksi) dulu teks seperti x<sup>2y+1</sup> atau H<sub>2</sub>O, lalu klik tombol. Klik tombol sekali lagi untuk mengembalikannya ke teks normal.</p>
                        </div>
                        <div class="flex items-center justify-end gap-2 shrink-0">
                            <button type="button" onclick="insertCustomTag('sup')" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5 whitespace-nowrap cursor-pointer" title="Blok teks dulu, lalu klik untuk menjadikannya pangkat panjang (contoh: x&lt;sup&gt;2y+1&lt;/sup&gt;). Klik lagi untuk membatalkan.">
                                <i class="bi bi-superscript"></i> Pangkat Panjang
                                <span class="text-[10px] font-normal bg-white/20 rounded px-1">x<sup>2y+1</sup></span>
                            </button>
                            <button type="button" onclick="insertCustomTag('sub')" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5 whitespace-nowrap cursor-pointer" title="Blok teks dulu, lalu klik untuk menjadikannya indeks bawah (contoh: H&lt;sub&gt;2&lt;/sub&gt;O). Klik lagi untuk membatalkan.">
                                <i class="bi bi-subscript"></i> Indeks Bawah
                                <span class="text-[10px] font-normal bg-white/20 rounded px-1">H<sub>2</sub>O</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. TAB: ARITMATIKA & ALJABAR --}}
                <div id="tab-aritmatika" class="symbol-pane hidden space-y-4">
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        @php
                            $mathSymbols = [
                                ['s' => '+', 'n' => 'Tambah'],
                                ['s' => '-', 'n' => 'Kurang'],
                                ['s' => '×', 'n' => 'Kali (Perkalian Silang)'],
                                ['s' => '·', 'n' => 'Titik Kali (Dot Product)'],
                                ['s' => '÷', 'n' => 'Bagi'],
                                ['s' => '±', 'n' => 'Plus Minus'],
                                ['s' => '∓', 'n' => 'Minus Plus'],
                                ['s' => '=', 'n' => 'Sama Dengan'],
                                ['s' => '≠', 'n' => 'Tidak Sama Dengan'],
                                ['s' => '≈', 'n' => 'Mendekati / Kira-kira'],
                                ['s' => '≡', 'n' => 'Kongruen / Ekuivalen'],
                                ['s' => '≤', 'n' => 'Kurang Dari atau Sama'],
                                ['s' => '≥', 'n' => 'Lebih Dari atau Sama'],
                                ['s' => '<', 'n' => 'Kurang Dari'],
                                ['s' => '>', 'n' => 'Lebih Dari'],
                                ['s' => '√', 'n' => 'Akar Kuadrat'],
                                ['s' => '∛', 'n' => 'Akar Pangkat Tiga'],
                                ['s' => '∜', 'n' => 'Akar Pangkat Empat'],
                                ['s' => '∞', 'n' => 'Tak Hingga'],
                                ['s' => '%', 'n' => 'Persen'],
                                ['s' => '‰', 'n' => 'Permil'],
                                ['s' => '°', 'n' => 'Derajat'],
                                ['s' => '∝', 'n' => 'Sebanding'],
                                ['s' => '|x|', 'n' => 'Nilai Mutlak', 'custom' => '|'],
                            ];
                        @endphp
                        @foreach($mathSymbols as $m)
                            <button type="button" onclick="insertSymbol('{{ $m['custom'] ?? $m['s'] }}')" class="h-12 rounded-xl bg-slate-50 hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-800 text-base font-bold transition-all hover:scale-105 active:scale-95 flex flex-col items-center justify-center cursor-pointer shadow-2xs group" title="{{ $m['n'] }}">
                                <span>{{ $m['s'] }}</span>
                                <span class="text-[9px] text-slate-400 group-hover:text-cyan-100 font-normal truncate max-w-[90%]">{{ $m['n'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 3. TAB: PECAHAN --}}
                <div id="tab-pecahan" class="symbol-pane hidden space-y-4">
                    
                    {{-- A. Pembuat Pecahan Kustom (Atas & Bawah) --}}
                    <div class="p-3.5 bg-gradient-to-br from-blue-50/80 to-indigo-50/60 rounded-2xl border border-blue-200/80 shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="bi bi-slash-lg"></i>
                                </span>
                                <span>Buat Pecahan Kustom (Atas & Bawah)</span>
                            </span>
                            <span class="text-[10px] text-blue-700 bg-blue-100/70 font-semibold px-2 py-0.5 rounded-full border border-blue-200">
                                Angka / Variabel Bebas
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            {{-- Input Pembilang & Penyebut --}}
                            <div class="sm:col-span-7 flex flex-col items-center justify-center gap-1 bg-white p-2.5 rounded-xl border border-blue-200 shadow-2xs">
                                <div class="w-full">
                                    <input type="text" id="custom-frac-top" placeholder="Pembilang (cth: 1 atau x)" oninput="updateCustomFracPreview()"
                                           class="w-full text-center text-sm font-bold text-slate-800 bg-slate-50 hover:bg-slate-100 focus:bg-white border border-slate-200 focus:border-blue-500 rounded-lg px-2.5 py-1.5 outline-none transition-all placeholder:font-normal placeholder:text-slate-400 placeholder:text-xs">
                                </div>
                                <div class="w-full h-0.5 bg-blue-600 rounded-full my-0.5"></div>
                                <div class="w-full">
                                    <input type="text" id="custom-frac-bottom" placeholder="Penyebut (cth: 6 atau 2y)" oninput="updateCustomFracPreview()"
                                           class="w-full text-center text-sm font-bold text-slate-800 bg-slate-50 hover:bg-slate-100 focus:bg-white border border-slate-200 focus:border-blue-500 rounded-lg px-2.5 py-1.5 outline-none transition-all placeholder:font-normal placeholder:text-slate-400 placeholder:text-xs">
                                </div>
                            </div>

                            {{-- Live Visual Preview Box --}}
                            <div class="sm:col-span-5 flex flex-col items-center justify-center gap-2">
                                <div id="custom-frac-preview-box" class="w-full h-20 bg-white border border-blue-200 rounded-xl flex flex-col items-center justify-center p-2 shadow-2xs transition-all">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hasil Tampilan:</span>
                                    <div id="custom-frac-preview-render" class="text-lg font-bold text-slate-800 flex items-center justify-center">
                                        <span class="text-xs text-slate-300 font-normal italic">Pecahan akan tampil di sini</span>
                                    </div>
                                </div>
                                
                                <div class="w-full flex items-center gap-1.5">
                                    <button type="button" onclick="insertCustomFraction()" 
                                            class="flex-1 py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold shadow-xs hover:shadow transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="bi bi-plus-circle-fill"></i> Sisipkan Pecahan
                                    </button>
                                    <button type="button" onclick="resetCustomFraction()" title="Kosongkan input"
                                            class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-500 hover:text-slate-700 border border-slate-200 flex items-center justify-center text-xs transition-colors cursor-pointer shrink-0">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Template Pecahan Cepat yang Sering Dipakai Guru --}}
                        <div class="mt-3 pt-2.5 border-t border-blue-200/60 flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs">
                            <span class="text-[10px] font-bold text-blue-800 uppercase tracking-wider shrink-0 mr-1">Contoh Cepat:</span>
                            @foreach([['1','6'], ['2','5'], ['1','5'], ['1','4'], ['3','4'], ['2','3'], ['5','6'], ['7','12'], ['x','2'], ['a','b']] as $ex)
                                <button type="button" onclick="setAndInsertCustomFraction('{{ $ex[0] }}', '{{ $ex[1] }}')"
                                        class="h-7 px-2 rounded-lg bg-white/90 hover:bg-blue-600 hover:text-white border border-blue-200/90 text-blue-900 text-xs font-bold transition-all shadow-2xs hover:scale-105 active:scale-95 shrink-0 flex items-center justify-center cursor-pointer"
                                        title="Sisipkan {{ $ex[0] }}/{{ $ex[1] }}">
                                    <span class="math-frac text-[10px]"><span class="frac-top">{{ $ex[0] }}</span><span class="frac-bottom">{{ $ex[1] }}</span></span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- B. Pecahan Standar Siap Pakai (Tampilan Atas-Bawah) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Pecahan Standar Populer (1-Klik Masuk)
                            </span>
                            <span class="text-[10px] text-slate-400">Tampil bertingkat atas &amp; bawah</span>
                        </div>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @php
                                $standardFractions = [
                                    ['1', '2'], ['1', '3'], ['2', '3'], ['1', '4'], ['3', '4'],
                                    ['1', '5'], ['2', '5'], ['3', '5'], ['4', '5'], ['1', '6'],
                                    ['5', '6'], ['1', '8'], ['3', '8'], ['5', '8'], ['7', '8'],
                                    ['1', '10'], ['1', '7'], ['1', '9']
                                ];
                            @endphp
                            @foreach($standardFractions as $frac)
                                <button type="button" onclick="insertCustomFraction('{{ $frac[0] }}', '{{ $frac[1] }}')" class="h-13 rounded-xl bg-slate-50 hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-800 transition-all hover:scale-105 active:scale-95 flex flex-col items-center justify-center cursor-pointer shadow-2xs group" title="Pecahan {{ $frac[0] }}/{{ $frac[1] }}">
                                    <span class="math-frac text-base font-bold"><span class="frac-top">{{ $frac[0] }}</span><span class="frac-bottom">{{ $frac[1] }}</span></span>
                                    <span class="text-[9px] text-slate-400 group-hover:text-cyan-100 font-normal mt-0.5">{{ $frac[0] }}/{{ $frac[1] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- 4. TAB: GEOMETRI & SAINS --}}
                <div id="tab-geometri" class="symbol-pane hidden space-y-4">
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        @php
                            $geoSymbols = [
                                ['s' => 'π', 'n' => 'Pi (3.14...)'],
                                ['s' => '∠', 'n' => 'Sudut'],
                                ['s' => '∟', 'n' => 'Sudut Siku-Siku'],
                                ['s' => '∆', 'n' => 'Segitiga / Delta'],
                                ['s' => '⊥', 'n' => 'Tegak Lurus'],
                                ['s' => '∥', 'n' => 'Sejajar'],
                                ['s' => '≅', 'n' => 'Kongruen'],
                                ['s' => '∼', 'n' => 'Sebangun'],
                                ['s' => '∑', 'n' => 'Sigma (Jumlah)'],
                                ['s' => '∏', 'n' => 'Produk'],
                                ['s' => '∫', 'n' => 'Integral'],
                                ['s' => '∂', 'n' => 'Turunan Parsial'],
                                ['s' => 'α', 'n' => 'Alpha'],
                                ['s' => 'β', 'n' => 'Beta'],
                                ['s' => 'γ', 'n' => 'Gamma'],
                                ['s' => 'θ', 'n' => 'Theta (Sudut)'],
                                ['s' => 'λ', 'n' => 'Lambda (Panjang Gelombang)'],
                                ['s' => 'µ', 'n' => 'Mikro (µ)'],
                                ['s' => 'Ω', 'n' => 'Ohm (Hambatan)'],
                                ['s' => 'ρ', 'n' => 'Rho (Massa Jenis)'],
                                ['s' => 'σ', 'n' => 'Sigma'],
                                ['s' => 'φ', 'n' => 'Phi'],
                                ['s' => 'ω', 'n' => 'Omega'],
                                ['s' => '℃', 'n' => 'Derajat Celsius'],
                                ['s' => '℉', 'n' => 'Derajat Fahrenheit'],
                                ['s' => 'Å', 'n' => 'Angstrom'],
                            ];
                        @endphp
                        @foreach($geoSymbols as $g)
                            <button type="button" onclick="insertSymbol('{{ $g['s'] }}')" class="h-12 rounded-xl bg-slate-50 hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-800 text-base font-bold transition-all hover:scale-105 active:scale-95 flex flex-col items-center justify-center cursor-pointer shadow-2xs group" title="{{ $g['n'] }}">
                                <span>{{ $g['s'] }}</span>
                                <span class="text-[9px] text-slate-400 group-hover:text-cyan-100 font-normal truncate max-w-[90%]">{{ $g['n'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- 5. TAB: LOGIKA & PANAH --}}
                <div id="tab-logika" class="symbol-pane hidden space-y-4">
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        @php
                            $logicSymbols = [
                                ['s' => '→', 'n' => 'Panah Kanan'],
                                ['s' => '←', 'n' => 'Panah Kiri'],
                                ['s' => '↔', 'n' => 'Panah Bolak-balik'],
                                ['s' => '⇒', 'n' => 'Maka / Implikasi'],
                                ['s' => '⇔', 'n' => 'Jika dan Hanya Jika'],
                                ['s' => '∈', 'n' => 'Elemen / Anggota'],
                                ['s' => '∉', 'n' => 'Bukan Elemen'],
                                ['s' => '⊂', 'n' => 'Himpunan Bagian'],
                                ['s' => '⊆', 'n' => 'Bagian atau Sama'],
                                ['s' => '⊄', 'n' => 'Bukan Bagian'],
                                ['s' => '∪', 'n' => 'Gabungan (Union)'],
                                ['s' => '∩', 'n' => 'Irisan (Intersect)'],
                                ['s' => '∅', 'n' => 'Himpunan Kosong'],
                                ['s' => '∀', 'n' => 'Untuk Semua (For All)'],
                                ['s' => '∃', 'n' => 'Ada / Terdapat'],
                                ['s' => '∴', 'n' => 'Oleh Karena Itu'],
                                ['s' => '∵', 'n' => 'Karena'],
                            ];
                        @endphp
                        @foreach($logicSymbols as $l)
                            <button type="button" onclick="insertSymbol('{{ $l['s'] }}')" class="h-12 rounded-xl bg-slate-50 hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-800 text-base font-bold transition-all hover:scale-105 active:scale-95 flex flex-col items-center justify-center cursor-pointer shadow-2xs group" title="{{ $l['n'] }}">
                                <span>{{ $l['s'] }}</span>
                                <span class="text-[9px] text-slate-400 group-hover:text-cyan-100 font-normal truncate max-w-[90%]">{{ $l['n'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /Tab Content Panes --}}

            {{-- Footer --}}
            <div class="p-3 bg-slate-100 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <i class="bi bi-info-circle text-blue-600"></i> Simbol dapat dikombinasikan dengan huruf atau angka bebas
                </span>
                <button type="button" onclick="closeSymbolPicker()" class="px-4 py-1.5 rounded-xl bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer shadow-2xs">
                    Selesai
                </button>
            </div>

        </div>
    </div>
</div>

{{-- JAVASCRIPT GLOBAL HANDLER UNTUK PALET SIMBOL --}}
<script>
    // State element target saat ini
    window.currentSymbolTarget = null;
    window.lastSavedSelection = null;
    // True bila guru sedang mengaktifkan/menyunting kotak Pratinjau
    window.previewActive = false;

    // Tandai saat Pratinjau diaktifkan, supaya tombol simbol mengarah ke Pratinjau
    (function() {
        const preview = document.getElementById('symbol-preview');
        if (!preview) return;
        ['focusin', 'mousedown', 'click', 'keyup'].forEach(function(ev) {
            preview.addEventListener(ev, function() { window.previewActive = true; });
        });
    })();

    /**
     * Bersihkan markup pecahan yang bocor atau setengah jadi agar kembali ke sintaks bersih \frac{a}{b}
     */
    window.cleanCorruptedFractions = function(str) {
        if (!str) return '';
        let s = String(str);
        
        // 1. Tangani entitas &lt;span class="math-frac"... yang bocor sebagai teks mentah
        s = s.replace(/&lt;span\s+class=["']?math-frac["']?&gt;\s*&lt;span\s+class=["']?frac-top["']?&gt;([\s\S]*?)&lt;\/span&gt;\s*&lt;span\s+class=["']?frac-bottom["']?&gt;([\s\S]*?)(?:&lt;\/span&gt;)+/gi, '\\frac{$1}{$2}');
        s = s.replace(/&lt;span\s+class=["']?math-frac["']?&gt;&lt;span\s+class=["']?frac-top["']?&gt;([\s\S]*?)&lt;span\s+class=["']?frac-bottom["']?&gt;([^&<]*)/gi, '\\frac{$1}{$2}');
        s = s.replace(/&lt;span\s+class=["']?math-frac["']?&gt;[\s\S]*?&lt;span\s+class=["']?frac-top["']?&gt;([\s\S]*?)&lt;\/span&gt;[\s\S]*?&lt;span\s+class=["']?frac-bottom["']?&gt;([\s\S]*?)&lt;\/span&gt;[\s\S]*?&lt;\/span&gt;/gi, '\\frac{$1}{$2}');
        
        // 2. Tangani tag asli <span class="math-frac">... yang unclosed atau tersimpan mentah
        s = s.replace(/<span\s+class=["']?math-frac["']?>\s*<span\s+class=["']?frac-top["']?>([\s\S]*?)<\/span>\s*<span\s+class=["']?frac-bottom["']?>([\s\S]*?)<\/span>\s*<\/span>/gi, '\\frac{$1}{$2}');
        s = s.replace(/<span\s+class=["']?math-frac["']?>\s*<span\s+class=["']?frac-top["']?>([\s\S]*?)<span\s+class=["']?frac-bottom["']?>([\s\S]*?)(?:<\/span>)+/gi, '\\frac{$1}{$2}');
        s = s.replace(/<span\s+class=["']?math-frac["']?>[\s\S]*?<span\s+class=["']?frac-top["']?>([\s\S]*?)<\/span>[\s\S]*?<span\s+class=["']?frac-bottom["']?>([\s\S]*?)<\/span>[\s\S]*?<\/span>/gi, '\\frac{$1}{$2}');

        // 3. Unescape entitas formatting yang bocor (&lt;b&gt;, &lt;sup&gt;, &lt;span&gt;, dll)
        s = s.replace(/&lt;(\/?(?:span|sup|sub|b|strong|i|em|u|s|strike|mark|small|br|p))((?:\s+[^&<>]*?)?)&gt;/gi, function(_, tag, attrs) {
            let cleanAttrs = attrs ? attrs.replace(/&quot;/g, '"').replace(/&#39;/g, "'") : '';
            return '<' + tag + cleanAttrs + '>';
        });

        return s;
    };

    /**
     * Konversi pecahan unicode dan sintaks \frac / [frac] menjadi stacked fraction HTML
     */
    window.formatFractions = function(text) {
        if (!text) return '';
        const unicodeMap = {
            '½': ['1', '2'], '⅓': ['1', '3'], '⅔': ['2', '3'],
            '¼': ['1', '4'], '¾': ['3', '4'], '⅕': ['1', '5'],
            '⅖': ['2', '5'], '⅗': ['3', '5'], '⅘': ['4', '5'],
            '⅙': ['1', '6'], '⅚': ['5', '6'], '⅛': ['1', '8'],
            '⅜': ['3', '8'], '⅝': ['5', '8'], '⅞': ['7', '8'],
            '⅑': ['1', '9'], '⅒': ['1', '10'], '↉': ['0', '3']
        };
        for (const [char, [top, bot]] of Object.entries(unicodeMap)) {
            if (text.indexOf(char) !== -1) {
                text = text.split(char).join('\u200B<span class="math-frac"><span class="frac-top">' + top + '</span><span class="frac-bottom">' + bot + '</span></span>\u200B');
            }
        }
        let loop = 0;
        const patternLatex = /\\frac\s*\{([^{}]+?)\}\s*\{([^{}]+?)\}/g;
        while (/\\frac\s*\{([^{}]+?)\}\s*\{([^{}]+?)\}/.test(text) && loop < 5) {
            text = text.replace(patternLatex, '\u200B<span class="math-frac"><span class="frac-top">$1</span><span class="frac-bottom">$2</span></span>\u200B');
            loop++;
        }
        loop = 0;
        const patternBbcode = /\[frac\]\s*([^\[\]\/\|]+?)\s*(?:\/|\|)\s*([^\[\]\/\|]+?)\s*\[\/frac\]/gi;
        while (/\[frac\]\s*([^\[\]\/\|]+?)\s*(?:\/|\|)\s*([^\[\]\/\|]+?)\s*\[\/frac\]/i.test(text) && loop < 5) {
            text = text.replace(patternBbcode, '\u200B<span class="math-frac"><span class="frac-top">$1</span><span class="frac-bottom">$2</span></span>\u200B');
            loop++;
        }
        return text;
    };

    /**
     * Format dan sanitasi HTML menggunakan DOMParser:
     * Ubah pecahan \frac{a}{b} atau unicode menjadi tampilan bertingkat
     * dan izinkan tag format aman (b, i, u, sup, sub, span.math-frac).
     */
    window.safeFormatHtml = function(str) {
        if (str === null || str === undefined) return '';
        let text = window.cleanCorruptedFractions(String(str));

        // Format pecahan unicode & kustom menjadi pecahan bertingkat (atas-bawah)
        text = window.formatFractions(text);

        // Parsing dan sanitasi aman menggunakan DOMParser
        try {
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const allowedTags = new Set(['B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE', 'SUB', 'SUP', 'MARK', 'SMALL', 'BR', 'P', 'SPAN']);
            const allowedClasses = new Set(['math-frac', 'frac-top', 'frac-bottom']);

            function sanitizeTree(node) {
                Array.from(node.childNodes).forEach(child => {
                    if (child.nodeType === 1) { // ELEMENT_NODE
                        if (!allowedTags.has(child.tagName)) {
                            const textNode = document.createTextNode(child.textContent || '');
                            node.replaceChild(textNode, child);
                        } else {
                            // Hapus semua atribut berbahaya, pertahankan class pecahan
                            Array.from(child.attributes).forEach(attr => {
                                if (attr.name.toLowerCase() === 'class') {
                                    const filtered = attr.value.split(/\s+/).filter(c => allowedClasses.has(c));
                                    if (filtered.length > 0) {
                                        child.setAttribute('class', filtered.join(' '));
                                    } else {
                                        child.removeAttribute('class');
                                    }
                                } else {
                                    child.removeAttribute(attr.name);
                                }
                            });
                            sanitizeTree(child);
                        }
                    }
                });
            }

            sanitizeTree(doc.body);
            let result = doc.body.innerHTML;
            if (!/<br\s*\/?>|<p>/i.test(result)) {
                result = result.replace(/\r?\n/g, '<br>');
            }
            return result;
        } catch (e) {
            return text;
        }
    };

    /**
     * Apakah guru sedang mengetik / menyeleksi DI PRATINJAU?
     * Jika ya, Pratinjau-lah yang jadi sasaran edit (bukan kolom aslinya).
     */
    window.previewIsEditing = function() {
        const preview = document.getElementById('symbol-preview');
        if (!preview) return false;
        if (window.previewActive) return true;
        if (document.activeElement === preview) return true;
        const sel = window.getSelection();
        return !!(sel && sel.rangeCount > 0 && sel.anchorNode && preview.contains(sel.anchorNode));
    };

    /**
     * Ambil nilai teks bersih dari elemen target (tanpa raw HTML math-frac).
     */
    window.getTargetCleanValue = function(target) {
        if (!target) return '';
        if (target.__richStore && target.__richStore.value !== undefined) {
            return window.cleanCorruptedFractions(target.__richStore.value);
        }
        if (target.previousElementSibling && target.previousElementSibling.tagName === 'TEXTAREA' && target.previousElementSibling.classList.contains('rich-store')) {
            return window.cleanCorruptedFractions(target.previousElementSibling.value);
        }
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
            return window.cleanCorruptedFractions(target.value || '');
        }
        if (target.isContentEditable) {
            return window.cleanCorruptedFractions(window.serializePreview(target));
        }
        return window.cleanCorruptedFractions(target.value || target.textContent || '');
    };

    /**
     * Ubah isi Pratinjau (HTML) menjadi teks ber-tag yang aman disimpan ke kolom asli.
     * Hanya tag format yang diizinkan (mirror TextHelper::formatSoal).
     */
    window.serializePreview = function(root) {
        if (!root) return '';
        const allowed = ['sup', 'sub', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'mark', 'small'];
        let out = '';
        Array.from(root.childNodes).forEach(function(n) {
            if (n.nodeType === 3) { out += n.nodeValue; return; }
            if (n.nodeType !== 1) return;
            const tag = n.tagName.toLowerCase();
            if (tag === 'br') { out += '\n'; return; }
            if (tag === 'div' || tag === 'p') {
                if (out && !out.endsWith('\n')) out += '\n';
                out += window.serializePreview(n);
                return;
            }
            // Tangani pecahan bertingkat: kembalikan ke format \frac{a}{b} yang bersih
            if (n.classList && n.classList.contains('math-frac')) {
                const topEl = n.querySelector('.frac-top');
                const botEl = n.querySelector('.frac-bottom');
                const topText = topEl ? window.serializePreview(topEl).trim() : '';
                const botText = botEl ? window.serializePreview(botEl).trim() : '';
                out += '\\frac{' + topText + '}{' + botText + '}';
                return;
            }
            if (allowed.indexOf(tag) !== -1) {
                out += '<' + tag + '>' + window.serializePreview(n) + '</' + tag + '>';
                return;
            }
            out += window.serializePreview(n);
        });
        return window.cleanCorruptedFractions(out);
    };

    /**
     * Salin perubahan Pratinjau ke kolom asli (textarea/input), lalu beri tahu
     * aplikasi (autogrow, papan kunci, dsb) lewat event input/change.
     */
    window.syncPreviewToTarget = function() {
        const preview = document.getElementById('symbol-preview');
        const target = window.currentSymbolTarget;
        if (!preview || !target) return;
        let val = window.serializePreview(preview).replace(/\u200B/g, '').replace(/\u00a0/g, ' ').replace(/\n+$/, '');
        val = window.cleanCorruptedFractions(val);

        if (target.isContentEditable) {
            target.innerHTML = window.safeFormatHtml(val);
            if (target.__richStore) {
                target.__richStore.value = val;
                target.__richStore.dispatchEvent(new Event('input', { bubbles: true }));
                target.__richStore.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } else {
            target.value = val;
            if (target.__richEditor) {
                target.__richEditor.innerHTML = window.safeFormatHtml(val);
                refreshRichEmpty(target.__richEditor);
            }
        }
        target.dispatchEvent(new Event('input', { bubbles: true }));
        target.dispatchEvent(new Event('change', { bubbles: true }));
    };

    /**
     * Letakkan kursor pada seleksi terakhir di dalam Pratinjau (dipakai sebelum
     * menyisipkan simbol/tag, karena klik tombol sempat memindah fokus).
     */
    function restorePreviewSelection(preview) {
        restoreSavedSelection(preview);
    }

    /**
     * Pulihkan seleksi yang tersimpan bila node-nya masih valid & berada di
     * dalam `root`. Mencegah DOMException saat node lama sudah terlepas dari
     * dokumen (mis. editor lama yang sudah diganti).
     */
    function restoreSavedSelection(root) {
        const saved = window.lastSavedSelection;
        if (!saved || !saved.startContainer || !saved.endContainer) return;
        if (!saved.startContainer.isConnected || !saved.endContainer.isConnected) return;
        if (root && !root.contains(saved.startContainer)) return;
        const sel = window.getSelection();
        sel.removeAllRanges();
        try { sel.addRange(saved); } catch (e) { /* abaikan seleksi tak valid */ }
    }

    /**
     * Cari elemen <tag> (sup/sub) di dalam `root` yang menjadi sasaran seleksi.
     * Mendukung dua bentuk seleksi:
     *   (a) seleksi berada di dalam elemen (teksnya disorot), dan
     *   (b) seleksi menutupi elemen secara utuh (hasil surroundContents).
     * Mengembalikan elemen <tag> atau null bila tidak ada.
     */
    function pickSelectedTag(root, tag) {
        const sel = window.getSelection();
        if (!sel || sel.rangeCount === 0 || sel.isCollapsed) return null;
        const rg = sel.getRangeAt(0);

        const startEl = rg.startContainer.nodeType === 1 ? rg.startContainer : rg.startContainer.parentElement;
        if (!startEl) return null;

        // (a) Seleksi berada di dalam elemen <tag>
        const inside = startEl.closest ? startEl.closest(tag) : null;
        if (inside && root.contains(inside) && inside.contains(rg.startContainer) && inside.contains(rg.endContainer)) {
            return inside;
        }

        // (b) Seleksi menutupi elemen <tag> secara utuh (boundary di level induk)
        if (rg.startContainer.nodeType === 1 && rg.startContainer === rg.endContainer) {
            const kids = Array.prototype.slice.call(rg.startContainer.childNodes, rg.startOffset, rg.endOffset);
            if (kids.length === 1 && kids[0].nodeType === 1 && kids[0].tagName.toLowerCase() === tag) {
                return kids[0];
            }
        }
        return null;
    }

    /**
     * Pastikan ada text node TEPAT setelah `el` dan sisipkan karakter zero-width
     * di depannya. Ini mencegah peramban menaruh kursor "di dalam" elemen
     * (Chrome menempelkan kursor ke elemen inline terakhir pada akhir baris).
     */
    function ensureOutsideTextNode(el) {
        let next = el.nextSibling;
        if (!next || next.nodeType !== 3) {
            next = document.createTextNode('\u200B');
            el.parentNode.insertBefore(next, el.nextSibling);
        } else if (next.nodeValue.charAt(0) !== '\u200B') {
            next.nodeValue = '\u200B' + next.nodeValue;
        }
        return next;
    }

    /**
     * Bungkus seleksi saat ini dengan <tag> dan PERTAHANKAN seleksi pada isinya,
     * sehingga klik tombol sekali lagi akan membatalkannya (toggle).
     * Kursor juga bisa keluar dengan sendirinya saat mulai mengetik (lihat handler
     * "beforeinput" di bawah) supaya ketikan lanjutan tidak ikut jadi pangkat.
     */
    function wrapSelectionWithTag(root, tag) {
        const sel = window.getSelection();
        if (!sel || sel.rangeCount === 0) return false;
        const rg = sel.getRangeAt(0);
        if (rg.collapsed) return false;

        const el = document.createElement(tag);
        try {
            rg.surroundContents(el);
        } catch (err) {
            const frag = rg.extractContents();
            el.appendChild(frag);
            rg.insertNode(el);
        }
        ensureOutsideTextNode(el);

        const newRange = document.createRange();
        newRange.selectNodeContents(el);
        sel.removeAllRanges();
        sel.addRange(newRange);
        return true;
    }

    /**
     * Bongkar elemen <tag> (isinya kembali menjadi teks biasa) dan sorot ulang
     * isinya supaya klik tombol berikutnya membungkusnya kembali.
     */
    function unwrapSelectionTag(root, el) {
        const parent = el.parentNode;
        if (!parent) return false;

        const sel = window.getSelection();
        const rg = document.createRange();
        rg.selectNodeContents(el);

        while (el.firstChild) parent.insertBefore(el.firstChild, el);
        parent.removeChild(el);

        sel.removeAllRanges();
        sel.addRange(rg);
        return true;
    }

    /**
     * Saat guru LANGSUNG mengetik selama teks pangkat/indeks masih tersorot:
     * pindahkan titik sisip ke SETELAH elemen (mode normal) lalu tuliskan
     * karakternya. Tanpa ini, Chrome menuliskan huruf berikutnya di dalam
     * <sup>/<sub> sehingga semua tulisan lanjutan ikut jadi pangkat/indeks.
     */
    document.addEventListener('beforeinput', function(e) {
        if (e.inputType !== 'insertText') return;
        const t = e.target;
        const root = (t && t.closest) ? t.closest('[contenteditable="true"]') : null;
        if (!root) return;
        const wrap = pickSelectedTag(root, 'sup') || pickSelectedTag(root, 'sub');
        if (!wrap) return;

        e.preventDefault();
        const next = ensureOutsideTextNode(wrap);
        const sel = window.getSelection();
        const rg = document.createRange();
        rg.setStart(next, 1);
        rg.collapse(true);
        sel.removeAllRanges();
        sel.addRange(rg);
        if (e.data) document.execCommand('insertText', false, e.data);
    });

    /**
     * Navigasi Arrow (Kiri / Kanan / Atas / Bawah) untuk keluar dan berpindah dari blok pecahan (.math-frac)
     * saat mengetik di Pratinjau maupun Soal-Editor contenteditable.
     */
    document.addEventListener('keydown', function(e) {
        if (!['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp'].includes(e.key)) return;

        const sel = window.getSelection();
        if (!sel || !sel.rangeCount) return;
        const rg = sel.getRangeAt(0);

        const node = rg.startContainer;
        const el = node.nodeType === 1 ? node : node.parentElement;
        const frac = el ? el.closest('.math-frac') : null;
        if (!frac) return;

        // Pastikan berada di dalam elemen yang editable
        const editor = frac.closest('[contenteditable="true"]');
        if (!editor) return;

        const isTop = !!el.closest('.frac-top');
        const isBot = !!el.closest('.frac-bottom');

        if (e.key === 'ArrowRight') {
            const isTextAtEnd = (node.nodeType === 3 && rg.startOffset >= node.nodeValue.length);
            // Keluar dari pecahan jika kursor di akhir teks, atau sedang berada di penyebut / kontainer pecahan
            if (isTextAtEnd || isBot || node === frac) {
                let next = frac.nextSibling;
                if (!next || next.nodeType !== 3) {
                    next = document.createTextNode('\u200B');
                    frac.parentNode.insertBefore(next, frac.nextSibling);
                } else if (!next.nodeValue.startsWith('\u200B')) {
                    next.nodeValue = '\u200B' + next.nodeValue;
                }
                const newRg = document.createRange();
                newRg.setStart(next, next.nodeValue.length > 0 ? 1 : 0);
                newRg.collapse(true);
                sel.removeAllRanges();
                sel.addRange(newRg);
                e.preventDefault();
            }
        } else if (e.key === 'ArrowLeft') {
            const isTextAtStart = (node.nodeType === 3 && rg.startOffset === 0);
            // Keluar dari pecahan jika kursor di awal teks, atau sedang berada di pembilang / kontainer pecahan
            if (isTextAtStart || isTop || node === frac) {
                let prev = frac.previousSibling;
                if (!prev || prev.nodeType !== 3) {
                    prev = document.createTextNode('\u200B');
                    frac.parentNode.insertBefore(prev, frac);
                } else if (!prev.nodeValue.endsWith('\u200B')) {
                    prev.nodeValue = prev.nodeValue + '\u200B';
                }
                const newRg = document.createRange();
                newRg.setStart(prev, prev.nodeValue.length);
                newRg.collapse(true);
                sel.removeAllRanges();
                sel.addRange(newRg);
                e.preventDefault();
            }
        } else if (e.key === 'ArrowDown' && isTop) {
            // Pindah dari pembilang (atas) ke penyebut (bawah)
            const botEl = frac.querySelector('.frac-bottom');
            if (botEl) {
                const newRg = document.createRange();
                newRg.selectNodeContents(botEl);
                newRg.collapse(false);
                sel.removeAllRanges();
                sel.addRange(newRg);
                e.preventDefault();
            }
        } else if (e.key === 'ArrowUp' && isBot) {
            // Pindah dari penyebut (bawah) ke pembilang (atas)
            const topEl = frac.querySelector('.frac-top');
            if (topEl) {
                const newRg = document.createRange();
                newRg.selectNodeContents(topEl);
                newRg.collapse(false);
                sel.removeAllRanges();
                sel.addRange(newRg);
                e.preventDefault();
            }
        }
    });

    // Klik di area kosong Pratinjau: tempatkan kursor di luar pecahan
    (function() {
        const preview = document.getElementById('symbol-preview');
        if (!preview) return;
        preview.addEventListener('click', function(e) {
            if (e.target === preview) {
                let last = preview.lastChild;
                if (!last || last.nodeType !== 3) {
                    last = document.createTextNode('\u200B');
                    preview.appendChild(last);
                }
                const sel = window.getSelection();
                const rg = document.createRange();
                rg.setStart(last, last.nodeValue.length);
                rg.collapse(true);
                sel.removeAllRanges();
                sel.addRange(rg);
            }
        });
    })();

    /**
     * Perbarui pratinjau live berdasarkan isi kolom target yang aktif.
     * (Tidak menimpa isi saat guru sedang mengedit di dalam Pratinjau.)
     */
    window.updateSymbolPreview = function(force) {
        const wrap = document.getElementById('symbol-preview-wrap');
        const box = document.getElementById('symbol-preview');
        if (!wrap || !box) return;

        const target = window.currentSymbolTarget;
        if (!target) {
            wrap.style.display = 'none';
            return;
        }

        wrap.style.display = 'flex';

        if (!force && window.previewIsEditing()) {
            // Jika di dalam box masih ada sintaks \frac atau [frac] mentah, segera render jadi pecahan
            if (/\\frac\s*\{[^{}]+?\}\s*\{[^{}]+?\}/.test(box.innerHTML) || /\[frac\]/.test(box.innerHTML)) {
                box.innerHTML = window.safeFormatHtml(box.innerHTML);
            }
            return;
        }

        let raw = window.getTargetCleanValue(target);

        // Auto-heal bila target menyimpan tag atau entitas bocor dari sesi sebelumnya
        if (target.isContentEditable && target.__richStore && /&lt;span|<span\s+class="math-frac"/.test(target.__richStore.value)) {
            target.__richStore.value = raw;
        } else if (target.tagName === 'TEXTAREA' && /&lt;span|<span\s+class="math-frac"/.test(target.value)) {
            target.value = raw;
        }

        box.innerHTML = window.safeFormatHtml(raw);
    };

    /**
     * Sesuaikan tinggi textarea (kelas .auto-grow-field) mengikuti isinya,
     * sehingga jawaban panjang tampil beberapa baris alih-alih terpotong.
     */
    window.autoGrowField = function(el) {
        if (!el || !el.classList || !el.classList.contains('auto-grow-field')) return;
        if (!el.isConnected) return;
        // Lewati elemen yang sedang tersembunyi (display:none) agar tinggi tidak kolaps
        if (el.offsetParent === null && el.offsetHeight === 0) return;

        el.style.height = 'auto';
        const maxPx = 240;
        const next = Math.min(el.scrollHeight, maxPx);
        el.style.height = next + 'px';
        el.style.overflowY = el.scrollHeight > maxPx ? 'auto' : 'hidden';
    };

    window.initAutoGrowFields = function(root) {
        const scope = root || document;
        if (!scope.querySelectorAll) return;
        scope.querySelectorAll('textarea.auto-grow-field').forEach(function(el) {
            window.autoGrowField(el);
        });
        // Sekalian siapkan versi kaya (WYSIWYG) untuk kolom jawaban
        window.initRichFields(root);
    };

    // Otomatis memanjang saat mengetik
    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('auto-grow-field')) {
            window.autoGrowField(e.target);
        }
    });
    document.addEventListener('DOMContentLoaded', function() { window.initAutoGrowFields(); });
    window.addEventListener('load', function() { window.initAutoGrowFields(); });

    // ============================================================
    // KOLOM JAWABAN KAYA (WYSIWYG)
    // Textarea jawaban (kelas .auto-grow-field) tetap menjadi sumber data
    // (name & value terkirim ke server apa adanya), tetapi tampilannya
    // digantikan editor contenteditable sehingga <sup>/<sub> langsung
    // ter-render — guru tidak lagi melihat tag perintah mentah.
    // ============================================================

    // Ubah isi editor menjadi teks ber-tag yang aman disimpan ke textarea
    window.serializeRichField = function(root) {
        return window.serializePreview(root)
            .replace(/\u200B/g, '')
            .replace(/\u00a0/g, ' ')
            .replace(/\n+$/, '');
    };

    function refreshRichEmpty(editor) {
        const empty = editor.textContent.trim() === '' && !editor.querySelector('img, br + img');
        editor.classList.toggle('is-empty', empty);
    }

    // Turunkan tampilan editor dari nilai textarea (sumber data)
    function syncRichEditorFromStore(store) {
        const editor = store.__richEditor;
        if (!editor) return;
        const html = window.safeFormatHtml(store.value || '');
        if (editor.innerHTML !== html) {
            store.__syncing = true;
            editor.innerHTML = html;
            store.__syncing = false;
        }
        refreshRichEmpty(editor);
    }

    function enhanceRichTextarea(store) {
        if (!store || store.__richReady) return;
        if (!store.parentNode) return;
        store.__richReady = true;

        // Pakai editor yang sudah ada (mis. hasil clone) atau buat baru
        let editor = (store.nextElementSibling && store.nextElementSibling.classList.contains('rich-editor'))
            ? store.nextElementSibling
            : null;

        if (!editor) {
            editor = document.createElement('div');
            editor.className = store.className.replace(/\bauto-grow-field\b/g, '').replace(/\s+/g, ' ').trim() + ' rich-editor';
            editor.setAttribute('contenteditable', 'true');
            editor.setAttribute('spellcheck', 'false');
            store.parentNode.insertBefore(editor, store.nextSibling);
        }
        store.classList.add('rich-store');
        if (!editor.dataset.placeholder) editor.dataset.placeholder = store.getAttribute('placeholder') || '';
        store.__richEditor = editor;
        editor.__richStore = store;

        if (!store.__richBound) {
            store.__richBound = true;

            store.addEventListener('input', function() {
                if (!store.__syncing) syncRichEditorFromStore(store);
            });

            // Penulisan nilai secara programatik (import, muat data, dsb)
            // otomatis ikut memperbarui tampilan editor.
            const desc = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(store), 'value');
            if (desc && desc.set) {
                Object.defineProperty(store, 'value', {
                    configurable: true,
                    get: function() { return desc.get.call(this); },
                    set: function(v) {
                        desc.set.call(this, v);
                        if (!store.__syncing) syncRichEditorFromStore(store);
                    }
                });
            }

            // Ketikan di editor -> simpan tag ke textarea -> beri tahu aplikasi
            editor.addEventListener('input', function() {
                store.__syncing = true;
                store.value = window.serializeRichField(editor);
                store.__syncing = false;
                refreshRichEmpty(editor);
                store.dispatchEvent(new Event('input', { bubbles: true }));
                store.dispatchEvent(new Event('change', { bubbles: true }));
            });

            // Tempel selalu sebagai teks biasa (samakan dengan perilaku textarea lama)
            editor.addEventListener('paste', function(e) {
                e.preventDefault();
                const cb = e.clipboardData || window.clipboardData;
                const txt = cb ? cb.getData('text/plain') : '';
                document.execCommand('insertText', false, txt);
            });
        }

        syncRichEditorFromStore(store);
    }

    window.initRichFields = function(root) {
        const scope = (root && root.querySelectorAll) ? root : document;
        scope.querySelectorAll('textarea.auto-grow-field').forEach(enhanceRichTextarea);
        if (root && root.matches && root.matches('textarea.auto-grow-field')) enhanceRichTextarea(root);
    };

    window.enhanceRichTextarea = enhanceRichTextarea;

    // Jaring pengaman untuk kolom yang disisipkan tanpa memanggil initAutoGrowFields
    if (window.MutationObserver) {
        const richObserver = new MutationObserver(function(records) {
            records.forEach(function(rec) {
                rec.addedNodes && rec.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;
                    if (node.matches && node.matches('textarea.auto-grow-field')) {
                        enhanceRichTextarea(node);
                    } else if (node.querySelector && node.querySelector('textarea.auto-grow-field')) {
                        node.querySelectorAll('textarea.auto-grow-field').forEach(enhanceRichTextarea);
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            richObserver.observe(document.body, { childList: true, subtree: true });
            window.initRichFields();
        });
    }

    // Jaga tab palet simbol tetap satu baris saat ukuran jendela berubah
    window.addEventListener('resize', function() { window.fitSymbolTabs(); });
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function() { window.fitSymbolTabs(); });
    }

    // Track input/textarea/editor yang terakhir difokuskan
    document.addEventListener('focusin', function(e) {
        const el = e.target;
        // Abaikan elemen apa pun di dalam modal (pratinjau, input pecahan kustom, dsb)
        if (el && el.closest && el.closest('#math-symbol-modal')) return;
        if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.isContentEditable)) {
            // Abaikan input type file/hidden/checkbox/radio
            if (el.tagName === 'INPUT' && ['file', 'hidden', 'checkbox', 'radio', 'submit'].includes(el.type)) {
                return;
            }
            window.previewActive = false;
            window.currentSymbolTarget = el;
        }
    });

    // Save selection untuk contenteditable
    document.addEventListener('selectionchange', function() {
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const anchor = sel.anchorNode;
            if (anchor && (anchor.nodeType === 3 ? anchor.parentElement : anchor).closest('[contenteditable="true"]')) {
                window.lastSavedSelection = sel.getRangeAt(0).cloneRange();
            }
        }
    });

    // Sinkron dua arah Pratinjau <-> kolom target, dan perbarui pratinjau live
    document.addEventListener('input', function(e) {
        const preview = document.getElementById('symbol-preview');
        if (preview && e.target === preview) {
            window.syncPreviewToTarget();
            return;
        }
        // Abaikan ketikan di dalam input modal (seperti input pembilang/penyebut pecahan)
        if (e.target && e.target.closest && e.target.closest('#math-symbol-modal')) {
            return;
        }
        if (e.target && e.target === window.currentSymbolTarget) {
            window.updateSymbolPreview();
        }
    });

    /**
     * Buka Modal Symbol Picker
     * @param {HTMLElement|string|null} targetEl - Input atau tombol trigger
     * @param {string} displayName - Label nama kolom untuk keterangan
     */
    window.openSymbolPicker = function(targetEl = null, displayName = '') {
        const modal = document.getElementById('math-symbol-modal');
        if (!modal) return;

        // Tentukan target element
        if (targetEl instanceof HTMLElement) {
            if (targetEl.closest && targetEl.closest('#math-symbol-modal')) {
                // Abaikan jika target berasal dari dalam modal
            } else if (targetEl.tagName === 'INPUT' || targetEl.tagName === 'TEXTAREA' || targetEl.isContentEditable) {
                window.currentSymbolTarget = targetEl;
            } else {
                // Jika yang diklik adalah tombol trigger, cari input terkait:
                let foundInput = null;
                const relWrapper = targetEl.closest('.relative');
                if (relWrapper) {
                    foundInput = relWrapper.querySelector('input[type="text"], textarea, [contenteditable="true"]');
                }
                if (!foundInput) {
                    const compWrapper = targetEl.closest('.jg-item, .bs-item, .match-left-item, .match-right-item, .modal-match-left-item, .modal-match-right-item, .pertanyaan-container, .soal-card, .type-section');
                    if (compWrapper) {
                        foundInput = compWrapper.querySelector('input[type="text"], textarea, [contenteditable="true"]');
                    }
                }
                if (!foundInput && targetEl.parentElement) {
                    foundInput = targetEl.parentElement.querySelector('input[type="text"], textarea, [contenteditable="true"]');
                }
                if (foundInput && (!foundInput.closest || !foundInput.closest('#math-symbol-modal'))) {
                    window.currentSymbolTarget = foundInput;
                }
            }
        }

        // Jika belum ada target, cari input pertama yang terlihat DI LUAR modal
        if (!window.currentSymbolTarget || (window.currentSymbolTarget.closest && window.currentSymbolTarget.closest('#math-symbol-modal'))) {
            window.currentSymbolTarget = document.querySelector('form input[type="text"]:focus, form textarea:focus') || 
                                         document.querySelector('form [contenteditable="true"]:focus') ||
                                         document.querySelector('.soal-editor, textarea.auto-grow-field, input[type="text"]');
        }

        // Update keterangan target di modal
        const targetNameEl = document.getElementById('symbol-target-name');
        if (targetNameEl) {
            let name = displayName;
            if (!name && window.currentSymbolTarget) {
                name = window.currentSymbolTarget.getAttribute('placeholder') || 
                       window.currentSymbolTarget.getAttribute('name') || 
                       'Kolom Aktif';
            }
            targetNameEl.textContent = name || 'Kolom Jawaban';
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        // Bersihkan state seleksi lama agar Pratinjau selalu dimuat dari kolom target
        window.previewActive = false;
        window.lastSavedSelection = null;
        const _sel = window.getSelection();
        if (_sel) _sel.removeAllRanges();
        window.updateSymbolPreview(true);
        window.resetCustomFraction(false);
        window.fitSymbolTabs();
    };

    /**
     * Tutup Modal Symbol Picker
     */
    window.closeSymbolPicker = function() {
        const modal = document.getElementById('math-symbol-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        // Kembalikan fokus ke target
        if (window.currentSymbolTarget) {
            window.currentSymbolTarget.focus();
        }
    };

    /**
     * Ganti Tab pada Modal
     */
    window.switchSymbolTab = function(tabId) {
        document.querySelectorAll('.symbol-pane').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.symbol-tab-btn').forEach(b => {
            b.classList.remove('active', 'border-blue-600', 'text-blue-600');
            b.classList.add('border-transparent', 'text-slate-500');
        });

        const activePane = document.getElementById(`tab-${tabId}`);
        if (activePane) activePane.classList.remove('hidden');

        const activeBtn = document.querySelector(`.symbol-tab-btn[data-tab="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active', 'border-blue-600', 'text-blue-600');
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
        }
    };

    /**
     * Susun tab palet simbol agar selalu dalam SATU baris.
     * Jika semua tab tidak muat, ukuran font dikecilkan bertahap
     * (padding & gap ikut mengecil karena memakai satuan em).
     */
    window.fitSymbolTabs = function() {
        const bar = document.querySelector('#math-symbol-modal .symbol-tab-bar');
        if (!bar || !bar.clientWidth) return;
        const maxSize = 12, minSize = 8.5;
        let size = maxSize;
        bar.style.fontSize = size + 'px';
        while (bar.scrollWidth > bar.clientWidth + 1 && size > minSize) {
            size -= 0.5;
            bar.style.fontSize = size + 'px';
        }
    };

    /**
     * Sisipkan Simbol ke Input atau Editor Aktif
     */
    window.insertSymbol = function(symbol) {
        // Jika guru sedang mengetik di Pratinjau, sisipkan simbol di situ
        if (window.previewIsEditing()) {
            const preview = document.getElementById('symbol-preview');
            preview.focus();
            restorePreviewSelection(preview);

            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0 || !preview.contains(sel.anchorNode)) {
                const rg = document.createRange();
                rg.selectNodeContents(preview);
                rg.collapse(false);
                sel.removeAllRanges();
                sel.addRange(rg);
            }

            // Bila yang tersorot justru pangkat/indeks, letakkan simbol SETELAH-nya
            // (jangan menimpanya) supaya pangkat tidak hilang begitu saja.
            const wrapTag = pickSelectedTag(preview, 'sup') || pickSelectedTag(preview, 'sub');
            if (wrapTag) {
                const next = ensureOutsideTextNode(wrapTag);
                const rg = document.createRange();
                rg.setStart(next, 1);
                rg.collapse(true);
                sel.removeAllRanges();
                sel.addRange(rg);
            }

            if (symbol.includes('\\frac') || symbol.includes('[frac]')) {
                const htmlToInsert = window.formatFractions(symbol);
                document.execCommand('insertHTML', false, htmlToInsert);

                // Buat text node spasi di luar pecahan agar kursor langsung siap ketik teks biasa
                const curSel = window.getSelection();
                if (curSel && curSel.rangeCount > 0) {
                    const fracNode = (curSel.anchorNode && curSel.anchorNode.closest) 
                        ? curSel.anchorNode.closest('.math-frac') 
                        : preview.querySelector('.math-frac:last-of-type');
                    if (fracNode) {
                        let nextNode = fracNode.nextSibling;
                        if (!nextNode || nextNode.nodeType !== 3) {
                            nextNode = document.createTextNode('\u200B ');
                            fracNode.parentNode.insertBefore(nextNode, fracNode.nextSibling);
                        } else if (!nextNode.nodeValue.startsWith('\u200B')) {
                            nextNode.nodeValue = '\u200B ' + nextNode.nodeValue;
                        }
                        const outRg = document.createRange();
                        outRg.setStart(nextNode, nextNode.nodeValue.length);
                        outRg.collapse(true);
                        curSel.removeAllRanges();
                        curSel.addRange(outRg);
                        window.lastSavedSelection = outRg.cloneRange();
                    }
                }
            } else {
                document.execCommand('insertText', false, symbol);
            }

            const selAfter = window.getSelection();
            if (selAfter && selAfter.rangeCount > 0) {
                window.lastSavedSelection = selAfter.getRangeAt(0).cloneRange();
            }

            window.syncPreviewToTarget();
            return;
        }

        const target = window.currentSymbolTarget;
        if (!target) return;

        // A. Jika Target berupa INPUT atau TEXTAREA
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
            const start = target.selectionStart ?? target.value.length;
            const end = target.selectionEnd ?? target.value.length;
            const text = target.value;

            // Sisipkan di posisi kursor
            target.value = text.substring(0, start) + symbol + text.substring(end);
            
            // Pindahkan kursor ke setelah simbol yang baru dimasukkan
            const newCursorPos = start + symbol.length;
            target.setSelectionRange(newCursorPos, newCursorPos);
            target.focus();

            // Pemicu event input & change agar state/live view terupdate otomatis
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Feedback kilat
            showSymbolFeedback(target);
            window.updateSymbolPreview(true);
        }
        // B. Jika Target berupa ContentEditable (Soal Editor)
        else if (target.isContentEditable) {
            target.focus();
            restoreSavedSelection(target);
            if (symbol.includes('\\frac') || symbol.includes('[frac]')) {
                const htmlToInsert = window.formatFractions(symbol);
                document.execCommand('insertHTML', false, htmlToInsert);
            } else {
                document.execCommand('insertText', false, symbol);
            }
            
            // Simpan range baru
            const sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                window.lastSavedSelection = sel.getRangeAt(0).cloneRange();
            }

            target.dispatchEvent(new Event('input', { bubbles: true }));
            showSymbolFeedback(target);
            window.updateSymbolPreview(true);
        }
    };

    /**
     * Jadikan teks yang SEDANG DISELEKSI sebagai pangkat (sup) atau indeks (sub).
     *
     * - WAJIB ada teks yang diseleksi. Tanpa seleksi: tidak ada efek apa pun.
     * - Jika teks yang diseleksi sudah berpangkat/berindeks, klik tombol = kembali normal.
     * - Setelah dibungkus, seleksi dipertahankan agar klik kedua = toggle.
     * - Ketikan berikutnya otomatis di luar pangkat/indeks (lihat handler beforeinput).
     */
    window.insertCustomTag = function(tag) {
        // Jika guru sedang mengetik di Pratinjau, terapkan pangkat/indeks di situ
        if (window.previewIsEditing()) {
            const preview = document.getElementById('symbol-preview');
            preview.focus();
            restorePreviewSelection(preview);
            const sel = window.getSelection();
            const selectedText = (sel && sel.rangeCount) ? sel.toString() : '';

            // Teks harus diseleksi dulu. Tanpa seleksi: tidak ada efek apa pun.
            if (!selectedText) return;

            const other = (tag === 'sup') ? 'sub' : 'sup';
            const own = pickSelectedTag(preview, tag);
            if (own) {
                // Klik kedua pada teks yang sudah berpangkat -> kembali normal
                unwrapSelectionTag(preview, own);
            } else {
                const opp = pickSelectedTag(preview, other);
                if (opp) unwrapSelectionTag(preview, opp);
                wrapSelectionWithTag(preview, tag);
            }
            window.syncPreviewToTarget();
            return;
        }

        const target = window.currentSymbolTarget;
        if (!target) return;

        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
            const start = target.selectionStart ?? target.value.length;
            const end = target.selectionEnd ?? target.value.length;

            // Teks harus diseleksi dulu. Tanpa seleksi: tidak ada efek apa pun.
            if (start === end) return;

            const text = target.value;
            const selectedText = text.substring(start, end);
            const before = text.substring(0, start);
            const after = text.substring(end);
            const openTag = '<' + tag + '>';
            const closeTag = '</' + tag + '>';
            const other = (tag === 'sup') ? 'sub' : 'sup';
            const otherOpen = '<' + other + '>';
            const otherClose = '</' + other + '>';

            let newValue, selStart, selEnd;
            const fullMatch = selectedText.match(new RegExp('^<' + tag + '>([\\s\\S]*)</' + tag + '>$'));

            if (fullMatch) {
                // Seleksi berupa <sup>…</sup> utuh -> buka kembali (normal)
                const inner = fullMatch[1];
                newValue = before + inner + after;
                selStart = before.length;
                selEnd = selStart + inner.length;
            } else if (before.endsWith(openTag) && after.startsWith(closeTag)) {
                // Teks sudah diapit tag yang sama -> klik lagi = kembali normal
                newValue = before.slice(0, -openTag.length) + selectedText + after.slice(closeTag.length);
                selStart = before.length - openTag.length;
                selEnd = selStart + selectedText.length;
            } else if (before.endsWith(otherOpen) && after.startsWith(otherClose)) {
                // Diapit tag lain -> ganti jenisnya (pangkat <-> indeks)
                newValue = before.slice(0, -otherOpen.length) + openTag + selectedText + closeTag + after.slice(otherClose.length);
                selStart = before.length - otherOpen.length + openTag.length;
                selEnd = selStart + selectedText.length;
            } else {
                // Bungkus teks yang diseleksi
                newValue = before + openTag + selectedText + closeTag + after;
                selStart = start + openTag.length;
                selEnd = selStart + selectedText.length;
            }

            target.value = newValue;
            target.setSelectionRange(selStart, selEnd);
            target.focus();

            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
            showSymbolFeedback(target);
            window.updateSymbolPreview();
        } else if (target.isContentEditable) {
            target.focus();
            restoreSavedSelection(target);
            const sel = window.getSelection();
            const selectedText = (sel && sel.rangeCount) ? sel.toString() : '';

            // Teks harus diseleksi dulu. Tanpa seleksi: tidak ada efek apa pun.
            if (!selectedText) return;

            const other = (tag === 'sup') ? 'sub' : 'sup';
            const own = pickSelectedTag(target, tag);
            if (own) {
                // Klik kedua pada teks yang sudah berpangkat -> kembali normal
                unwrapSelectionTag(target, own);
            } else {
                const opp = pickSelectedTag(target, other);
                if (opp) unwrapSelectionTag(target, opp);
                wrapSelectionWithTag(target, tag);
            }

            const selAfter = window.getSelection();
            if (selAfter && selAfter.rangeCount > 0) {
                window.lastSavedSelection = selAfter.getRangeAt(0).cloneRange();
            }
            target.dispatchEvent(new Event('input', { bubbles: true }));
            window.updateSymbolPreview();
        }
    };

    /**
     * Efek visual ring singkat saat simbol dimasukkan
     */
    function showSymbolFeedback(el) {
        el.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50/30');
        setTimeout(() => {
            el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50/30');
        }, 350);
    }

    /**
     * Perbarui pratinjau live kotak pecahan kustom di modal
     */
    window.updateCustomFracPreview = function() {
        const top = document.getElementById('custom-frac-top')?.value.trim() || '';
        const bot = document.getElementById('custom-frac-bottom')?.value.trim() || '';
        const box = document.getElementById('custom-frac-preview-render');
        if (box) {
            if (top && bot) {
                box.innerHTML = '<span class="math-frac"><span class="frac-top">' + window.safeFormatHtml(top) + '</span><span class="frac-bottom">' + window.safeFormatHtml(bot) + '</span></span>';
            } else if (top || bot) {
                const topHtml = top ? window.safeFormatHtml(top) : '<span class="text-slate-300 font-normal">...</span>';
                const botHtml = bot ? window.safeFormatHtml(bot) : '<span class="text-slate-300 font-normal">...</span>';
                box.innerHTML = '<span class="math-frac"><span class="frac-top">' + topHtml + '</span><span class="frac-bottom">' + botHtml + '</span></span>';
            } else {
                box.innerHTML = '<span class="text-xs text-slate-300 font-normal italic">Pecahan akan tampil di sini</span>';
            }
        }
    };

    /**
     * Sisipkan pecahan kustom ke kolom target yang aktif
     */
    window.insertCustomFraction = function(top, bottom) {
        const num = (top !== undefined ? String(top) : (document.getElementById('custom-frac-top')?.value || '')).trim();
        const den = (bottom !== undefined ? String(bottom) : (document.getElementById('custom-frac-bottom')?.value || '')).trim();

        if (!num || !den) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Mohon isi Pembilang (atas) dan Penyebut (bawah) terlebih dahulu.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Mohon isi Pembilang (atas) dan Penyebut (bawah) terlebih dahulu.');
            }
            return;
        }

        const fractionCode = '\\frac{' + num + '}{' + den + '}';
        window.insertSymbol(fractionCode);
        window.updateSymbolPreview(true);

        // Feedback visual kilat pada kotak pratinjau pecahan
        const previewEl = document.getElementById('custom-frac-preview-box');
        if (previewEl) {
            previewEl.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50/50');
            setTimeout(() => previewEl.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50/50'), 400);
        }

        // Kosongkan bagian buat pecahan kustom dan hasil tampilannya setelah ditambahkan
        window.resetCustomFraction(false);
    };

    /**
     * Set input pembilang/penyebut dan langsung sisipkan
     */
    window.setAndInsertCustomFraction = function(top, bottom) {
        const topInput = document.getElementById('custom-frac-top');
        const botInput = document.getElementById('custom-frac-bottom');
        if (topInput) topInput.value = top;
        if (botInput) botInput.value = bottom;
        window.updateCustomFracPreview();
        window.insertCustomFraction(top, bottom);
    };

    /**
     * Reset input pecahan kustom
     */
    window.resetCustomFraction = function(focusTop = true) {
        const topInput = document.getElementById('custom-frac-top');
        const botInput = document.getElementById('custom-frac-bottom');
        if (topInput) topInput.value = '';
        if (botInput) botInput.value = '';
        window.updateCustomFracPreview();
        if (focusTop && topInput) topInput.focus();
    };
</script>
