@extends('layouts.app')

@section('title', 'Detail Hasil Ujian')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link active rounded-xl">
            <i class="bi bi-award"></i> <span>Ujian</span>
        </a>
        <a href="{{ route('siswa.profil') }}" class="nav-link rounded-xl">
            <i class="bi bi-person-circle"></i> <span>Profil</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link rounded-xl">
            <i class="bi bi-file-earmark-text"></i> <span>Arsip Soal Siswa</span>
        </a>
    </div>
@endsection

@section('content')
@php
    $isUtsUas = in_array(strtoupper($ujian->jenis_ujian ?? ''), ['UTS', 'UAS']);
    $jenisUjian = strtoupper($ujian->jenis_ujian ?? 'UAS');
    $tagColor = 'bg-rose-50 text-rose-700 border-rose-200';
    if (str_contains($jenisUjian, 'KUIS')) $tagColor = 'bg-violet-50 text-violet-700 border-violet-200';
    elseif (str_contains($jenisUjian, 'UTS')) $tagColor = 'bg-amber-50 text-amber-700 border-amber-200';
@endphp

<div class="max-w-7xl mx-auto md:p-4 space-y-6">

    {{-- 1. HERO APP BAR WITH SCHOOL PHOTO & BLUE GRADIENT OVERLAY --}}
    <div class="rounded-b-[2.5rem] md:rounded-3xl px-6 sm:px-8 pt-8 pb-16 md:pb-14 text-white shadow-[0_15px_35px_-5px_rgba(0,65,90,0.3)] relative overflow-hidden">
        {{-- Background School Image --}}
        <img src="{{ asset('image/foto_sekolah2.jpeg') }}" alt="SMPN 4 Tilatang Kamang" class="absolute inset-0 w-full h-full object-cover object-center transform scale-105 pointer-events-none">

        {{-- Gradient Overlay: Light Sky Blue at Top to Deep Solid Blue at Bottom --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#0284c7]/75 via-[#00415a]/90 to-[#002133] pointer-events-none"></div>

        {{-- Subtle Ambient Glows --}}
        <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/4 top-0 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Back Button --}}
            <a href="{{ route('siswa.nilai') }}" 
               class="inline-flex items-center gap-2 text-xs !text-white font-[Poppins-Bold] mb-3 hover:bg-white/25 active:scale-95 transition-all bg-white/15 px-4 py-2 rounded-xl border border-white/20 shadow-2xs backdrop-blur-xl">
                <i class="bi bi-arrow-left text-sm"></i>
                <span>Kembali ke Riwayat Ujian</span>
            </a>

            {{-- Exam Title & Subject --}}
            <h1 class="text-2xl sm:text-3xl font-[Poppins-Bold] !text-white tracking-tight leading-tight drop-shadow-md">
                {{ $ujian->nama_ujian }}
            </h1>

            <div class="flex flex-wrap items-center gap-2.5 mt-2.5 text-xs">
                <span class="px-3 py-1 rounded-lg bg-sky-400/30 !text-white font-bold text-xs uppercase border border-white/30 backdrop-blur-xs shadow-2xs">
                    {{ $ujian->mapel->nama_mapel ?? 'Mapel Umum' }}
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-white/15 !text-white font-bold text-xs uppercase border border-white/20">
                    {{ $ujian->jenis_ujian }}
                </span>
                <span class="text-sky-100 font-medium drop-shadow-xs flex items-center gap-1">
                    <i class="bi bi-calendar4"></i>
                    <span>{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->isoFormat('D MMMM Y') }}</span>
                </span>
                @if($ujian->is_susulan)
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-400 text-slate-950 shadow-xs">Susulan</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. FLOATING SUMMARY & SCORE CARD --}}
    <div class="px-4 sm:px-6 md:px-0 -mt-10 md:-mt-8 relative z-20">
        <div class="bg-white/95 backdrop-blur-md rounded-3xl p-5 sm:p-6 card-glow border border-white ring-1 ring-slate-900/[0.04]">
            
            @if(!$isUtsUas)
                @php
                    $score = round($hasilUjian->nilai);
                    $isPass = ($score >= 75);
                @endphp

                <div class="flex flex-col md:flex-row items-center justify-between gap-5">
                    {{-- Score & Status --}}
                    <div class="flex items-center gap-4.5 w-full md:w-auto">
                        <div class="w-20 h-20 sm:w-22 sm:h-22 rounded-2xl bg-gradient-to-tr {{ $isPass ? 'from-emerald-400 via-teal-500 to-emerald-600 shadow-emerald-500/25' : 'from-rose-500 via-pink-500 to-rose-600 shadow-rose-500/25' }} text-white flex flex-col items-center justify-center shadow-lg shrink-0">
                            <span class="text-3xl sm:text-4xl font-[Poppins-Bold] leading-none">{{ $score }}</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-white/90 mt-1">Nilai</span>
                        </div>
                        <div>
                            <span class="text-xs font-[Poppins-Bold] !text-slate-400 uppercase tracking-wider block">Hasil Evaluasi</span>
                            <h3 class="text-base sm:text-lg font-[Poppins-Bold] {{ $isPass ? '!text-emerald-600' : '!text-rose-600' }} mt-0.5 leading-snug">
                                {{ $score >= 85 ? 'Sangat Baik (A) 🏆' : ($score >= 75 ? 'Tuntas / Baik (B) 👍' : 'Perlu Belajar Lagi (C) 📚') }}
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ $isPass ? 'Selamat, kamu telah melampaui kriteria ketuntasan.' : 'Tingkatkan pemahaman materi pada pembahasan soal di bawah.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Timings --}}
                    <div class="flex items-center gap-3 text-xs w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                        <div class="flex items-center gap-2 bg-slate-50 p-2.5 sm:p-3 rounded-2xl border border-slate-100">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs">
                                <i class="bi bi-play-fill text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Mulai</p>
                                <p class="font-bold !text-slate-800 font-mono">{{ \Carbon\Carbon::parse($hasilUjian->waktu_mulai)->format('H:i') }} WIB</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-50 p-2.5 sm:p-3 rounded-2xl border border-slate-100">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-2xs">
                                <i class="bi bi-stop-fill text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Selesai</p>
                                <p class="font-bold !text-slate-800 font-mono">{{ \Carbon\Carbon::parse($hasilUjian->waktu_selesai)->format('H:i') }} WIB</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3 Mini Stats Cards (1 Row Responsive Grid) --}}
                <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-5 pt-5 border-t border-slate-100">
                    {{-- Jawaban Benar --}}
                    <div class="bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 border border-emerald-100 rounded-2xl p-3 sm:p-4 text-center shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-sm mx-auto mb-1.5 shadow-sm shadow-emerald-500/20">
                            <i class="bi bi-check-lg font-bold"></i>
                        </div>
                        <div class="text-xl sm:text-2xl font-[Poppins-Bold] !text-emerald-900">{{ $jumlahBenar }}</div>
                        <div class="text-[10px] sm:text-xs font-bold text-emerald-600 uppercase tracking-tight">Benar</div>
                    </div>

                    {{-- Jawaban Salah --}}
                    <div class="bg-gradient-to-br from-rose-50/80 via-white to-pink-50/50 border border-rose-100 rounded-2xl p-3 sm:p-4 text-center shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center text-sm mx-auto mb-1.5 shadow-sm shadow-rose-500/20">
                            <i class="bi bi-x-lg font-bold"></i>
                        </div>
                        <div class="text-xl sm:text-2xl font-[Poppins-Bold] !text-rose-900">{{ $jumlahSalah }}</div>
                        <div class="text-[10px] sm:text-xs font-bold text-rose-600 uppercase tracking-tight">Salah</div>
                    </div>

                    {{-- Total Soal --}}
                    <div class="bg-gradient-to-br from-sky-50/80 via-white to-blue-50/50 border border-sky-100 rounded-2xl p-3 sm:p-4 text-center shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-sky-500 text-white flex items-center justify-center text-sm mx-auto mb-1.5 shadow-sm shadow-sky-500/20">
                            <i class="bi bi-list-task font-bold"></i>
                        </div>
                        <div class="text-xl sm:text-2xl font-[Poppins-Bold] !text-sky-900">{{ count($daftarSoal) }}</div>
                        <div class="text-[10px] sm:text-xs font-bold text-sky-600 uppercase tracking-tight">Total Soal</div>
                    </div>
                </div>

            @else
                {{-- UTS/UAS: Skor Rahasia --}}
                <div class="flex items-center gap-4 p-2">
                    <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl shrink-0 border border-sky-100 shadow-2xs">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h3 class="font-[Poppins-Bold] !text-slate-800 text-base">Nilai {{ strtoupper($ujian->jenis_ujian) }} Dirahasiakan</h3>
                        <p class="text-slate-500 text-xs mt-0.5">
                            Rincian penilaian ujian {{ strtoupper($ujian->jenis_ujian) }} direkap langsung oleh guru pengampu. Total soal: {{ count($daftarSoal) }}.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- 3. ANALISIS JAWABAN (REVIEW SOAL) --}}
    @if(!$isUtsUas)
        <div class="px-4 sm:px-6 md:px-0">
            <div class="bg-white rounded-3xl card-glow border border-slate-100/90 overflow-hidden mb-8">
                
                {{-- Header Bar --}}
                <div style="background: linear-gradient(135deg, #00415a 0%, #005a7d 100%);" class="text-white px-5 py-4 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-lg !text-white border border-white/20 shadow-2xs">
                            <i class="bi bi-card-checklist"></i>
                        </div>
                        <div>
                            <span class="font-[Poppins-Bold] !text-white text-sm sm:text-base uppercase tracking-wider block">
                                Analisis Pembahasan Soal
                            </span>
                            <p class="text-[11px] text-sky-100 font-medium hidden sm:block">
                                Pelajari kunci jawaban dan evaluasi pengerjaanmu di bawah ini
                            </p>
                        </div>
                    </div>

                    <span class="px-3.5 py-1 rounded-xl text-xs font-[Poppins-Bold] bg-white/15 !text-white border border-white/25 uppercase tracking-wider">
                        Mode Review
                    </span>
                </div>

                {{-- List of Question Cards --}}
                <div class="p-4 sm:p-6 space-y-4">
                    @foreach($daftarSoal as $index => $soal)
                        @php
                            $isCorrect = $soal->status_jawaban;
                        @endphp
                        <div class="bg-gradient-to-br {{ $isCorrect ? 'from-emerald-50/20 via-white to-slate-50/40 border-slate-200/80' : 'from-rose-50/20 via-white to-slate-50/40 border-rose-200/80' }} rounded-2xl p-4 sm:p-5 border card-glow relative overflow-hidden">
                            {{-- Accent Strip --}}
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $isCorrect ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>

                            {{-- Top Header Row: Nomor & Badges --}}
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-xl flex items-center justify-center font-[Poppins-Bold] text-xs {{ $isCorrect ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $isCorrect ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                        {{ $isCorrect ? 'Benar ✓' : 'Salah ✗' }}
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ str_replace('_', ' ', $soal->tipe) }}
                                </span>
                            </div>

                            {{-- Question Text --}}
                            <div class="!text-slate-800 font-medium text-xs sm:text-sm leading-relaxed mb-3 pl-2">
                                {!! format_soal($soal->pertanyaan) !!}
                            </div>

                            @if($soal->gambar)
                                <div class="mb-3 pl-2">
                                    <img src="{{ asset('storage/' . $soal->gambar) }}" class="max-h-48 rounded-xl border border-slate-200 shadow-2xs">
                                </div>
                            @endif

                            {{-- Options Evaluation --}}
                            {{-- 1. Pilihan Ganda --}}
                            @if($soal->tipe == 'pilihan_ganda')
                                <div class="space-y-2 mt-3 pl-2">
                                    @foreach(['A', 'B', 'C', 'D'] as $opt)
                                        @php
                                            $isKey = (strtoupper($soal->kunci_jawaban) == $opt);
                                            $isSelected = (strtoupper($soal->jawaban_siswa) == $opt);
                                            $optionText = $soal->{'opsi_'.strtolower($opt)};
                                            
                                            $styleClass = 'bg-slate-50/80 border-slate-200/80 text-slate-700';
                                            $icon = '';

                                            if($isKey) {
                                                $styleClass = 'bg-emerald-50/90 border-emerald-500 text-emerald-900 font-bold shadow-2xs';
                                                $icon = '<span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md"><i class="bi bi-check-circle-fill"></i> Kunci Jawaban</span>';
                                            } elseif($isSelected) { 
                                                $styleClass = 'bg-rose-50/90 border-rose-400 text-rose-900 font-bold shadow-2xs'; 
                                                $icon = '<span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-md"><i class="bi bi-x-circle-fill"></i> Pilihan Kamu</span>';
                                            }
                                        @endphp
                                        
                                        <div class="flex items-center p-3 rounded-xl border {{ $styleClass }} text-xs transition-all">
                                            <div class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-bold text-[11px] mr-3 shadow-2xs shrink-0 {{ $isKey ? 'text-emerald-700' : ($isSelected ? 'text-rose-700' : 'text-slate-600') }}">
                                                {{ $opt }}
                                            </div>
                                            <div class="flex-1 font-medium">
                                                @if($soal->{'gambar_'.strtolower($opt)})
                                                    <div class="mb-1">
                                                        <img src="{{ asset('storage/' . $soal->{'gambar_'.strtolower($opt)}) }}" class="max-h-24 rounded border border-slate-200">
                                                    </div>
                                                @endif
                                                {!! format_soal($optionText) !!}
                                            </div>
                                            <div class="ml-2 shrink-0">{!! $icon !!}</div>
                                        </div>
                                    @endforeach
                                </div>

                            {{-- 2. Benar / Salah --}}
                            @elseif($soal->tipe == 'benar_salah')
                                @if(isset($soal->data_soal['pernyataan']) && is_array($soal->data_soal['pernyataan']))
                                    <div class="mt-3 space-y-2 pl-2">
                                        @php
                                            $userAnswers = json_decode($soal->jawaban_siswa, true);
                                            if(!is_array($userAnswers)) $userAnswers = [];
                                        @endphp
                                        @foreach($soal->data_soal['pernyataan'] as $idx => $item)
                                            @php
                                                $uAns = $userAnswers[$idx] ?? '-';
                                                $kAns = $item['correct'] ?? '';
                                                $isMatch = ($uAns == $kAns);
                                                $uLabel = ($uAns == 'TRUE') ? 'BENAR' : (($uAns == 'FALSE') ? 'SALAH' : '-');
                                                $kLabel = ($kAns == 'TRUE') ? 'BENAR' : 'SALAH';
                                            @endphp
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 text-xs">
                                                <div class="flex-1 font-medium !text-slate-800">{!! format_soal($item['text'] ?? '') !!}</div>
                                                <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-auto">
                                                    <span class="px-2.5 py-1 rounded-lg font-bold {{ $isMatch ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                        {{ $uLabel }} @if($isMatch) ✓ @else ✗ @endif
                                                    </span>
                                                    @if(!$isMatch)
                                                        <span class="text-slate-400">→</span>
                                                        <span class="px-2.5 py-1 rounded-lg font-bold bg-sky-100 text-sky-800">
                                                            Kunci: {{ $kLabel }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex gap-3 mt-3 pl-2">
                                        @foreach(['A' => 'BENAR', 'B' => 'SALAH'] as $val => $label)
                                            @php
                                                $isKey = ($soal->kunci_jawaban == ($val == 'A' ? 'TRUE' : 'FALSE') || $soal->kunci_jawaban == $val);
                                                $isSelected = ($soal->jawaban_siswa == $val);
                                            @endphp
                                            <div class="flex-1 p-3 rounded-xl border flex items-center justify-between text-xs
                                                {{ $isSelected 
                                                    ? ($isKey ? 'bg-emerald-50 border-emerald-500 text-emerald-800 font-bold' : 'bg-rose-50 border-rose-500 text-rose-800 font-bold')
                                                    : ($isKey ? 'bg-emerald-50 border-emerald-500 text-emerald-800 font-medium' : 'bg-slate-50 border-slate-200 text-slate-600') }}">
                                                <span>{{ $label }}</span>
                                                @if($isKey) <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                                @elseif($isSelected) <i class="bi bi-x-circle-fill text-rose-600"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                            {{-- 3. Jawaban Ganda --}}
                            @elseif($soal->tipe == 'jawaban_ganda')
                                <div class="space-y-2 mt-3 pl-2">
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
                                    @endphp
                                    @foreach($opsiDinamic as $opt)
                                        @php
                                            $optId = $opt['id'];
                                            $keys = array_map(fn($v) => trim(strtoupper($v)), explode(',', $soal->kunci_jawaban));
                                            $answers = array_map(fn($v) => trim(strtoupper($v)), explode(',', $soal->jawaban_siswa ?? ''));
                                            
                                            $isKey = in_array($optId, $keys);
                                            $isSelected = in_array($optId, $answers);

                                            $styleClass = 'bg-slate-50/80 border-slate-200/80 text-slate-700';
                                            $icon = '';

                                            if($isKey && $isSelected) {
                                                $styleClass = 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold';
                                                $icon = '<span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded"><i class="bi bi-check-circle-fill"></i> Kunci (Dipilih)</span>';
                                            } elseif($isKey && !$isSelected) {
                                                $styleClass = 'bg-sky-50 border-sky-300 text-sky-800 font-bold';
                                                $icon = '<span class="text-[10px] font-bold text-sky-700 bg-sky-100 px-2 py-0.5 rounded">Kunci Jawaban</span>';
                                            } elseif(!$isKey && $isSelected) {
                                                $styleClass = 'bg-rose-50 border-rose-400 text-rose-900 font-bold';
                                                $icon = '<span class="text-[10px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded"><i class="bi bi-x-circle-fill"></i> Pilihan Kamu</span>';
                                            }
                                        @endphp
                                        <div class="flex items-center p-3 rounded-xl border {{ $styleClass }} text-xs font-medium">
                                            <div class="w-6 h-6 rounded-lg border bg-white flex items-center justify-center font-bold text-[11px] mr-2.5 shrink-0 shadow-2xs">
                                                {{ $optId }}
                                            </div>
                                            <div class="flex-1">{!! format_soal($opt['text']) !!}</div>
                                            <div class="ml-2 shrink-0">{!! $icon !!}</div>
                                        </div>
                                    @endforeach
                                </div>

                            {{-- 4. Menjodohkan --}}
                            @elseif($soal->tipe == 'menjodohkan')
                                <div class="mt-3 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80 space-y-2.5 text-xs pl-2">
                                    @php
                                        $matches = $soal->data_soal['matches'] ?? [];
                                        $userPairs = is_array($soal->jawaban_siswa) ? $soal->jawaban_siswa : (json_decode($soal->jawaban_siswa, true) ?? []);
                                    @endphp
                                    @foreach($matches as $k => $match)
                                        @php
                                            $userRightId = $userPairs['L'.$k] ?? null;
                                            $correctRightId = 'R'.$k;
                                            $leftText = $match['left'] ?? $match['pertanyaan'] ?? '-';
                                            $userAnswerText = "(Tidak Dijawab)";
                                            $pairIsCorrect = false;

                                            if ($userRightId) {
                                                $rIndex = (int) str_replace('R', '', $userRightId);
                                                $userAnswerText = $matches[$rIndex]['right'] ?? $matches[$rIndex]['jawaban'] ?? 'Unknown';
                                                $pairIsCorrect = ($userRightId === $correctRightId);
                                            }
                                            $correctAnswerText = $match['right'] ?? $match['jawaban'] ?? '-';
                                        @endphp
                                        <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs flex flex-col gap-1.5">
                                            <div class="font-bold !text-slate-800">{!! format_soal($leftText) !!}</div>
                                            <div class="flex flex-wrap items-center gap-2 text-[11px] pt-1 border-t border-slate-100">
                                                <span class="px-2.5 py-0.5 rounded-md font-bold {{ $pairIsCorrect ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                                    Jawabanmu: {{ $userAnswerText }} @if($pairIsCorrect) ✓ @else ✗ @endif
                                                </span>
                                                @if(!$pairIsCorrect)
                                                    <span class="text-slate-400">→</span>
                                                    <span class="px-2.5 py-0.5 rounded-md font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                                        Kunci: {{ $correctAnswerText }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    @endif

</div>
@endsection
