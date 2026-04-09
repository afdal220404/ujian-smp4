<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian: {{ $ujian->nama_ujian }}</title>
    
    {{-- Tailwind & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; user-select: none; }
        .font-poppins { font-family: 'Poppins', sans-serif; }
        
        /* Hide Scrollbar but keep functionality */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
</head>
<body class="bg-slate-200 h-screen flex flex-col overflow-hidden" oncontextmenu="return false;">
    @php
        // Shuffle PHP Logic (Same as before)
        if (!function_exists('seededShuffle')) {
            function seededShuffle(array $array, $seed) {
                mt_srand($seed);
                $count = count($array);
                for ($i = $count - 1; $i > 0; $i--) {
                    $j = mt_rand(0, $i);
                    $temp = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $temp;
                }
                return $array;
            }
        }
        $seed = (int) $siswa->id + (int) $ujian->id;
        $shuffledSoals = seededShuffle($ujian->soals->all(), $seed);
    @endphp
        
    {{-- SAFE ENTRY OVERLAY (INI YANG AKAN MUNCUL PERTAMA KALI) --}}
    <div id="safe-entry-overlay" class="fixed inset-0 z-[9999] bg-white flex flex-col items-center justify-center text-center p-8 transition-all duration-500">
        <div class="max-w-xl w-full">
            <div class="w-24 h-24 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6 mx-auto shadow-lg shadow-blue-200 animate-bounce">
                <i class="bi bi-shield-lock-fill text-4xl"></i>
            </div>
            <h2 class="text-3xl font-[Poppins-Bold] text-gray-800 mb-4">Mode Ujian Aman</h2>
            <div class="bg-red-50 border border-red-100 rounded-xl p-6 mb-8 text-left">
                <h3 class="font-bold text-red-700 mb-2">PERHATIAN KERAS:</h3>
                <ul class="list-disc pl-5 text-sm text-red-600 space-y-2">
                    <li>Ujian ini wajib menggunakan mode <strong>Layar Penuh (Fullscreen)</strong>.</li>
                    <li>Dilarang berpindah ke aplikasi lain (Alt+Tab) atau membuka tab baru.</li>
                    <li>Jika Anda melanggar (keluar fullscreen/pindah aplikasi), ujian akan <strong>OTOMATIS DIKUMPULKAN</strong>.</li>
                </ul>
            </div>
            <button onclick="startSafeExam()" class="w-full group relative inline-flex items-center justify-center gap-3 px-10 py-5 rounded-2xl bg-blue-600 text-white font-bold text-xl shadow-xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all overflow-hidden">
                <span class="relative z-10">SAYA PAHAM & MULAI UJIAN</span>
                <i class="bi bi-arrow-right relative z-10 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </div>
    </div>

    {{-- TOP BAR --}}
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shrink-0 z-20 shadow-sm relative">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold shadow-blue-200">
                <i class="bi bi-journal-text text-xl"></i>
            </div>
            <div>
                <h1 class="text-sm font-bold text-gray-800 uppercase tracking-wide">{{ $ujian->nama_ujian }}</h1>
                <p class="text-xs text-gray-500 font-medium">{{ $ujian->mapel->nama_mapel }}</p>
            </div>
        </div>

        {{-- Timer Center --}}
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-full border border-blue-100 shadow-sm">
                <i class="bi bi-stopwatch-fill animate-pulse"></i>
                <span id="exam-timer" class="font-mono font-bold text-lg tracking-widest">--:--:--</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
             <div class="hidden md:block text-right">
                <p class="text-xs font-bold text-gray-700">{{ $siswa->nama_lengkap }}</p>
                <p class="text-[10px] text-gray-500">{{ $siswa->kelas->kelas }}</p>
            </div>
            <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                <i class="bi bi-person-fill"></i>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 flex overflow-hidden">
        
        {{-- LEFT SIDEBAR: NAVIGASI SOAL --}}
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col shrink-0 transition-all duration-300 hidden md:flex" id="sidebar-nav">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-gray-700 text-sm">Navigasi Soal</h3>
                    <span class="text-xs text-gray-400">{{ $ujian->soals->count() }} Soal</span>
                </div>
                <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 w-0 transition-all duration-500 ease-out" id="progress-bar-nav"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-1 text-right">Progress: <span id="progress-text">0%</span></p>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-5 gap-2">
                    @foreach($shuffledSoals as $index => $soal)
                        @php $isAnswered = isset($jawabanTersimpan[$soal->id]); @endphp
                        <button onclick="jumpToQuestion({{ $index }})" 
                                id="nav-btn-{{ $index }}"
                                class="w-10 h-10 rounded-lg text-xs font-bold flex items-center justify-center transition-all border
                                {{ $isAnswered ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>
            
            {{-- Legend (Footer) --}}
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Keterangan</h4>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded bg-blue-600 border border-blue-600 shadow-sm"></div>
                        <span class="text-xs text-gray-600">Sudah Dijawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded bg-amber-400 border border-amber-400 shadow-sm"></div>
                        <span class="text-xs text-gray-600">Ragu-ragu</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded bg-white border border-gray-200"></div>
                        <span class="text-xs text-gray-600">Belum Dijawab</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded bg-white border-2 border-blue-400"></div>
                        <span class="text-xs text-gray-600">Posisi Saat Ini</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- CENTER: QUESTION AREA --}}
        <section class="flex-1 flex flex-col bg-gray-50/50 relative overflow-hidden">
            <div class="flex-1 overflow-y-auto p-6 md:p-10" id="question-container">
                @foreach($shuffledSoals as $index => $soal)
                    <div class="question-item hidden" id="question-{{ $index }}" data-soal-id="{{ $soal->id }}">
                        
                        {{-- Question Header --}}
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex flex-col items-center justify-center shrink-0 shadow-lg shadow-blue-200">
                                <span class="text-[10px] font-medium uppercase opacity-80">Soal</span>
                                <span class="text-lg font-bold">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 pt-1">
                                <div class="prose max-w-none text-gray-800 font-medium text-lg leading-relaxed">
                                    {!! nl2br(e($soal->pertanyaan)) !!}
                                </div>
                                @if($soal->gambar)
                                    <div class="mt-4">
                                        <img src="{{ asset('storage/' . $soal->gambar) }}" class="max-h-64 rounded-lg border border-gray-200 shadow-sm">
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="pl-0 md:pl-16 space-y-3 max-w-3xl">
                            {{-- PILIHAN GANDA --}}
                            @if($soal->tipe == 'pilihan_ganda')
                                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                                    <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-bold text-blue-800">Petunjuk Pengerjaan:</p>
                                        <p class="text-xs text-blue-600">Pilihlah salah satu jawaban yang menurut Anda paling tepat.</p>
                                    </div>
                                </div>
                                @php
                                    $optionKeys = ['A', 'B', 'C', 'D'];
                                    $optSeed = $seed + $soal->id;
                                    $shuffledOptions = seededShuffle($optionKeys, $optSeed);
                                @endphp
                                @foreach($shuffledOptions as $opt)
                                    @php
                                        $savedAnswer = $jawabanTersimpan[$soal->id] ?? '';
                                        $isChecked = ($savedAnswer == $opt);
                                    @endphp
                                    <label class="group relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:bg-white hover:border-blue-200 hover:shadow-sm
                                        {{ $isChecked ? 'bg-blue-50/50 border-blue-500 ring-1 ring-blue-500' : 'bg-white border-gray-200' }}">
                                        <input type="radio" name="jawaban_{{ $soal->id }}" value="{{ $opt }}" class="peer sr-only" 
                                               onchange="saveAnswer({{ $soal->id }}, '{{ $opt }}', {{ $index }})" {{ $isChecked ? 'checked' : '' }}>
                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-4 flex items-center justify-center peer-checked:border-blue-600 peer-checked:bg-blue-600 transition-all shrink-0">
                                            <div class="w-2.5 h-2.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </div>
                                        <div class="flex-1 font-medium text-gray-700 peer-checked:text-blue-800">
                                            @if($soal->{'gambar_'.strtolower($opt)})
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $soal->{'gambar_'.strtolower($opt)}) }}" class="max-h-32 rounded border border-gray-200">
                                                </div>
                                            @endif
                                            {{ $soal->{'opsi_'.strtolower($opt)} }}
                                        </div>
                                    </label>
                                @endforeach
                            @endif

                            {{-- 2. BENAR / SALAH (COMPLEX) --}}
                            @if($soal->tipe == 'benar_salah')
                                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                                    <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-bold text-blue-800">Petunjuk Pengerjaan:</p>
                                        <p class="text-xs text-blue-600">Tentukan apakah pernyataan berikut <span class="font-bold">BENAR</span> atau <span class="font-bold">SALAH</span>.</p>
                                    </div>
                                </div>

                                <div class="space-y-4" id="tf-complex-{{ $soal->id }}">
                                    @php
                                        $pernyataan = $soal->data_soal['pernyataan'] ?? [];
                                        $savedJson = $jawabanTersimpan[$soal->id] ?? '{}';
                                        $savedAnswers = json_decode($savedJson, true);
                                        if(!is_array($savedAnswers)) $savedAnswers = [];

                                        // 1. Kunci Indeks Asli Sebelum Diacak
                                        $pernyataanWithIndex = [];
                                        foreach($pernyataan as $k => $v) {
                                            $v['_original_index'] = $k;
                                            $pernyataanWithIndex[] = $v;
                                        }

                                        // 2. Acak Array yang sudah ada Indeks Aslinya
                                        $seedSoal = (int) $siswa->id + (int) $soal->id;
                                        $pernyataanAcak = seededShuffle($pernyataanWithIndex, $seedSoal);
                                    @endphp

                                    @if(empty($pernyataan))
                                        {{-- FALLBACK TO OLD SIMPLE UI IF NO STATEMENTS --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            @foreach(['TRUE' => 'BENAR', 'FALSE' => 'SALAH'] as $val => $label)
                                                @php
                                                    $isChecked = ($savedJson == $val) || ($savedJson == 'A' && $val == 'TRUE') || ($savedJson == 'B' && $val == 'FALSE');
                                                    $isBenar = $val == 'TRUE';
                                                    $color = $isBenar ? 'emerald' : 'rose';
                                                @endphp
                                                <label class="group relative flex flex-col items-center justify-center p-6 rounded-2xl border-2 cursor-pointer transition-all duration-200 hover:bg-gray-50
                                                    {{ $isChecked ? "bg-{$color}-50 border-{$color}-500 ring-1 ring-{$color}-500" : "bg-white border-gray-200" }}">
                                                    <input type="radio" name="jawaban_{{ $soal->id }}" value="{{ $val }}" class="peer sr-only" 
                                                           onchange="saveAnswer({{ $soal->id }}, '{{ $val }}', {{ $index }})" {{ $isChecked ? 'checked' : '' }}>
                                                    <span class="font-bold text-lg {{ $isChecked ? "text-{$color}-700" : "text-gray-600" }}">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        {{-- NEW COMPLEX UI --}}
                                        <input type="hidden" id="jawaban_tf_complex_{{ $soal->id }}" value="{{ htmlspecialchars($savedJson) }}">
                                        
                                        {{-- Loop menggunakan array yang sudah diacak --}}
                                        @foreach($pernyataanAcak as $item)
                                            @php 
                                                // 3. Panggil kembali indeks aslinya untuk input name dan value
                                                $idx = $item['_original_index']; 
                                            @endphp
                                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                                                @if(isset($item['gambar']) && $item['gambar'])
                                                    <div class="mb-3">
                                                        <img src="{{ asset('storage/' . $item['gambar']) }}" class="max-h-32 rounded border border-gray-200">
                                                    </div>
                                                @endif
                                                <p class="text-sm font-medium text-gray-800 mb-3">{{ $item['text'] ?? '' }}</p>
                                                <div class="flex items-center gap-4">
                                                    @foreach(['TRUE' => 'BENAR', 'FALSE' => 'SALAH'] as $val => $label)
                                                        @php
                                                            $myAnswer = $savedAnswers[$idx] ?? '';
                                                            $isChecked = ($myAnswer == $val);
                                                            $isBenar = $val == 'TRUE';
                                                            $color = $isBenar ? 'emerald' : 'rose';
                                                        @endphp
                                                        <label class="flex-1 cursor-pointer">
                                                            <input type="radio" name="tf_{{ $soal->id }}_{{ $idx }}" value="{{ $val }}" class="peer sr-only"
                                                                   onchange="saveAnswerComplexTF({{ $soal->id }}, {{ $index }})" {{ $isChecked ? 'checked' : '' }}>
                                                            <div class="px-4 py-2 rounded-lg border border-gray-200 text-center text-sm font-bold text-gray-500 peer-checked:bg-{{ $color }}-600 peer-checked:text-white peer-checked:border-{{ $color }}-600 transition-all hover:bg-gray-50">
                                                                {{ $label }}
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                            {{-- 3. JAWABAN GANDA (CHECKBOX) --}}
                            @elseif($soal->tipe == 'jawaban_ganda')
                                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                                    <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-bold text-blue-800">Petunjuk Pengerjaan:</p>
                                        <p class="text-xs text-blue-600">Pilihlah <strong>satu atau lebih</strong> jawaban yang menurut anda benar.</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-3">
                                    @php
                                        $opsiDinamic = $soal->data_soal['options'] ?? [];
                                        if (empty($opsiDinamic)) {
                                            $opsiDinamic = [
                                                ['id' => 'A', 'text' => $soal->opsi_a, 'gambar' => $soal->gambar_a],
                                                ['id' => 'B', 'text' => $soal->opsi_b, 'gambar' => $soal->gambar_b],
                                                ['id' => 'C', 'text' => $soal->opsi_c, 'gambar' => $soal->gambar_c],
                                                ['id' => 'D', 'text' => $soal->opsi_d, 'gambar' => $soal->gambar_d],
                                            ];
                                        }
                                        
                                        // Shuffle Options Deterministically
                                        $optSeed = $seed + $soal->id;
                                        $shuffledOptions = seededShuffle($opsiDinamic, $optSeed);
                                    @endphp
                                    @foreach($shuffledOptions as $opt)
                                        @php
                                            $savedAnswers = explode(',', $jawabanTersimpan[$soal->id] ?? '');
                                            $isChecked = in_array($opt['id'], $savedAnswers);
                                        @endphp
                                        <label class="group relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:bg-white hover:border-blue-200 hover:shadow-sm
                                            {{ $isChecked ? 'bg-indigo-50/50 border-indigo-500 ring-1 ring-indigo-500' : 'bg-white border-gray-200' }}">
                                            
                                            <input type="checkbox" name="jawaban_{{ $soal->id }}[]" value="{{ $opt['id'] }}" 
                                                   class="peer sr-only" 
                                                   onchange="saveAnswerComplex({{ $soal->id }}, {{ $index }}, 'jawaban_ganda')"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            
                                            <div class="w-6 h-6 rounded border-2 border-gray-300 mr-4 flex items-center justify-center text-white peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all shrink-0">
                                                <i class="bi bi-check-lg text-sm"></i>
                                            </div>
                                            
                                            <div class="flex-1 font-medium text-gray-700 peer-checked:text-indigo-800">
                                                @if(!empty($opt['gambar']))
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $opt['gambar']) }}" class="max-h-32 rounded border border-gray-200">
                                                    </div>
                                                @endif
                                                {{ $opt['text'] }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-400 mt-2"><i class="bi bi-info-circle me-1"></i> Pilih lebih dari satu jawaban yang menurut Anda benar.</p>

                            {{-- 4. MENJODOHKAN (Revamped) --}}
                            @elseif($soal->tipe == 'menjodohkan')
                                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-3">
                                    <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-bold text-blue-800">Petunjuk Pengerjaan:</p>
                                        <p class="text-xs text-blue-600">Hubungkan item di sebelah <strong>Kiri</strong> dengan pasangan yang tepat di sebelah <strong>Kanan</strong>. Klik item kiri lalu klik item kanan untuk membuat garis penghubung.</p>
                                    </div>
                                </div>
                                @php
                                    $matches = isset($soal->data_soal['matches']) ? $soal->data_soal['matches'] : [];
                                    $savedJson = $jawabanTersimpan[$soal->id] ?? '{}';

                                    // 1. Kunci Indeks Asli untuk Kiri dan Kanan
                                    $itemsWithIndex = collect($matches)->map(function($item, $key) {
                                        return ['data' => $item, 'index' => $key];
                                    })->toArray();

                                    $seedSoal = (int) $siswa->id + (int) $soal->id;

                                    // 2. Acak Sisi Kiri dan Kanan secara independen
                                    $shuffledLeft = seededShuffle($itemsWithIndex, $seedSoal + 1);
                                    $shuffledRight = seededShuffle($itemsWithIndex, $seedSoal + 2);
                                @endphp
                                <div class="matching-container bg-white p-6 rounded-2xl border border-gray-200 shadow-sm relative select-none" id="matching-{{ $soal->id }}" data-saved='{{ $savedJson }}'>
                                    
                                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" id="svg-{{ $soal->id }}"></svg>

                                    <div class="flex justify-between items-center mb-6">
                                        <p class="text-sm text-gray-500 font-medium flex items-center gap-2">
                                            <i class="bi bi-info-circle text-blue-500"></i> Hubungkan item kiri dengan kanan
                                        </p>
                                        <button type="button" onclick="resetMatching({{ $soal->id }}, {{ $index }})" class="text-xs font-bold text-red-500 hover:text-red-700 bg-red-50 px-3 py-1.5 rounded-lg transition-colors z-20 relative">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                        </button>
                                    </div>
                                    
                                    <div class="flex flex-row justify-between relative z-20 gap-12">
                                        {{-- SISI KIRI (PERTANYAAN) --}}
                                        <div class="flex-1 space-y-6">
                                            @foreach($shuffledLeft as $leftItem)
                                                @php 
                                                    $k = $leftItem['index']; 
                                                    $match = $leftItem['data'];
                                                @endphp
                                                <div class="relative">
                                                    <button type="button" 
                                                            class="match-item-left w-full p-4 rounded-xl border-2 border-gray-100 bg-gray-50 text-left text-sm font-semibold text-gray-700 hover:border-blue-400 hover:shadow-md transition-all active:scale-95 flex items-center justify-between group"
                                                            data-id="L{{ $k }}" data-soal="{{ $soal->id }}"
                                                            onclick="selectMatchLeft(this, {{ $soal->id }}, {{ $index }})">
                                                        <div class="flex flex-col gap-2 w-full pr-4">
                                                            @if(isset($match['gambar_left']) && $match['gambar_left'])
                                                                <img src="{{ asset('storage/' . $match['gambar_left']) }}" class="max-h-24 object-contain rounded border border-gray-200 bg-white">
                                                            @endif
                                                            <span>{{ $match['pertanyaan'] ?? $match['left'] ?? 'Item ' . ($k+1) }}</span>
                                                        </div>
                                                        <div class="w-3 h-3 rounded-full bg-gray-300 group-hover:bg-blue-400 transition-colors shrink-0" id="dot-L{{ $k }}-{{ $soal->id }}"></div>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- SISI KANAN (JAWABAN) --}}
                                        <div class="flex-1 space-y-6">
                                            @foreach($shuffledRight as $rightItem)
                                                @php 
                                                    $k = $rightItem['index']; 
                                                    $itemData = $rightItem['data'];
                                                @endphp
                                                <div class="relative">
                                                     <button type="button" 
                                                            class="match-item-right w-full p-4 rounded-xl border-2 border-gray-100 bg-white text-left text-sm font-medium text-gray-600 hover:border-purple-400 hover:shadow-md transition-all active:scale-95 flex items-center justify-between group"
                                                            data-id="R{{ $k }}" 
                                                            onclick="selectMatchRight(this, {{ $soal->id }}, {{ $index }})">
                                                        <div class="w-3 h-3 rounded-full bg-gray-300 group-hover:bg-purple-400 transition-colors shrink-0" id="dot-R{{ $k }}-{{ $soal->id }}"></div>
                                                        <div class="flex flex-col gap-2 w-full pl-4 text-right">
                                                            @if(isset($itemData['gambar_right']) && $itemData['gambar_right'])
                                                                <img src="{{ asset('storage/' . $itemData['gambar_right']) }}" class="max-h-24 object-contain rounded border border-gray-200 bg-white ml-auto">
                                                            @endif
                                                            <span>{{ $itemData['jawaban'] ?? $itemData['right'] ?? 'Item ' . ($k+1) }}</span>
                                                        </div>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" id="jawaban_matching_{{ $soal->id }}" value="{{ $savedJson }}">
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Bottom Toolbar --}}
            <div class="h-20 bg-white border-t border-gray-200 px-6 md:px-10 flex items-center justify-between shrink-0 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] z-10 w-full">
                <button onclick="prevQuestion()" id="btn-prev" class="flex items-center gap-2 px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 font-bold transition-all disabled:opacity-50">
                    <i class="bi bi-arrow-left"></i> Sebelumnya
                </button>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg hover:bg-amber-50 text-amber-600 transition-colors select-none">
                        <input type="checkbox" id="ragu-check" class="w-4 h-4 rounded border-amber-400 text-amber-500" onchange="toggleRagu()">
                        <span class="text-sm font-bold">Ragu-ragu</span>
                    </label>
                </div>
                <div>
                     <button onclick="nextQuestion()" id="btn-next" class="flex items-center gap-2 px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-200 transition-all">
                        Selanjutnya <i class="bi bi-arrow-right"></i>
                    </button>
                    <button onclick="confirmSubmit()" id="btn-submit" class="hidden flex items-center gap-2 px-8 py-3 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-bold shadow-lg shadow-emerald-200 transition-all animate-pulse">
                        <i class="bi bi-check-circle-fill"></i> Kumpulkan Ujian
                    </button>
                </div>
            </div>
        </section>
    </main>

    {{-- Submit Confirmation Modal --}}
    <dialog id="confirm-modal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md backdrop:bg-black/50">
        <div class="p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-question-lg text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Yakin Ingin Mengumpulkan?</h3>
            <p class="text-gray-500 text-sm" id="modal-status-text">Periksa kembali jawaban Anda.</p>
            <div class="grid grid-cols-2 gap-3 mt-8">
                <button onclick="document.getElementById('confirm-modal').close()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-colors">Periksa Lagi</button>
                <button onclick="submitExamForce()" class="w-full px-4 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors shadow-lg">Ya, Kumpulkan</button>
            </div>
        </div>
    </dialog>

    {{-- Violation Modal --}}
    <dialog id="violation-modal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md bg-white backdrop:bg-black/80 m-auto">
        <div class="p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gray-100">
                <div id="violation-progress" class="h-full bg-red-500 w-full transition-all duration-[5000ms] ease-linear"></div>
            </div>

            <div class="w-20 h-20 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-6 animate-pulse">
                <i class="bi bi-exclamation-triangle-fill text-4xl"></i>
            </div>
            
            <h3 class="text-2xl font-[Poppins-Bold] text-gray-900 mb-2">PELANGGARAN TERDETEKSI</h3>
            
            <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6">
                <p class="text-xs font-bold text-red-500 uppercase tracking-wide mb-1">ALASAN PELANGGARAN</p>
                <p class="text-gray-800 font-medium" id="modal-violation-reason">-</p>
            </div>

            <p class="text-gray-500 text-sm mb-8 leading-relaxed">
                Sistem keamanan mendeteksi aktivitas mencurigakan. 
                Sesuai peraturan, ujian Anda akan <span class="text-red-600 font-bold">OTOMATIS DIKUMPULKAN</span> dalam 5 detik.
            </p>

            <button onclick="submitExamForce()" class="w-full px-6 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-200 hover:-translate-y-1">
                Kumpulkan Sekarang
            </button>
        </div>
    </dialog>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // --- CONFIG ---
        const totalQuestions = {{ $ujian->soals->count() }};
        const examId = {{ $ujian->id }};
        const endTimeStr = "{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('Y-m-d H:i:s') }}";
        const examEndTime = new Date(endTimeStr).getTime();
        
        // --- STATE ---
        let currentQuestionIndex = 0;
        let isExamActive = false; // Status Ujian
        let timerInterval;

        // --- 1. CORE: START EXAM LOGIC ---
        function startSafeExam() {
            const elem = document.documentElement;
            // Coba masuk Fullscreen
            const reqFS = elem.requestFullscreen || elem.webkitRequestFullscreen || elem.msRequestFullscreen;

            if (reqFS) {
                reqFS.call(elem).then(() => {
                    initExamSystem();
                }).catch(err => {
                    alert("Browser memblokir fullscreen. Harap izinkan fullscreen untuk memulai.");
                    // Fallback jika gagal (opsional, tapi sebaiknya dipaksa)
                    initExamSystem();
                });
            } else {
                initExamSystem();
            }
        }

        function initExamSystem() {
            // Sembunyikan Overlay
            document.getElementById('safe-entry-overlay').style.display = 'none';
            isExamActive = true;
            
            // Start Timer
            timerInterval = setInterval(updateTimer, 1000);
            
            // Show First Question
            showQuestion(0);
            
            // Auto Update Progress
            updateNavProgress();
            
            // Aktifkan Deteksi Curang (Delay dikit biar ga langsung trigger saat loading)
            setTimeout(armSecuritySystem, 1000);
        }

        // --- 2. SECURITY SYSTEM (ANTI-CHEAT) ---
        function armSecuritySystem() {
            // A. Deteksi Pindah Tab (Visibility Change)
            document.addEventListener("visibilitychange", function() {
                if (document.hidden && isExamActive) {
                    handleViolation("Meninggalkan halaman / Minimize Browser");
                }
            });

            // B. Deteksi Klik di luar browser (Blur)
            window.addEventListener("blur", function() {
                if (isExamActive) {
                    // Cek lagi apakah benar2 pindah window (kadang alert bikin blur)
                    // Tapi untuk ujian ketat, blur = violation
                     handleViolation("Membuka aplikasi lain (Kehilangan Fokus)");
                }
            });

            // C. Deteksi Keluar Fullscreen
            const fsEvents = ['fullscreenchange', 'webkitfullscreenchange', 'msfullscreenchange'];
            fsEvents.forEach(evt => {
                document.addEventListener(evt, function() {
                    if (!document.fullscreenElement && !document.webkitFullscreenElement && isExamActive) {
                        handleViolation("Keluar dari Mode Fullscreen");
                    }
                });
            });

            // D. Blokir Klik Kanan & Keyboard shortcuts
            document.addEventListener('contextmenu', event => event.preventDefault());
            window.addEventListener('keydown', function(e) {
                // Blokir F12, F5, Ctrl+R, Ctrl+U, Alt+Tab (sebisa mungkin)
                if (
                    e.key === 'F12' || 
                    e.key === 'F5' ||
                    (e.ctrlKey && e.key === 'r') ||
                    (e.ctrlKey && e.key === 'u') ||
                    e.key === 'Escape'
                ) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        function handleViolation(reason) {
            if (!isExamActive) return; // Supaya tidak double submit
            isExamActive = false; // Stop monitoring

            // Show Modal
            const modal = document.getElementById('violation-modal');
            document.getElementById('modal-violation-reason').innerText = reason;
            modal.showModal();
            
            // Start Countdown Animation
            // Small delay to ensure transition triggers
            setTimeout(() => {
                document.getElementById('violation-progress').style.width = '0%';
            }, 100);

            // Auto Submit after 5 seconds
            setTimeout(() => {
                submitExamForce();
            }, 5000);
        }

        // --- 3. SUBMIT FUNCTION ---
        function submitExamForce() {
            isExamActive = false; // Matikan security
            
            // Buat Form Submit POST secara dinamis
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('siswa.ujian.selesai', $ujian->id) }}";
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }

        function confirmSubmit() {
            let answeredCount = 0;
            
            // Iterate over all question containers to check if answered
            document.querySelectorAll('.question-item').forEach(item => {
                const soalId = item.dataset.soalId;
                let isAnswered = false;

                // 1. Check Radio (Pilihan Ganda / Benar Salah)
                if (item.querySelector(`input[type="radio"][name="jawaban_${soalId}"]:checked`)) {
                    isAnswered = true;
                }
                // 2. Check Checkbox (Jawaban Ganda)
                else if (item.querySelector(`input[type="checkbox"][name="jawaban_${soalId}[]"]:checked`)) {
                    isAnswered = true;
                }
                // 3. Check Matching (Hidden Input with JSON)
                else {
                    const matchInput = document.getElementById(`jawaban_matching_${soalId}`);
                    if (matchInput && matchInput.value) {
                        try {
                            const val = JSON.parse(matchInput.value);
                            // Check if object is not empty (has at least one pair)
                            if (Object.keys(val).length > 0) {
                                isAnswered = true;
                            }
                        } catch (e) {
                            // invalid json, ignore
                        }
                    } 
                    // 4. Check Complex TF
                    else {
                        const tfInputs = item.querySelectorAll(`input[name^="tf_${soalId}_"]:checked`);
                        if (tfInputs.length > 0) {
                             // Optional: Check if ALL are answered
                             // const totalData = item.querySelectorAll('input[name^="tf_' + soalId + '_"][value="TRUE"]').length;
                             // if(tfInputs.length === totalData) isAnswered = true;
                             isAnswered = true;
                        }
                    }
                }

                if (isAnswered) answeredCount++;
            });

            const remaining = totalQuestions - answeredCount;
            const msg = document.getElementById('modal-status-text');
            
            if(remaining > 0) {
                msg.innerHTML = `<span class="text-red-500 font-bold">Peringatan:</span> Masih ada ${remaining} soal belum dijawab.`;
            } else {
                msg.innerHTML = "Anda sudah menjawab semua soal.";
            }
            document.getElementById('confirm-modal').showModal();
        }

        // --- 4. TIMER & NAVIGATION ---
        function updateTimer() {
            if (!isExamActive) return;
            
            const now = new Date().getTime();
            const distance = examEndTime - now;

            if (distance < 0) {
                clearInterval(timerInterval);
                document.getElementById("exam-timer").innerHTML = "00:00:00";
                alert("WAKTU HABIS!");
                submitExamForce();
                return;
            }

            const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById("exam-timer").innerHTML = 
                `${h<10?'0'+h:h}:${m<10?'0'+m:m}:${s<10?'0'+s:s}`;
        }

        function showQuestion(index) {
            // Hide all
            document.querySelectorAll('.question-item').forEach(el => el.classList.add('hidden'));
            // Show target
            document.getElementById(`question-${index}`).classList.remove('hidden');
            
            // Nav Button Styles
            document.querySelectorAll('[id^="nav-btn-"]').forEach(btn => btn.classList.remove('ring-2', 'ring-blue-400'));
            document.getElementById(`nav-btn-${index}`).classList.add('ring-2', 'ring-blue-400');
            
            // Prev/Next/Submit visibility
            document.getElementById('btn-prev').disabled = (index === 0);
            if (index === totalQuestions - 1) {
                document.getElementById('btn-next').classList.add('hidden');
                document.getElementById('btn-submit').classList.remove('hidden');
            } else {
                document.getElementById('btn-next').classList.remove('hidden');
                document.getElementById('btn-submit').classList.add('hidden');
            }
            currentQuestionIndex = index;
            
            // Sync Ragu Checkbox (Optional, simple implementation)
            document.getElementById('ragu-check').checked = false; 
        }

        function nextQuestion() { if(currentQuestionIndex < totalQuestions - 1) showQuestion(currentQuestionIndex+1); }
        function prevQuestion() { if(currentQuestionIndex > 0) showQuestion(currentQuestionIndex-1); }
        function jumpToQuestion(idx) { showQuestion(idx); }

        // --- 5. SAVING ANSWER ---
        // --- 5. SAVING ANSWER ---
        function saveAnswer(soalId, jawaban, index) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch("{{ route('siswa.ujian.simpan_jawaban') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ ujian_id: examId, soal_id: soalId, jawaban: jawaban })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const btn = document.getElementById(`nav-btn-${index}`);
                    // Update style ONLY if not marked as Ragu
                    if(!document.getElementById('ragu-check').checked || currentQuestionIndex !== index) {
                         // Double check ragu class just in case logic is out of sync
                         if(!btn.classList.contains('bg-amber-400')) {
                             btn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                             btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                         }
                    }
                    updateNavProgress();
                }
            })
            .catch(err => console.error(err));
        }

        function toggleRagu() {
            const btn = document.getElementById(`nav-btn-${currentQuestionIndex}`);
            const isChecked = document.getElementById('ragu-check').checked;
            
            if(isChecked) {
                // Set style RAGU (Amber)
                btn.classList.remove('bg-blue-600', 'bg-white', 'text-gray-600', 'border-gray-200', 'border-blue-600');
                btn.classList.add('bg-amber-400', 'text-white', 'border-amber-400');
            } else {
                // Restore style based on ANSWER STATUS
                // Check if current question has answer
                let isAnswered = false;
                const qItem = document.getElementById(`question-${currentQuestionIndex}`);
                if(qItem) {
                    const soalId = qItem.dataset.soalId;
                    if (qItem.querySelector(`input[name="jawaban_${soalId}"]:checked`)) isAnswered = true;
                    else if (qItem.querySelector(`input[name="jawaban_${soalId}[]"]:checked`)) isAnswered = true;
                    else {
                         const matchVal = document.getElementById(`jawaban_matching_${soalId}`)?.value;
                         if(matchVal && matchVal.length > 2 && matchVal !== '{}') isAnswered = true;
                    }
                    // For Complex TF
                    if(!isAnswered && qItem.querySelectorAll(`input[name^="tf_${soalId}_"]:checked`).length > 0) isAnswered = true;
                }

                btn.classList.remove('bg-amber-400', 'border-amber-400');
                if(isAnswered) {
                    btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                } else {
                    btn.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                }
            }
        }

        function updateNavProgress() {
             const answered = document.querySelectorAll('.bg-blue-600.text-white[id^="nav-btn-"]').length;
             const pct = Math.round((answered / totalQuestions) * 100);
             const bar = document.getElementById('progress-bar-nav');
             if(bar) {
                 bar.style.width = `${pct}%`;
                 document.getElementById('progress-text').innerText = `${pct}% Selesai`;
             }
        }

        // --- COMPLEX ANSWER LOGIC (Restored) ---
        function saveAnswerComplex(soalId, index, tipe) {
            let jawaban = "";
            
            if (tipe === 'jawaban_ganda') {
                // Collect all checked checkbox values
                const checkboxes = document.querySelectorAll(`input[name="jawaban_${soalId}[]"]:checked`);
                const values = Array.from(checkboxes).map(cb => cb.value);
                jawaban = values.join(','); // Send as comma separated string
            } 
            else if (tipe === 'menjodohkan') {
                // Taken from hidden input
                jawaban = document.getElementById(`jawaban_matching_${soalId}`).value;
            }

            saveAnswer(soalId, jawaban, index);
        }

        function saveAnswerComplexTF(soalId, index) {
             const inputs = document.querySelectorAll(`input[name^="tf_${soalId}_"]:checked`);
             let answers = {};
             inputs.forEach(input => {
                 const name = input.name; // tf_123_0
                 const parts = name.split('_');
                 const idx = parts[2];
                 answers[idx] = input.value;
             });
             
             // Convert to JSON
             const jsonAnswer = JSON.stringify(answers);
             saveAnswer(soalId, jsonAnswer, index);
        }

        // --- MATCHING LOGIC (Restored) ---
        let selectedLeft = null;
        let pairs = {}; // { soalId: { leftId: rightId } }
        
        const matchColors = [
            { border: 'border-blue-500', bg: 'bg-blue-50', text: 'text-blue-700', stroke: '#3b82f6' },
            { border: 'border-orange-500', bg: 'bg-orange-50', text: 'text-orange-700', stroke: '#f97316' },
            { border: 'border-emerald-500', bg: 'bg-emerald-50', text: 'text-emerald-700', stroke: '#10b981' },
            { border: 'border-purple-500', bg: 'bg-purple-50', text: 'text-purple-700', stroke: '#a855f7' },
            { border: 'border-pink-500', bg: 'bg-pink-50', text: 'text-pink-700', stroke: '#ec4899' },
            { border: 'border-cyan-500', bg: 'bg-cyan-50', text: 'text-cyan-700', stroke: '#06b6d4' },
        ];

        function getPairColor(index) {
            return matchColors[index % matchColors.length];
        }

        function selectMatchLeft(el, soalId, index) {
            if (selectedLeft && selectedLeft.el !== el) {
                selectedLeft.el.classList.remove('ring-4', 'ring-blue-200', 'border-blue-400');
            }
            el.classList.add('ring-4', 'ring-blue-200', 'border-blue-400');
            selectedLeft = { el: el, id: el.dataset.id };
        }

        function selectMatchRight(el, soalId, index) {
            if (!selectedLeft) return; 

            const rightId = el.dataset.id;
            const leftId = selectedLeft.id;
            
            if(!pairs[soalId]) pairs[soalId] = {};
            
            selectedLeft.el.classList.remove('ring-4', 'ring-blue-200', 'border-blue-400');
            
            pairs[soalId][leftId] = rightId;
            
            document.getElementById(`jawaban_matching_${soalId}`).value = JSON.stringify(pairs[soalId]);
            saveAnswerComplex(soalId, index, 'menjodohkan');
            
            drawMatchingLines(soalId);
            selectedLeft = null;
        }

        function drawMatchingLines(soalId) {
            const container = document.getElementById(`matching-${soalId}`);
            if(!container) return;
            
            const svg = document.getElementById(`svg-${soalId}`);
            const questionData = pairs[soalId] || {};
            
            svg.innerHTML = '';
            
            container.querySelectorAll('button[data-id^="L"], button[data-id^="R"]').forEach(btn => {
                matchColors.forEach(c => {
                    btn.classList.remove(c.border, c.bg, c.text, 'shadow-md');
                });
                btn.classList.add('border-gray-100');
                if(btn.dataset.id.startsWith('L')) btn.classList.add('bg-gray-50', 'text-gray-700');
                else btn.classList.add('bg-white', 'text-gray-600');
                
                const dot = btn.querySelector('[id^="dot-"]');
                if(dot) dot.style.backgroundColor = '#d1d5db'; 
            });

            let pairIndex = 0;
            const containerRect = container.getBoundingClientRect();

            for (const [leftId, rightId] of Object.entries(questionData)) {
                const leftBtn = container.querySelector(`[data-id="${leftId}"]`);
                const rightBtn = container.querySelector(`[data-id="${rightId}"]`);
                
                if (leftBtn && rightBtn) {
                    const color = getPairColor(pairIndex);
                    
                    [leftBtn, rightBtn].forEach(btn => {
                        btn.classList.remove('border-gray-100', 'bg-gray-50', 'bg-white', 'text-gray-700', 'text-gray-600');
                        btn.classList.add(color.border, color.bg, color.text, 'shadow-md');
                        const dot = btn.querySelector('[id^="dot-"]');
                        if(dot) dot.style.backgroundColor = color.stroke;
                    });

                    const leftDot = leftBtn.querySelector('[id^="dot-"]');
                    const rightDot = rightBtn.querySelector('[id^="dot-"]');
                    
                    if (leftDot && rightDot) {
                        const lRect = leftDot.getBoundingClientRect();
                        const rRect = rightDot.getBoundingClientRect();
                        
                        const x1 = lRect.left + (lRect.width / 2) - containerRect.left;
                        const y1 = lRect.top + (lRect.height / 2) - containerRect.top;
                        const x2 = rRect.left + (rRect.width / 2) - containerRect.left;
                        const y2 = rRect.top + (rRect.height / 2) - containerRect.top;

                        const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
                        line.setAttribute("x1", x1);
                        line.setAttribute("y1", y1);
                        line.setAttribute("x2", x2);
                        line.setAttribute("y2", y2);
                        line.setAttribute("stroke", color.stroke);
                        line.setAttribute("stroke-width", "3");
                        line.setAttribute("stroke-linecap", "round");
                        line.setAttribute("class", "transition-all duration-500");
                        
                        svg.appendChild(line);
                    }
                    pairIndex++;
                }
            }
        }

        function resetMatching(soalId, index) {
            pairs[soalId] = {};
            document.getElementById(`jawaban_matching_${soalId}`).value = "";
            saveAnswerComplex(soalId, index, 'menjodohkan');
            drawMatchingLines(soalId);
        }

        function updateTrueFalseStyles(soalId, selectedVal) {
            ['A', 'B'].forEach(val => {
                const label = document.getElementById(`tf-label-${soalId}-${val}`);
                const icon = document.getElementById(`tf-icon-${soalId}-${val}`);
                const text = document.getElementById(`tf-text-${soalId}-${val}`);
                
                if (label && icon && text) {
                    if (val === selectedVal) {
                        label.className = `group relative flex flex-col items-center justify-center p-8 rounded-3xl border-2 cursor-pointer transition-all duration-300 overflow-hidden ${label.dataset.activeClass}`;
                        icon.className = `w-16 h-16 mx-auto rounded-full flex items-center justify-center text-3xl mb-4 transition-all duration-300 shadow-sm ${label.dataset.activeIcon}`;
                        text.className = `text-2xl font-[Poppins-Bold] tracking-wide ${label.dataset.activeText}`;
                    } else {
                        label.className = `group relative flex flex-col items-center justify-center p-8 rounded-3xl border-2 cursor-pointer transition-all duration-300 overflow-hidden ${label.dataset.inactiveClass}`;
                        icon.className = `w-16 h-16 mx-auto rounded-full flex items-center justify-center text-3xl mb-4 transition-all duration-300 shadow-sm ${label.dataset.inactiveIcon}`;
                        text.className = `text-2xl font-[Poppins-Bold] tracking-wide ${label.dataset.inactiveText}`;
                    }
                }
            });
        }

        // Initialize Matching & Resize Events
        document.addEventListener("DOMContentLoaded", () => {
             document.querySelectorAll('.matching-container').forEach(container => {
                  const saved = JSON.parse(container.dataset.saved || "{}");
                  const soalId = container.id.split('-')[1];
                  pairs[soalId] = saved;
                  setTimeout(() => drawMatchingLines(soalId), 500);
             });
             
             window.addEventListener('resize', () => {
                 document.querySelectorAll('.matching-container').forEach(container => {
                     const soalId = container.id.split('-')[1];
                     drawMatchingLines(soalId);
                 });
             });
        });
        
        // Hook into showQuestion for redrawing
        const originalShowQuestion = showQuestion;
        showQuestion = function(index) {
            originalShowQuestion(index); 
            const qEl = document.getElementById(`question-${index}`);
            
            // Check matching
            const matchingContainer = qEl.querySelector('.matching-container');
            if (matchingContainer) {
                const soalId = matchingContainer.id.split('-')[1];
                setTimeout(() => drawMatchingLines(soalId), 100); 
            }
        };
    </script>
</body>
</html>