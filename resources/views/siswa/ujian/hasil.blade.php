@extends('layouts.app')

@section('title', 'Hasil Ujian')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link active rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
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
    $score = round($hasilUjian->nilai);
    $isUtsUas = in_array(strtoupper($ujian->jenis_ujian ?? ''), ['UTS', 'UAS']);
    
    // Determine Theme & Assets
    if ($isUtsUas) {
        $theme = 'blue';
        $gradient = 'from-sky-500 to-indigo-600';
        $textMain = 'text-blue-600';
        $message = 'Ujian Selesai! 🎉';
        $subMessage = 'Jawaban kamu telah berhasil terkirim ke sistem.';
        $ringColor = 'text-blue-500';
        $btnColor = 'bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 shadow-blue-500/25';
    } elseif ($score >= 80) {
        $theme = 'emerald';
        $gradient = 'from-emerald-500 to-teal-600';
        $textMain = 'text-emerald-600';
        $message = 'Luar Biasa! 🏆';
        $subMessage = 'Hasil yang sangat memuaskan, pertahankan prestasimu!';
        $ringColor = 'text-emerald-500';
        $btnColor = 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 shadow-emerald-500/25';
    } elseif ($score >= 70) {
        $theme = 'amber';
        $gradient = 'from-amber-500 to-orange-500';
        $textMain = 'text-amber-600';
        $message = 'Bagus! 👍';
        $subMessage = 'Usaha yang bagus, terus tingkatkan belajarmu!';
        $ringColor = 'text-amber-500';
        $btnColor = 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-amber-500/25';
    } else {
        $theme = 'rose';
        $gradient = 'from-rose-500 to-pink-600';
        $textMain = 'text-rose-600';
        $message = 'Jangan Menyerah! 💪';
        $subMessage = 'Jadikan ini evaluasi dan ayo belajar lebih giat lagi!';
        $ringColor = 'text-rose-500';
        $btnColor = 'bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 shadow-rose-500/25';
    }
@endphp

<div class="max-w-md mx-auto px-4 py-3 sm:py-4 flex flex-col justify-center min-h-[calc(100vh-6rem)] md:min-h-[80vh]">

    {{-- 1. HEADER RINGKAS DENGAN SHADOW GLOW --}}
    <div class="text-center mb-3.5">
        {{-- Exam Badge --}}
        <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/90 backdrop-blur-md border border-slate-200/80 shadow-2xs mb-2">
            <span class="w-2 h-2 rounded-full bg-{{ $theme }}-500 animate-pulse"></span>
            <span class="text-[11px] font-[Poppins-Bold] text-slate-700 uppercase tracking-wide truncate max-w-[220px]">
                {{ $ujian->nama_ujian }}
            </span>
            @if($ujian->is_susulan)
                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-700">Susulan</span>
            @endif
        </div>
        
        <h1 class="text-2xl sm:text-3xl font-[Poppins-Bold] {{ $textMain }} leading-tight tracking-tight">
            {{ $message }}
        </h1>
        <p class="text-slate-500 text-xs sm:text-sm font-medium mt-1 leading-snug">
            {{ $subMessage }}
        </p>
    </div>

    {{-- 2. UNIFIED COMPACT CARD DENGAN CARD GLOW --}}
    <div class="bg-white/95 backdrop-blur-md rounded-3xl p-5 card-glow border border-white ring-1 ring-slate-900/[0.04] flex flex-col items-center gap-4">
        
        @if($isUtsUas)
            {{-- UTS/UAS View --}}
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 text-3xl shadow-inner mt-1">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            
            <div class="text-center">
                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Ujian {{ strtoupper($ujian->jenis_ujian) }}</p>
                <h2 class="font-[Poppins-Bold] text-lg text-slate-800 mt-0.5">{{ $ujian->mapel->nama_mapel ?? 'Mata Pelajaran' }}</h2>
                <p class="text-xs text-slate-400 mt-1 max-w-xs leading-tight">
                    Nilai dan evaluasi akan direkap dan diumumkan langsung oleh bapak/ibu guru.
                </p>
            </div>

            <div class="flex items-center justify-between px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 w-full text-xs">
                <span class="font-medium text-slate-500">Jumlah Soal</span>
                <span class="font-[Poppins-Bold] text-slate-800">{{ $totalSoal }} Butir</span>
            </div>
        @else
            {{-- Regular Exam View with Modern SVG Ring --}}
            <div class="relative w-32 h-32 sm:w-36 sm:h-36 shrink-0 mt-1">
                <svg class="w-full h-full transform -rotate-90 drop-shadow-sm">
                    <circle cx="50%" cy="50%" r="42%" stroke="#f1f5f9" stroke-width="10" fill="transparent" />
                    @php
                        $circumference = 2 * 3.14159 * 58; 
                        $dashArray = ($score / 100) * $circumference;
                    @endphp
                    <circle cx="50%" cy="50%" r="58" stroke="currentColor" stroke-width="10" fill="transparent" 
                        class="{{ $ringColor }} transition-all duration-[1200ms] ease-out" 
                        stroke-linecap="round"
                        stroke-dasharray="{{ $dashArray }} {{ $circumference }}" 
                        stroke-dashoffset="0" />
                </svg>
                
                {{-- Score Text In Center --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Nilai Akhir</span>
                    <span class="text-4xl sm:text-5xl font-[Poppins-Bold] text-slate-800 tracking-tight leading-none mt-0.5">{{ $score }}</span>
                </div>
            </div>

            {{-- 2 Mini Stats Grid (Side-by-Side) --}}
            <div class="grid grid-cols-2 gap-2.5 w-full">
                {{-- Jawaban Benar --}}
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-2.5 flex items-center justify-between gap-2 shadow-2xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                            <i class="bi bi-check-lg font-bold"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-[10px] font-bold text-emerald-900 uppercase leading-none">Benar</span>
                            <span class="text-[9px] text-emerald-600 truncate block mt-0.5">Tepat</span>
                        </div>
                    </div>
                    <span class="text-base sm:text-lg font-[Poppins-Bold] text-emerald-700 shrink-0">{{ $hasilUjian->jumlah_benar }}</span>
                </div>

                {{-- Jawaban Salah --}}
                <div class="bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-100 rounded-2xl p-2.5 flex items-center justify-between gap-2 shadow-2xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs shrink-0 shadow-xs">
                            <i class="bi bi-x-lg font-bold"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-[10px] font-bold text-rose-900 uppercase leading-none">Salah</span>
                            <span class="text-[9px] text-rose-600 truncate block mt-0.5">Keliru</span>
                        </div>
                    </div>
                    <span class="text-base sm:text-lg font-[Poppins-Bold] text-rose-700 shrink-0">{{ $jumlahSalah }}</span>
                </div>
            </div>

            {{-- Total Soal Pill --}}
            <div class="flex items-center justify-between px-3.5 py-1.5 bg-slate-50/80 rounded-xl border border-slate-100 w-full text-[11px] text-slate-500 font-medium">
                <span>Total Soal:</span>
                <span class="font-[Poppins-Bold] text-slate-700">{{ $totalSoal }} Butir</span>
            </div>
        @endif

        {{-- 3. ACTION BUTTONS --}}
        <div class="w-full space-y-2 pt-2 border-t border-slate-100">
            @if(!$isUtsUas)
            @php
                $btnStyle = 'background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff !important;';
                if ($theme === 'blue') $btnStyle = 'background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); color: #ffffff !important;';
                elseif ($theme === 'amber') $btnStyle = 'background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: #ffffff !important;';
                elseif ($theme === 'rose') $btnStyle = 'background: linear-gradient(135deg, #e11d48 0%, #db2777 100%); color: #ffffff !important;';
            @endphp
            <a href="{{ route('siswa.ujian.detail', $ujian->id) }}" 
               style="{{ $btnStyle }}"
               class="w-full py-3 px-4 rounded-2xl !text-white font-[Poppins-Bold] text-xs uppercase tracking-wider transition-all shadow-md active:scale-98 flex items-center justify-center gap-2 cursor-pointer">
                <i class="bi bi-journal-check text-sm !text-white"></i>
                <span class="!text-white">Lihat Pembahasan Soal</span>
            </a>
            @endif

            <a href="{{ route('siswa.dashboard') }}" 
               class="w-full py-2.5 px-4 rounded-2xl bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-[Poppins-Bold] text-xs uppercase tracking-wider transition-all active:scale-98 flex items-center justify-center gap-2 shadow-2xs cursor-pointer">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

    </div>

</div>
@endsection
