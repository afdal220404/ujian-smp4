@extends('layouts.app')

@section('title', 'Hasil Ujian')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link active rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
            <i class="bi bi-award"></i> <span>Nilai</span>
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
        // Tema netral untuk UTS/UAS (nilai tidak ditampilkan)
        $theme = 'blue';
        $gradient = 'from-blue-500 to-indigo-600';
        $bgLight = 'bg-blue-50';
        $border = 'border-blue-100';
        $textMain = 'text-blue-600';
        $icon = 'bi-journal-check';
        $message = 'Ujian Selesai!';
        $subMessage = 'Terima kasih, jawaban kamu sudah berhasil dikirim. Tetap semangat!';
        $ringColor = 'text-blue-500';
        $btnColor = 'bg-blue-600 hover:bg-blue-700 shadow-blue-200';
    } elseif ($score > 80) {
        $theme = 'emerald';
        $gradient = 'from-emerald-500 to-teal-600';
        $bgLight = 'bg-emerald-50';
        $border = 'border-emerald-100';
        $textMain = 'text-emerald-600';
        $icon = 'bi-trophy-fill';
        $message = 'Luar Biasa!';
        $subMessage = 'Hasil yang sangat memuaskan, pertahankan prestasimu!';
        $ringColor = 'text-emerald-500';
        $btnColor = 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200';
    } elseif ($score >= 70) {
        $theme = 'amber';
        $gradient = 'from-amber-400 to-orange-500';
        $bgLight = 'bg-amber-50';
        $border = 'border-amber-100';
        $textMain = 'text-amber-600';
        $icon = 'bi-emoji-smile-fill';
        $message = 'Bagus!';
        $subMessage = 'Usaha yang bagus, tingkatkan lagi belajarmu!';
        $ringColor = 'text-amber-500';
        $btnColor = 'bg-amber-500 hover:bg-amber-600 shadow-amber-200';
    } else {
        $theme = 'rose';
        $gradient = 'from-rose-500 to-pink-600';
        $bgLight = 'bg-rose-50';
        $border = 'border-rose-100';
        $textMain = 'text-rose-600';
        $icon = 'bi-emoji-frown-fill';
        $message = 'Jangan Menyerah!';
        $subMessage = 'Jadikan ini pelajaran, ayo belajar lebih giat lagi!';
        $ringColor = 'text-rose-500';
        $btnColor = 'bg-rose-500 hover:bg-rose-600 shadow-rose-200';
    }
@endphp

<div class="min-h-[85vh] flex flex-col justify-center items-center relative px-4 py-12">

    {{-- Decorative Background --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10 bg-gray-50/50">
        <div class="absolute top-0 left-0 w-full h-[40vh] bg-gradient-to-b {{ $gradient }} opacity-10 blur-3xl rounded-b-[50%]"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-blue-100 rounded-full blur-3xl opacity-40"></div>
    </div>

    {{-- 1. TITLE & MESSAGE SECTION (Top) --}}
    <div class="text-center mb-8 relative z-10 animate-fade-in-down">
        {{-- Exam Name Badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-gray-200 shadow-sm mb-4 mx-auto">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                <i class="bi bi-journal-text text-{{ $theme }}-500"></i>
                {{ $ujian->nama_ujian }}
            </span>
            @if($ujian->is_susulan)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 uppercase">Susulan</span>
            @endif
        </div>
        
        <h1 class="text-4xl md:text-5xl font-[Poppins-Bold] {{ $textMain }} mb-3 drop-shadow-sm tracking-tight text-center">
            {{ $message }}
        </h1>
        <p class="text-gray-600 text-base md:text-lg font-medium max-w-md mx-auto leading-relaxed">
            {{ $subMessage }}
        </p>
    </div>

    {{-- 2. MAIN RESULT CARD --}}
    @if($isUtsUas)
        {{-- UTS/UAS: Halaman Terima Kasih — latar netral, TANPA nilai & benar/salah --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/60 border border-indigo-100 w-full max-w-2xl relative overflow-hidden mb-8">
            {{-- Top accent bar --}}
            <div class="h-2 w-full bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-400 rounded-t-3xl"></div>

            <div class="p-10 flex flex-col items-center gap-5 text-center">
                {{-- Icon --}}
                <div class="w-24 h-24 rounded-full bg-indigo-50 border-4 border-indigo-100 flex items-center justify-center text-indigo-500 shadow-inner">
                    <i class="bi bi-patch-check-fill text-5xl"></i>
                </div>

                {{-- Teks Terima Kasih --}}
                <div>
                    <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Ujian {{ strtoupper($ujian->jenis_ujian) }}</p>
                    <h2 class="font-[Poppins-Bold] text-2xl text-indigo-700 mb-1">Terima Kasih!</h2>
                    <p class="font-[Poppins-Bold] text-gray-700 text-base">{{ $ujian->nama_ujian }}</p>
                    <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto leading-relaxed">
                        Jawaban kamu sudah berhasil dikirim. <br>
                        Tetap semangat dan terus belajar! 🎉
                    </p>
                </div>

                {{-- Total Soal --}}
                <div class="flex items-center justify-between px-6 py-3 bg-indigo-50 rounded-2xl border border-indigo-100 w-full max-w-xs">
                    <span class="text-sm font-bold text-indigo-500">Total Soal</span>
                    <span class="text-sm font-[Poppins-Bold] text-indigo-800">{{ $totalSoal }} Butir</span>
                </div>

                <p class="text-xs text-gray-400 italic max-w-xs">Nilai akan diumumkan oleh guru setelah proses penilaian selesai.</p>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 w-full max-w-4xl relative overflow-hidden mb-8 grid grid-cols-1 md:grid-cols-2">
            
            {{-- Left: Score --}}
            <div class="p-8 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-100 bg-gradient-to-b from-white to-gray-50/50 relative">
                 <div class="relative w-48 h-48">
                     {{-- SVG Circle --}}
                     <svg class="w-full h-full transform -rotate-90 relative z-10 drop-shadow-md">
                        <circle cx="96" cy="96" r="80" stroke="#e2e8f0" stroke-width="12" fill="transparent" />
                        @php
                            $circumference = 2 * 3.14159 * 80;
                            $dashArray = $score / 100 * $circumference;
                        @endphp
                        <circle cx="96" cy="96" r="80" stroke="currentColor" stroke-width="12" fill="transparent" 
                            class="{{ $ringColor }} transition-all duration-[1500ms] ease-out" 
                            stroke-linecap="round"
                            stroke-dasharray="{{ $dashArray }} {{ $circumference }}" 
                            stroke-dashoffset="0" />
                    </svg>
                    
                    {{-- Score Text --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center z-20">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">NILAI AKHIR</span>
                        <span class="text-6xl font-[Poppins-Bold] text-gray-800 tracking-tighter">{{ $score }}</span>
                    </div>
                </div>
            </div>

            {{-- Right: Stats --}}
            <div class="p-8 flex flex-col justify-center gap-6 bg-white">
                <h3 class="font-[Poppins-Bold] text-gray-700 text-lg mb-2">Rincian Jawaban</h3>
                
                {{-- Benar --}}
                <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="bi bi-check-lg text-xl"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase">Jawaban Benar</span>
                            <span class="block font-bold text-gray-700">Soal Terjawab Tepat</span>
                        </div>
                    </div>
                    <span class="text-2xl font-[Poppins-Bold] text-emerald-600">{{ $hasilUjian->jumlah_benar }}</span>
                </div>

                {{-- Salah --}}
                <div class="flex items-center justify-between p-4 rounded-xl bg-rose-50 border border-rose-100">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                            <i class="bi bi-x-lg text-xl"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase">Jawaban Salah</span>
                            <span class="block font-bold text-gray-700">Perlu Perbaikan</span>
                        </div>
                    </div>
                    <span class="text-2xl font-[Poppins-Bold] text-rose-600">{{ $jumlahSalah }}</span>
                </div>
                
                {{-- Total --}}
                <div class="flex items-center justify-between px-4 pt-2">
                    <span class="text-sm font-bold text-gray-500">Total Soal </span>
                    <span class="text-sm font-[Poppins-Bold] text-gray-800">{{ $totalSoal }} Butir Soal</span>
                </div>
            </div>
        </div>
    @endif

    {{-- 3. ACTION BUTTONS --}}
    <div class="w-full max-w-4xl flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('siswa.dashboard') }}" 
           class="px-8 py-3.5 rounded-xl border-2 border-slate-200 text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-300 font-bold text-sm transition-all shadow-sm flex items-center justify-center gap-2 min-w-[180px]">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>

        @if(!$isUtsUas)
        <a href="{{ route('siswa.ujian.detail', $ujian->id) }}" 
           class="px-8 py-3.5 rounded-xl {{ $btnColor }} text-white font-bold text-sm transition-all shadow-lg shadow-gray-200 hover:-translate-y-1 flex items-center justify-center gap-2 min-w-[200px]">
            <i class="bi bi-journal-check"></i> Lihat Pembahasan Soal
        </a>
        @endif
    </div>

</div>
@endsection
