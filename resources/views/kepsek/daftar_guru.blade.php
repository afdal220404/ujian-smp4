@extends('layouts.app')

@section('title', 'Data Guru Pengajar')

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
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Direktori Pengajar</h1>
            <p class="text-gray-500 mt-1">
                Menampilkan <span class="font-bold text-primary">{{ $gurus->count() }}</span> staf & guru aktif.
            </p>
        </div>

        {{-- Search Bar --}}
        <form action="{{ route('kepsek.guru') }}" method="GET" class="relative group w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="bi bi-search text-gray-400 group-focus-within:text-primary transition-colors"></i>
            </div>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Cari nama, NIP, atau mapel..." 
                   class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white border-2 border-gray-100 focus:border-primary/30 focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all text-sm font-medium shadow-sm text-darkblue placeholder-gray-400"
                   autocomplete="off">
            @if(request('search'))
                <a href="{{ route('kepsek.guru') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors" title="Hapus Pencarian">
                    <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- 2. TABEL GAYA "FLOATING CARDS" --}}
    <div class="overflow-x-auto pb-10">
        <table class="w-full text-left border-separate border-spacing-y-5">
            <thead>
                <tr class="text-xs uppercase text-gray-400 font-bold tracking-wider">
                    <th class="px-6 pb-2 pl-8">Identitas Guru & NIP</th>
                    <th class="px-6 pb-2">Status & Peran</th>
                    <th class="px-6 pb-2">Bidang Studi & Kelas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gurus as $guru)
                    @php
                        // Logika Warna & Icon Role
                        $rawRole = $guru->role ?? optional($guru->user)->role ?? 'Guru';
                        $role = ucwords(strtolower($rawRole));

                        if (stripos($role, 'Operator') !== false) {
                            $borderClass = 'border-l-4 border-purple-500';
                            $badgeClass = 'bg-purple-100 text-purple-700 border-purple-200';
                            $iconClass = 'bi-gear-wide-connected';
                            $avatarBg = 'from-purple-500 to-indigo-600';
                        } elseif (stripos($role, 'Kepala Sekolah') !== false) {
                            $borderClass = 'border-l-4 border-[#00415a]';
                            $badgeClass = 'bg-[#00415a] text-white border-[#00314a] shadow-sm';
                            $iconClass = 'bi-award-fill';
                            $avatarBg = 'from-[#00415a] to-blue-900';
                        } else {
                            $borderClass = 'border-l-4 border-blue-400';
                            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-100';
                            $iconClass = 'bi-person-badge';
                            $avatarBg = 'from-blue-400 to-blue-600';
                        }
                    @endphp

                {{-- Baris Kartu --}}
                <tr class="bg-white shadow-[0_2px_15px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300 group rounded-2xl relative">
                    
                    {{-- Kolom 1: FOTO, NAMA, NIP --}}
                    <td class="py-5 pl-6 pr-4 rounded-l-2xl border-y border-r-0 border-gray-100 group-hover:border-gray-200 {{ $borderClass }}">
                        <div class="flex items-center gap-5">
                            {{-- Foto Avatar --}}
                            <div class="relative flex-shrink-0">
                                @php $foto = $guru->foto ?? $guru->user->foto ?? null; @endphp
                                @if($foto)
                                    <img src="{{ asset('storage/' . $foto) }}" alt="{{ $guru->nama_lengkap }}" 
                                         class="w-16 h-16 rounded-2xl object-cover shadow-sm group-hover:shadow-md transition-shadow ring-2 ring-white">
                                @else
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $avatarBg }} flex items-center justify-center text-white font-bold text-2xl shadow-sm group-hover:shadow-md transition-shadow ring-2 ring-white">
                                        {{ substr($guru->nama_lengkap ?? $guru->nama ?? 'G', 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Info Teks --}}
                            <div class="min-w-0">
                                <h3 class="font-[Poppins-Bold] text-darkblue text-lg leading-tight mb-1 truncate">
                                    {{ $guru->nama_lengkap ?? $guru->nama ?? 'Tanpa Nama' }}
                                </h3>
                                
                                {{-- NIP: Tambahkan 'whitespace-nowrap' agar tidak turun baris --}}
                                <div class="flex items-center gap-2 text-sm text-gray-500 font-medium bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 w-fit whitespace-nowrap">
                                    <i class="bi bi-postcard text-gray-400"></i>
                                    <span>NIP: <span class="text-gray-700 tracking-wide font-semibold">{{ $guru->nip ?? '-' }}</span></span>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom 2: JABATAN & TUGAS --}}
                    <td class="px-6 py-5 border-y border-gray-100 group-hover:border-gray-200 align-middle">
                        <div class="flex flex-col gap-3 items-start">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border {{ $badgeClass }}">
                                <i class="bi {{ $iconClass }}"></i> {{ $role }}
                            </span>

                            @if($guru->waliKelas && $guru->waliKelas->kelas)
                                <div class="flex items-center gap-2 text-xs font-semibold text-yellow-700 bg-yellow-50 px-3 py-1.5 rounded-lg border border-yellow-100">
                                    <i class="bi bi-star-fill text-yellow-500"></i>
                                    <span>Wali Kelas {{ $guru->waliKelas->kelas->kelas }}</span>
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- Kolom 3: LIST MAPEL (WARNA BARU: INDIGO) --}}
                    <td class="px-6 py-5 rounded-r-2xl border-y border-l-0 border-gray-100 group-hover:border-gray-200 align-middle">
                        @if($guru->mapels && $guru->mapels->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($guru->mapels as $mapel)
                                    {{-- Chip Mapel dengan Warna Baru (Indigo) --}}
                                    <div class="group/mapel flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-100 shadow-sm hover:bg-indigo-100 transition-colors cursor-default">
                                        {{-- Ikon Buku Kecil --}}
                                        <i class="bi bi-journal-text text-indigo-400 group-hover/mapel:text-indigo-600"></i>
                                        
                                        <span class="text-xs font-bold text-indigo-700">
                                            {{ $mapel->nama_mapel }}
                                        </span>
                                        
                                        @if($mapel->kelas)
                                            <span class="text-[10px] font-bold text-white bg-indigo-400 group-hover/mapel:bg-indigo-600 px-1.5 py-0.5 rounded ml-1 transition-colors">
                                                {{ $mapel->kelas->kelas }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic flex items-center gap-2 px-2 py-1">
                                <i class="bi bi-slash-circle"></i> Tidak ada mapel
                            </span>
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-12">
                        <div class="inline-flex flex-col items-center justify-center p-8 bg-white rounded-3xl border border-dashed border-gray-300">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                <i class="bi bi-search text-2xl"></i>
                            </div>
                            <h3 class="text-gray-900 font-bold text-lg">Tidak ada data ditemukan</h3>
                            <p class="text-gray-500 text-sm mt-1">
                                @if(request('search'))
                                    Tidak ada guru dengan kata kunci "<strong>{{ request('search') }}</strong>".
                                @else
                                    Belum ada data guru di sistem.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
