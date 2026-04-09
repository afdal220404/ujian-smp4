@extends('layouts.app')

@section('title', 'Laporan Nilai Siswa')

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
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Rekapitulasi Nilai</h1>
            <p class="text-gray-500 mt-1">
                Laporan rata-rata nilai akademik per siswa.
            </p>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <form action="{{ route('kepsek.nilai') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full xl:w-auto">
            
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
                       placeholder="Cari Nama Siswa..." 
                       class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border-2 border-gray-100 focus:border-primary/30 focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all text-sm font-medium shadow-sm text-darkblue placeholder-gray-400"
                       autocomplete="off">
                @if(request('search') || request('kelas_id'))
                    <a href="{{ route('kepsek.nilai') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors" title="Reset Filter">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 2. TABEL NILAI --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                
                {{-- Table Header --}}
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-bold tracking-wider">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4">Identitas Siswa</th>
                        <th class="px-6 py-4 text-center text-blue-600 bg-blue-50/50">Rata Rata Kuis</th>
                        <th class="px-6 py-4 text-center text-orange-600 bg-orange-50/50">Rata Rata UTS</th>
                        <th class="px-6 py-4 text-center text-red-600 bg-red-50/50">Rata Rata UAS</th>
                        <th class="px-6 py-4 text-center text-darkblue bg-gray-100/50 font-extrabold">Rata Rata Nilai Akhir</th>
                        {{-- Header Aksi Baru --}}
                        <th class="px-6 py-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($siswas as $index => $siswa)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        
                        {{-- No --}}
                        <td class="px-6 py-4 text-center text-gray-400 font-medium text-sm">
                            {{ $index + 1 }}
                        </td>

                        {{-- Identitas --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-[Poppins-Bold] text-darkblue text-sm">
                                    {{ $siswa->nama }}
                                </span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 border border-gray-200 font-mono">
                                        {{ $siswa->nisn ?? '-' }}
                                    </span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-100 font-bold">
                                        Kelas {{ $siswa->kelas }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Nilai Kuis --}}
                        <td class="px-6 py-4 text-center">
                            @php
                                $getColor = function($val) {
                                    $v = floatval($val);
                                    if ($val === '-' || $val === null) return 'text-gray-300';
                                    if ($v >= 85) return 'text-green-600 font-bold';
                                    if ($v >= 75) return 'text-blue-600 font-medium';
                                    if ($v >= 70) return 'text-yellow-600 font-medium';
                                    return 'text-red-500 font-bold';
                                };
                            @endphp
                            <span class="{{ $getColor($siswa->rata_kuis) }}">{{ $siswa->rata_kuis }}</span>
                        </td>

                        {{-- Nilai UTS --}}
                        <td class="px-6 py-4 text-center">
                            <span class="{{ $getColor($siswa->rata_uts) }}">{{ $siswa->rata_uts }}</span>
                        </td>

                        {{-- Nilai UAS --}}
                        <td class="px-6 py-4 text-center">
                            <span class="{{ $getColor($siswa->rata_uas) }}">{{ $siswa->rata_uas }}</span>
                        </td>

                        {{-- Nilai Akhir --}}
                        <td class="px-6 py-4 text-center bg-gray-50/30">
                            @php
                                $gr  = floatval($siswa->grade_raw);
                                $bg  = $siswa->nilai_akhir === '-'
                                     ? 'bg-gray-100 text-gray-400 border-gray-200'
                                     : ($gr >= 85 ? 'bg-green-100 text-green-700 border-green-200'
                                     : ($gr >= 75 ? 'bg-blue-100 text-blue-700 border-blue-200'
                                     : ($gr >= 70 ? 'bg-yellow-100 text-yellow-700 border-yellow-200'
                                     :              'bg-red-100 text-red-700 border-red-200')));
                            @endphp
                            <span class="px-3 py-1 rounded-lg text-sm font-bold border {{ $bg }}">
                                {{ $siswa->nilai_akhir }}
                            </span>
                        </td>

                        {{-- Tombol Aksi Baru --}}
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('kepsek.nilai.detail', $siswa->id) }}" class="group/btn inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 hover:border-primary hover:bg-blue-50 rounded-lg transition-all shadow-sm">
                                <span class="text-xs font-bold text-gray-600 group-hover/btn:text-primary">Rincian</span>
                                <i class="bi bi-chevron-right text-[10px] text-gray-400 group-hover/btn:text-primary"></i>
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400 border border-gray-100">
                                    <i class="bi bi-bar-chart text-2xl"></i>
                                </div>
                                <h3 class="text-gray-900 font-bold text-sm">Belum ada data nilai</h3>
                                <p class="text-gray-500 text-xs mt-1">
                                    @if(request('search'))
                                        Pencarian "<strong>{{ request('search') }}</strong>" tidak ditemukan.
                                    @else
                                        Siswa belum mengikuti ujian apapun.
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
