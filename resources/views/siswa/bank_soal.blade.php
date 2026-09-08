@extends('layouts.app')

@section('title', 'Arsip Soal Siswa')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
            <i class="bi bi-award"></i> <span>Ujian</span>
        </a>
        <a href="{{ route('siswa.profil') }}" class="nav-link rounded-xl">
            <i class="bi bi-person-circle"></i> <span>Profil</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link active rounded-xl">
            <i class="bi bi-file-earmark-text"></i> <span>Arsip Soal Siswa</span>
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto md:p-4">

    {{-- 1. HERO APP BAR WITH SCHOOL PHOTO & BLUE GRADIENT OVERLAY --}}
    <div class="rounded-b-[2.5rem] md:rounded-3xl px-6 sm:px-8 pt-8 pb-12 text-white shadow-[0_15px_35px_-5px_rgba(0,65,90,0.3)] relative overflow-hidden mb-6">
        {{-- Background School Image --}}
        <img src="{{ asset('image/foto_sekolah2.jpeg') }}" alt="SMPN 4 Tilatang Kamang" class="absolute inset-0 w-full h-full object-cover object-center transform scale-105 pointer-events-none">

        {{-- Gradient Overlay: Light Sky Blue at Top to Deep Solid Blue at Bottom --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#0284c7]/75 via-[#00415a]/90 to-[#002133] pointer-events-none"></div>

        {{-- Subtle Ambient Glows --}}
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute left-1/4 bottom-0 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xl text-[11px] font-semibold text-sky-100 border border-white/20 mb-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]">
                <i class="bi bi-folder2-open text-amber-300"></i>
                <span>Bank Materi Belajar</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-[Poppins-Bold] text-white tracking-tight leading-tight drop-shadow-md">
                Arsip Soal Siswa
            </h1>
            <p class="text-xs sm:text-sm text-sky-100 mt-1 font-medium drop-shadow-xs">
                Kumpulan materi pelajaran dan latihan soal resmi dari bapak/ibu guru
            </p>
        </div>
    </div>

    {{-- 2. FILTERS (RESPONSIVE ROW ON PC) --}}
    <div class="px-4 sm:px-6 md:px-0 mb-6">
        <form action="{{ route('siswa.bank_soal') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            {{-- Mapel Filter --}}
            <div class="relative flex-1">
                <i class="bi bi-funnel-fill absolute left-3.5 top-1/2 -translate-y-1/2 text-sky-500 text-xs pointer-events-none"></i>
                <select name="mapel_id" onchange="this.form.submit()" 
                        class="w-full pl-9 pr-9 py-3 rounded-2xl bg-white border border-slate-200/80 text-xs sm:text-sm font-[Poppins-Bold] text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 appearance-none shadow-[0_4px_20px_rgba(0,0,0,0.02)] cursor-pointer">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}" {{ $selectedMapelId == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
                <i class="bi bi-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
            </div>

            {{-- Search --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="bi bi-search text-sky-500 text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $keyword }}" placeholder="Cari judul dokumen materi..." 
                       class="w-full pl-9 pr-4 py-3 rounded-2xl bg-white border border-slate-200/80 text-xs sm:text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-sky-500 focus:outline-none shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            </div>
        </form>
    </div>

    {{-- 3. DOCUMENTS GRID (RESPONSIVE 3-COLUMN GRID ON DESKTOP) --}}
    <div class="px-4 sm:px-6 md:px-0 mb-8">
        @if($arsipSoalSiswas->isEmpty())
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-300 flex items-center justify-center text-3xl mx-auto mb-3">
                    <i class="bi bi-folder-x"></i>
                </div>
                <h3 class="font-[Poppins-Bold] text-slate-700 text-base">
                    {{ $keyword || $selectedMapelId ? 'Dokumen Tidak Ditemukan' : 'Belum Ada Dokumen' }}
                </h3>
                <p class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">
                    {{ $keyword || $selectedMapelId ? 'Coba ubah filter mata pelajaran atau kata kunci pencarian Anda.' : 'Saat ini belum ada dokumen materi yang dibagikan untuk kelas Anda.' }}
                </p>
                @if($keyword || $selectedMapelId)
                    <a href="{{ route('siswa.bank_soal') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-50 text-sky-600 font-bold text-xs hover:bg-sky-100 transition-all">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($arsipSoalSiswas as $soal)
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-md hover:border-sky-200 transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="w-11 h-11 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform shadow-2xs border border-red-100">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 border border-sky-200 text-[10px] font-bold uppercase">
                                {{ $soal->mapel->nama_mapel ?? 'Umum' }}
                            </span>
                        </div>

                        <h3 class="font-[Poppins-Bold] text-slate-800 text-sm sm:text-base leading-snug group-hover:text-sky-600 transition-colors line-clamp-2 mb-1">
                            {{ $soal->nama }}
                        </h3>

                        <p class="text-xs text-slate-400 flex items-center gap-1.5 mb-4 font-medium">
                            <i class="bi bi-person-fill text-slate-400"></i>
                            <span class="truncate">{{ $soal->guru->nama_lengkap ?? 'Guru Pengampu' }}</span>
                        </p>
                    </div>

                    <a href="{{ asset('storage/' . $soal->file_path) }}" target="_blank" 
                       style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff !important;"
                       class="w-full py-2.5 px-4 rounded-xl !text-white font-[Poppins-Bold] text-xs transition-all flex items-center justify-center gap-2 shadow-md shadow-sky-500/20 active:scale-95 cursor-pointer">
                        <i class="bi bi-file-pdf !text-white"></i>
                        <span class="!text-white">Buka & Unduh PDF</span>
                    </a>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
