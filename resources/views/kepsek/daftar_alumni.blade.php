@extends('layouts.app')

@section('title', 'Data Alumni')

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Menu Utama</div>
    <a href="{{ route('kepsek.index') }}" class="nav-link">
        <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('kepsek.guru') }}" class="nav-link">
        <i class="bi bi-person-workspace"></i> <span>Data Guru</span>
    </a>
    <a href="{{ route('kepsek.siswa') }}" class="nav-link">
        <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('kepsek.alumni.index') }}" class="nav-link active">
        <i class="bi bi-mortarboard-fill"></i> <span>Data Alumni</span>
    </a>
    <a href="{{ route('kepsek.nilai') }}" class="nav-link">
        <i class="bi bi-bar-chart-line-fill"></i> <span>Laporan Nilai</span>
    </a>
@endsection

@section('content')

    {{-- 1. HEADER HALAMAN --}}
    <div class="flex flex-col xl:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Direktori Alumni</h1>
            <p class="text-gray-500 mt-1">
                Total <span class="font-bold text-primary">{{ $siswas->count() }}</span> lulusan terdaftar.
            </p>
        </div>

        {{-- PENCARIAN --}}
        <form action="{{ route('kepsek.alumni.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full xl:w-auto">
            
            {{-- Search Bar --}}
            <div class="relative group w-full md:w-96 flex">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400 group-focus-within:text-primary transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari Nama atau NISN..." 
                       class="w-full pl-11 pr-4 py-3 rounded-l-xl bg-white border-2 border-gray-100 focus:border-primary/30 focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all text-sm font-medium shadow-sm text-darkblue placeholder-gray-400"
                       autocomplete="off">
                <button type="submit" class="bg-blue-600 text-white px-6 rounded-r-xl font-bold hover:bg-blue-700 transition">Cari</button>
            </div>
            @if(request('search'))
                <a href="{{ route('kepsek.alumni.index') }}" class="flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition gap-2 h-full">
                    <i class="bi bi-x-circle-fill"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- 2. TABEL DATA ALUMNI --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                
                {{-- Table Header --}}
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-bold tracking-wider">
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Nomor Induk (NISN)</th>
                        <th class="px-6 py-4">Tahun Lulus</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($siswas as $index => $siswa)
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        
                        {{-- No --}}
                        <td class="px-6 py-4 text-center">
                            <span class="text-gray-400 font-medium text-sm group-hover:text-primary transition-colors">
                                {{ $index + 1 }}
                            </span>
                        </td>

                        {{-- Nama Siswa --}}
                        <td class="px-6 py-4">
                            <div class="font-[Poppins-Bold] text-darkblue text-sm group-hover:text-primary transition-colors">
                                {{ $siswa->nama_lengkap }}
                            </div>
                        </td>

                        {{-- NISN (Badge Mono) --}}
                        <td class="px-6 py-4">
                            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-gray-50 border border-gray-200 text-gray-600">
                                <i class="bi bi-upc text-gray-400"></i>
                                <span class="font-mono text-sm font-medium tracking-wide">
                                    {{ $siswa->nisn ?? '-' }}
                                </span>
                            </div>
                        </td>

                        {{-- Tahun Lulus --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                {{ $siswa->updated_at ? $siswa->updated_at->format('Y') : '-' }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('kepsek.alumni.detail', $siswa->id) }}" 
                               class="flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm font-bold text-xs">
                                <i class="bi bi-file-earmark-text"></i> Transkrip Detail
                            </a>
                        </div>
                    </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400 border border-gray-100">
                                    <i class="bi bi-search text-2xl"></i>
                                </div>
                                <h3 class="text-gray-900 font-bold text-sm">Data tidak ditemukan</h3>
                                <p class="text-gray-500 text-xs mt-1">
                                    @if(request('search'))
                                        Tidak ada alumni dengan kata kunci "<strong>{{ request('search') }}</strong>".
                                    @else
                                        Belum ada data alumni yang didaftarkan.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
