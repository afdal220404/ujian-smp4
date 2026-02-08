@extends('layouts.app')

@section('title', 'Detail Nilai Ujian')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-cyan-200/50 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link active rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link active rounded-xl">
            <i class="bi bi-award"></i> <span>Nilai</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link rounded-xl">
             <i class="bi bi-file-earmark-text"></i> <span>Bank Soal</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    
    {{-- Breadcrumbs --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
        <i class="bi bi-chevron-right text-xs"></i>
        <a href="{{ route('siswa.nilai') }}" class="hover:text-blue-600 transition-colors">Nilai</a>
        <i class="bi bi-chevron-right text-xs"></i>
        <span class="text-blue-600 font-medium truncate max-w-[200px]">{{ $ujian->nama_ujian }}</span>
    </div>

    {{-- Header Info Ujian --}}
    <div class="bg-white rounded-3xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 p-8 relative overflow-hidden">
        {{-- Decorative Elements --}}
        <div class="absolute top-0 right-0 p-8 opacity-5">
            <i class="bi bi-award-fill text-9xl text-blue-600"></i>
        </div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl opacity-50"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider border border-blue-100">
                        <i class="bi bi-journal-check me-1"></i> {{ $ujian->mapel->nama_mapel ?? 'Mapel Umum' }}
                    </span>
                    <span class="text-gray-500 text-xs font-medium px-2 py-0.5 border border-gray-100 rounded-full bg-gray-50">
                        <i class="bi bi-calendar4 me-1"></i> {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d F Y') }}
                    </span>
                    <span class="text-gray-500 text-xs font-medium px-2 py-0.5 border border-gray-100 rounded-full bg-gray-50">
                        <i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                    </span>
                </div>
                
                {{-- Styled Exam Title --}}
                <div class="mt-2 px-4 py-2 bg-[#00415a]/5 rounded-xl border border-[#00415a]/10 inline-block">
                    <h1 class="text-2xl font-[Poppins-Bold] text-[#00415a] tracking-tight">{{ $ujian->nama_ujian }}</h1>
                </div>

                <p class="text-gray-500 mt-4 text-base">
                    Berikut adalah detail hasil pengerjaan ujian Anda.
                </p>

                {{-- Detailed Timings --}}
                <div class="flex flex-wrap items-center gap-6 mt-6 pt-6 border-t border-gray-100">
                    {{-- Mulai --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                            <i class="bi bi-play-fill text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mulai Mengerjakan</p>
                            <p class="text-sm font-bold text-gray-700">
                                {{ \Carbon\Carbon::parse($hasilUjian->waktu_mulai)->format('H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    {{-- Selesai --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center border border-red-100 shadow-sm">
                            <i class="bi bi-stop-fill text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Selesai Mengerjakan</p>
                            <p class="text-sm font-bold text-gray-700">
                                {{ \Carbon\Carbon::parse($hasilUjian->waktu_selesai)->format('H:i') }} WIB
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="text-right hidden md:block">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nilai Akhir</p>
                    <p class="text-sm font-[Poppins-Bold] text-[#00415a]">Hasil Kalkulasi</p>
                </div>
                <div class="relative group">
                    @php
                        $score = round($hasilUjian->nilai);
                        $scoreColor = $score > 80 ? 'text-emerald-600' : ($score >= 70 ? 'text-yellow-600' : 'text-red-600');
                    @endphp
                    <div class="relative bg-white p-6 rounded-2xl border border-gray-200 shadow-sm min-w-[140px] text-center">
                        <div class="text-5xl font-[Poppins-Bold] {{ $scoreColor }} tracking-tight">
                            {{ $score }}
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-1">Skor Total</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Ringkas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm flex items-center gap-5 hover:border-emerald-300 transition-colors group">
            <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 text-2xl group-hover:scale-110 transition-transform">
                <i class="bi bi-check-lg"></i>
            </div>
            <div>
                <p class="text-3xl font-[Poppins-Bold] text-gray-800">{{ $jumlahBenar }}</p>
                <p class="text-sm font-medium text-gray-500">Jawaban Benar</p>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 border border-red-100 shadow-sm flex items-center gap-5 hover:border-red-300 transition-colors group">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center text-red-600 text-2xl group-hover:scale-110 transition-transform">
                <i class="bi bi-x-lg"></i>
            </div>
            <div>
                <p class="text-3xl font-[Poppins-Bold] text-gray-800">{{ $jumlahSalah }}</p>
                <p class="text-sm font-medium text-gray-500">Jawaban Salah</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-blue-100 shadow-sm flex items-center gap-5 hover:border-blue-300 transition-colors group">
            <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-2xl group-hover:scale-110 transition-transform">
                <i class="bi bi-list-task"></i>
            </div>
            <div>
                <p class="text-3xl font-[Poppins-Bold] text-gray-800">{{ count($daftarSoal) }}</p>
                <p class="text-sm font-medium text-gray-500">Total Soal</p>
            </div>
        </div>
    </div>

    {{-- Detail Jawaban --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="font-[Poppins-Bold] text-[#00415a] text-xl flex items-center gap-2">
                <i class="bi bi-card-checklist text-blue-600"></i> Analisis Jawaban
            </h3>
            <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-bold text-gray-500 border border-gray-200">
                Mode Review
            </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($daftarSoal as $index => $soal)
            @php
                // Use the status_jawaban which now comes from DB is_correct
                $isCorrect = $soal->status_jawaban;
            @endphp
        <div class="bg-white rounded-xl border {{ $isCorrect ? 'border-emerald-100' : 'border-red-100' }} shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-4 transition-all hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] relative overflow-hidden group">
            
            {{-- Status Strip --}}
            <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isCorrect ? 'bg-emerald-500' : 'bg-red-500' }}"></div>

            <div class="flex flex-col md:flex-row gap-4 relative z-10">
                {{-- Number & Status Icon --}}
                <div class="flex-shrink-0 flex flex-col items-center gap-2">
                    <div class="w-8 h-8 rounded-lg {{ $isCorrect ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center font-[Poppins-Bold] text-sm shadow-sm border {{ $isCorrect ? 'border-emerald-100' : 'border-red-100' }}">
                        {{ $index + 1 }}
                    </div>
                </div>

                {{-- Question Content --}}
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $isCorrect ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' }}">
                            {{ $isCorrect ? 'Benar' : 'Salah' }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-gray-50 text-gray-500 border-gray-200">
                            {{ str_replace('_', ' ', $soal->tipe) }}
                        </span>
                    </div>

                    <div class="text-gray-800 font-medium text-sm leading-relaxed mb-3 font-[Poppins-Medium]">
                        {!! nl2br(e($soal->pertanyaan)) !!}
                    </div>
                    
                    @if($soal->gambar)
                        <div class="mb-3 p-1.5 bg-gray-50 rounded-lg border border-gray-100 inline-block">
                            <img src="{{ asset('storage/' . $soal->gambar) }}" class="max-h-40 rounded shadow-sm hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif

                    {{-- Options Review --}}
                    @if($soal->tipe == 'pilihan_ganda')
                    <div class="space-y-2 mt-3">
                        @foreach(['A', 'B', 'C', 'D'] as $opt)
                            @php
                                $isKey = (strtoupper($soal->kunci_jawaban) == $opt);
                                $isSelected = (strtoupper($soal->jawaban_siswa) == $opt);
                                $optionText = $soal->{'opsi_'.strtolower($opt)};
                                
                                // Logic Styling
                                $styleClass = 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'; // Default
                                $icon = '';

                                if($isKey) {
                                    $styleClass = 'bg-emerald-50 border-emerald-500 text-emerald-700 ring-1 ring-emerald-500 font-bold';
                                    $icon = '<i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>';
                                } elseif($isSelected) { 
                                    // Selected but NOT Key (Wrong)
                                    $styleClass = 'bg-red-50 border-red-500 text-red-700 ring-1 ring-red-500'; 
                                    $icon = '<i class="bi bi-x-circle-fill text-red-600 text-sm"></i>';
                                }
                            @endphp
                            
                            <div class="relative flex items-center p-2 rounded-lg border {{ $styleClass }} transition-all">
                                <div class="w-6 h-6 rounded bg-white border border-gray-200 flex items-center justify-center font-bold text-xs mr-2 shadow-sm shrink-0 uppercase {{ $isKey ? 'text-emerald-600 border-emerald-200' : ($isSelected ? 'text-red-600 border-red-200' : 'text-gray-500') }}">
                                    {{ $opt }}
                                </div>
                                <div class="flex-1 font-medium text-xs">
                                    {{ $optionText }}
                                </div>
                                <div class="ml-2 flex items-center gap-2">
                                    @if($isSelected)
                                        
                                    @endif
                                    {!! $icon !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- 2. BENAR / SALAH --}}
                    @elseif($soal->tipe == 'benar_salah')
                        <div class="flex gap-4 mt-3">
                            @foreach(['A' => 'BENAR', 'B' => 'SALAH'] as $val => $label)
                                @php
                                    $isKey = ($soal->kunci_jawaban == ($val == 'A' ? 'TRUE' : 'FALSE') || $soal->kunci_jawaban == $val); // Support TRUE/FALSE or A/B storage
                                    $isSelected = ($soal->jawaban_siswa == $val);
                                    
                                    $baseColor = ($val == 'A') ? 'emerald' : 'red';
                                    $opacity = ($isSelected || $isKey) ? '100' : '40';
                                    $ring = ($isKey) ? "ring-2 ring-blue-400 ring-offset-1" : "";
                                @endphp
                                <div class="flex-1 p-3 rounded-lg border flex items-center justify-between opacity-{{ $opacity }}
                                    {{ $isSelected 
                                        ? ($isKey ? "bg-{$baseColor}-50 border-{$baseColor}-500 text-{$baseColor}-700" : "bg-red-50 border-red-500 text-red-700")
                                        : ($isKey ? "bg-emerald-50 border-emerald-500 text-emerald-700" : "bg-white border-gray-200") 
                                    }} {{ $ring }}">
                                    
                                    <span class="font-bold text-sm">{{ $label }}</span>
                                    
                                    @if($isSelected)
                                        <span class="text-[10px] bg-white border px-1.5 rounded font-bold uppercase">Kamu</span>
                                    @endif
                                    @if($isKey)
                                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                    @elseif($isSelected)
                                        <i class="bi bi-x-circle-fill text-red-600"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    {{-- 3. JAWABAN GANDA --}}
                    @elseif($soal->tipe == 'jawaban_ganda')
                        <div class="space-y-2 mt-3">
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Opsi Jawaban</p>
                            @foreach(['A', 'B', 'C', 'D'] as $opt)
                                @php
                                    // Parse CSV with Trim & Upper
                                    $keys = array_map(function($val) { return trim(strtoupper($val)); }, explode(',', $soal->kunci_jawaban));
                                    $answers = array_map(function($val) { return trim(strtoupper($val)); }, explode(',', $soal->jawaban_siswa ?? ''));
                                    
                                    $isKey = in_array($opt, $keys);
                                    $isSelected = in_array($opt, $answers);
                                    $optionText = $soal->{'opsi_'.strtolower($opt)};
                                    
                                    // Logic Styling
                                    $styleClass = 'bg-gray-50 border-gray-200 text-gray-400';
                                    $icon = '';

                                    if($isKey && $isSelected) {
                                        $styleClass = 'bg-emerald-50 border-emerald-500 text-emerald-700 font-bold';
                                        $icon = '<i class="bi bi-check-circle-fill text-emerald-600"></i>';
                                    } elseif($isKey && !$isSelected) {
                                        $styleClass = 'bg-blue-50 border-blue-300 text-blue-600'; // Missed correct answer
                                        $icon = '<span class="text-[10px] text-blue-500 font-bold">Kunci</span>';
                                    } elseif(!$isKey && $isSelected) {
                                        $styleClass = 'bg-red-50 border-red-500 text-red-700'; // Wrong selection
                                        $icon = '<i class="bi bi-x-circle-fill text-red-600"></i>';
                                    }
                                @endphp
                                <div class="flex items-center p-2.5 rounded-lg border {{ $styleClass }} text-xs">
                                    <div class="w-5 h-5 flex items-center justify-center border rounded mr-2 bg-white text-gray-500 font-bold shadow-sm">
                                        {{ $opt }}
                                    </div>
                                    <div class="flex-1">{{ $optionText }}</div>
                                    <div class="ml-2">{!! $icon !!}</div>
                                </div>
                            @endforeach
                        </div>

                    {{-- 4. MENJODOHKAN --}}
                    @elseif($soal->tipe == 'menjodohkan')
                        <div class="mt-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                             <p class="text-[10px] font-bold text-gray-400 uppercase mb-3">Evaluasi Pasangan</p>
                             @php
                                $matches = isset($soal->data_soal['matches']) ? $soal->data_soal['matches'] : [];
                                
                                $userPairs = [];
                                if (isset($soal->jawaban_siswa)) {
                                    if (is_array($soal->jawaban_siswa)) {
                                        $userPairs = $soal->jawaban_siswa;
                                    } elseif (is_string($soal->jawaban_siswa)) {
                                        $userPairs = json_decode($soal->jawaban_siswa, true);
                                        if (!is_array($userPairs)) $userPairs = [];
                                    }
                                }
                             @endphp
                             
                             <div class="space-y-3">
                                @foreach($matches as $k => $match)
                                    @php
                                        // Student's choice for this Left Item (L$k)
                                        $userRightId = $userPairs['L'.$k] ?? null; // e.g., "R1"
                                        
                                        // Correct Right Id is always "R$k" (Assuming L0->R0 as key)
                                        $correctRightId = 'R'.$k;
                                        
                                        // Find text content
                                        $leftText = $match['left'] ?? $match['pertanyaan'] ?? '-'; // Support both old and new format if any
                                        
                                        // User Answer Text
                                        $userAnswerText = "(Tidak Dijawab)";
                                        $pairIsCorrect = false;
                                        
                                        if ($userRightId) {
                                            $rIndex = (int) str_replace('R', '', $userRightId);
                                            // Ensure index exists
                                            if (isset($matches[$rIndex])) {
                                                $userAnswerText = $matches[$rIndex]['right'] ?? $matches[$rIndex]['jawaban'] ?? 'Unknown';
                                            } else {
                                                $userAnswerText = 'Unknown Index';
                                            }
                                            $pairIsCorrect = ($userRightId === $correctRightId);
                                        }
                                        
                                        // Correct Answer Text
                                        $correctAnswerText = $match['right'] ?? $match['jawaban'] ?? '-';
                                    @endphp
                                    
                                    <div class="flex flex-col md:flex-row gap-2 text-xs">
                                        {{-- Left --}}
                                        <div class="flex-1 p-2 bg-white border border-gray-200 rounded-lg shadow-sm font-medium text-gray-700">
                                            {{ $leftText }}
                                        </div>
                                        
                                        {{-- Connector --}}
                                        <div class="flex items-center justify-center text-gray-300">
                                            <i class="bi bi-arrow-right"></i>
                                        </div>

                                        {{-- Right (User Answer) --}}
                                        <div class="flex-1 p-2 border rounded-lg shadow-sm flex items-center justify-between
                                            {{ $pairIsCorrect ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-red-50 border-red-300 text-red-700' }}">
                                            <span>{{ $userAnswerText }}</span>
                                            @if($pairIsCorrect)
                                                <i class="bi bi-check-lg"></i>
                                            @else
                                                <i class="bi bi-x-lg"></i>
                                            @endif
                                        </div>

                                        @if(!$pairIsCorrect)
                                            {{-- Correction --}}
                                            <div class="flex-1 p-2 bg-blue-50 border border-blue-200 rounded-lg shadow-sm text-blue-700 opacity-80">
                                                <span class="text-[9px] font-bold block text-blue-400 uppercase">Seharusnya:</span>
                                                {{ $correctAnswerText }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                             </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
        @endforeach
        </div>
    </div>

</div>
@endsection
