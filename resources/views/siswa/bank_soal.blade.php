@extends('layouts.app')

@section('title', 'Bank Soal')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
            <i class="bi bi-award"></i> <span>Nilai</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link active rounded-xl">
            <i class="bi bi-file-earmark-text"></i> <span>Bank Soal</span>
        </a>
    </div>
@endsection

@section('content')
<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-gray-400 text-xs mb-6">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600"><i class="bi bi-house-door"></i> Home</a>
        <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
        <span class="text-blue-600 font-bold">Bank Soal</span>
    </div>

    {{-- Main Card Container --}}
    <div class="bg-white rounded-[2rem] p-8 border border-gray-200 shadow-[0_10px_40px_rgba(0,0,0,0.05)] relative overflow-hidden">
        
        {{-- Background Decoration --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50/50 to-transparent rounded-bl-[10rem] pointer-events-none"></div>

        {{-- Header & Filters --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 relative z-10">
            <div>
                <h1 class="text-2xl font-[Poppins-Bold] text-darkblue tracking-tight flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                        <i class="bi bi-folder-fill"></i>
                    </span>
                    Bank Soal <span class="text-blue-600">Publik</span>
                </h1>
                <p class="text-gray-500 mt-2 text-sm leading-relaxed ml-14">
                    Kumpulan materi dan latihan soal resmi dari bapak/ibu guru.
                </p>
            </div>

            <form action="{{ route('siswa.bank_soal') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                {{-- Mapel Filter --}}
                <div class="relative min-w-[220px]">
                    <i class="bi bi-funnel-fill absolute left-4 top-1/2 -translate-y-1/2 text-blue-500 z-10 text-xs"></i>
                    <select name="mapel_id" onchange="this.form.submit()" 
                            class="w-full pl-10 pr-10 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-bold text-gray-700 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-400 cursor-pointer appearance-none transition-all shadow-sm">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ $selectedMapelId == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500 pointer-events-none"></i>
                </div>

                {{-- Search --}}
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ $keyword }}" placeholder="Cari judul dokumen..." 
                           class="block w-full pl-11 pr-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-700 placeholder:text-gray-400 focus:ring-4 focus:ring-blue-100 focus:border-blue-400 focus:outline-none transition-all shadow-sm">
                </div>
            </form>
        </div>

        {{-- Content Grid --}}
        @if($bankSoals->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-200 hover:border-blue-200 transition-colors group">
                <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center text-gray-300 mb-4 shadow-sm group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-emoji-smile-upside-down text-4xl"></i>
                </div>
                <h3 class="text-lg font-[Poppins-Bold] text-gray-800 mb-1">
                    {{ $keyword || $selectedMapelId ? 'Tidak Ditemukan' : 'Belum Ada Dokumen' }}
                </h3>
                <p class="text-gray-400 text-sm max-w-sm mx-auto leading-relaxed">
                    {{ $keyword || $selectedMapelId ? 'Coba ubah filter mata pelajaran atau kata kunci pencarian Anda.' : 'Saat ini belum ada dokumen publik yang dibagikan oleh guru untuk kelas Anda.' }}
                </p>
                @if($keyword || $selectedMapelId)
                    <a href="{{ route('siswa.bank_soal') }}" class="mt-6 px-6 py-2 rounded-xl bg-white border border-gray-200 text-blue-600 font-bold text-sm hover:bg-blue-50 transition-all shadow-sm">
                        <i class="bi bi-arrow-counterclockwise mr-2"></i> Reset Filter
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($bankSoals as $soal)
                <div class="group bg-white p-5 rounded-3xl border border-gray-200 shadow-sm hover:shadow-[0_20px_50px_rgba(0,0,0,0.08)] hover:border-blue-300 transition-all duration-300 relative overflow-hidden flex flex-col h-full transform hover:-translate-y-1">
                    
                    {{-- Card Gradient Background --}}
                    <div class="absolute inset-0 bg-gradient-to-b from-gray-50/80 to-transparent opacity-50"></div>
                    
                    {{-- Decorative Top Accent --}}
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gray-100 group-hover:bg-gradient-to-r group-hover:from-blue-500 group-hover:to-indigo-500 transition-all duration-500"></div>
                    
                    {{-- Relative Content --}}
                    <div class="relative z-10 flex flex-col h-full">
                        {{-- Icon & Category --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-red-500 flex items-center justify-center text-2xl shadow-sm group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[10px] font-bold text-blue-600 uppercase tracking-wider border border-blue-100">
                                {{ $soal->mapel->nama_mapel ?? 'Umum' }}
                            </span>
                        </div>

                        {{-- Title --}}
                        <h3 class="font-[Poppins-Bold] text-gray-800 text-base leading-snug mb-2 line-clamp-2 group-hover:text-blue-700 transition-colors h-[2.75rem]">
                            {{ $soal->nama }}
                        </h3>

                        {{-- Guru Info --}}
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-medium mb-6 bg-gray-50 p-2 rounded-lg border border-gray-100">
                            <i class="bi bi-person-circle text-gray-400"></i>
                            <span class="truncate">{{ $soal->guru->nama_lengkap ?? 'Guru' }}</span>
                        </div>

                        {{-- Spacer --}}
                        <div class="flex-grow"></div>

                        {{-- Action Button --}}
                        <a href="{{ asset('storage/' . $soal->file_path) }}" target="_blank" 
                           class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-white border border-blue-200 text-blue-600 font-bold text-sm hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm group-hover:shadow-lg group-hover:shadow-blue-200/50">
                            <span>Download PDF</span>
                            <i class="bi bi-download"></i>
                        </a>
                    </div>

                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
