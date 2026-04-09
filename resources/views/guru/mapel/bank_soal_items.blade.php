@extends('layouts.app')

@section('title', 'Bank Soal - ' . $mapel->nama_mapel)

@section('sidebar-menu')
    {{-- Tombol Kembali --}}
    <div class="mb-4 px-3">
        <a href="{{ route('guru.index') }}" 
           class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
        </a>
    </div>    
    
    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.siswa') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.bank_soal.*') ? 'active' : '' }}">
        <i class="bi bi-archive-fill"></i> <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.mapel.arsip_soal_siswa.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.arsip_soal_siswa.*') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> <span>Arsip Soal Siswa</span>
    </a>
@endsection

@section('content')

@php
    $tipeLabels = [
        'pilihan_ganda' => ['label' => 'Pilihan Ganda', 'color' => 'blue'],
        'benar_salah'   => ['label' => 'Benar / Salah', 'color' => 'emerald'],
        'jawaban_ganda' => ['label' => 'Pilih Banyak', 'color' => 'purple'],
        'menjodohkan'   => ['label' => 'Mencocokkan', 'color' => 'amber'],
    ];
@endphp

{{-- HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
    <div>
        <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
            <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="hover:text-blue-600">{{ $mapel->nama_mapel }}</a>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-blue-600 font-bold">Bank Soal</span>
        </div>
        <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Bank Soal</h1>
        <p class="text-sm text-gray-500 mt-1">Kumpulan soal digital untuk mapel <span class="font-bold text-blue-600">{{ $mapel->nama_mapel }}</span></p>
    </div>
    <button type="button" onclick="openTambahSoalModal()"
       class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
        <i class="bi bi-plus-lg"></i> Tambah Soal Baru
    </button>
</div>

{{-- ALERTS --}}
@if(session('success'))
<div class="mb-6 px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-bold flex items-center gap-3">
    <i class="bi bi-check-circle-fill text-lg"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 text-red-700 rounded-xl text-sm font-bold flex items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill text-lg"></i> {{ session('error') }}
</div>
@endif

{{-- BULK ACTION BAR --}}
<div id="bulk-action-bar" class="hidden mb-4 bg-white p-3 rounded-xl border border-blue-100 shadow-sm flex justify-between items-center transition-all animate-in fade-in slide-in-from-top-2">
    <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 cursor-pointer group">
            <input type="checkbox" id="check-all" class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
            <span class="text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors">Pilih Semua</span>
        </label>
        <div class="h-4 w-px bg-gray-200 mx-1"></div>
        <span class="text-xs font-medium text-gray-500"><span id="selected-count-bulk" class="font-bold text-blue-600">0</span> soal terpilih</span>
    </div>
    <button type="button" id="btn-bulk-delete" class="px-5 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm font-bold hover:bg-red-600 hover:text-white transition-all flex items-center gap-2 shadow-sm">
        <i class="bi bi-trash3-fill"></i> Hapus Terpilih
    </button>
</div>

{{-- SEARCH + FILTER BAR --}}
<div class="mb-5 space-y-3">
    {{-- Search --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
            <i class="bi bi-search text-sm"></i>
        </div>
        <input type="text" id="search-soal" placeholder="Cari pertanyaan..."
               oninput="applyFilter()"
               class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all">
    </div>

    {{-- Filter Baris: Tipe + Status Penggunaan --}}
    <div class="flex flex-wrap items-center gap-2">
        {{-- Filter Tipe --}}
        <button onclick="setFilter('semua')" id="filter-semua"
                class="filter-btn active-filter px-4 py-1.5 rounded-lg text-xs font-bold transition-all border border-blue-200 bg-blue-50 text-blue-700">
            Semua ({{ $soals->count() }})
        </button>
        @foreach($tipeLabels as $tipe => $info)
            @php $count = $soals->where('tipe', $tipe)->count(); @endphp
            @if($count > 0)
            <button onclick="setFilter('{{ $tipe }}')" id="filter-{{ $tipe }}"
                    class="filter-btn px-4 py-1.5 rounded-lg text-xs font-bold transition-all border border-gray-200 bg-white text-gray-600 hover:border-blue-200 hover:text-blue-600">
                {{ $info['label'] }} ({{ $count }})
            </button>
            @endif
        @endforeach

        {{-- Separator --}}
        <span class="text-gray-300 font-bold">|</span>

        {{-- Filter Status Penggunaan --}}
        @php
            $usedCount   = $soals->filter(fn($s) => $s->soals_count > 0)->count();
            $unusedCount = $soals->filter(fn($s) => $s->soals_count == 0)->count();
        @endphp
        <button onclick="setUsageFilter('semua')" id="usage-filter-semua"
                class="usage-filter-btn usage-active px-4 py-1.5 rounded-lg text-xs font-bold transition-all border border-gray-200 bg-white text-gray-600">
            Semua
        </button>
        @if($usedCount > 0)
        <button onclick="setUsageFilter('digunakan')" id="usage-filter-digunakan"
                class="usage-filter-btn px-4 py-1.5 rounded-lg text-xs font-bold transition-all border border-gray-200 bg-white text-gray-600 hover:border-green-200 hover:text-green-600">
            <i class="bi bi-check2-circle mr-1"></i>Sudah Dipakai ({{ $usedCount }})
        </button>
        @endif
        @if($unusedCount > 0)
        <button onclick="setUsageFilter('belum')" id="usage-filter-belum"
                class="usage-filter-btn px-4 py-1.5 rounded-lg text-xs font-bold transition-all border border-gray-200 bg-white text-gray-600 hover:border-orange-200 hover:text-orange-600">
            <i class="bi bi-hourglass mr-1"></i>Belum Dipakai ({{ $unusedCount }})
        </button>
        @endif
    </div>
</div>

{{-- DAFTAR SOAL --}}
@if($soals->isEmpty())
<div class="bg-white border border-gray-100 rounded-2xl p-16 text-center">
    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="bi bi-archive text-4xl text-gray-300"></i>
    </div>
    <h3 class="font-bold text-gray-600 text-lg">Bank Soal Masih Kosong</h3>
    <p class="text-gray-400 text-sm mt-2 mb-6">Soal yang pernah disimpan di ujian akan otomatis masuk ke sini.<br>Atau klik tombol di bawah untuk tambah soal baru.</p>
    <button type="button" onclick="openTambahSoalModal()"
       class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
        <i class="bi bi-plus-lg"></i> Tambah Soal Pertama
    </button>
</div>
@else
<div class="grid grid-cols-1 gap-4" id="soal-grid">
    @foreach($soals as $soal)
    @php
        $info = $tipeLabels[$soal->tipe] ?? ['label' => $soal->tipe, 'color' => 'gray'];
        $colorMap = [
            'blue'    => 'bg-blue-50 text-blue-700 border-blue-100',
            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'purple'  => 'bg-purple-50 text-purple-700 border-purple-100',
            'amber'   => 'bg-amber-50 text-amber-700 border-amber-100',
            'gray'    => 'bg-gray-100 text-gray-600 border-gray-200',
        ];
        $colorClass = $colorMap[$info['color']] ?? $colorMap['gray'];
        $soalJson = json_encode([
            'id'            => $soal->id,
            'tipe'          => $soal->tipe,
            'pertanyaan'    => $soal->pertanyaan,
            'gambar'        => $soal->gambar ? asset('storage/'.$soal->gambar) : null,
            'opsi_a'        => $soal->opsi_a,
            'opsi_b'        => $soal->opsi_b,
            'opsi_c'        => $soal->opsi_c,
            'opsi_d'        => $soal->opsi_d,
            'gambar_a'      => $soal->gambar_a ? asset('storage/'.$soal->gambar_a) : null,
            'gambar_b'      => $soal->gambar_b ? asset('storage/'.$soal->gambar_b) : null,
            'gambar_c'      => $soal->gambar_c ? asset('storage/'.$soal->gambar_c) : null,
            'gambar_d'      => $soal->gambar_d ? asset('storage/'.$soal->gambar_d) : null,
            'kunci_jawaban' => $soal->kunci_jawaban,
            'data_soal'     => $soal->data_soal,
        ], JSON_HEX_APOS | JSON_HEX_QUOT);
    @endphp
    @php $soalUsed = $soal->soals_count > 0; @endphp
    <div class="soal-card bg-white border border-gray-100 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] hover:shadow-[0_4px_20px_rgba(0,0,0,0.07)] transition-all"
         data-tipe="{{ $soal->tipe }}"
         data-used="{{ $soalUsed ? '1' : '0' }}"
         data-soal="{{ $soalJson }}">

        {{-- Header Card (selalu terlihat) --}}
        <div class="flex items-start gap-4 p-5">
            <div class="flex flex-col items-center gap-2 shrink-0">
                @if($soalUsed)
                    <div class="w-5 h-5 flex items-center justify-center text-gray-300 cursor-not-allowed" title="Soal ini sudah digunakan di ujian dan tidak bisa dihapus">
                        <i class="bi bi-lock-fill text-xs"></i>
                    </div>
                @else
                    <input type="checkbox" class="bulk-cb w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer" value="{{ $soal->id }}">
                @endif
                <span class="text-[10px] font-bold {{ $soalUsed ? 'text-gray-300' : 'text-gray-400' }}">#{{ $loop->iteration }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $colorClass }}">
                        {{ $info['label'] }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $soal->created_at->format('d M Y') }}</span>
                    @if($soalUsed)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">
                            <i class="bi bi-check2-circle"></i> Digunakan di {{ $soal->soals_count }} ujian
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-50 text-orange-600 border border-orange-100">
                            <i class="bi bi-hourglass"></i> Belum dipakai
                        </span>
                    @endif
                </div>
                <p class="text-gray-800 font-medium text-sm leading-relaxed">
                    {{ $soal->pertanyaan }}
                </p>
                @if($soal->gambar)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $soal->gambar) }}" class="h-16 rounded-lg border border-gray-200 object-cover">
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 shrink-0">
                {{-- Toggle Jawaban --}}
                <button type="button"
                    onclick="toggleJawaban(this)"
                    class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-center border border-gray-200 transition-all"
                    title="Lihat Jawaban">
                    <i class="bi bi-chevron-down text-sm toggle-icon"></i>
                </button>
                {{-- Edit: hanya tampil jika soal BELUM digunakan di ujian manapun --}}
                @if(!$soalUsed)
                <button type="button"
                    onclick="openEditModal({{ $soal->id }})"
                    class="w-9 h-9 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center border border-blue-100 transition-all"
                    title="Edit Soal">
                    <i class="bi bi-pencil-fill text-xs"></i>
                </button>
                {{-- Hapus: juga hanya tampil jika soal BELUM digunakan di ujian manapun --}}
                {{-- Hapus: juga hanya tampil jika soal BELUM digunakan di ujian manapun --}}
                <button type="button"
                    onclick="openDeleteModal({{ $soal->id }})"
                    class="w-9 h-9 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center border border-red-100 transition-all"
                    title="Hapus dari Bank Soal">
                    <i class="bi bi-trash3-fill text-xs"></i>
                </button>
                @else
                {{-- Placeholder abu-abu jika sudah digunakan (tidak bisa diedit) --}}
                <div class="w-9 h-9 rounded-lg bg-gray-50 text-gray-300 flex items-center justify-center border border-gray-100 cursor-not-allowed" title="Soal sudah digunakan di ujian, tidak dapat diedit">
                    <i class="bi bi-lock-fill text-xs"></i>
                </div>
                @endif
            </div>
        </div>

        {{-- Panel Jawaban (hidden by default) --}}
        <div class="soal-jawaban hidden border-t border-gray-100 px-5 pb-5 pt-3">

                {{-- ── Opsi Jawaban & Kunci (semua tipe) ──────────────────── --}}
                @php
                    $dataSoal = is_array($soal->data_soal) ? $soal->data_soal : [];
                @endphp

                {{-- 1. Pilihan Ganda & Jawaban Ganda --}}
                @if(in_array($soal->tipe, ['pilihan_ganda', 'jawaban_ganda']) && $soal->opsi_a)
                    @php
                        $keys = $soal->tipe === 'pilihan_ganda'
                            ? [strtoupper($soal->kunci_jawaban ?? '')]
                            : array_map('strtoupper', array_map('trim', explode(',', $soal->kunci_jawaban ?? '')));
                    @endphp
                    <div class="mt-3 space-y-1.5">
                        @foreach(['a','b','c','d'] as $opsi)
                            @php
                                $teksOpsi = $soal->{'opsi_'.$opsi};
                                $gambarOpsi = $soal->{'gambar_'.$opsi};
                                $isKey = in_array(strtoupper($opsi), $keys);
                            @endphp
                            @if($teksOpsi)
                            <div class="flex items-start gap-2 p-2 rounded-lg border text-xs
                                {{ $isKey ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-100' }}">
                                <div class="shrink-0 w-6 h-6 rounded-md flex items-center justify-center font-bold text-[11px]
                                    {{ $isKey ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                                    {{ strtoupper($opsi) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="{{ $isKey ? 'text-green-800 font-semibold' : 'text-gray-700' }}">
                                        {{ $teksOpsi }}
                                    </span>
                                    @if($gambarOpsi)
                                        <img src="{{ asset('storage/'.$gambarOpsi) }}"
                                             class="mt-1 h-12 rounded border border-gray-200 object-contain bg-white">
                                    @endif
                                </div>
                                @if($isKey)
                                    <i class="bi bi-check-circle-fill text-green-500 shrink-0 mt-0.5"></i>
                                @endif
                            </div>
                            @endif
                        @endforeach
                        {{-- Label kunci jawaban --}}
                        <div class="pt-1 text-[10px] text-gray-400 font-bold uppercase tracking-wide">
                            Kunci: <span class="text-green-600">{{ $soal->tipe === 'pilihan_ganda' ? 'Jawaban '.$soal->kunci_jawaban : $soal->kunci_jawaban }}</span>
                        </div>
                    </div>

                {{-- 2. Benar / Salah --}}
                @elseif($soal->tipe === 'benar_salah' && !empty($dataSoal['pernyataan']))
                    <div class="mt-3 space-y-1.5">
                        @foreach($dataSoal['pernyataan'] as $i => $stmt)
                        @php $isBenar = strtoupper($stmt['correct'] ?? '') === 'TRUE'; @endphp
                        <div class="flex items-start gap-2 p-2 rounded-lg border bg-gray-50 border-gray-100 text-xs">
                            <span class="shrink-0 w-5 h-5 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-bold text-[10px]">{{ $i+1 }}</span>
                            <div class="flex-1 min-w-0">
                                <span class="text-gray-700">{{ $stmt['text'] ?? '-' }}</span>
                                @if(!empty($stmt['gambar']))
                                    <img src="{{ asset('storage/'.$stmt['gambar']) }}" class="mt-1 h-10 rounded border border-gray-200 object-contain bg-white">
                                @endif
                            </div>
                            <span class="shrink-0 px-2 py-0.5 rounded font-bold text-[10px]
                                {{ $isBenar ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                {{ $isBenar ? 'BENAR' : 'SALAH' }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                {{-- 3. Mencocokkan --}}
                @elseif($soal->tipe === 'menjodohkan' && !empty($dataSoal['matches']))
                    <div class="mt-3 space-y-1.5">
                        @foreach($dataSoal['matches'] as $match)
                        @php
                            $left  = $match['left']  ?? '-';
                            $right = $match['right'] ?? '-';
                        @endphp
                        <div class="flex items-center gap-2 text-xs">
                            <div class="flex-1 p-2 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 min-w-0">
                                {{ $left }}
                                @if(!empty($match['gambar_left']))
                                    <img src="{{ asset('storage/'.$match['gambar_left']) }}" class="mt-1 h-10 rounded border object-contain">
                                @endif
                            </div>
                            <i class="bi bi-arrow-right text-gray-400 shrink-0"></i>
                            <div class="flex-1 p-2 rounded-lg bg-green-50 border border-green-200 text-green-800 font-medium min-w-0">
                                {{ $right }}
                                @if(!empty($match['gambar_right']))
                                    <img src="{{ asset('storage/'.$match['gambar_right']) }}" class="mt-1 h-10 rounded border object-contain">
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

        </div>{{-- /panel jawaban --}}
    </div>{{-- /soal-card --}}
    @endforeach
</div>
@endif

{{-- FORM DELETE GLOBAL (Hidden) --}}
<form id="form-delete-global" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- =========================================================
     MODAL TAMBAH SOAL KE BANK
========================================================== --}}
<div id="modal-tambah-soal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeTambahSoalModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-3 md:p-6">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] flex flex-col">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-[Poppins-Bold] text-lg text-gray-800">
                        <i class="bi bi-plus-circle-fill text-blue-600 mr-2"></i>Tambah Soal ke Bank
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Mapel: <span class="font-bold text-blue-600">{{ $mapel->nama_mapel }}</span></p>
                </div>
                <button onclick="closeTambahSoalModal()" class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Scrollable Body --}}
            <div class="flex-1 overflow-y-auto p-6">
                <form action="{{ route('guru.mapel.bank_soal.store', $mapel->id) }}" method="POST" enctype="multipart/form-data" id="form-bank-soal" novalidate>
                    @csrf

                    {{-- ── Pilih Tipe --}}
                    <div class="flex items-center gap-3 mb-5">
                        <label class="text-xs font-bold text-gray-500 uppercase shrink-0">Tipe Soal:</label>
                        <select id="modal-tipe-soal" name="tipe" class="bg-white border border-gray-200 text-gray-700 text-sm font-bold py-2 px-4 rounded-xl focus:outline-none focus:border-blue-500 uppercase cursor-pointer" onchange="modalUpdateUI(this.value)">
                            <option value="pilihan_ganda">Pilihan Ganda</option>
                            <option value="benar_salah">Benar / Salah</option>
                            <option value="jawaban_ganda">Pilih Banyak Jawaban</option>
                            <option value="menjodohkan">Mencocokkan</option>
                        </select>
                    </div>

                    {{-- ── Layout Kiri + Kanan --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        {{-- Kiri: Pertanyaan & Jawaban --}}
                        <div class="lg:col-span-8 space-y-4">

                            {{-- Pertanyaan --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pertanyaan</label>
                                <textarea name="pertanyaan" id="modal-pertanyaan"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800"
                                    rows="3" placeholder="Tulis pertanyaan di sini..."></textarea>
                            </div>

                            {{-- Container Jawaban Dinamis --}}
                            <div id="modal-answers-container" class="space-y-4">

                                {{-- 1. PILIHAN GANDA --}}
                                <div class="modal-type-section modal-type-pilihan_ganda space-y-3">
                                    @foreach(['a','b','c','d'] as $opsi)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">{{ $opsi }}</div>
                                        <input type="text" name="opsi_{{ $opsi }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                                        {{-- Tombol Gambar Opsi (gambar menggantikan ikon) --}}
                                        <label class="shrink-0 cursor-pointer w-10 h-10 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar Opsi">
                                            <i class="bi bi-image text-gray-400 modal-opsi-icon"></i>
                                            <img src="" class="modal-opsi-preview hidden w-full h-full object-cover rounded-lg">
                                            <input type="file" name="gambar_{{ $opsi }}" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
                                        </label>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- 2. BENAR / SALAH --}}
                                <div class="modal-type-section modal-type-benar_salah hidden space-y-4">
                                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                                        <i class="bi bi-info-circle mr-1"></i> Masukkan pernyataan dan tentukan apakah Benar atau Salah.
                                    </div>
                                    <div id="modal-tf-container" class="space-y-2"></div>
                                    <button type="button" onclick="modalAddTf()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Pernyataan
                                    </button>
                                </div>

                                <!-- 3. JAWABAN GANDA Dinamis -->
                                <div class="modal-type-section modal-type-jawaban_ganda hidden space-y-3">
                                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                                        <i class="bi bi-info-circle mr-1"></i> Centang kotak di kanan untuk menandai jawaban benar (bisa lebih dari satu). Minimal 2 opsi.
                                    </div>
                                    <div id="modal-jg-container" class="space-y-2"></div>
                                    <button type="button" onclick="modalAddJg()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Opsi Jawaban
                                    </button>
                                </div>

                                {{-- 4. MENCOCOKKAN --}}
                                <div class="modal-type-section modal-type-menjodohkan hidden space-y-4">
                                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                                        <i class="bi bi-info-circle mr-1"></i> Buat pasangan pertanyaan (kiri) dan jawaban kanan yang sesuai.
                                    </div>
                                    <div id="modal-matches-container" class="space-y-2"></div>
                                    <button type="button" onclick="modalAddMatch()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Pasangan
                                    </button>
                                </div>

                            </div>{{-- /answers-container --}}
                        </div>{{-- /Kiri --}}

                        {{-- Kanan: Gambar Soal & Kunci --}}
                        <div class="lg:col-span-4 space-y-4">

                            {{-- Upload Gambar Soal --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Soal</label>
                                <div id="modal-upload-box" class="relative w-full h-40 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden" onclick="document.getElementById('modal-gambar-input').click()">
                                    <img id="modal-gambar-preview" class="absolute inset-0 w-full h-full object-contain bg-white p-2" style="display:none;">
                                    <div id="modal-upload-text" class="text-center p-4">
                                        <i class="bi bi-cloud-arrow-up-fill text-3xl text-gray-300"></i>
                                        <p class="text-xs text-gray-500 mt-2 font-medium">Klik untuk Upload</p>
                                    </div>
                                    <input type="file" name="gambar" id="modal-gambar-input" class="hidden" accept="image/*" onchange="modalPreviewGambar(this)">
                                </div>
                            </div>

                            {{-- Kunci Jawaban Pilihan Ganda --}}
                            <div id="modal-key-pg" class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Kunci Jawaban</label>
                                <div class="relative">
                                    <select name="kunci_jawaban" id="modal-kunci" class="w-full px-3 py-2 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-sm font-bold text-blue-700 appearance-none cursor-pointer">
                                        <option value="" disabled selected>-- Pilih Kunci --</option>
                                        @foreach(['A','B','C','D'] as $huruf)
                                        <option value="{{ $huruf }}">Jawaban {{ $huruf }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-500">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /Kanan --}}
                    </div>{{-- /grid --}}

                    {{-- Footer dalam form --}}
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-100">
                        <button type="button" onclick="closeTambahSoalModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                            <i class="bi bi-plus-circle-fill"></i> Simpan ke Bank Soal
                        </button>
                    </div>

                </form>
            </div>{{-- /scrollable body --}}
        </div>
    </div>
</div>

{{-- TEMPLATE: Match Item --}}
<template id="modal-match-template">
    <div class="modal-match-item flex items-center gap-2">
        <div class="flex-1 flex gap-2">
            <input type="text" name="matches[MIDX][left]" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-blue-500 outline-none" placeholder="Pertanyaan / Pernyataan">
            <label class="shrink-0 cursor-pointer w-10 h-[38px] rounded border border-gray-200 flex items-center justify-center overflow-hidden bg-white" title="Upload Gambar">
                <i class="bi bi-image text-gray-400 modal-opsi-icon relative top-px"></i>
                <img src="" class="modal-opsi-preview hidden w-full h-full object-cover">
                <input type="file" name="matches[MIDX][gambar_left]" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
                <input type="hidden" name="matches[MIDX][existing_gambar_left]" class="modal-existing-img">
            </label>
        </div>
        <div class="text-gray-400"><i class="bi bi-arrow-right"></i></div>
        <div class="flex-1 flex gap-2">
            <input type="text" name="matches[MIDX][right]" class="w-full px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm focus:bg-white focus:border-green-500 outline-none" placeholder="Pasangan Benar">
            <label class="shrink-0 cursor-pointer w-10 h-[38px] rounded border border-gray-200 flex items-center justify-center overflow-hidden bg-white" title="Upload Gambar">
                <i class="bi bi-image text-gray-400 modal-opsi-icon relative top-px"></i>
                <img src="" class="modal-opsi-preview hidden w-full h-full object-cover">
                <input type="file" name="matches[MIDX][gambar_right]" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
                <input type="hidden" name="matches[MIDX][existing_gambar_right]" class="modal-existing-img">
            </label>
        </div>
        <button type="button" onclick="this.closest('.modal-match-item').remove()" class="text-red-400 hover:text-red-600">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>
</template>

{{-- TEMPLATE: TF Item --}}
<template id="modal-tf-template">
    <div class="modal-tf-item flex items-center gap-2">
        <div class="flex-1 flex gap-2">
            <input type="text" name="pernyataan[TIDX][text]" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-blue-500 outline-none" placeholder="Tulis Pernyataan...">
            <label class="shrink-0 cursor-pointer w-10 h-[38px] rounded border border-gray-200 flex items-center justify-center overflow-hidden bg-white" title="Upload Gambar">
                <i class="bi bi-image text-gray-400 modal-opsi-icon relative top-px"></i>
                <img src="" class="modal-opsi-preview hidden w-full h-full object-cover">
                <input type="file" name="pernyataan[TIDX][gambar]" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
                <input type="hidden" name="pernyataan[TIDX][existing_gambar]" class="modal-existing-img">
            </label>
        </div>
        <div class="flex items-center gap-1 border border-gray-200 rounded-lg p-1 bg-white">
            <label class="cursor-pointer px-3 py-1.5 rounded hover:bg-green-50 has-[:checked]:bg-green-100 has-[:checked]:text-green-700 transition-colors">
                <input type="radio" name="pernyataan[TIDX][correct]" value="TRUE" class="hidden">
                <span class="text-xs font-bold">BENAR</span>
            </label>
            <div class="w-px h-4 bg-gray-200"></div>
            <label class="cursor-pointer px-3 py-1.5 rounded hover:bg-red-50 has-[:checked]:bg-red-100 has-[:checked]:text-red-700 transition-colors">
                <input type="radio" name="pernyataan[TIDX][correct]" value="FALSE" class="hidden">
                <span class="text-xs font-bold">SALAH</span>
            </label>
        </div>
        <button type="button" onclick="this.closest('.modal-tf-item').remove()" class="text-red-400 hover:text-red-600">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>
</template>

{{-- TEMPLATE: Jawaban Ganda (Pilih Banyak) Dinamis --}}
<template id="modal-jg-template">
    <div class="modal-jg-item flex items-center gap-3">
        <div class="modal-jg-label w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">
            -
        </div>
        <input type="text" name="jg_options[JIDX][text]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Teks Opsi">
        
        <label class="shrink-0 cursor-pointer w-10 h-10 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar Opsi">
            <i class="bi bi-image text-gray-400 modal-opsi-icon"></i>
            <img src="" class="modal-opsi-preview hidden w-full h-full object-cover rounded-lg">
            <input type="file" name="jg_options[JIDX][gambar]" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
            <input type="hidden" name="jg_options[JIDX][existing_gambar]" class="modal-existing-img">
        </label>
        
        <div class="shrink-0">
            <input type="hidden" name="jg_options[JIDX][correct]" value="0">
            <input type="checkbox" name="jg_options[JIDX][correct]" value="1" class="w-6 h-6 text-blue-600 rounded cursor-pointer border-gray-300" title="Centang jika jawaban ini benar">
        </div>

        <button type="button" onclick="modalRemoveJg(this)" class="text-red-400 hover:text-red-600">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>
</template>


{{-- =========================================================
     MODAL EDIT SOAL
========================================================== --}}
<div id="modal-edit-soal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-3 md:p-6">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] flex flex-col">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-[Poppins-Bold] text-lg text-gray-800">
                        <i class="bi bi-pencil-square text-blue-600 mr-2"></i>Edit Soal Bank
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Mapel: <span class="font-bold text-blue-600">{{ $mapel->nama_mapel }}</span></p>
                </div>
                <button onclick="closeEditModal()" class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6">
                <form id="form-edit-soal" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Tipe --}}
                    <div class="flex items-center gap-3 mb-5">
                        <label class="text-xs font-bold text-gray-500 uppercase shrink-0">Tipe Soal:</label>
                        <select id="edit-tipe-soal" name="tipe"
                            class="bg-white border border-gray-200 text-gray-700 text-sm font-bold py-2 px-4 rounded-xl focus:outline-none focus:border-blue-500 uppercase cursor-pointer"
                            onchange="editUpdateUI(this.value)">
                            <option value="pilihan_ganda">Pilihan Ganda</option>
                            <option value="benar_salah">Benar / Salah</option>
                            <option value="jawaban_ganda">Pilih Banyak Jawaban</option>
                            <option value="menjodohkan">Mencocokkan</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        {{-- Kiri --}}
                        <div class="lg:col-span-8 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pertanyaan</label>
                                <textarea name="pertanyaan" id="edit-pertanyaan"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800"
                                    rows="3"></textarea>
                            </div>

                            <div id="edit-answers-container" class="space-y-4">
                                {{-- 1. PG --}}
                                <div class="edit-type-section edit-type-pilihan_ganda space-y-3">
                                    @foreach(['a','b','c','d'] as $opsi)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">{{ $opsi }}</div>
                                        <input type="text" name="opsi_{{ $opsi }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                                        <label class="shrink-0 cursor-pointer w-10 h-10 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar Opsi">
                                            <i class="bi bi-image text-gray-400 modal-opsi-icon"></i>
                                            <img src="" class="modal-opsi-preview hidden w-full h-full object-cover rounded-lg">
                                            <input type="file" name="gambar_{{ $opsi }}" class="hidden" accept="image/*" onchange="modalPreviewOpsi(this)">
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                {{-- 2. TF --}}
                                <div class="edit-type-section edit-type-benar_salah hidden space-y-4">
                                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700"><i class="bi bi-info-circle mr-1"></i> Masukkan pernyataan dan tentukan Benar atau Salah.</div>
                                    <div id="edit-tf-container" class="space-y-2"></div>
                                    <button type="button" onclick="editAddTf()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Pernyataan
                                    </button>
                                </div>
                                <!-- 3. JAWABAN GANDA Dinamis -->
                                <div class="edit-type-section edit-type-jawaban_ganda hidden space-y-3">
                                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                                        <i class="bi bi-info-circle mr-1"></i> Centang kotak di kanan untuk menandai jawaban benar (bisa lebih dari satu). Minimal 2 opsi.
                                    </div>
                                    <div id="edit-jg-container" class="space-y-2"></div>
                                    <button type="button" onclick="editAddJg()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Opsi Jawaban
                                    </button>
                                </div>
                                {{-- 4. Menjodohkan --}}
                                <div class="edit-type-section edit-type-menjodohkan hidden space-y-4">
                                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700"><i class="bi bi-info-circle mr-1"></i> Buat pasangan pertanyaan dan jawaban.</div>
                                    <div id="edit-matches-container" class="space-y-2"></div>
                                    <button type="button" onclick="editAddMatch()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-1">
                                        <i class="bi bi-plus-circle-fill"></i> Tambah Pasangan
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan --}}
                        <div class="lg:col-span-4 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Soal</label>
                                <div id="edit-upload-box" class="relative w-full h-40 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden" onclick="document.getElementById('edit-gambar-input').click()">
                                    <img id="edit-gambar-preview" class="absolute inset-0 w-full h-full object-contain bg-white p-2" style="display:none;">
                                    <div id="edit-upload-text" class="text-center p-4">
                                        <i class="bi bi-cloud-arrow-up-fill text-3xl text-gray-300"></i>
                                        <p class="text-xs text-gray-500 mt-2 font-medium">Klik untuk Upload / Ganti</p>
                                    </div>
                                    <input type="file" name="gambar" id="edit-gambar-input" class="hidden" accept="image/*" onchange="editPreviewGambar(this)">
                                </div>
                            </div>
                            <div id="edit-key-pg" class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Kunci Jawaban</label>
                                <div class="relative">
                                    <select name="kunci_jawaban" id="edit-kunci" class="w-full px-3 py-2 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-sm font-bold text-blue-700 appearance-none cursor-pointer">
                                        <option value="" disabled>-- Pilih Kunci --</option>
                                        @foreach(['A','B','C','D'] as $huruf)
                                        <option value="{{ $huruf }}">Jawaban {{ $huruf }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-500"><i class="bi bi-check-circle-fill"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-100">
                        <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
// =========================================================================
// FUNGSI PENGENDALI MODAL CUSTOM UNIVERSAL
// =========================================================================
function showNotificationModal(title, message, type = 'error', callback = null) {
    const modal = document.getElementById('modal-notification');
    const content = document.getElementById('notification-modal-content');
    const iconContainer = document.getElementById('notif-icon-container');
    const icon = document.getElementById('notif-icon');
    const btn = document.getElementById('notif-btn');
    
    document.getElementById('notif-title').innerText = title;
    document.getElementById('notif-message').innerText = message;

    iconContainer.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border-[6px]';
    icon.className = 'text-2xl';
    btn.className = 'px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2';

    if (type === 'error') {
        iconContainer.classList.add('bg-red-50', 'border-red-100');
        icon.classList.add('bi', 'bi-exclamation-circle-fill', 'text-red-500');
        btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-200');
    } else if (type === 'success') {
        iconContainer.classList.add('bg-green-50', 'border-green-100');
        icon.classList.add('bi', 'bi-check-circle-fill', 'text-green-500');
        btn.classList.add('bg-green-600', 'hover:bg-green-700', 'shadow-green-200');
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    btn.onclick = () => {
        closeNotificationModal();
        if (callback) setTimeout(callback, 300);
    };
}

function closeNotificationModal() {
    const modal = document.getElementById('modal-notification');
    const content = document.getElementById('notification-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function showConfirmModal(title, message, onConfirm, confirmText = 'Ya', confirmBg = 'bg-blue-600', confirmHover = 'hover:bg-blue-700') {
    const modal = document.getElementById('modal-custom-confirm');
    const content = document.getElementById('confirm-modal-content');
    const btn = document.getElementById('confirm-action-btn');

    document.getElementById('confirm-title').innerText = title;
    document.getElementById('confirm-message').innerText = message;
    btn.innerText = confirmText;
    btn.className = `px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2 ${confirmBg} ${confirmHover}`;

    btn.onclick = () => {
        closeConfirmModal();
        if (onConfirm) onConfirm();
    };

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeConfirmModal() {
    const modal = document.getElementById('modal-custom-confirm');
    const content = document.getElementById('confirm-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function showLoadingModal() {
    const modal = document.getElementById('modal-loading');
    const content = document.getElementById('loading-modal-content');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function hideLoadingModal() {
    const modal = document.getElementById('modal-loading');
    const content = document.getElementById('loading-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
// ── MODAL OPEN / CLOSE ────────────────────────────────────
function openTambahSoalModal() {
    document.getElementById('modal-tambah-soal').classList.remove('hidden');
    // Reset form
    document.getElementById('form-bank-soal').reset();
    document.getElementById('modal-tf-container').innerHTML = '';
    document.getElementById('modal-matches-container').innerHTML = '';
    document.getElementById('modal-gambar-preview').style.display = 'none';
    document.getElementById('modal-upload-text').style.display = '';
    modalUpdateUI('pilihan_ganda');
    document.getElementById('modal-tipe-soal').value = 'pilihan_ganda';
    // Default 2 match + 1 TF items
    modalAddMatch(); modalAddMatch();
    modalAddTf();
}

function closeTambahSoalModal() {
    document.getElementById('modal-tambah-soal').classList.add('hidden');
}

// ── UI UPDATE BERDASARKAN TIPE ────────────────────────────
function modalUpdateUI(tipe) {
    document.querySelectorAll('.modal-type-section').forEach(el => el.classList.add('hidden'));
    const active = document.querySelector(`.modal-type-${tipe}`);
    if (active) active.classList.remove('hidden');

    // Kunci Jawaban (hanya tampil untuk PG)
    const keyPG = document.getElementById('modal-key-pg');
    if (keyPG) keyPG.classList.toggle('hidden', tipe !== 'pilihan_ganda');
}

// ── GAMBAR SOAL PREVIEW ───────────────────────────────────
function modalPreviewGambar(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('Gambar terlalu besar! Maksimal 2MB.'); input.value = ''; return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById('modal-gambar-preview');
            prev.src = e.target.result;
            prev.style.display = 'block';
            document.getElementById('modal-upload-text').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── GAMBAR OPSI PREVIEW ───────────────────────────────────
// Gambar menggantikan ikon di dalam tombol label
function modalPreviewOpsi(input) {
    const label   = input.closest('label');
    const preview = label ? label.querySelector('.modal-opsi-preview') : null;
    const icon    = label ? label.querySelector('.modal-opsi-icon') : null;

    if (!preview) return;

    if (input.files && input.files[0]) {
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('Gambar terlalu besar! Maksimal 2MB.'); input.value = ''; return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            // Sembunyikan ikon, tampilkan gambar
            if (icon) icon.classList.add('hidden');
            label.classList.add('border-blue-400', 'p-0');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        if (icon) icon.classList.remove('hidden');
        label.classList.remove('border-blue-400', 'p-0');
    }
}

// ── ADD MATCHING PAIR ─────────────────────────────────────
function modalAddMatch() {
    const container = document.getElementById('modal-matches-container');
    const template  = document.getElementById('modal-match-template');
    const idx = Date.now() + Math.random().toString(36).substr(2, 5);
    const clone = template.content.cloneNode(true);
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/MIDX/g, idx);
    });
    container.appendChild(clone);
}

// ── ADD TF STATEMENT ──────────────────────────────────────
function modalAddTf() {
    const container = document.getElementById('modal-tf-container');
    const template  = document.getElementById('modal-tf-template');
    const idx = Date.now() + Math.random().toString(36).substr(2, 5);
    const clone = template.content.cloneNode(true);
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/TIDX/g, idx);
    });
    container.appendChild(clone);
}

// ── ADD JG OPTION ─────────────────────────────────────────
function modalAddJg(text='', correct='0', gambarUrl=null, gambarPath='') {
    const container = document.getElementById('modal-jg-container');
    const template  = document.getElementById('modal-jg-template');
    const idx = Date.now() + Math.random().toString(36).substr(2, 5);
    const clone = template.content.cloneNode(true);
    
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/JIDX/g, idx);
    });
    
    // Fill values if edit
    if (text) {
        const textInp = clone.querySelector('input[type="text"]');
        if(textInp) textInp.value = text;
    }
    if (correct && correct != '0') {
        const checkInp = clone.querySelector('input[type="checkbox"]');
        if(checkInp) checkInp.checked = true;
    }
    if (gambarUrl) {
        const previewImg = clone.querySelector('.modal-opsi-preview');
        const icon       = clone.querySelector('.modal-opsi-icon');
        const hiddenImgInput = clone.querySelector('.modal-existing-img');
        if(previewImg) {
            previewImg.src = gambarUrl;
            previewImg.classList.remove('hidden');
        }
        if(icon) icon.classList.add('hidden');
        if(hiddenImgInput) hiddenImgInput.value = gambarPath;
    }

    container.appendChild(clone);
    reindexJgLabels('modal-jg-container');
}

function modalRemoveJg(btn) {
    const containerId = btn.closest('#modal-jg-container, #edit-jg-container').id;
    btn.closest('.modal-jg-item').remove();
    reindexJgLabels(containerId);
}

function reindexJgLabels(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const items = container.querySelectorAll('.modal-jg-item');
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    items.forEach((item, index) => {
        const label = item.querySelector('.modal-jg-label');
        if(label) {
            label.textContent = alphabet[index] || ('O'+index);
        }
    });
}

// ── VALIDASI & SUBMIT ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-bank-soal');
    form.addEventListener('submit', function(e) {
        const tipe = document.getElementById('modal-tipe-soal').value;

        // PG: harus ada kunci
        if (tipe === 'pilihan_ganda') {
            const kunci = document.getElementById('modal-kunci').value;
            if (!kunci) {
                e.preventDefault();
                showNotificationModal('Pilih Kunci Jawaban terlebih dahulu!');
                return;
            }
        }
        // Jawaban Ganda: minimal 1 centang form
        if (tipe === 'jawaban_ganda') {
            const checked = form.querySelectorAll('input[type="checkbox"]:checked');
            // karena ada input hidden bernilai 0 dan checked bernilai 1.
            let checkedCount = 0;
            checked.forEach((c) => {
                if(c.value == '1') checkedCount++;
            });
            const totalOptions = form.querySelectorAll('.modal-jg-item').length;
            
            if (totalOptions < 2) {
                e.preventDefault();
                showNotificationModal('Minimal harus ada 2 opsi jawaban!');
                return;
            }
            if (checkedCount === 0) {
                e.preventDefault();
                showNotificationModal('Centang minimal satu jawaban benar!');
                return;
            }
            
            // Validasi teks/gambar
            let anyEmpty = false;
            form.querySelectorAll('.modal-jg-item').forEach(item => {
                const text = item.querySelector('input[type="text"]').value.trim();
                const fileInp = item.querySelector('input[type="file"]').files.length;
                const existing = item.querySelector('.modal-existing-img')?.value;
                if (!text && fileInp === 0 && !existing) anyEmpty = true;
            });
            if (anyEmpty) {
                e.preventDefault();
                showNotificationModal('Semua opsi jawaban dinamis harus diisi teks atau gambarnya!');
                return;
            }
        }
        // Benar Salah: minimal 1 pernyataan
        if (tipe === 'benar_salah') {
            const items = document.getElementById('modal-tf-container').querySelectorAll('.modal-tf-item').length;
            if (items === 0) {
                e.preventDefault();
                showNotificationModal('Tambahkan minimal satu pernyataan Benar/Salah!');
                return;
            }
        }
        // Mencocokkan: minimal 1 pasangan
        if (tipe === 'menjodohkan') {
            const items = document.getElementById('modal-matches-container').querySelectorAll('.modal-match-item').length;
            if (items === 0) {
                e.preventDefault();
                showNotificationModal('Tambahkan minimal satu pasangan!');
                return;
            }
        }
    });

    // Init UI
    modalUpdateUI('pilihan_ganda');
});

// ── FILTER & SEARCH ──────────────────────────────────────
let _activeFilter      = 'semua';
let _activeUsageFilter = 'semua'; // 'semua' | 'digunakan' | 'belum'

function setFilter(tipe) {
    _activeFilter = tipe;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active-filter', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
        btn.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
    });
    const activeBtn = document.getElementById('filter-' + tipe);
    if (activeBtn) {
        activeBtn.classList.add('active-filter', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
        activeBtn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
    }
    applyFilter();
}

function setUsageFilter(status) {
    _activeUsageFilter = status;
    document.querySelectorAll('.usage-filter-btn').forEach(btn => {
        btn.classList.remove('usage-active', 'bg-green-50', 'text-green-700', 'border-green-200',
                             'bg-orange-50', 'text-orange-700', 'border-orange-200',
                             'bg-blue-50', 'text-blue-700', 'border-blue-200');
        btn.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
    });
    const activeBtnId = status === 'digunakan' ? 'usage-filter-digunakan'
                      : status === 'belum'      ? 'usage-filter-belum'
                      :                           'usage-filter-semua';
    const colorClass = status === 'digunakan' ? ['bg-green-50', 'text-green-700', 'border-green-200']
                     : status === 'belum'      ? ['bg-orange-50', 'text-orange-700', 'border-orange-200']
                     :                           ['bg-blue-50', 'text-blue-700', 'border-blue-200'];
    const activeBtn = document.getElementById(activeBtnId);
    if (activeBtn) {
        activeBtn.classList.add('usage-active', ...colorClass);
        activeBtn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
    }
    applyFilter();
}

function applyFilter() {
    const keyword = (document.getElementById('search-soal')?.value || '').toLowerCase().trim();
    let visibleCount = 0;
    document.querySelectorAll('#soal-grid .soal-card').forEach(card => {
        const tipe       = card.getAttribute('data-tipe') || '';
        const used       = card.getAttribute('data-used') || '0'; // '1' = digunakan
        const pertanyaan = card.querySelector('p.text-gray-800')?.textContent.toLowerCase() || '';

        const matchFilter = (_activeFilter === 'semua' || tipe === _activeFilter);
        const matchUsage  = (_activeUsageFilter === 'semua')
                         || (_activeUsageFilter === 'digunakan' && used === '1')
                         || (_activeUsageFilter === 'belum'     && used === '0');
        const matchSearch = !keyword || pertanyaan.includes(keyword);

        const show = matchFilter && matchUsage && matchSearch;
        card.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    // Tampilkan pesan kosong jika tidak ada hasil
    let emptyMsg = document.getElementById('filter-empty-msg');
    if (!emptyMsg) {
        emptyMsg = document.createElement('div');
        emptyMsg.id = 'filter-empty-msg';
        emptyMsg.className = 'col-span-1 py-12 flex flex-col items-center justify-center text-center';
        emptyMsg.innerHTML = '<i class="bi bi-search text-4xl text-gray-300 mb-3"></i><p class="text-gray-500 font-medium">Tidak ada soal yang cocok.</p>';
        document.getElementById('soal-grid')?.appendChild(emptyMsg);
    }
    emptyMsg.style.display = visibleCount === 0 ? '' : 'none';

    // Toggle Bulk Action Bar based on Usage Filter
    const bulkBar = document.getElementById('bulk-action-bar');
    if (bulkBar) {
        if (_activeUsageFilter === 'belum') {
            bulkBar.classList.remove('hidden');
            bulkBar.classList.add('flex');
        } else {
            bulkBar.classList.add('hidden');
            bulkBar.classList.remove('flex');
            // Reset selection when leaving "belum" filter
            document.querySelectorAll('.bulk-cb').forEach(cb => cb.checked = false);
            updateBulkUI();
        }
    }
}

// Alias untuk backward compatibility (tombol filter lama memanggil filterSoal)
function filterSoal(tipe) { setFilter(tipe); }

// ── TOGGLE JAWABAN ────────────────────────────────────────
function toggleJawaban(btn) {
    const card   = btn.closest('.soal-card');
    const panel  = card.querySelector('.soal-jawaban');
    const icon   = btn.querySelector('.toggle-icon');
    const isOpen = !panel.classList.contains('hidden');
    panel.classList.toggle('hidden', isOpen);
    icon.classList.toggle('bi-chevron-down', isOpen);
    icon.classList.toggle('bi-chevron-up',   !isOpen);
    btn.classList.toggle('bg-blue-50',     !isOpen);
    btn.classList.toggle('text-blue-600',  !isOpen);
    btn.classList.toggle('border-blue-200',!isOpen);
    btn.classList.toggle('bg-gray-100',     isOpen);
    btn.classList.toggle('text-gray-500',   isOpen);
    btn.classList.toggle('border-gray-200', isOpen);
}

// ── EDIT MODAL OPEN / CLOSE ───────────────────────────────
const EDIT_URL_BASE = '{{ url("guru/mapel/{mapel}/bank-soal") }}'.replace('{mapel}', {{ $mapel->id }});

function openEditModal(soalId) {
    // Ambil data soal dari attribute card
    const card = document.querySelector(`[data-soal]`);
    let soalData = null;
    document.querySelectorAll('.soal-card').forEach(c => {
        try {
            const d = JSON.parse(c.getAttribute('data-soal'));
            if (d && d.id == soalId) soalData = d;
        } catch(e) {}
    });
    if (!soalData) return;

    // Set form action
    const form = document.getElementById('form-edit-soal');
    form.action = `${EDIT_URL_BASE}/${soalId}`;

    // Set tipe & pertanyaan
    document.getElementById('edit-tipe-soal').value  = soalData.tipe;
    document.getElementById('edit-pertanyaan').value = soalData.pertanyaan;

    // Gambar soal
    const prev = document.getElementById('edit-gambar-preview');
    const upText = document.getElementById('edit-upload-text');
    if (soalData.gambar) {
        prev.src = soalData.gambar;
        prev.style.display = 'block';
        upText.style.display = 'none';
    } else {
        prev.src = ''; prev.style.display = 'none';
        upText.style.display = '';
    }
    document.getElementById('edit-gambar-input').value = '';

    // Reset containers
    document.getElementById('edit-tf-container').innerHTML = '';
    document.getElementById('edit-matches-container').innerHTML = '';
    document.getElementById('edit-jg-container').innerHTML = '';
    // Reset semua opsi image buttons
    form.querySelectorAll('.modal-opsi-preview').forEach(img => { img.src=''; img.classList.add('hidden'); });
    form.querySelectorAll('.modal-opsi-icon').forEach(ic => ic.classList.remove('hidden'));

    // Fill data berdasarkan tipe
    if (soalData.tipe === 'pilihan_ganda') {
        ['a','b','c','d'].forEach(o => {
            const inp = form.querySelector(`input[name="opsi_${o}"]`);
            if (inp) inp.value = soalData[`opsi_${o}`] || '';
            // Preview gambar opsi jika ada
            const labels = form.querySelectorAll('.edit-type-pilihan_ganda label');
            labels.forEach(lbl => {
                const fileInp = lbl.querySelector('input[type="file"]');
                if (fileInp && fileInp.name === `gambar_${o}` && soalData[`gambar_${o}`]) {
                    const previewImg = lbl.querySelector('.modal-opsi-preview');
                    const icon       = lbl.querySelector('.modal-opsi-icon');
                    if (previewImg) { previewImg.src = soalData[`gambar_${o}`]; previewImg.classList.remove('hidden'); }
                    if (icon) icon.classList.add('hidden');
                }
            });
        });
        const kunci = form.querySelector('select[name="kunci_jawaban"]');
        if (kunci) kunci.value = soalData.kunci_jawaban || '';
    } else if (soalData.tipe === 'jawaban_ganda') {
        const opsiDinamic = soalData.data_soal && soalData.data_soal.options ? soalData.data_soal.options : [];
        if (opsiDinamic.length > 0) {
            opsiDinamic.forEach(opt => {
                const keys = (soalData.kunci_jawaban || '').split(',').map(k => k.trim().toUpperCase());
                const correct = keys.includes(opt.id) ? '1' : '0';
                const gambarUrl = opt.gambar ? (opt.gambar.startsWith('http') ? opt.gambar : `{{ asset('storage') }}/${opt.gambar}`) : null;
                editAddJg(opt.text, correct, gambarUrl, opt.gambar);
            });
        } else {
            // Backward compatibility
            ['A','B','C','D'].forEach(o => {
                const text = soalData[`opsi_${o.toLowerCase()}`] || '';
                const keys = (soalData.kunci_jawaban || '').split(',').map(k => k.trim().toUpperCase());
                const correct = keys.includes(o) ? '1' : '0';
                const gambar = soalData[`gambar_${o.toLowerCase()}`];
                const gambarUrl = gambar ? (gambar.startsWith('http') ? gambar : `{{ asset('storage') }}/${gambar}`) : null;
                if (text || gambarUrl || correct == '1') {
                    editAddJg(text, correct, gambarUrl, gambar);
                }
            });
        }
        // Minimal 2
        const container = document.getElementById('edit-jg-container');
        while (container.querySelectorAll('.modal-jg-item').length < 2) {
            editAddJg();
        }
    } else if (soalData.tipe === 'benar_salah' && soalData.data_soal && soalData.data_soal.pernyataan) {
        soalData.data_soal.pernyataan.forEach(stmt => {
            const gambarUrl = stmt.gambar ? (stmt.gambar.startsWith('http') ? stmt.gambar : `{{ asset('storage') }}/${stmt.gambar}`) : null;
            editAddTf(stmt.text, stmt.correct, gambarUrl, stmt.gambar);
        });
    } else if (soalData.tipe === 'menjodohkan' && soalData.data_soal && soalData.data_soal.matches) {
        soalData.data_soal.matches.forEach(m => {
            const leftGambarUrl = m.gambar_left ? (m.gambar_left.startsWith('http') ? m.gambar_left : `{{ asset('storage') }}/${m.gambar_left}`) : null;
            const rightGambarUrl = m.gambar_right ? (m.gambar_right.startsWith('http') ? m.gambar_right : `{{ asset('storage') }}/${m.gambar_right}`) : null;
            editAddMatch(m.left, m.right, leftGambarUrl, m.gambar_left, rightGambarUrl, m.gambar_right);
        });
    }

    editUpdateUI(soalData.tipe);
    document.getElementById('modal-edit-soal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('modal-edit-soal').classList.add('hidden');
}

// ── DELETE MODAL OPEN / CLOSE ─────────────────────────────
const DELETE_URL_BASE = '{{ url("guru/mapel/{mapel}/bank-soal") }}'.replace('{mapel}', {{ $mapel->id }});

function openDeleteModal(soalId) {
    showConfirmModal(
        'Hapus Soal Permanen?', 
        'Tindakan ini tidak dapat dibatalkan. Soal beserta file gambarnya akan dihapus sepenuhnya dari server.', 
        function() {
            const form = document.getElementById('form-delete-global');
            form.action = `${DELETE_URL_BASE}/${soalId}`;
            form.submit();
        }, 
        'Hapus Soal', 
        'bg-red-600', 
        'hover:bg-red-700'
    );
}

function editUpdateUI(tipe) {
    document.querySelectorAll('.edit-type-section').forEach(el => el.classList.add('hidden'));
    const active = document.querySelector(`.edit-type-${tipe}`);
    if (active) active.classList.remove('hidden');
    const keyPG = document.getElementById('edit-key-pg');
    if (keyPG) keyPG.classList.toggle('hidden', tipe !== 'pilihan_ganda');
}

function editPreviewGambar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById('edit-gambar-preview');
            prev.src = e.target.result; prev.style.display = 'block';
            document.getElementById('edit-upload-text').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Reuse template fungsi modal tambah untuk edit TF & Match & JG

function editAddJg(text = '', correct = '0', gambarUrl = null, relativeGambar = null) {
    const container = document.getElementById('edit-jg-container');
    const template  = document.getElementById('modal-jg-template');
    const idx = Date.now() + Math.random().toString(36).substr(2, 5);
    const clone = template.content.cloneNode(true);
    
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/JIDX/g, idx);
    });
    
    // Append
    const div = document.createElement('div');
    div.appendChild(clone);
    container.appendChild(div.firstChild || div);
    const item = container.lastElementChild;
    
    if (item) {
        if (text) {
            const textInp = item.querySelector('input[type="text"]');
            if(textInp) textInp.value = text;
        }
        if (correct && correct != '0') {
            const checkInp = item.querySelector('input[type="checkbox"]');
            if(checkInp) checkInp.checked = true;
        }
        if (gambarUrl) {
            const previewImg = item.querySelector('.modal-opsi-preview');
            const icon       = item.querySelector('.modal-opsi-icon');
            const hiddenImgInput = item.querySelector('.modal-existing-img');
            const label = previewImg.closest('label');
            
            if(previewImg) {
                previewImg.src = gambarUrl;
                previewImg.classList.remove('hidden');
            }
            if(icon) icon.classList.add('hidden');
            if(hiddenImgInput) hiddenImgInput.value = relativeGambar;
            if(label) label.classList.add('border-blue-400', 'p-0');
        }
    }
    reindexJgLabels('edit-jg-container');
}

function editAddTf(text = '', correct = '', gambarUrl = null, relativeGambar = null) {
    const container = document.getElementById('edit-tf-container');
    const template  = document.getElementById('modal-tf-template');
    const idx = Date.now() + Math.random().toString(36).substr(2, 5);
    const clone = template.content.cloneNode(true);
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/TIDX/g, idx);
    });
    const div = document.createElement('div');
    div.appendChild(clone);
    container.appendChild(div.firstChild || div);
    const item = container.lastElementChild;
    if (item) {
        const textInp = item.querySelector(`input[name*="[text]"]`);
        if (textInp && text) textInp.value = text;
        const radio = item.querySelector(`input[value="${correct}"]`);
        if (radio) radio.checked = true;

        // Preview gambar dan input hidden
        if (gambarUrl) {
            const preview = item.querySelector('.modal-opsi-preview');
            const icon    = item.querySelector('.bi-image');
            const hidden  = item.querySelector('input[name*="[existing_gambar]"]');
            const label   = preview.closest('label');
            
            if (preview) { preview.src = gambarUrl; preview.classList.remove('hidden'); }
            if (icon) icon.classList.add('hidden');
            if (hidden) hidden.value = relativeGambar;
            if (label) label.classList.add('border-blue-400', 'p-0');
        }
    }
}

function editAddMatch(left = '', right = '', leftGambarUrl = null, leftRel = null, rightGambarUrl = null, rightRel = null) {
    const container = document.getElementById('edit-matches-container');
    const template  = document.getElementById('modal-match-template');
    const idx = Date.now() + Math.random().toString(36).substr(2, 5);
    const clone = template.content.cloneNode(true);
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/MIDX/g, idx);
    });
    const div = document.createElement('div');
    div.appendChild(clone);
    container.appendChild(div.firstChild || div);
    const item = container.lastElementChild;
    if (item) {
        const leftInp  = item.querySelector(`input[name*="[left]"]`);
        const rightInp = item.querySelector(`input[name*="[right]"]`);
        if (leftInp  && left)  leftInp.value  = left;
        if (rightInp && right) rightInp.value = right;

        // Preview kiri
        if (leftGambarUrl) {
            const labels  = item.querySelectorAll('label');
            const preview = labels[0].querySelector('.modal-opsi-preview');
            const icon    = labels[0].querySelector('.bi-image');
            const hidden  = labels[0].querySelector('input[name*="[existing_gambar_left]"]');
            
            if (preview) { preview.src = leftGambarUrl; preview.classList.remove('hidden'); }
            if (icon) icon.classList.add('hidden');
            if (hidden) hidden.value = leftRel;
            labels[0].classList.add('border-blue-400', 'p-0');
        }

        // Preview kanan
        if (rightGambarUrl) {
            const labels  = item.querySelectorAll('label');
            const preview = labels[1].querySelector('.modal-opsi-preview');
            const icon    = labels[1].querySelector('.bi-image');
            const hidden  = labels[1].querySelector('input[name*="[existing_gambar_right]"]');
            
            if (preview) { preview.src = rightGambarUrl; preview.classList.remove('hidden'); }
            if (icon) icon.classList.add('hidden');
            if (hidden) hidden.value = rightRel;
            labels[1].classList.add('border-blue-400', 'p-0');
        }
    }
}

// Validasi form helper universal
function validateSoalForm(form, tipe) {
    let isValid = true;
    let errorMsg = '';

    // 1. Pilihan Ganda
    if (tipe === 'pilihan_ganda') {
        const kunci = form.querySelector('select[name="kunci_jawaban"]');
        if (!kunci || !kunci.value) {
            isValid = false;
            errorMsg = 'Soal Pilihan Ganda belum memiliki kunci jawaban!';
        } else {
            let allOptionsFilled = true;
            ['a', 'b', 'c', 'd'].forEach(opt => {
                const textInput = form.querySelector(`input[name="opsi_${opt}"]`);
                const imgInput = form.querySelector(`input[name="gambar_${opt}"]`);
                const imgPreviewContainer = imgInput ? imgInput.closest('label') : null;
                const imgPreview = imgPreviewContainer ? imgPreviewContainer.querySelector('img') : null;
                
                const hasText = textInput && textInput.value.trim() !== '';
                const hasImgNew = imgInput && imgInput.files && imgInput.files.length > 0;
                const hasImgExisting = imgPreview && !imgPreview.classList.contains('hidden') && imgPreview.getAttribute('src') !== '';
                
                if (!hasText && !hasImgNew && !hasImgExisting) {
                    allOptionsFilled = false;
                }
            });
            if (!allOptionsFilled) {
                isValid = false;
                errorMsg = 'Soal Pilihan Ganda semua opsi (A, B, C, D) harus diisi teks atau gambarnya!';
            }
        }
    } 
    // 2. Benar/Salah
    else if (tipe === 'benar_salah') {
        const tfItems = form.querySelectorAll(form.id === 'form-edit-soal' ? '#edit-tf-container > div' : '#modal-tf-container > div');
        if (tfItems.length === 0) {
            isValid = false;
            errorMsg = 'Soal Benar/Salah minimal harus memiliki 1 pernyataan!';
        } else {
            let allAnswered = true;
            for (let item of tfItems) {
                 if(!item.querySelector('input[type="radio"]:checked')) {
                     allAnswered = false; break;
                 }
            }
            if (!allAnswered) {
                isValid = false;
                errorMsg = 'Ada pernyataan Benar/Salah yang belum ditentukan kuncinya!';
            }
        }
    }
    // 3. Jawaban Ganda
    else if (tipe === 'jawaban_ganda') {
        const checkedMap = form.querySelectorAll('input[name="kunci_jawaban_jg[]"]:checked');
        if (checkedMap.length === 0) {
            isValid = false;
            errorMsg = 'Soal Jawaban Ganda minimal harus memilih 1 jawaban benar!';
        } else {
            let allOptionsFilled = true;
            ['a', 'b', 'c', 'd'].forEach(opt => {
                const textInput = form.querySelector(`input[name="opsi_${opt}_jg"]`);
                const imgInput = form.querySelector(`input[name="gambar_${opt}_jg"]`);
                const imgPreviewContainer = imgInput ? imgInput.closest('label') : null;
                const imgPreview = imgPreviewContainer ? imgPreviewContainer.querySelector('img') : null;
                
                const hasText = textInput && textInput.value.trim() !== '';
                const hasImgNew = imgInput && imgInput.files && imgInput.files.length > 0;
                const hasImgExisting = imgPreview && !imgPreview.classList.contains('hidden') && imgPreview.getAttribute('src') !== '';
                
                if (!hasText && !hasImgNew && !hasImgExisting) {
                    allOptionsFilled = false;
                }
            });
            if (!allOptionsFilled) {
                isValid = false;
                errorMsg = 'Soal Jawaban Ganda semua opsi (A, B, C, D) harus diisi teks atau gambarnya!';
            }
        }
    }
    // 4. Menjodohkan
    else if (tipe === 'menjodohkan') {
        const matchItems = form.querySelectorAll(form.id === 'form-edit-soal' ? '#edit-matches-container > div' : '#modal-matches-container > div');
        if (matchItems.length === 0) {
            isValid = false;
            errorMsg = 'Soal Menjodohkan minimal harus memiliki 1 pasang pertanyaan-jawaban!';
        } else {
            let hasFullPair = false;
            let incompletePair = false;

            for(let item of matchItems) {
                const leftText = (item.querySelector('input[name*="[left]"]')?.value || '').trim();
                const rightText = (item.querySelector('input[name*="[right]"]')?.value || '').trim();
                
                const leftImg = item.querySelector('input[name*="[gambar_left]"]');
                const rightImg = item.querySelector('input[name*="[gambar_right]"]');
                
                // Cek apalah ada input file atau preview gambar yang terisi
                const leftImgPreview = item.querySelectorAll('.modal-opsi-preview')[0];
                const rightImgPreview = item.querySelectorAll('.modal-opsi-preview')[1];

                const leftImgHasContent = (leftImg && leftImg.files.length > 0) || (leftImgPreview && !leftImgPreview.classList.contains('hidden'));
                const rightImgHasContent = (rightImg && rightImg.files.length > 0) || (rightImgPreview && !rightImgPreview.classList.contains('hidden'));

                const hasLeftContent = leftText || leftImgHasContent;
                const hasRightContent = rightText || rightImgHasContent;

                if (hasLeftContent !== hasRightContent) {
                    incompletePair = true;
                    errorMsg = hasLeftContent 
                        ? 'Ada pertanyaan (kiri) yang belum memiliki pasangan jawaban (kanan)!' 
                        : 'Ada jawaban (kanan) yang belum memiliki pasangan pertanyaan (kiri)!';
                    break;
                }
                
                if(hasLeftContent && hasRightContent) {
                    hasFullPair = true;
                }
            }

            if (incompletePair) {
                isValid = false;
            } else if (!hasFullPair) {
                isValid = false;
                errorMsg = 'Mohon lengkapi minimal satu pasang pertanyaan dan jawaban valid (bisa teks/gambar)!';
            }
        }
    }

    if (!isValid) {
        if (typeof showNotificationModal === 'function') {
            showNotificationModal(errorMsg);
        } else {
            console.error(errorMsg);
        }
    }
    return isValid;
}

// Bind Submit Events
document.addEventListener('DOMContentLoaded', () => {
    // Helper validasi pertanyaan kosong
    function cekPertanyaanKosong(form) {
        const textarea = form.querySelector('textarea[name="pertanyaan"]');
        if (textarea && textarea.value.trim() === '') {
            showNotificationModal('Mohon isikan teks Pertanyaan terlebih dahulu.');
            textarea.scrollIntoView({behavior: 'smooth', block: 'center'});
            textarea.classList.add('ring-2', 'ring-red-500');
            setTimeout(() => textarea.classList.remove('ring-2', 'ring-red-500'), 3000);
            return true;
        }
        return false;
    }

    // Form TAMBAH Soal Bank Soal
    const formTambah = document.getElementById('form-bank-soal');
    if (formTambah) {
        formTambah.addEventListener('submit', function(e) {
            const tipe = document.getElementById('modal-tipe-soal').value;
            if (cekPertanyaanKosong(formTambah) || !validateSoalForm(formTambah, tipe)) {
                e.preventDefault();
            }
        });
    }

    // Form EDIT Soal Bank Soal
    const formEdit = document.getElementById('form-edit-soal');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            const tipe = document.getElementById('edit-tipe-soal').value;
            if (cekPertanyaanKosong(formEdit) || !validateSoalForm(formEdit, tipe)) {
                e.preventDefault();
            }
        });
    }

    // --- LOGIKA BULK DELETE ---
    const checkAll = document.getElementById('check-all');
    const bulkActionBox = document.getElementById('bulk-action-bar');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    const selectedCountText = document.getElementById('selected-count-bulk');

    function updateBulkUI() {
        const checkboxes = document.querySelectorAll('.bulk-cb');
        const checked = document.querySelectorAll('.bulk-cb:checked');
        const count = checked.length;
        
        selectedCountText.textContent = count;
        
        if (count > 0 && _activeUsageFilter === 'belum') {
            btnBulkDelete.classList.remove('hidden');
        } else {
            btnBulkDelete.classList.add('hidden');
            if(count === 0 && checkAll) checkAll.checked = false;
        }

        // Update card styles
        checkboxes.forEach(cb => {
            const card = cb.closest('.soal-card');
            if (cb.checked) {
                card.classList.add('ring-2', 'ring-blue-500', 'border-transparent');
            } else {
                card.classList.remove('ring-2', 'ring-blue-500', 'border-transparent');
            }
        });
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const visibleCbs = Array.from(document.querySelectorAll('.bulk-cb:not(:disabled)')).filter(cb => {
                const card = cb.closest('.soal-card');
                return card.style.display !== 'none';
            });
            visibleCbs.forEach(cb => cb.checked = this.checked);
            updateBulkUI();
        });
    }

    document.querySelectorAll('.bulk-cb').forEach(cb => {
        cb.addEventListener('change', updateBulkUI);
    });

    if (btnBulkDelete) {
        btnBulkDelete.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.bulk-cb:checked')).map(cb => cb.value);
            
            showConfirmModal(
                'Hapus ' + selectedIds.length + ' Soal Terpilih?',
                'Soal yang sudah terdaftar di ujian akan otomatis dilewati demi keamanan. Tindakan ini tidak dapat dibatalkan.',
                function() {
                    showLoadingModal();
                    
                    fetch("{{ route('guru.mapel.bank_soal.bulk_delete', $mapel->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoadingModal();
                        if (data.status === 'success') {
                            showNotificationModal('Berhasil!', data.message, 'success', function() {
                                window.location.reload();
                            });
                        } else {
                            showNotificationModal('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoadingModal();
                        console.error(error);
                        showNotificationModal('Error', 'Terjadi kesalahan pada server.', 'error');
                    });
                },
                'Hapus Terpilih',
                'bg-red-600',
                'hover:bg-red-700'
            );
        });
    }
});
</script>

{{-- FORM DELETE GLOBAL (Hidden) --}}
<form id="form-delete-global" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- =========================================================
     3 MODAL CUSTOM UNIVERSAL (NOTIFIKASI, KONFIRMASI, LOADING)
========================================================== --}}

{{-- 1. Modal Notifikasi (Sukses, Error, Info) --}}
<div id="modal-notification" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeNotificationModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="notification-modal-content">
            <div class="p-6 text-center">
                <div id="notif-icon-container" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border-[6px]">
                    <i id="notif-icon" class="text-2xl"></i>
                </div>
                <h3 id="notif-title" class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Title</h3>
                <p id="notif-message" class="text-sm text-gray-500 mb-6 font-medium">Message here.</p>
                <button type="button" id="notif-btn" onclick="closeNotificationModal()" class="px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal Konfirmasi (Hapus, Bulk Delete, dll) --}}
<div id="modal-custom-confirm" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeConfirmModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="confirm-modal-content">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-orange-50 flex items-center justify-center mx-auto mb-4 border-[6px] border-orange-100">
                    <i class="bi bi-question-circle-fill text-2xl text-orange-500"></i>
                </div>
                <h3 id="confirm-title" class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Konfirmasi</h3>
                <p id="confirm-message" class="text-sm text-gray-500 mb-6 font-medium">Apakah Anda yakin?</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" onclick="closeConfirmModal()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors w-full">
                        Batal
                    </button>
                    <button type="button" id="confirm-action-btn" class="px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. Modal Loading --}}
<div id="modal-loading" class="fixed inset-0 z-[110] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center transform scale-95 opacity-0 transition-all duration-300" id="loading-modal-content">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mb-4"></div>
            <h3 class="text-lg font-bold text-gray-800">Memproses...</h3>
            <p class="text-sm text-gray-500 mt-1 text-center">Mohon tunggu sebentar, jangan tutup halaman ini.</p>
        </div>
    </div>
</div>
@endsection

