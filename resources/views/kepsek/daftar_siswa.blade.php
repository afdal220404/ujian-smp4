@extends('layouts.app')

@section('title', 'Data Siswa')

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Menu Utama</div>
    <a href="{{ route('kepsek.index') }}" class="nav-link">
        <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('kepsek.guru') }}" class="nav-link">
        <i class="bi bi-person-workspace"></i> <span>Monitoring Guru</span>
    </a>
    <a href="{{ route('kepsek.siswa') }}" class="nav-link active">
        <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('kepsek.nilai') }}" class="nav-link">
        <i class="bi bi-bar-chart-line-fill"></i> <span>Laporan Nilai</span>
    </a>
@endsection

@section('content')

    {{-- 1. HEADER HALAMAN --}}
    <div class="flex flex-col xl:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Direktori Siswa</h1>
            <p class="text-gray-500 mt-1">
                Total <span class="font-bold text-primary">{{ $siswas->count() }}</span> siswa aktif terdaftar.
            </p>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <form action="{{ route('kepsek.siswa') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full xl:w-auto">
            
            {{-- Dropdown Filter Kelas --}}
            <div class="relative min-w-[160px]">
                <select name="kelas_id" onchange="this.form.submit()" 
                        class="w-full appearance-none pl-4 pr-10 py-3 rounded-xl bg-white border-2 border-gray-100 focus:border-primary/30 focus:outline-none text-sm font-bold text-gray-600 cursor-pointer hover:border-gray-200 transition-colors shadow-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            Kelas {{ $k->kelas }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                    <i class="bi bi-chevron-down text-xs"></i>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="relative group w-full md:w-72">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400 group-focus-within:text-primary transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari Nama atau NISN..." 
                       class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border-2 border-gray-100 focus:border-primary/30 focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all text-sm font-medium shadow-sm text-darkblue placeholder-gray-400"
                       autocomplete="off">
                @if(request('search') || request('kelas_id'))
                    <a href="{{ route('kepsek.siswa') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors" title="Reset Filter">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 2. TABEL DATA SISWA --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                
                {{-- Table Header --}}
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-bold tracking-wider">
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Nomor Induk (NISN)</th>
                        <th class="px-6 py-4 text-center">Kelas</th>
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

                        {{-- Kelas (Badge Warna-Warni) --}}
                        <td class="px-6 py-4 text-center">
                            @if($siswa->kelas)
                                @php
                                    $kelasStr = $siswa->kelas->kelas;
                                    
                                    // PERBAIKAN LOGIKA WARNA:
                                    // Cek 'VIII' (8) TERLEBIH DAHULU sebelum 'VII' (7)
                                    // Karena string 'VIII' mengandung kata 'VII', urutan ini penting.
                                    
                                    if(str_contains($kelasStr, 'IX')) {
                                        // Kelas 9: Hijau (Senior)
                                        $badgeClass = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                    } 
                                    elseif(str_contains($kelasStr, 'VIII')) {
                                        // Kelas 8: Kuning/Oranye (Middle)
                                        $badgeClass = 'bg-yellow-100 text-yellow-700 border border-yellow-200';
                                    } 
                                    elseif(str_contains($kelasStr, 'VII')) {
                                        // Kelas 7: Biru (Junior)
                                        $badgeClass = 'bg-blue-100 text-blue-700 border border-blue-200';
                                    } 
                                    else {
                                        $badgeClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                                    }
                                @endphp
                                <span class="inline-block px-4 py-1.5 rounded-lg text-xs font-bold shadow-sm {{ $badgeClass }}">
                                    {{ $kelasStr }}
                                </span>
                            @else
                                <span class="text-xs text-red-400 italic font-medium">
                                    Belum ada kelas
                                </span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400 border border-gray-100">
                                    <i class="bi bi-search text-2xl"></i>
                                </div>
                                <h3 class="text-gray-900 font-bold text-sm">Data tidak ditemukan</h3>
                                <p class="text-gray-500 text-xs mt-1">
                                    @if(request('search'))
                                        Tidak ada siswa dengan kata kunci "<strong>{{ request('search') }}</strong>".
                                    @else
                                        Belum ada data siswa di kelas ini.
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