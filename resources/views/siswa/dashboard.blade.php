@extends('layouts.app')

@section('title', 'Dashboard Siswa')

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
    $hour = date('H');
    $sapaanWaktu = 'Selamat Pagi ☀️';
    if ($hour >= 11 && $hour < 15) $sapaanWaktu = 'Selamat Siang 🌤️';
    elseif ($hour >= 15 && $hour < 18) $sapaanWaktu = 'Selamat Sore 🌇';
    elseif ($hour >= 18 || $hour < 5) $sapaanWaktu = 'Selamat Malam 🌙';
@endphp

<div class="max-w-7xl mx-auto md:p-4 space-y-6">

    {{-- 1. LUXURIOUS HERO APP BAR WITH SCHOOL PHOTO & BLUE GRADIENT OVERLAY --}}
    <div class="rounded-b-[2.5rem] md:rounded-3xl px-6 sm:px-8 pt-8 pb-16 md:pb-14 text-white flex items-center justify-between shadow-[0_15px_35px_-5px_rgba(0,65,90,0.3)] relative overflow-hidden">
        {{-- Background School Image --}}
        <img src="{{ asset('image/foto_sekolah2.jpeg') }}" alt="SMPN 4 Tilatang Kamang" class="absolute inset-0 w-full h-full object-cover object-center transform scale-105 pointer-events-none">

        {{-- Gradient Overlay: Light Sky Blue at Top to Deep Solid Blue at Bottom --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#0284c7]/75 via-[#00415a]/90 to-[#002133] pointer-events-none"></div>

        {{-- Subtle Ambient Glows --}}
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-10 bottom-0 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

        {{-- Left: Greeting & Full Student Name --}}
        <div class="relative z-10 max-w-[70%] sm:max-w-none">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xl text-[11px] font-semibold text-sky-100 border border-white/20 mb-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]">
                <span>{{ $sapaanWaktu }}</span>
            </div>
            
            <h1 class="text-xl sm:text-2xl md:text-3xl font-[Poppins-Bold] !text-white tracking-tight leading-tight drop-shadow-md">
                {{ $siswa->nama_lengkap }} 👋
            </h1>
            
            <div class="flex flex-wrap items-center gap-2 mt-2">
                <span style="background-color: #f59e0b; color: #0f172a !important;" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg font-[Poppins-Bold] text-xs uppercase shadow-sm tracking-wide">
                    <i class="bi bi-mortarboard-fill text-slate-950"></i> 
                    <span style="color: #0f172a !important; font-weight: 700;">Kelas {{ $siswa->kelas->kelas ?? '-' }}</span>
                </span>
                <span class="text-xs text-sky-100/90 font-medium drop-shadow-xs">SMPN 4 Tilatang Kamang</span>
            </div>
        </div>

        {{-- Right: Profile Button (Khusus Mobile, di PC disembunyikan karena sudah ada di sidebar) --}}
        <div class="relative z-10 shrink-0 md:hidden">
            <a href="{{ route('siswa.profil') }}" 
               class="relative group block w-12 h-12 rounded-full p-0.5 bg-gradient-to-tr from-sky-300 via-white to-blue-400 shadow-[0_4px_20px_rgba(0,0,0,0.25)] hover:scale-105 active:scale-95 transition-all duration-300" 
               title="Lihat Profil">
                <div class="w-full h-full rounded-full bg-[#00415a] flex items-center justify-center text-xl text-white group-hover:bg-[#005a7d] transition-colors">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-400 border-2 border-white rounded-full shadow-xs"></div>
            </a>
        </div>
    </div>

    {{-- 2. BAGIAN ATAS: ARSIP SOAL SISWA & STATUS AKUN SISWA --}}
    <div class="px-4 sm:px-6 md:px-0 -mt-10 md:-mt-8 relative z-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            
            {{-- CARD 1: ARSIP SOAL SISWA --}}
            <a href="{{ route('siswa.bank_soal') }}" 
               class="bg-white/95 backdrop-blur-md rounded-3xl p-5 card-glow border border-slate-100/80 flex items-center justify-between active:scale-98 transition-all duration-300 group hover:border-sky-300">
                <div class="flex items-center gap-4 min-w-0">
                    <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff;" class="w-12 h-12 sm:w-13 sm:h-13 rounded-2xl text-white flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform duration-300 shadow-md shadow-sky-500/20">
                        <i class="bi bi-folder-fill text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="font-[Poppins-Bold] !text-slate-800 text-base group-hover:!text-sky-600 transition-colors truncate">
                                Arsip Soal Siswa
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200 uppercase tracking-wide">
                                Materi
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs truncate">
                            Akses materi pelajaran dan bank latihan resmi
                        </p>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-50 group-hover:bg-sky-50 text-slate-400 group-hover:text-sky-600 flex items-center justify-center transition-all ml-2 shrink-0 border border-slate-100 group-hover:border-sky-200">
                    <i class="bi bi-chevron-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                </div>
            </a>

            {{-- CARD 2: STATUS AKUN SISWA --}}
            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-5 card-glow border border-white ring-1 ring-slate-900/[0.04] flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shadow-2xs border border-sky-100">
                            <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <div>
                            <span class="font-[Poppins-Bold] !text-slate-800 text-xs sm:text-sm uppercase tracking-wider block">Status Akun Siswa</span>
                            <p class="text-[11px] text-emerald-600 font-bold flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Aktif & Terverifikasi</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5 text-xs pt-2.5 border-t border-slate-100">
                    <div class="bg-slate-50/80 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-medium block">Tingkat Kelas</span>
                        <span class="font-[Poppins-Bold] !text-slate-800 text-xs sm:text-sm">Kelas {{ $siswa->kelas->kelas ?? '-' }}</span>
                    </div>
                    <div class="bg-slate-50/80 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-medium block">Nomor Induk / NISN</span>
                        <span class="font-[Poppins-Bold] !text-slate-800 text-xs font-mono truncate block">{{ $siswa->nisn ?? '-' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 3. BAGIAN ATAS: UJIAN SEDANG BERLANGSUNG & AKAN DATANG (CARD TABEL & LIVE AUTO-SYNC) --}}
    <div class="px-4 sm:px-6 md:px-0" id="live-exam-section">
        <div id="live-exam-grid" class="grid grid-cols-1 {{ $sedangBerlangsung->isNotEmpty() ? 'lg:grid-cols-2' : 'grid-cols-1' }} gap-4 md:gap-6">
            
            {{-- CARD TABEL: UJIAN SEDANG BERLANGSUNG --}}
            <div id="ongoing-card-wrapper" class="{{ $sedangBerlangsung->isNotEmpty() ? '' : 'hidden' }} bg-white rounded-3xl card-glow border border-slate-100/90 overflow-hidden flex flex-col justify-start shadow-sm transition-all duration-500">
                {{-- Card Table Header --}}
                <div style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%);" class="px-5 py-3.5 flex items-center justify-between shadow-xs text-white">
                    <div class="flex items-center gap-2.5 font-[Poppins-Bold] !text-white text-xs sm:text-sm uppercase tracking-wider">
                        <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center text-sm text-white">
                            <i class="bi bi-broadcast"></i>
                        </div>
                        <span class="!text-white">Ujian Sedang Berlangsung</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 !text-white border border-white/30 backdrop-blur-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> Live Session
                    </span>
                </div>

                {{-- Card Table Body --}}
                <div class="p-4 sm:p-5 space-y-3.5" id="ongoing-exam-list">
                    @foreach($sedangBerlangsung as $ujian)
                    <div class="bg-gradient-to-br from-emerald-50/40 via-white to-teal-50/20 rounded-2xl p-4 sm:p-5 border border-emerald-200/80 flex flex-col gap-3" data-ujian-id="{{ $ujian->id }}">
                        
                        {{-- Top Row: Subject & Exam Type Badges --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                {{ $ujian->mapel->nama_mapel ?? 'Mata Pelajaran' }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if($ujian->is_susulan)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Susulan</span>
                                @endif
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 uppercase tracking-wider shrink-0 shadow-2xs">
                                    {{ $ujian->jenis_ujian }}
                                </span>
                            </div>
                        </div>

                        {{-- Exam Title & Teacher Name --}}
                        <div>
                            <h4 class="font-[Poppins-Bold] !text-slate-800 text-base leading-snug truncate">
                                {{ $ujian->nama_ujian }}
                            </h4>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                <i class="bi bi-person text-slate-400"></i>
                                <span>{{ $ujian->mapel->guru->nama_lengkap ?? 'Guru Pengampu' }}</span>
                            </p>
                        </div>

                        {{-- Bottom Row: Timer & Action Button --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-emerald-100/80 mt-0.5">
                            {{-- Countdown Timer --}}
                            <div class="flex items-center gap-2 text-xs font-mono font-bold text-rose-700 bg-rose-50 px-3.5 py-1.5 rounded-xl border border-rose-200 shadow-2xs" 
                                 id="timer-{{ $ujian->id }}" data-end="{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('Y-m-d H:i:s') }}">
                                <i class="bi bi-stopwatch-fill animate-pulse text-sm text-rose-600"></i>
                                <span class="countdown">--:--:--</span>
                            </div>

                            {{-- Tombol Kerjakan / Lanjutkan --}}
                            @php
                                $hasilSiswa = $ujian->hasilUjians ? $ujian->hasilUjians->first() : null;
                                $isResume = $hasilSiswa && is_null($hasilSiswa->waktu_selesai) && !is_null($hasilSiswa->waktu_mulai);
                            @endphp
                            <a href="{{ route('siswa.ujian.konfirmasi', $ujian->id) }}" 
                               style="{{ $isResume ? 'background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);' : 'background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);' }} color: #ffffff !important;"
                               class="px-5 py-2.5 rounded-xl text-xs font-[Poppins-Bold] !text-white shadow-md active:scale-95 transition-all inline-flex items-center gap-2 ml-auto tracking-wide cursor-pointer">
                                <span class="!text-white">{{ $isResume ? 'LANJUTKAN' : 'MULAI UJIAN' }}</span>
                                <i class="bi {{ $isResume ? 'bi-play-circle-fill' : 'bi-arrow-right-circle-fill' }} text-sm !text-white"></i>
                            </a>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>

            {{-- CARD TABEL: JADWAL UJIAN AKAN DATANG --}}
            <div id="upcoming-card-wrapper" class="bg-white rounded-3xl card-glow border border-slate-100/90 overflow-hidden flex flex-col justify-start shadow-sm">
                {{-- Card Table Header --}}
                <div style="background: linear-gradient(135deg, #00415a 0%, #005a7d 100%);" class="px-5 py-3.5 flex items-center justify-between shadow-xs text-white">
                    <div class="flex items-center gap-2.5 font-[Poppins-Bold] !text-white text-xs sm:text-sm uppercase tracking-wider">
                        <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center text-sm text-cyan-200">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <span class="!text-white">Jadwal Ujian Akan Datang</span>
                    </div>
                    <span id="upcoming-count-badge" class="text-[10px] bg-white/15 px-2.5 py-0.5 rounded-full font-mono font-bold !text-white border border-white/20">
                        {{ $akanDatang->count() }} Jadwal
                    </span>
                </div>

                {{-- Card Table Body --}}
                <div class="p-4 sm:p-5" id="upcoming-exam-list">
                    @if($akanDatang->isEmpty())
                        <div class="p-6 text-center text-slate-400" id="upcoming-empty-placeholder">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl mx-auto mb-2">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h4 class="font-[Poppins-Bold] !text-slate-700 text-sm">Belum Ada Jadwal</h4>
                            <p class="text-slate-400 text-xs mt-0.5">Saat ini tidak ada jadwal ujian baru yang akan datang.</p>
                        </div>
                    @else
                        <div class="space-y-3.5" id="upcoming-items-container">
                            @foreach($akanDatang as $ujian)
                            <div class="bg-gradient-to-br from-amber-50/40 via-white to-slate-50/50 rounded-2xl p-4 sm:p-5 border border-slate-200/80 hover:border-amber-300 hover:shadow-sm flex flex-col gap-3 transition-all" data-ujian-id="{{ $ujian->id }}">
                                
                                {{-- Top Row: Subject & Exam Type Badges --}}
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                        {{ $ujian->mapel->nama_mapel ?? 'Mata Pelajaran' }}
                                    </span>

                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider shrink-0">
                                            {{ $ujian->jenis_ujian }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Exam Title & Teacher Name --}}
                                <div>
                                    <h4 class="font-[Poppins-Bold] !text-slate-800 text-base leading-snug truncate">
                                        {{ $ujian->nama_ujian }}
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                        <i class="bi bi-person text-slate-400"></i>
                                        <span>{{ $ujian->mapel->guru->nama_lengkap ?? 'Guru Pengampu' }}</span>
                                    </p>
                                </div>

                                {{-- Bottom Row: Schedule Time & Status Badge --}}
                                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 mt-0.5">
                                    <div class="flex items-center gap-2 text-xs font-mono font-bold text-amber-800 bg-amber-50 px-3.5 py-1.5 rounded-xl border border-amber-200/80 shadow-2xs">
                                        <i class="bi bi-clock-fill text-amber-500"></i>
                                        <span>{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</span>
                                    </div>

                                    <span class="px-3.5 py-1.5 rounded-xl text-xs font-[Poppins-Bold] bg-amber-100 text-amber-800 border border-amber-300 uppercase tracking-wider shrink-0 shadow-2xs">
                                        Terjadwal
                                    </span>
                                </div>

                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- 4. BAGIAN BAWAH: RIWAYAT UJIAN (CARD TABEL UTUH) --}}
    <div class="px-4 sm:px-6 md:px-0 mb-8">
        <div class="bg-white rounded-3xl card-glow border border-slate-100/90 overflow-hidden shadow-sm">
            
            {{-- Card Table Header --}}
            <div style="background: linear-gradient(135deg, #00415a 0%, #005a7d 100%);" class="px-5 py-4 flex items-center justify-between shadow-xs text-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg !text-white border border-white/20 shadow-2xs">
                        <i class="bi bi-clock-history !text-white"></i>
                    </div>
                    <div>
                        <span class="font-[Poppins-Bold] !text-white text-sm sm:text-base uppercase tracking-wider block">
                            Riwayat Ujian Siswa
                        </span>
                        <p class="text-[11px] text-sky-100 font-medium hidden sm:block">
                            Daftar seluruh ujian yang telah kamu selesaikan
                        </p>
                    </div>
                </div>

                <a href="{{ route('siswa.nilai') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 !text-white text-xs font-bold transition-all backdrop-blur-xs border border-white/20 active:scale-95 shadow-2xs">
                    <span class="!text-white">Lihat Rekap Nilai</span>
                    <i class="bi bi-arrow-right text-[11px] !text-white"></i>
                </a>
            </div>

            {{-- Card Table Body (Desktop: 2 Columns, Mobile: 1 Column) --}}
            <div class="p-4 sm:p-6">
                @if($telahBerlalu->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-2">
                            <i class="bi bi-journal-x"></i>
                        </div>
                        <h4 class="font-[Poppins-Bold] !text-slate-700 text-sm">Belum Ada Riwayat Ujian</h4>
                        <p class="text-slate-400 text-xs mt-0.5">Nilai ujian yang telah kamu selesaikan akan otomatis tercatat di sini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($telahBerlalu as $hasil)
                        @php 
                            $jenisUjian = strtoupper($hasil->ujian->jenis_ujian ?? 'UAS');
                            $tagColor = 'bg-rose-50 text-rose-700 border-rose-200';
                            if (str_contains($jenisUjian, 'KUIS')) $tagColor = 'bg-violet-50 text-violet-700 border-violet-200';
                            elseif (str_contains($jenisUjian, 'UTS')) $tagColor = 'bg-amber-50 text-amber-700 border-amber-200';
                        @endphp
                        <div class="bg-gradient-to-br from-slate-50/80 via-white to-slate-50/40 hover:bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 hover:border-sky-300 hover:shadow-md transition-all duration-300 flex flex-col justify-between gap-3 group">
                            
                            {{-- Top Row: Subject & Exam Type Badges --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                    {{ $hasil->ujian->mapel->nama_mapel ?? 'Mata Pelajaran' }}
                                </span>

                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $tagColor }} border uppercase tracking-wider shrink-0 shadow-2xs">
                                    {{ $hasil->ujian->jenis_ujian ?? 'UAS' }}
                                </span>
                            </div>

                            {{-- Exam Title & Teacher Name --}}
                            <div>
                                <h4 class="font-[Poppins-Bold] !text-slate-800 text-base leading-snug truncate group-hover:!text-sky-600 transition-colors">
                                    {{ $hasil->ujian->nama_ujian ?? '-' }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                    <i class="bi bi-person text-slate-400"></i>
                                    <span>{{ $hasil->ujian->mapel->guru->nama_lengkap ?? 'Guru Pengampu' }}</span>
                                </p>
                            </div>

                            {{-- Bottom Metadata: Date, Time & Detail Button --}}
                            <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-100 text-[11px] text-slate-400 mt-0.5">
                                <div class="flex items-center gap-1.5 font-mono text-[11px] text-slate-500 truncate">
                                    <i class="bi bi-calendar4 text-slate-400"></i>
                                    <span>{{ \Carbon\Carbon::parse($hasil->ujian->waktu_mulai)->locale('id')->isoFormat('D MMM Y') }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span>{{ \Carbon\Carbon::parse($hasil->ujian->waktu_mulai)->format('H:i') }} WIB</span>
                                </div>

                                <a href="{{ route('siswa.ujian.detail', $hasil->ujian_id) }}" 
                                   style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff !important;"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl !text-white font-[Poppins-Bold] text-xs transition-all shadow-sm shadow-sky-500/20 shrink-0 ml-2 active:scale-95 cursor-pointer">
                                    <span class="!text-white">Detail</span>
                                    <i class="bi bi-chevron-right text-[10px] !text-white"></i>
                                </a>
                            </div>

                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- TOAST LIVE NOTIFICATION CONTAINER --}}
    <div id="toast-live-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 max-w-sm w-full pointer-events-none"></div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let knownOngoingIds = @json($sedangBerlangsung->pluck('id'));
        let knownUpcomingIds = @json($akanDatang->pluck('id'));
        let isInitialLoad = true;

        // 1. Toast Notification for Live Newly Created Exam/Quiz
        function showLiveToast(title, message) {
            const container = document.getElementById('toast-live-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'bg-white/95 backdrop-blur-md border border-emerald-300 border-l-4 border-l-emerald-500 p-4 rounded-2xl shadow-xl flex items-start gap-3 transition-all duration-300 transform -translate-y-4 opacity-0 pointer-events-auto';
            toast.innerHTML = `
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 shadow-2xs border border-emerald-100">
                    <i class="bi bi-broadcast animate-pulse"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <h4 class="font-[Poppins-Bold] text-xs text-slate-800 leading-tight">${escapeHtml(title)}</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">${escapeHtml(message)}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 p-1 -mr-1 text-xs cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('-translate-y-4', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 350);
            }, 5000);
        }

        // 2. Countdown Timer Updater
        function updateTimers() {
            const now = new Date();
            const timers = document.querySelectorAll('[id^="timer-"]');

            timers.forEach(timer => {
                const endString = timer.getAttribute('data-end');
                if (!endString) return;
                const endDate = new Date(endString.replace(' ', 'T'));
                const diff = endDate - now;
                const display = timer.querySelector('.countdown');

                if (diff <= 0) {
                    if (display) display.innerText = "Berakhir";
                    timer.classList.replace('text-rose-700', 'text-slate-400');
                    timer.classList.replace('bg-rose-50', 'bg-slate-100');
                } else {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    const h = hours.toString().padStart(2, '0');
                    const m = minutes.toString().padStart(2, '0');
                    const s = seconds.toString().padStart(2, '0');
                    
                    if (display) display.innerText = `${h}:${m}:${s}`;
                }
            });
        }

        // 3. Realtime Auto-Sync Poller (Background Heartbeat)
        async function fetchLiveExams() {
            try {
                const response = await fetch("{{ route('siswa.dashboard.live_exams') }}", {
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                if (!response.ok) return;
                const data = await response.json();
                if (data.success) {
                    renderLiveExams(data);
                }
            } catch (err) {
                console.warn("Live sync warning:", err);
            }
        }

        // 4. Reactive DOM Injection
        function renderLiveExams(data) {
            const currentOngoingIds = data.sedang_berlangsung.map(u => u.id);
            const currentUpcomingIds = data.akan_datang.map(u => u.id);

            // Check if there are newly added ongoing exams
            const newlyAdded = data.sedang_berlangsung.filter(u => !knownOngoingIds.includes(u.id));
            if (newlyAdded.length > 0 && !isInitialLoad) {
                newlyAdded.forEach(ujian => {
                    showLiveToast("📢 Kuis / Ujian Baru Tersedia!", `${ujian.nama_ujian} (${ujian.nama_mapel}) sudah dapat dikerjakan.`);
                });
            }

            const ongoingChanged = JSON.stringify(currentOngoingIds.sort()) !== JSON.stringify(knownOngoingIds.sort());
            const upcomingChanged = JSON.stringify(currentUpcomingIds.sort()) !== JSON.stringify(knownUpcomingIds.sort());

            // A. Update Ongoing Exams Card
            if (ongoingChanged || isInitialLoad) {
                knownOngoingIds = currentOngoingIds;
                const grid = document.getElementById('live-exam-grid');
                const wrapper = document.getElementById('ongoing-card-wrapper');
                const list = document.getElementById('ongoing-exam-list');

                if (data.sedang_berlangsung.length > 0) {
                    if (grid) {
                        grid.classList.remove('grid-cols-1');
                        grid.classList.add('lg:grid-cols-2');
                    }
                    if (wrapper) wrapper.classList.remove('hidden');

                    let html = '';
                    data.sedang_berlangsung.forEach(ujian => {
                        const susulanBadge = ujian.is_susulan 
                            ? '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Susulan</span>' 
                            : '';
                        const btnStyle = ujian.is_resume 
                            ? 'background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: #ffffff !important;'
                            : 'background: linear-gradient(135deg, #10b981 0%, #0d9488 100%); color: #ffffff !important;';
                        const btnText = ujian.is_resume ? 'LANJUTKAN' : 'MULAI UJIAN';
                        const btnIcon = ujian.is_resume ? 'bi-play-circle-fill' : 'bi-arrow-right-circle-fill';

                        html += `
                        <div class="bg-gradient-to-br from-emerald-50/40 via-white to-teal-50/20 rounded-2xl p-4 sm:p-5 border border-emerald-200/80 flex flex-col gap-3 animate-in fade-in duration-300" data-ujian-id="${ujian.id}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                    ${escapeHtml(ujian.nama_mapel)}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    ${susulanBadge}
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 uppercase tracking-wider shrink-0 shadow-2xs">
                                        ${escapeHtml(ujian.jenis_ujian)}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-[Poppins-Bold] !text-slate-800 text-base leading-snug truncate">
                                    ${escapeHtml(ujian.nama_ujian)}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                    <i class="bi bi-person text-slate-400"></i>
                                    <span>${escapeHtml(ujian.guru)}</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-emerald-100/80 mt-0.5">
                                <div class="flex items-center gap-2 text-xs font-mono font-bold text-rose-700 bg-rose-50 px-3.5 py-1.5 rounded-xl border border-rose-200 shadow-2xs" 
                                     id="timer-${ujian.id}" data-end="${ujian.waktu_selesai_iso}">
                                    <i class="bi bi-stopwatch-fill animate-pulse text-sm text-rose-600"></i>
                                    <span class="countdown">--:--:--</span>
                                </div>
                                <a href="${ujian.konfirmasi_url}" 
                                   style="${btnStyle}"
                                   class="px-5 py-2.5 rounded-xl text-xs font-[Poppins-Bold] !text-white shadow-md active:scale-95 transition-all inline-flex items-center gap-2 ml-auto tracking-wide cursor-pointer">
                                    <span class="!text-white">${btnText}</span>
                                    <i class="bi ${btnIcon} text-sm !text-white"></i>
                                </a>
                            </div>
                        </div>
                        `;
                    });
                    if (list) list.innerHTML = html;
                } else {
                    if (wrapper) wrapper.classList.add('hidden');
                    if (grid) {
                        grid.classList.remove('lg:grid-cols-2');
                        grid.classList.add('grid-cols-1');
                    }
                }
            }

            // B. Update Upcoming Exams Card
            if (upcomingChanged || isInitialLoad) {
                knownUpcomingIds = currentUpcomingIds;
                const badge = document.getElementById('upcoming-count-badge');
                const list = document.getElementById('upcoming-exam-list');
                if (badge) badge.innerText = `${data.akan_datang.length} Jadwal`;

                if (data.akan_datang.length === 0) {
                    if (list) {
                        list.innerHTML = `
                        <div class="p-6 text-center text-slate-400" id="upcoming-empty-placeholder">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl mx-auto mb-2">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h4 class="font-[Poppins-Bold] !text-slate-700 text-sm">Belum Ada Jadwal</h4>
                            <p class="text-slate-400 text-xs mt-0.5">Saat ini tidak ada jadwal ujian baru yang akan datang.</p>
                        </div>`;
                    }
                } else {
                    let html = '<div class="space-y-3.5" id="upcoming-items-container">';
                    data.akan_datang.forEach(ujian => {
                        html += `
                        <div class="bg-gradient-to-br from-amber-50/40 via-white to-slate-50/50 rounded-2xl p-4 sm:p-5 border border-slate-200/80 hover:border-amber-300 hover:shadow-sm flex flex-col gap-3 transition-all animate-in fade-in duration-300" data-ujian-id="${ujian.id}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                    ${escapeHtml(ujian.nama_mapel)}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider shrink-0">
                                        ${escapeHtml(ujian.jenis_ujian)}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-[Poppins-Bold] !text-slate-800 text-base leading-snug truncate">
                                    ${escapeHtml(ujian.nama_ujian)}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                    <i class="bi bi-person text-slate-400"></i>
                                    <span>${escapeHtml(ujian.guru)}</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 mt-0.5">
                                <div class="flex items-center gap-2 text-xs font-mono font-bold text-amber-800 bg-amber-50 px-3.5 py-1.5 rounded-xl border border-amber-200/80 shadow-2xs">
                                    <i class="bi bi-clock-fill text-amber-500"></i>
                                    <span>${escapeHtml(ujian.waktu_mulai_formatted)}</span>
                                </div>
                                <span class="px-3.5 py-1.5 rounded-xl text-xs font-[Poppins-Bold] bg-amber-100 text-amber-800 border border-amber-300 uppercase tracking-wider shrink-0 shadow-2xs">
                                    Terjadwal
                                </span>
                            </div>
                        </div>
                        `;
                    });
                    html += '</div>';
                    if (list) list.innerHTML = html;
                }
            }

            isInitialLoad = false;
            updateTimers();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Jalankan Timer Countdown setiap 1 detik
        setInterval(updateTimers, 1000);
        updateTimers();

        // Jalankan Live Polling Auto-Sync setiap 5 detik
        setInterval(fetchLiveExams, 5000);
    });
</script>
@endsection