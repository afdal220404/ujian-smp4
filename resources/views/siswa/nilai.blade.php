@extends('layouts.app')

@section('title', 'Nilai Siswa')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link active rounded-xl">
            <i class="bi bi-award"></i> <span>Nilai</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link rounded-xl">
             <i class="bi bi-file-earmark-text"></i> <span>Arsip Soal Siswa</span>
        </a>
    </div>
@endsection

@section('content')
<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-gray-400 text-xs mb-6">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600"><i class="bi bi-house-door"></i> Home</a>
        <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
        <span class="text-blue-600 font-bold">Nilai</span>
    </div>

    {{-- Fix #7: Helper pewarnaan & predikat terpusat — digunakan di seluruh view --}}
    @php
        $getColor = function($val) {
            $v = floatval($val);
            if ($val === '-' || $val === null) return 'text-gray-300'; // Belum ada nilai
            if ($v >= 85) return 'text-green-600 font-bold';
            if ($v >= 75) return 'text-blue-600 font-medium';
            if ($v >= 70) return 'text-yellow-600 font-medium';
            return 'text-red-500 font-bold'; // Termasuk nilai 0
        };
        $getPredikat = function($nilai) {
            if ($nilai >= 85) return ['predikat' => 'A', 'label' => 'Sangat Baik', 'badge' => 'bg-green-100 text-green-700 border-green-200'];
            if ($nilai >= 75) return ['predikat' => 'B', 'label' => 'Baik',        'badge' => 'bg-blue-100 text-blue-700 border-blue-200'];
            if ($nilai >= 70) return ['predikat' => 'C', 'label' => 'Cukup',       'badge' => 'bg-yellow-100 text-yellow-700 border-yellow-200'];
            if ($nilai >  0)  return ['predikat' => 'D', 'label' => 'Kurang',      'badge' => 'bg-red-100 text-red-700 border-red-200'];
            return                   ['predikat' => '-', 'label' => '-',            'badge' => 'bg-gray-100 text-gray-500 border-gray-200'];
        };
    @endphp

    <div class="space-y-8">
    
    {{-- Header & Stats Area --}}
    <div>
        {{-- Title & Search --}}
        <div class="flex flex-col space-y-6">
            <div>
                <h1 class="text-3xl font-[Poppins-Bold] text-darkblue tracking-tight">
                    Riwayat Nilai <span class="text-blue-600">Akademik</span>
                </h1>
                <p class="text-gray-500 mt-2 text-base leading-relaxed">
                    Pantau pencapaian belajarmu di sini. Tingkatkan terus nilaimu untuk hasil yang memuaskan! 🚀
                </p>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('siswa.nilai') }}" method="GET" class="relative group flex-1">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400 group-focus-within:text-blue-500 transition-colors text-lg"></i>
                    </div>
                    <input type="text" name="search" value="{{ $keyword }}" placeholder="Cari mata pelajaran atau ujian..." 
                           class="block w-full pl-14 pr-12 py-4 rounded-2xl bg-white border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] text-gray-700 placeholder:text-gray-400 focus:ring-4 focus:ring-blue-100 focus:border-blue-300 focus:outline-none transition-all duration-300">
                    @if($keyword)
                        <a href="{{ route('siswa.nilai') }}" class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                            <i class="bi bi-x-circle-fill text-lg"></i>
                        </a>
                    @endif
                </form>

                {{-- View Mode Toggle --}}
                <div class="flex items-center gap-1 flex-shrink-0 bg-white p-1.5 rounded-2xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <button id="btn-card-view" onclick="switchView('card')" title="Tampilan Kartu"
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-sm transition-all duration-300 bg-blue-600 text-white shadow-md shadow-blue-600/20">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </button>
                    <button id="btn-table-view" onclick="switchView('table')" title="Tampilan Tabel"
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-sm transition-all duration-300 text-gray-400 hover:text-blue-600 hover:bg-blue-50">
                        <i class="bi bi-table"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================= --}}
    {{-- SHARED KELAS FILTER (berlaku untuk kedua mode) --}}
    {{-- ======================================= --}}
    @php
        $allKelasShared = collect([
            [
                'kelas'      => (object)['nama_kelas' => $namaKelasAktif],
                'mapels'     => $mapels,
                'is_current' => true,
                'ada_nilai'  => $allMapels->isNotEmpty(),
                'tingkat'    => $kelasAktif,
            ]
        ]);
        foreach($riwayatKelas as $r) {
            $allKelasShared->push($r);
        }
    @endphp

    <div class="flex items-center gap-3 flex-wrap bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-4">
        <div class="flex items-center gap-2 mr-1 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="bi bi-building"></i>
            </div>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas:</span>
        </div>
        <div class="flex flex-wrap gap-2" id="shared-kelas-tabs">
            @foreach($allKelasShared as $kidx => $kentry)
                @php
                    $hasData   = $kentry['is_current'] ? $allMapels->isNotEmpty() : (bool)($kentry['ada_nilai'] ?? false);
                    $isCurrent = $kentry['is_current'];
                @endphp
                @if($hasData)
                    <button onclick="switchKelas({{ $kidx }})"
                            id="shared-tab-{{ $kidx }}"
                            class="shared-kelas-btn px-4 py-2 rounded-xl border text-sm font-bold transition-all
                                {{ $kidx === 0 ? 'bg-blue-600 border-blue-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-500 hover:border-blue-300 hover:text-blue-600' }}">
                        {{ $kentry['kelas']->nama_kelas ?? 'Kelas' }}
                        @if($isCurrent)
                            <span class="ml-1 text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-md font-bold">Aktif</span>
                        @endif
                    </button>
                @else
                    <button disabled
                            class="px-4 py-2 rounded-xl border text-sm font-bold cursor-not-allowed opacity-50
                                bg-gray-100 border-gray-200 text-gray-400"
                            title="Belum ada nilai untuk {{ $kentry['kelas']->nama_kelas ?? 'kelas ini' }}">
                        {{ $kentry['kelas']->nama_kelas ?? 'Kelas' }}
                        <span class="ml-1 text-[10px] bg-gray-200 text-gray-400 px-1.5 py-0.5 rounded-md font-bold">Kosong</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ======================================= --}}
    {{-- CARD VIEW (default) --}}
    {{-- ======================================= --}}
    <div id="view-card">

    {{-- Panel per kelas — data dari $allKelasShared (shared dengan table view) --}}
    @php $allKelasForCard = $allKelasShared; @endphp
    @foreach($allKelasForCard as $cidx => $centry)
    <div id="card-panel-{{ $cidx }}" class="card-kelas-panel {{ $cidx !== 0 ? 'hidden' : '' }}">

        @php $mapelPanel = collect($centry['mapels']); @endphp

        @if($mapelPanel->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                @if($centry['is_current'] && $keyword)
                    {{-- Hasil pencarian kosong --}}
                    <div class="relative mb-6">
                        <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center text-red-300">
                            <i class="bi bi-search text-4xl"></i>
                        </div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm border border-red-100 text-red-500">
                            <i class="bi bi-x-lg text-xs"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Pencarian Tidak Ditemukan</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Tidak ada mata pelajaran atau ujian yang cocok dengan kata kunci "<span class="font-bold text-darkblue">{{ $keyword }}</span>".</p>
                    <a href="{{ route('siswa.nilai') }}" class="mt-6 px-6 py-2.5 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200 transition-colors">
                       Reset Pencarian
                    </a>
                @else
                    {{-- Tidak ada data sama sekali --}}
                    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center text-blue-300 mb-6">
                        <i class="bi bi-journal-album text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Belum Ada Data Akademik</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Tidak ada mata pelajaran yang ditemukan untuk kelas ini.</p>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-5">
                @foreach($mapelPanel as $mapel)
                <div class="group bg-white rounded-3xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] hover:shadow-[0_15px_40px_rgba(0,0,0,0.06)] hover:border-blue-100 transition-all duration-300 overflow-hidden relative">

                    {{-- Side Accent Bar --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-blue-400 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    {{-- Card Header (Clickable) --}}
                    <div class="p-6 cursor-pointer flex flex-col md:flex-row gap-6 relative z-10" onclick="toggleMapelAccord(this)">

                        {{-- Teacher & Subject Icon Area --}}
                        <div class="flex items-center gap-5">
                            <div class="relative">
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 p-1 shadow-inner">
                                    @if($mapel->guru && $mapel->guru->foto)
                                        <img src="{{ asset('storage/' . $mapel->guru->foto) }}" alt="Foto Guru" class="w-full h-full object-cover rounded-xl">
                                    @else
                                        <div class="w-full h-full rounded-xl bg-white flex items-center justify-center border border-gray-100">
                                            <i class="bi bi-person-fill text-2xl text-gray-300"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md border border-gray-100 text-blue-600">
                                    <i class="bi bi-journal-text text-sm"></i>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-[Poppins-Bold] text-lg text-darkblue group-hover:text-blue-600 transition-colors mb-1.5">
                                    {{ $mapel->nama_mapel }}
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <span class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-xs">
                                        <i class="bi bi-person font-bold text-gray-400"></i>
                                    </span>
                                    {{ $mapel->guru->nama_lengkap ?? 'Guru Belum Ditentukan' }}
                                </div>
                            </div>
                        </div>

                        {{-- Stats & Toggle Area --}}
                        <div class="flex-1 flex items-center justify-between md:justify-end gap-4 md:gap-8 border-t md:border-t-0 border-gray-50 pt-4 md:pt-0 mt-2 md:mt-0">

                            <div class="grid grid-cols-2 gap-8 text-right">
                                {{-- Rata-Rata Kuis Stat --}}
                                @php
                                    $kuisCardNilais = ($mapel->ujian_selesai ?? collect())
                                        ->filter(fn($u) => stripos($u->jenis_ujian ?? '', 'Kuis') !== false)
                                        ->map(fn($u) => $u->hasilUjians->first()?->nilai)
                                        ->filter(fn($v) => $v !== null);
                                    $rataRataKuisCard = $kuisCardNilais->isNotEmpty() ? $kuisCardNilais->avg() : 0;
                                @endphp
                                <div class="flex flex-col items-end">
                                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-0.5">Rata-Rata Kuis</span>
                                    @if($rataRataKuisCard > 0)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-lg font-[Poppins-Bold] {{ $rataRataKuisCard >= 75 ? 'text-emerald-600' : 'text-amber-500' }}">
                                                {{ number_format($rataRataKuisCard, 1) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm font-bold text-gray-300">-</span>
                                    @endif
                                </div>

                                {{-- Total Ujian Stat --}}
                                <div class="flex flex-col items-end">
                                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-0.5">Total Ujian</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-lg font-[Poppins-Bold] text-gray-700">
                                            {{ ($mapel->ujian_selesai ?? collect())->count() }}
                                        </span>
                                        <span class="text-xs font-medium text-gray-400">Selesai</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Arrow --}}
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-600 group-hover:text-white transition-all transform rotate-0 arrow-icon shadow-sm group-hover:shadow-md">
                                <i class="bi bi-chevron-down text-sm"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Accordion Content --}}
                    <div class="mapel-content hidden border-t border-gray-100 bg-gray-50/50">
                        <div class="p-6">
                            @if(($mapel->ujian_selesai ?? collect())->isEmpty())
                                <div class="bg-white rounded-2xl p-8 border border-dashed border-gray-300 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 text-gray-400 mb-3">
                                        <i class="bi bi-clock-history text-xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium text-sm">Belum ada riwayat ujian untuk mata pelajaran ini.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($mapel->ujian_selesai as $index => $ujian)
                                        @php $hasil = $ujian->hasilUjians->first(); @endphp
                                        @if($hasil)
                                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 transition-all group/item relative overflow-hidden">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $ujian->jenis_ujian == 'UAS' ? 'bg-red-50 text-red-600 border-red-100' : ($ujian->jenis_ujian == 'UTS' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-blue-50 text-blue-600 border-blue-100') }}">
                                                                {{ $ujian->jenis_ujian }}
                                                            </span>
                                                            <span class="text-[10px] text-gray-400 font-medium">
                                                                <i class="bi bi-calendar4 me-1"></i> {{ $ujian->created_at->format('d M') }}
                                                            </span>
                                                        </div>
                                                        <h4 class="font-bold text-gray-800 text-sm group-hover/item:text-blue-600 transition-colors line-clamp-1" title="{{ $ujian->nama_ujian }}">
                                                            {{ $ujian->nama_ujian }}
                                                            @if($ujian->is_susulan)
                                                                <span class="ml-1 inline-block px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-100 text-amber-700 border border-amber-200 uppercase">Susulan</span>
                                                            @endif
                                                        </h4>
                                                    </div>
                                                    @php $isUtsUasCard = in_array(strtoupper($ujian->jenis_ujian ?? ''), ['UTS', 'UAS']); @endphp
                                                    @if(!$isUtsUasCard)
                                                    <div class="flex flex-col items-end">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase mb-0.5 tracking-wider">Nilai</span>
                                                        <span class="text-2xl font-[Poppins-Bold] {{ $hasil->nilai >= 75 ? 'text-emerald-600' : 'text-red-500' }}">
                                                            {{ $hasil->nilai }}
                                                        </span>
                                                    </div>
                                                    @else
                                                    <div class="flex flex-col items-end">
                                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                                            <i class="bi bi-lock-fill text-xs"></i>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="mt-4 pt-3 border-t border-gray-50 flex justify-end items-center">
                                                    <a href="{{ route('siswa.ujian.detail', $ujian->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors">
                                                        Lihat Detail <i class="bi bi-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endforeach

    </div> {{-- end #view-card --}}

    {{-- ======================================= --}}
    {{-- TABLE VIEW --}}
    {{-- ======================================= --}}
    <div id="view-table" class="hidden">

        {{-- Data tabel menggunakan $allKelasShared (dikontrol filter di atas) --}}
        @php $allKelasForTable = $allKelasShared; @endphp

        {{-- One table per kelas --}}
        @foreach($allKelasForTable as $idx => $entry)
            <div id="kelas-table-{{ $idx }}" class="kelas-table-panel {{ $idx !== 0 ? 'hidden' : '' }}">
                
                @php
                    // Hitung maks kuis saja — UTS dan UAS tidak ditampilkan
                    $maxK = 0;
                    foreach($entry['mapels'] as $mp) {
                        $ksE = $mp->ujian_selesai ?? collect();
                        $kC = $ksE->filter(fn($u) => stripos($u->jenis_ujian ?? '', 'Kuis') !== false)->count();
                        if($kC > $maxK) $maxK = $kC;
                    }
                @endphp

                <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-sm bg-white">
                    <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                        <thead>
                            <tr class="bg-[#00415a] text-white">
                                <th rowspan="2" class="px-4 py-3 text-center w-10 border-r border-white/10 text-xs font-bold">No</th>
                                <th rowspan="2" class="px-5 py-3 border-r border-white/10 min-w-[200px] text-xs font-bold">Mata Pelajaran</th>
                                @if($maxK > 0)
                                    <th colspan="{{ $maxK }}" class="px-3 py-2 text-center border-r border-white/10 text-xs font-bold bg-blue-800/40">Kuis</th>
                                @endif
                                <th rowspan="2" class="px-4 py-3 text-center bg-emerald-700 text-xs font-bold">Rata-rata Kuis</th>
                                <th rowspan="2" class="px-4 py-3 text-center bg-gray-700/60 text-xs font-bold">Predikat</th>
                            </tr>
                            <tr class="bg-[#005a7d] text-white">
                                @for($i = 1; $i <= $maxK; $i++)
                                    <th class="px-3 py-1.5 text-center border-r border-white/10 w-12 bg-blue-900/30 font-normal text-xs">K{{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($entry['mapels'] as $mpIdx => $mp)
                                @php
                                    $ujianSelesai = $mp->ujian_selesai ?? collect();
                                    $kData = $ujianSelesai->filter(fn($u) => stripos($u->jenis_ujian ?? '', 'Kuis') !== false)->values();

                                    // Hitung rata-rata kuis saja untuk kolom ini
                                    $kuisNilais = $kData->map(fn($u) => $u->hasilUjians->first()?->nilai)->filter(fn($v) => $v !== null);
                                    $nilaiAkhir = $kuisNilais->isNotEmpty() ? round($kuisNilais->avg(), 1) : 0;

                                    $pred       = $getPredikat($nilaiAkhir);
                                    $badgeColor = $pred['badge'];
                                    $badgeLabel = $pred['label'];
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-gray-400 text-sm border-r border-gray-100">{{ $mpIdx + 1 }}</td>
                                    <td class="px-5 py-3 border-r border-gray-100">
                                        <span class="font-bold text-darkblue text-sm">{{ $mp->nama_mapel }}</span>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $mp->guru->nama_lengkap ?? '-' }}</div>
                                    </td>
                                    @for($i = 0; $i < $maxK; $i++)
                                        @php $cell = $kData->get($i); $hasil = $cell?->hasilUjians?->first(); @endphp
                                        <td class="px-3 py-3 text-center text-sm border-r border-gray-50 bg-blue-50/10">
                                            @if($hasil)
                                                <span class="{{ $getColor($hasil->nilai) }}">{{ $hasil->nilai }}</span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="px-4 py-3 text-center bg-gray-50">
                                        <span class="{{ $getColor($nilaiAkhir) }} text-base">
                                            {{ number_format($nilaiAkhir, 1) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center bg-gray-50">
                                        <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ $badgeColor }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ 2 + $maxK + 2 }}" class="px-5 py-10 text-center text-gray-400 text-sm">Belum ada data nilai.</td></tr>
                            @endforelse

                            {{-- Footer: Rata-Rata Kuis Keseluruhan --}}
                            @php
                                // Hitung ulang rata-rata kuis keseluruhan dari semua mapel
                                $totalKuisFooter = 0; $countKuisFooter = 0;
                                foreach($entry['mapels'] as $mpF) {
                                    $ksF = $mpF->ujian_selesai ?? collect();
                                    $kF  = $ksF->filter(fn($u) => stripos($u->jenis_ujian ?? '', 'Kuis') !== false)->values();
                                    $nF  = $kF->map(fn($u) => $u->hasilUjians->first()?->nilai)->filter(fn($v) => $v !== null);
                                    if ($nF->isNotEmpty()) { $totalKuisFooter += $nF->avg(); $countKuisFooter++; }
                                }
                                $rataRataKuisFooter = $countKuisFooter > 0 ? round($totalKuisFooter / $countKuisFooter, 1) : 0;
                            @endphp
                            <tr class="bg-emerald-50 font-bold">
                                <td colspan="{{ 2 + $maxK }}" class="px-5 py-3 text-right text-sm text-gray-600 uppercase tracking-wider">Rata-Rata Kuis Keseluruhan</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="{{ $rataRataKuisFooter >= 85 ? 'text-green-600 font-bold' : ($rataRataKuisFooter >= 75 ? 'text-blue-600 font-medium' : 'text-red-500 font-bold') }} text-base">
                                        {{ number_format($rataRataKuisFooter, 1) }}
                                    </span>
                                </td>
                                <td class="bg-gray-50/50"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div> {{-- end #view-table --}}

</div>
@endsection

@section('scripts')
<script>
    function toggleMapelAccord(header) {
        const content = header.nextElementSibling;
        const arrow = header.querySelector('.arrow-icon');
        
        // Toggle Hidden
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    function showCardPanel(idx) {
        // Sembunyikan semua panel kelas card
        document.querySelectorAll('.card-kelas-panel').forEach(p => p.classList.add('hidden'));
        // Tampilkan panel yang dipilih
        const panel = document.getElementById('card-panel-' + idx);
        if (panel) panel.classList.remove('hidden');

        // Update style tab button
        document.querySelectorAll('.card-tab-btn').forEach((btn, i) => {
            if (i === idx) {
                btn.classList.add('bg-blue-600', 'border-blue-600', 'text-white', 'shadow-md');
                btn.classList.remove('bg-white', 'border-gray-200', 'text-gray-500');
            } else {
                btn.classList.remove('bg-blue-600', 'border-blue-600', 'text-white', 'shadow-md');
                btn.classList.add('bg-white', 'border-gray-200', 'text-gray-500');
            }
        });
    }

    function switchView(mode) {
        const cardView   = document.getElementById('view-card');
        const tableView  = document.getElementById('view-table');
        const btnCard    = document.getElementById('btn-card-view');
        const btnTable   = document.getElementById('btn-table-view');

        const activeClass   = "w-10 h-10 rounded-xl flex items-center justify-center text-sm transition-all duration-300 bg-blue-600 text-white shadow-md shadow-blue-600/20";
        const inactiveClass = "w-10 h-10 rounded-xl flex items-center justify-center text-sm transition-all duration-300 text-gray-400 hover:text-blue-600 hover:bg-blue-50";

        if (mode === 'card') {
            cardView.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnCard.className = activeClass;
            btnTable.className = inactiveClass;
        } else {
            tableView.classList.remove('hidden');
            cardView.classList.add('hidden');
            btnTable.className = activeClass;
            btnCard.className = inactiveClass;
        }
    }

    function showKelasTable(idx) {
        // Hide all panels
        document.querySelectorAll('.kelas-table-panel').forEach(p => p.classList.add('hidden'));
        // Reset all buttons
        document.querySelectorAll('.kelas-tab-btn').forEach(b => {
            b.classList.remove('bg-blue-600', 'border-blue-600', 'text-white', 'shadow-md');
            b.classList.add('bg-white', 'border-gray-200', 'text-gray-500');
        });
        // Show selected
        document.getElementById('kelas-table-' + idx).classList.remove('hidden');
        const activeBtn = document.getElementById('btn-kelas-' + idx);
        activeBtn.classList.add('bg-blue-600', 'border-blue-600', 'text-white', 'shadow-md');
        activeBtn.classList.remove('bg-white', 'border-gray-200', 'text-gray-500');
    }
</script>
@endsection
