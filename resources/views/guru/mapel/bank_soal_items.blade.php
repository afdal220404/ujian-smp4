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

<style>
.soal-editor {
    min-height: 85px;
    max-height: 250px;
    overflow-y: auto;
    word-break: break-word;
    white-space: pre-wrap;
}
.soal-editor:empty:before {
    content: attr(data-placeholder);
    color: #9ca3af;
    pointer-events: none;
    display: block;
}
.soal-editor b, .soal-editor strong {
    font-weight: 700 !important;
}
.soal-editor i, .soal-editor em {
    font-style: italic !important;
}
.soal-editor u {
    text-decoration: underline !important;
}
</style>

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
                <div class="text-gray-800 font-medium text-sm leading-relaxed prose max-w-none">
                    {!! format_soal($soal->pertanyaan) !!}
                </div>
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
                                        {!! format_soal($teksOpsi) !!}
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
                                <span class="text-gray-700">{!! format_soal($stmt['text'] ?? '-') !!}</span>
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

                {{-- 3. Mencocokkan Fleksibel --}}
                @elseif($soal->tipe === 'menjodohkan')
                    @php
                        $leftItems = $dataSoal['left_items'] ?? [];
                        $rightItems = $dataSoal['right_items'] ?? [];
                        $correctPairs = $dataSoal['correct_pairs'] ?? [];
                        $legacyMatches = $dataSoal['matches'] ?? [];
                        $alphabet = range('A', 'Z');
                        $rMap = [];
                        foreach ($rightItems as $ri => $r) {
                            $rMap[$r['id'] ?? ('R'.$ri)] = [
                                'label' => $alphabet[$ri] ?? ('R'.($ri+1)),
                                'text'  => $r['text'] ?? '',
                                'gambar'=> $r['gambar'] ?? null,
                            ];
                        }
                    @endphp
                    @if(!empty($leftItems) || !empty($rightItems))
                        <div class="mt-3 space-y-2">
                            {{-- Pilihan Kanan --}}
                            <div class="p-2.5 bg-emerald-50/70 border border-emerald-100 rounded-xl">
                                <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block mb-1.5">Pilihan Jawaban (Kanan):</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                    @foreach($rightItems as $ri => $r)
                                    @php $rLabel = $alphabet[$ri] ?? ('R'.($ri+1)); @endphp
                                    <div class="flex items-center gap-1.5 text-xs bg-white p-1.5 rounded-lg border border-emerald-200/80">
                                        <span class="w-5 h-5 rounded bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">{{ $rLabel }}</span>
                                        <span class="text-gray-700 truncate">{!! format_soal($r['text'] ?? '-') !!}</span>
                                        @if(!empty($r['gambar']))
                                            <img src="{{ asset('storage/'.$r['gambar']) }}" class="h-6 w-6 object-cover rounded border ml-auto">
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Premis Kiri + Kunci Pasangan --}}
                            <div class="space-y-1.5">
                                @foreach($leftItems as $li => $l)
                                @php
                                    $lId = $l['id'] ?? ('L'.$li);
                                    $matchedKeys = [];
                                    foreach ($correctPairs as $cp) {
                                        if (($cp['left'] ?? null) === $lId && isset($cp['right'])) {
                                            $matchedKeys[] = $rMap[$cp['right']]['label'] ?? $cp['right'];
                                        }
                                    }
                                @endphp
                                <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px] shrink-0">Item {{ $li+1 }}</span>
                                        <span class="text-gray-800 font-medium truncate">{!! format_soal($l['text'] ?? '-') !!}</span>
                                        @if(!empty($l['gambar']))
                                            <img src="{{ asset('storage/'.$l['gambar']) }}" class="h-6 w-6 object-cover rounded border">
                                        @endif
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1">
                                        <span class="text-[10px] text-gray-400 font-bold">Kunci:</span>
                                        @if(!empty($matchedKeys))
                                            <span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800 text-[10px]">
                                                {{ implode(', ', $matchedKeys) }}
                                            </span>
                                        @else
                                            <span class="text-red-500 italic text-[10px]">(Belum dipasangkan)</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif(!empty($legacyMatches))
                        <div class="mt-3 space-y-1.5">
                            @foreach($legacyMatches as $match)
                            @php
                                $left  = $match['left']  ?? '-';
                                $right = $match['right'] ?? '-';
                            @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <div class="flex-1 p-2 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 min-w-0">
                                    {!! format_soal($left) !!}
                                    @if(!empty($match['gambar_left']))
                                        <img src="{{ asset('storage/'.$match['gambar_left']) }}" class="mt-1 h-10 rounded border object-contain">
                                    @endif
                                </div>
                                <i class="bi bi-arrow-right text-gray-400 shrink-0"></i>
                                <div class="flex-1 p-2 rounded-lg bg-green-50 border border-green-200 text-green-800 font-medium min-w-0">
                                    {!! format_soal($right) !!}
                                    @if(!empty($match['gambar_right']))
                                        <img src="{{ asset('storage/'.$match['gambar_right']) }}" class="mt-1 h-10 rounded border object-contain">
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
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
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] flex flex-col">

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

                    {{-- ── 1. Baris Atas: Pertanyaan (Kiri) & Upload Gambar + Kunci PG (Kanan) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">

                        {{-- Kiri: Pertanyaan --}}
                        <div class="lg:col-span-8 pertanyaan-container">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Pertanyaan</label>
                                <div class="flex items-center gap-1 bg-gray-100 p-0.5 rounded-lg border border-gray-200 text-xs">
                                    <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'bold')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs font-bold text-gray-700 hover:text-blue-600 transition-all cursor-pointer" title="Tebal (Ctrl+B)">
                                        <b>B</b>
                                    </button>
                                    <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'italic')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs italic text-gray-700 hover:text-blue-600 transition-all font-serif cursor-pointer" title="Miring (Ctrl+I)">
                                        <i>I</i>
                                    </button>
                                    <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'underline')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs underline text-gray-700 hover:text-blue-600 transition-all cursor-pointer" title="Garis Bawah (Ctrl+U)">
                                        <u>U</u>
                                    </button>
                                </div>
                            </div>
                            <div contenteditable="true" id="modal-pertanyaan-editor"
                                class="soal-editor w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800"
                                data-placeholder="Tulis pertanyaan di sini..."></div>
                            <textarea name="pertanyaan" id="modal-pertanyaan" class="hidden"></textarea>
                        </div>

                        {{-- Kanan: Upload Gambar Soal & Kunci PG --}}
                        <div class="lg:col-span-4 flex flex-col gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Pendukung Soal</label>
                                <div id="modal-upload-box" class="relative w-full h-[110px] border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden" onclick="document.getElementById('modal-gambar-input').click()">
                                    <img id="modal-gambar-preview" class="absolute inset-0 w-full h-full object-contain bg-white p-2" style="display:none;">
                                    <div id="modal-upload-text" class="text-center p-2">
                                        <i class="bi bi-cloud-arrow-up-fill text-2xl text-gray-300"></i>
                                        <p class="text-[11px] text-gray-500 mt-0.5 font-medium">Klik untuk Upload Gambar</p>
                                    </div>
                                    <input type="file" name="gambar" id="modal-gambar-input" class="hidden" accept="image/*" onchange="modalPreviewGambar(this)">
                                </div>
                            </div>

                            {{-- Kunci Jawaban Pilihan Ganda --}}
                            <div id="modal-key-pg" class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                                <label class="block text-[11px] font-bold text-blue-800 uppercase mb-1">Kunci Jawaban PG</label>
                                <div class="relative">
                                    <select name="kunci_jawaban" id="modal-kunci" class="w-full px-3 py-1.5 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-xs font-bold text-blue-700 appearance-none cursor-pointer">
                                        <option value="" disabled selected>-- Pilih Kunci --</option>
                                        @foreach(['A','B','C','D'] as $huruf)
                                        <option value="{{ $huruf }}">Jawaban {{ $huruf }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-500">
                                        <i class="bi bi-check-circle-fill text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /Baris Atas --}}

                    {{-- ── 2. Baris Bawah: Container Jawaban Dinamis (FULL WIDTH) --}}
                    <div id="modal-answers-container" class="space-y-4 w-full">

                        {{-- 1. PILIHAN GANDA --}}
                        <div class="modal-type-section modal-type-pilihan_ganda space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                @foreach(['a','b','c','d'] as $opsi)
                                <div class="flex items-center gap-2.5 p-2 bg-slate-50/70 border border-slate-200/80 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center shrink-0 border border-blue-200 uppercase text-xs">{{ $opsi }}</div>
                                    <input type="text" name="opsi_{{ $opsi }}" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                                    {{-- Tombol Gambar Opsi (gambar menggantikan ikon) --}}
                                    <label class="shrink-0 cursor-pointer w-9 h-9 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar Opsi">
                                        <i class="bi bi-image text-gray-400 modal-opsi-icon text-xs"></i>
                                        <img src="" class="modal-opsi-preview hidden w-full h-full object-cover rounded-lg">
                                        <input type="file" name="gambar_{{ $opsi }}" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
                                    </label>
                                </div>
                                @endforeach
                            </div>
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

                        {{-- 4. MENCOCOKKAN INTERAKTIF --}}
                        <div class="modal-type-section modal-type-menjodohkan hidden space-y-4">
                            <div class="bg-blue-50 p-3.5 rounded-xl text-xs text-blue-700 border border-blue-100 flex items-start gap-2">
                                <i class="bi bi-info-circle-fill text-blue-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <b>Panduan Membuat Soal Mencocokkan:</b>
                                    <ol class="list-decimal list-inside mt-1 space-y-0.5 text-blue-800">
                                        <li>Tambahkan item pada daftar <b>Premis (Kiri)</b> dan <b>Pilihan Jawaban (Kanan)</b>.</li>
                                        <li>Tentukan kunci jawaban pada <b>Papan Kunci Pasangan</b> di bawah dengan cara mengklik item kiri lalu mengklik item kanan untuk menarik garis.</li>
                                        <li>Bebas menghubungkan item kiri ke kanan sebanyak yang diinginkan, dan diperbolehkan jika ada item yang tidak memiliki pasangan (pengecoh).</li>
                                    </ol>
                                </div>
                            </div>

                            {{-- DUA KOLOM INPUT: PREMIS KIRI & PILIHAN KANAN --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- SISI KIRI (PREMIS / PERTANYAAN) --}}
                                <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class="bi bi-card-text text-blue-600 text-sm"></i> Premis (Sisi Kiri)
                                        </span>
                                        <button type="button" onclick="modalAddMatchLeft()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-blue-200 shadow-2xs">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Item Kiri
                                        </button>
                                    </div>
                                    <div id="modal-match-lefts-container" class="space-y-2"></div>
                                </div>

                                {{-- SISI KANAN (PILIHAN JAWABAN) --}}
                                <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class="bi bi-list-check text-emerald-600 text-sm"></i> Pilihan (Sisi Kanan)
                                        </span>
                                        <button type="button" onclick="modalAddMatchRight()" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Pilihan Kanan
                                        </button>
                                    </div>
                                    <div id="modal-match-rights-container" class="space-y-2"></div>
                                </div>
                            </div>

                            {{-- PAPAN KUNCI PASANGAN (HUBUNGKAN DENGAN GARIS) --}}
                            <div class="modal-teacher-matching-board bg-white p-4 rounded-2xl border-2 border-indigo-100 shadow-sm space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-indigo-50">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                                            <i class="bi bi-bezier2"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-800">Papan Kunci Pasangan (Hubungkan dengan Garis)</h4>
                                            <p class="text-[11px] text-gray-400">Klik item Kiri lalu klik item Kanan untuk memasangkan (atau klik ulang untuk memutus).</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="modalResetTeacherMatching('modal')" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg border border-rose-200 transition-colors flex items-center gap-1 cursor-pointer">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset Garis
                                    </button>
                                </div>

                                {{-- Interactive Canvas --}}
                                <div id="modal-teacher-match-canvas" class="relative select-none p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 min-h-[160px]">
                                    <svg id="modal-teacher-match-svg" class="absolute inset-0 w-full h-full pointer-events-none z-10"></svg>
                                    
                                    <div class="flex flex-col sm:flex-row justify-between relative z-20 gap-6 sm:gap-10">
                                        {{-- Sisi Kiri Board --}}
                                        <div id="modal-teacher-board-left" class="flex-1 space-y-2.5">
                                            {{-- Dynamically populated by JS --}}
                                        </div>

                                        {{-- Sisi Kanan Board --}}
                                        <div id="modal-teacher-board-right" class="flex-1 space-y-2.5">
                                            {{-- Dynamically populated by JS --}}
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="correct_pairs_json" id="modal-correct-pairs-json" value="[]">
                            </div>
                        </div>

                    </div>{{-- /answers-container --}}

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

{{-- TEMPLATE: Match Right Item --}}
<template id="modal-match-right-template">
    <div class="modal-match-right-item flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-200" data-id="RUID">
        <div class="modal-right-badge w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs flex items-center justify-center shrink-0">
            A
        </div>
        <input type="hidden" name="right_items[RIDX][id]" value="RUID" class="modal-right-id">
        <input type="text" name="right_items[RIDX][text]" class="modal-right-text flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:bg-white focus:border-emerald-500 outline-none" placeholder="Teks pilihan jawaban...">
        
        <label class="shrink-0 cursor-pointer w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar">
            <i class="bi bi-image text-gray-400 modal-opsi-icon text-xs"></i>
            <img src="" class="modal-opsi-preview hidden w-full h-full object-cover">
            <input type="file" name="right_items[RIDX][gambar]" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
            <input type="hidden" name="right_items[RIDX][existing_gambar]" class="modal-existing-img">
        </label>

        <button type="button" onclick="modalRemoveMatchRight(this)" class="text-slate-300 hover:text-rose-500 shrink-0 p-1">
            <i class="bi bi-trash3-fill text-sm"></i>
        </button>
    </div>
</template>

{{-- TEMPLATE: Match Left Item --}}
<template id="modal-match-left-template">
    <div class="modal-match-left-item flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-200" data-id="LUID">
        <span class="modal-left-badge px-2 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs shrink-0">
            Item 1
        </span>
        <input type="hidden" name="left_items[LIDX][id]" value="LUID" class="modal-left-id">
        <input type="text" name="left_items[LIDX][text]" class="modal-left-text flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:bg-white focus:border-blue-500 outline-none" placeholder="Teks premis / pertanyaan...">
        
        <label class="shrink-0 cursor-pointer w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar">
            <i class="bi bi-image text-gray-400 modal-opsi-icon text-xs"></i>
            <img src="" class="modal-opsi-preview hidden w-full h-full object-cover">
            <input type="file" name="left_items[LIDX][gambar]" class="hidden modal-opsi-img" accept="image/*" onchange="modalPreviewOpsi(this)">
            <input type="hidden" name="left_items[LIDX][existing_gambar]" class="modal-existing-img">
        </label>

        <button type="button" onclick="modalRemoveMatchLeft(this)" class="text-slate-300 hover:text-rose-500 shrink-0 p-1">
            <i class="bi bi-trash3-fill text-sm"></i>
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
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] flex flex-col">
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

                    {{-- ── 1. Baris Atas: Pertanyaan (Kiri) & Upload Gambar + Kunci PG (Kanan) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
                        {{-- Kiri: Pertanyaan --}}
                        <div class="lg:col-span-8 pertanyaan-container">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Pertanyaan</label>
                                <div class="flex items-center gap-1 bg-gray-100 p-0.5 rounded-lg border border-gray-200 text-xs">
                                    <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'bold')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs font-bold text-gray-700 hover:text-blue-600 transition-all cursor-pointer" title="Tebal (Ctrl+B)">
                                        <b>B</b>
                                    </button>
                                    <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'italic')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs italic text-gray-700 hover:text-blue-600 transition-all font-serif cursor-pointer" title="Miring (Ctrl+I)">
                                        <i>I</i>
                                    </button>
                                    <button type="button" onmousedown="event.preventDefault(); formatDoc(this, 'underline')" class="px-2 py-0.5 rounded hover:bg-white hover:shadow-xs underline text-gray-700 hover:text-blue-600 transition-all cursor-pointer" title="Garis Bawah (Ctrl+U)">
                                        <u>U</u>
                                    </button>
                                </div>
                            </div>
                            <div contenteditable="true" id="edit-pertanyaan-editor"
                                class="soal-editor w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800"
                                data-placeholder="Tulis pertanyaan di sini..."></div>
                            <textarea name="pertanyaan" id="edit-pertanyaan" class="hidden"></textarea>
                        </div>

                        {{-- Kanan: Upload Gambar Soal & Kunci PG --}}
                        <div class="lg:col-span-4 flex flex-col gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Soal</label>
                                <div id="edit-upload-box" class="relative w-full h-[110px] border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden" onclick="document.getElementById('edit-gambar-input').click()">
                                    <img id="edit-gambar-preview" class="absolute inset-0 w-full h-full object-contain bg-white p-2" style="display:none;">
                                    <div id="edit-upload-text" class="text-center p-2">
                                        <i class="bi bi-cloud-arrow-up-fill text-2xl text-gray-300"></i>
                                        <p class="text-[11px] text-gray-500 mt-0.5 font-medium">Klik untuk Upload / Ganti</p>
                                    </div>
                                    <input type="file" name="gambar" id="edit-gambar-input" class="hidden" accept="image/*" onchange="editPreviewGambar(this)">
                                </div>
                            </div>
                            <div id="edit-key-pg" class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                                <label class="block text-[11px] font-bold text-blue-800 uppercase mb-1">Kunci Jawaban PG</label>
                                <div class="relative">
                                    <select name="kunci_jawaban" id="edit-kunci" class="w-full px-3 py-1.5 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-xs font-bold text-blue-700 appearance-none cursor-pointer">
                                        <option value="" disabled>-- Pilih Kunci --</option>
                                        @foreach(['A','B','C','D'] as $huruf)
                                        <option value="{{ $huruf }}">Jawaban {{ $huruf }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-500"><i class="bi bi-check-circle-fill text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── 2. Baris Bawah: Container Jawaban Dinamis (FULL WIDTH) --}}
                    <div id="edit-answers-container" class="space-y-4 w-full">
                        {{-- 1. PG --}}
                        <div class="edit-type-section edit-type-pilihan_ganda space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                @foreach(['a','b','c','d'] as $opsi)
                                <div class="flex items-center gap-2.5 p-2 bg-slate-50/70 border border-slate-200/80 rounded-xl">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center shrink-0 border border-blue-200 uppercase text-xs">{{ $opsi }}</div>
                                    <input type="text" name="opsi_{{ $opsi }}" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                                    <label class="shrink-0 cursor-pointer w-9 h-9 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 flex items-center justify-center overflow-hidden transition-colors" title="Upload Gambar Opsi">
                                        <i class="bi bi-image text-gray-400 modal-opsi-icon text-xs"></i>
                                        <img src="" class="modal-opsi-preview hidden w-full h-full object-cover rounded-lg">
                                        <input type="file" name="gambar_{{ $opsi }}" class="hidden" accept="image/*" onchange="modalPreviewOpsi(this)">
                                    </label>
                                </div>
                                @endforeach
                            </div>
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

                        {{-- 4. MENCOCOKKAN INTERAKTIF (EDIT) --}}
                        <div class="edit-type-section edit-type-menjodohkan hidden space-y-4">
                            <div class="bg-blue-50 p-3.5 rounded-xl text-xs text-blue-700 border border-blue-100 flex items-start gap-2">
                                <i class="bi bi-info-circle-fill text-blue-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <b>Panduan Membuat Soal Mencocokkan:</b>
                                    <ol class="list-decimal list-inside mt-1 space-y-0.5 text-blue-800">
                                        <li>Tambahkan item pada daftar <b>Premis (Kiri)</b> dan <b>Pilihan Jawaban (Kanan)</b>.</li>
                                        <li>Tentukan kunci jawaban pada <b>Papan Kunci Pasangan</b> di bawah dengan cara mengklik item kiri lalu mengklik item kanan untuk menarik garis.</li>
                                        <li>Bebas menghubungkan item kiri ke kanan sebanyak yang diinginkan, dan diperbolehkan jika ada item yang tidak memiliki pasangan (pengecoh).</li>
                                    </ol>
                                </div>
                            </div>

                            {{-- DUA KOLOM INPUT: PREMIS KIRI & PILIHAN KANAN --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- SISI KIRI (PREMIS / PERTANYAAN) --}}
                                <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class="bi bi-card-text text-blue-600 text-sm"></i> Premis (Sisi Kiri)
                                        </span>
                                        <button type="button" onclick="editAddMatchLeft()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-blue-200 shadow-2xs">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Item Kiri
                                        </button>
                                    </div>
                                    <div id="edit-match-lefts-container" class="space-y-2"></div>
                                </div>

                                {{-- SISI KANAN (PILIHAN JAWABAN) --}}
                                <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class="bi bi-list-check text-emerald-600 text-sm"></i> Pilihan (Sisi Kanan)
                                        </span>
                                        <button type="button" onclick="editAddMatchRight()" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Pilihan Kanan
                                        </button>
                                    </div>
                                    <div id="edit-match-rights-container" class="space-y-2"></div>
                                </div>
                            </div>

                            {{-- PAPAN KUNCI PASANGAN (HUBUNGKAN DENGAN GARIS) --}}
                            <div class="edit-teacher-matching-board bg-white p-4 rounded-2xl border-2 border-indigo-100 shadow-sm space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-indigo-50">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                                            <i class="bi bi-bezier2"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-800">Papan Kunci Pasangan (Hubungkan dengan Garis)</h4>
                                            <p class="text-[11px] text-gray-400">Klik item Kiri lalu klik item Kanan untuk memasangkan (atau klik ulang untuk memutus).</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="modalResetTeacherMatching('edit')" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg border border-rose-200 transition-colors flex items-center gap-1 cursor-pointer">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset Garis
                                    </button>
                                </div>

                                {{-- Interactive Canvas --}}
                                <div id="edit-teacher-match-canvas" class="relative select-none p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 min-h-[160px]">
                                    <svg id="edit-teacher-match-svg" class="absolute inset-0 w-full h-full pointer-events-none z-10"></svg>
                                    
                                    <div class="flex flex-col sm:flex-row justify-between relative z-20 gap-6 sm:gap-10">
                                        {{-- Sisi Kiri Board --}}
                                        <div id="edit-teacher-board-left" class="flex-1 space-y-2.5">
                                            {{-- Dynamically populated by JS --}}
                                        </div>

                                        {{-- Sisi Kanan Board --}}
                                        <div id="edit-teacher-board-right" class="flex-1 space-y-2.5">
                                            {{-- Dynamically populated by JS --}}
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="correct_pairs_json" id="edit-correct-pairs-json" value="[]">
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
window.formatDoc = function(button, cmd) {
    const container = button.closest('.pertanyaan-container') || button.closest('form') || button.closest('.modal-content') || document;
    const editor = container ? container.querySelector('.soal-editor, [contenteditable="true"]') : null;
    if (!editor) return;

    editor.focus();
    document.execCommand(cmd, false, null);

    const textarea = container.querySelector('.soal-textarea, textarea[name*="pertanyaan"], #modal-pertanyaan, #edit-pertanyaan');
    if (textarea) {
        textarea.value = editor.innerHTML;
    }
};

// Sync contenteditable with hidden textarea on typing
document.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('soal-editor')) {
        const container = e.target.closest('.pertanyaan-container') || e.target.parentElement;
        const textarea = container ? container.querySelector('.soal-textarea, textarea[name*="pertanyaan"], #modal-pertanyaan, #edit-pertanyaan') : null;
        if (textarea) {
            textarea.value = e.target.innerHTML;
        }
    }
});


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
    const edTambah = document.getElementById('modal-pertanyaan-editor');
    if (edTambah) edTambah.innerHTML = '';
    document.getElementById('modal-pertanyaan').value = '';
    document.getElementById('modal-tf-container').innerHTML = '';
    document.getElementById('modal-match-rights-container').innerHTML = '';
    document.getElementById('modal-match-lefts-container').innerHTML = '';
    document.getElementById('modal-gambar-preview').style.display = 'none';
    document.getElementById('modal-upload-text').style.display = '';
    window._modalMatchState.modal = { pairs: [], selectedLeft: null, selectedRight: null };
    const jsonInp = document.getElementById('modal-correct-pairs-json');
    if (jsonInp) jsonInp.value = '[]';

    modalUpdateUI('pilihan_ganda');
    document.getElementById('modal-tipe-soal').value = 'pilihan_ganda';
    // Default 2 match right & 2 match left + 1 TF items
    modalAddMatchRight(); modalAddMatchRight();
    modalAddMatchLeft(); modalAddMatchLeft();
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

    if (tipe === 'menjodohkan') {
        setTimeout(() => renderModalTeacherMatchingBoard('modal'), 100);
    }
}

// ── GAMBAR SOAL PREVIEW ───────────────────────────────────
function modalPreviewGambar(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > 2 * 1024 * 1024) {
            showNotificationModal('File Terlalu Besar', 'Ukuran gambar maksimal 2MB!', 'error');
            input.value = '';
            return;
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
            showNotificationModal('File Terlalu Besar', 'Ukuran gambar maksimal 2MB!', 'error');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            // Sembunyikan ikon, tampilkan gambar
            if (icon) icon.classList.add('hidden');
            label.classList.add('border-blue-400', 'p-0');

            const isEdit = input.closest('#form-edit-soal') !== null;
            const prefix = isEdit ? 'edit' : 'modal';
            renderModalTeacherMatchingBoard(prefix);
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        if (icon) icon.classList.remove('hidden');
        label.classList.remove('border-blue-400', 'p-0');

        const isEdit = input.closest('#form-edit-soal') !== null;
        const prefix = isEdit ? 'edit' : 'modal';
        renderModalTeacherMatchingBoard(prefix);
    }
}

// ── MATCHING MODAL HANDLERS (VISUAL BOARD & 2-COLUMN INPUTS) ────────────
const alphabetList = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

const modalTeacherPalette = [
    { border: 'border-blue-500', bg: 'bg-blue-50', text: 'text-blue-700', stroke: '#2563eb' },
    { border: 'border-emerald-500', bg: 'bg-emerald-50', text: 'text-emerald-700', stroke: '#059669' },
    { border: 'border-purple-500', bg: 'bg-purple-50', text: 'text-purple-700', stroke: '#9333ea' },
    { border: 'border-amber-500', bg: 'bg-amber-50', text: 'text-amber-700', stroke: '#d97706' },
    { border: 'border-rose-500', bg: 'bg-rose-50', text: 'text-rose-700', stroke: '#e11d48' },
    { border: 'border-cyan-500', bg: 'bg-cyan-50', text: 'text-cyan-700', stroke: '#0891b2' },
    { border: 'border-pink-500', bg: 'bg-pink-50', text: 'text-pink-700', stroke: '#db2777' },
    { border: 'border-indigo-500', bg: 'bg-indigo-50', text: 'text-indigo-700', stroke: '#4f46e5' },
    { border: 'border-teal-500', bg: 'bg-teal-50', text: 'text-teal-700', stroke: '#0d9488' },
    { border: 'border-orange-500', bg: 'bg-orange-50', text: 'text-orange-700', stroke: '#ea580c' },
];

window._modalMatchState = {
    modal: { pairs: [], selectedLeft: null, selectedRight: null },
    edit:  { pairs: [], selectedLeft: null, selectedRight: null },
};

function modalResetTeacherMatching(prefix = 'modal') {
    const state = window._modalMatchState[prefix] || { pairs: [] };
    state.pairs = [];
    state.selectedLeft = null;
    state.selectedRight = null;
    const jsonInput = document.getElementById(`${prefix}-correct-pairs-json`);
    if (jsonInput) jsonInput.value = '[]';
    renderModalTeacherMatchingBoard(prefix);
}

function renderModalTeacherMatchingBoard(prefix = 'modal') {
    const boardLeft = document.getElementById(`${prefix}-teacher-board-left`);
    const boardRight = document.getElementById(`${prefix}-teacher-board-right`);
    const jsonInput = document.getElementById(`${prefix}-correct-pairs-json`);
    const leftContainer = document.getElementById(`${prefix}-match-lefts-container`);
    const rightContainer = document.getElementById(`${prefix}-match-rights-container`);

    if (!boardLeft || !boardRight) return;

    const leftItems = leftContainer ? leftContainer.querySelectorAll('.modal-match-left-item') : [];
    const rightItems = rightContainer ? rightContainer.querySelectorAll('.modal-match-right-item') : [];

    const state = window._modalMatchState[prefix] || { pairs: [] };
    const currentPairs = state.pairs || [];
    const validLeftIds = new Set();
    const validRightIds = new Set();

    // Render Left Column of Board
    let leftHtml = '';
    if (leftItems.length === 0) {
        leftHtml = '<p class="text-xs text-gray-400 italic p-3 text-center bg-white rounded-xl border border-dashed border-gray-200">Belum ada item premis kiri.</p>';
    } else {
        leftItems.forEach((lItem, idx) => {
            const lId = lItem.getAttribute('data-id') || lItem.querySelector('.modal-left-id')?.value;
            validLeftIds.add(lId);
            const lText = lItem.querySelector('.modal-left-text')?.value || `Premis ${idx + 1}`;
            const imgPreview = lItem.querySelector('.modal-opsi-preview');
            const hasImg = imgPreview && !imgPreview.classList.contains('hidden') && imgPreview.getAttribute('src');

            leftHtml += `
                <button type="button" 
                        class="modal-teacher-node-left w-full p-2.5 rounded-xl border-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-800 hover:border-blue-400 hover:shadow-xs transition-all flex items-center justify-between group"
                        data-id="${lId}" onclick="onModalTeacherNodeLeftClick(this, '${prefix}')">
                    <div class="flex items-center gap-2 pr-2 min-w-0 flex-1">
                        <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-bold text-[10px] shrink-0">Item ${idx+1}</span>
                        ${hasImg ? `<img src="${imgPreview.getAttribute('src')}" class="h-6 w-6 object-cover rounded border shrink-0">` : ''}
                        <span class="truncate text-gray-700 font-medium">${lText}</span>
                    </div>
                    <div class="w-3.5 h-3.5 rounded-full bg-slate-300 group-hover:bg-blue-400 transition-colors shrink-0 modal-teacher-dot-left" id="${prefix}-tdot-${lId}"></div>
                </button>
            `;
        });
    }
    boardLeft.innerHTML = leftHtml;

    // Render Right Column of Board
    let rightHtml = '';
    if (rightItems.length === 0) {
        rightHtml = '<p class="text-xs text-gray-400 italic p-3 text-center bg-white rounded-xl border border-dashed border-gray-200">Belum ada item pilihan kanan.</p>';
    } else {
        rightItems.forEach((rItem, idx) => {
            const rId = rItem.getAttribute('data-id') || rItem.querySelector('.modal-right-id')?.value;
            validRightIds.add(rId);
            const rLabel = alphabetList[idx] || `R${idx+1}`;
            const rText = rItem.querySelector('.modal-right-text')?.value || `Pilihan ${rLabel}`;
            const imgPreview = rItem.querySelector('.modal-opsi-preview');
            const hasImg = imgPreview && !imgPreview.classList.contains('hidden') && imgPreview.getAttribute('src');

            rightHtml += `
                <button type="button" 
                        class="modal-teacher-node-right w-full p-2.5 rounded-xl border-2 border-gray-200 bg-white text-left text-xs font-medium text-gray-700 hover:border-emerald-400 hover:shadow-xs transition-all flex items-center justify-between group"
                        data-id="${rId}" onclick="onModalTeacherNodeRightClick(this, '${prefix}')">
                    <div class="w-3.5 h-3.5 rounded-full bg-slate-300 group-hover:bg-emerald-400 transition-colors shrink-0 modal-teacher-dot-right" id="${prefix}-tdot-${rId}"></div>
                    <div class="flex items-center gap-2 pl-2 min-w-0 flex-1 justify-end text-right">
                        <span class="truncate text-gray-700 font-medium">${rText}</span>
                        ${hasImg ? `<img src="${imgPreview.getAttribute('src')}" class="h-6 w-6 object-cover rounded border shrink-0">` : ''}
                        <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0">${rLabel}</span>
                    </div>
                </button>
            `;
        });
    }
    boardRight.innerHTML = rightHtml;

    // Filter out pairs with deleted items
    state.pairs = currentPairs.filter(p => validLeftIds.has(p.left) && validRightIds.has(p.right));
    if (jsonInput) jsonInput.value = JSON.stringify(state.pairs);

    // Redraw SVG lines
    setTimeout(() => drawModalTeacherMatchingLines(prefix), 50);
}

function onModalTeacherNodeLeftClick(btn, prefix = 'modal') {
    const state = window._modalMatchState[prefix] || { pairs: [] };
    const lId = btn.dataset.id;
    if (!state.pairs) state.pairs = [];

    if (state.selectedRight) {
        const rId = state.selectedRight.dataset.id;
        state.selectedRight.classList.remove('ring-4', 'ring-emerald-200', 'border-emerald-500');
        
        // Toggle pair
        const existingIdx = state.pairs.findIndex(p => p.left === lId && p.right === rId);
        if (existingIdx !== -1) {
            state.pairs.splice(existingIdx, 1);
        } else {
            state.pairs.push({ left: lId, right: rId });
        }

        const jsonInput = document.getElementById(`${prefix}-correct-pairs-json`);
        if (jsonInput) jsonInput.value = JSON.stringify(state.pairs);

        state.selectedRight = null;
        state.selectedLeft = null;
        drawModalTeacherMatchingLines(prefix);
        return;
    }

    if (state.selectedLeft && state.selectedLeft !== btn) {
        state.selectedLeft.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500');
    }

    if (state.selectedLeft === btn) {
        btn.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500');
        state.selectedLeft = null;
        return;
    }

    btn.classList.add('ring-4', 'ring-blue-200', 'border-blue-500');
    state.selectedLeft = btn;
}

function onModalTeacherNodeRightClick(btn, prefix = 'modal') {
    const state = window._modalMatchState[prefix] || { pairs: [] };
    const rId = btn.dataset.id;
    if (!state.pairs) state.pairs = [];

    if (state.selectedLeft) {
        const lId = state.selectedLeft.dataset.id;
        state.selectedLeft.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500');
        
        // Toggle pair
        const existingIdx = state.pairs.findIndex(p => p.left === lId && p.right === rId);
        if (existingIdx !== -1) {
            state.pairs.splice(existingIdx, 1);
        } else {
            state.pairs.push({ left: lId, right: rId });
        }

        const jsonInput = document.getElementById(`${prefix}-correct-pairs-json`);
        if (jsonInput) jsonInput.value = JSON.stringify(state.pairs);

        state.selectedLeft = null;
        state.selectedRight = null;
        drawModalTeacherMatchingLines(prefix);
        return;
    }

    if (state.selectedRight && state.selectedRight !== btn) {
        state.selectedRight.classList.remove('ring-4', 'ring-emerald-200', 'border-emerald-500');
    }

    if (state.selectedRight === btn) {
        btn.classList.remove('ring-4', 'ring-emerald-200', 'border-emerald-500');
        state.selectedRight = null;
        return;
    }

    btn.classList.add('ring-4', 'ring-emerald-200', 'border-emerald-500');
    state.selectedRight = btn;
}

function drawModalTeacherMatchingLines(prefix = 'modal') {
    const canvas = document.getElementById(`${prefix}-teacher-match-canvas`);
    if (!canvas) return;

    const svg = document.getElementById(`${prefix}-teacher-match-svg`);
    if (!svg) return;

    svg.innerHTML = '';
    const state = window._modalMatchState[prefix] || { pairs: [] };
    const pairs = state.pairs || [];
    const canvasRect = canvas.getBoundingClientRect();

    // Reset all node styling
    canvas.querySelectorAll('.modal-teacher-node-left, .modal-teacher-node-right').forEach(btn => {
        modalTeacherPalette.forEach(c => btn.classList.remove(c.border, c.bg, 'shadow-xs'));
        btn.classList.add('border-gray-200', 'bg-white');
        const dot = btn.querySelector('.modal-teacher-dot-left, .modal-teacher-dot-right');
        if (dot) dot.style.backgroundColor = '#cbd5e1';
    });

    // Color mapping per left item
    const leftNodes = Array.from(canvas.querySelectorAll('.modal-teacher-node-left'));
    const leftColorMap = {};
    leftNodes.forEach((node, idx) => {
        leftColorMap[node.dataset.id] = modalTeacherPalette[idx % modalTeacherPalette.length];
    });

    pairs.forEach(p => {
        const lNode = canvas.querySelector(`.modal-teacher-node-left[data-id="${p.left}"]`);
        const rNode = canvas.querySelector(`.modal-teacher-node-right[data-id="${p.right}"]`);

        if (lNode && rNode) {
            const color = leftColorMap[p.left] || modalTeacherPalette[0];

            lNode.classList.remove('border-gray-200', 'bg-white');
            lNode.classList.add(color.border, color.bg, 'shadow-xs');
            const lDot = lNode.querySelector('.modal-teacher-dot-left');
            if (lDot) lDot.style.backgroundColor = color.stroke;

            rNode.classList.remove('border-gray-200');
            rNode.classList.add('border-slate-400', 'bg-slate-50', 'shadow-xs');
            const rDot = rNode.querySelector('.modal-teacher-dot-right');
            if (rDot) rDot.style.backgroundColor = color.stroke;

            if (lDot && rDot) {
                const lRect = lDot.getBoundingClientRect();
                const rRect = rDot.getBoundingClientRect();

                const x1 = lRect.left + (lRect.width / 2) - canvasRect.left;
                const y1 = lRect.top + (lRect.height / 2) - canvasRect.top;
                const x2 = rRect.left + (rRect.width / 2) - canvasRect.left;
                const y2 = rRect.top + (rRect.height / 2) - canvasRect.top;

                const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
                line.setAttribute("x1", x1);
                line.setAttribute("y1", y1);
                line.setAttribute("x2", x2);
                line.setAttribute("y2", y2);
                line.setAttribute("stroke", color.stroke);
                line.setAttribute("stroke-width", "3");
                line.setAttribute("stroke-linecap", "round");
                line.setAttribute("class", "transition-all duration-300");

                svg.appendChild(line);
            }
        }
    });
}

window.addEventListener('resize', () => {
    const tambahModal = document.getElementById('modal-tambah-soal');
    if (tambahModal && !tambahModal.classList.contains('hidden')) {
        drawModalTeacherMatchingLines('modal');
    }
    const editModal = document.getElementById('modal-edit-soal');
    if (editModal && !editModal.classList.contains('hidden')) {
        drawModalTeacherMatchingLines('edit');
    }
});

function modalAddMatchRight(text = '', gambarUrl = null, relativeGambar = null, customId = null) {
    const container = document.getElementById('modal-match-rights-container');
    const template  = document.getElementById('modal-match-right-template');
    const rUid = customId || ('R' + Date.now() + Math.random().toString(36).substr(2, 4));
    const clone = template.content.cloneNode(true);
    const item = clone.querySelector('.modal-match-right-item');
    item.setAttribute('data-id', rUid);
    
    const idInput = clone.querySelector('.modal-right-id');
    const textInput = clone.querySelector('.modal-right-text');
    if (idInput) idInput.value = rUid;
    if (textInput) {
        textInput.value = text;
        textInput.addEventListener('input', () => renderModalTeacherMatchingBoard('modal'));
    }
    if (gambarUrl) {
        const preview = clone.querySelector('.modal-opsi-preview');
        const icon = clone.querySelector('.modal-opsi-icon');
        const hidden = clone.querySelector('.modal-existing-img');
        const label = preview ? preview.closest('label') : null;
        if (preview) { preview.src = gambarUrl; preview.classList.remove('hidden'); }
        if (icon) icon.classList.add('hidden');
        if (hidden) hidden.value = relativeGambar;
        if (label) label.classList.add('border-blue-400', 'p-0');
    }
    container.appendChild(item);
    reindexModalMatchRights('modal');
    renderModalTeacherMatchingBoard('modal');
}

function modalRemoveMatchRight(btn) {
    const item = btn.closest('.modal-match-right-item');
    const isEdit = btn.closest('#edit-match-rights-container') !== null;
    const prefix = isEdit ? 'edit' : 'modal';
    if (item) item.remove();
    reindexModalMatchRights(prefix);
    renderModalTeacherMatchingBoard(prefix);
}

function reindexModalMatchRights(prefix = 'modal') {
    const container = document.getElementById(`${prefix}-match-rights-container`);
    if (!container) return;
    const items = container.querySelectorAll('.modal-match-right-item');
    items.forEach((item, idx) => {
        const label = alphabetList[idx] || `R${idx+1}`;
        const badge = item.querySelector('.modal-right-badge');
        if (badge) badge.textContent = label;

        const idInput = item.querySelector('.modal-right-id');
        const textInput = item.querySelector('.modal-right-text');
        const fileInput = item.querySelector('.modal-opsi-img');
        const existingInput = item.querySelector('.modal-existing-img');

        if (idInput) idInput.name = `right_items[${idx}][id]`;
        if (textInput) textInput.name = `right_items[${idx}][text]`;
        if (fileInput) fileInput.name = `right_items[${idx}][gambar]`;
        if (existingInput) existingInput.name = `right_items[${idx}][existing_gambar]`;
    });
}

function modalAddMatchLeft(text = '', gambarUrl = null, relativeGambar = null, customId = null) {
    const container = document.getElementById('modal-match-lefts-container');
    const template  = document.getElementById('modal-match-left-template');
    const lUid = customId || ('L' + Date.now() + Math.random().toString(36).substr(2, 4));
    const clone = template.content.cloneNode(true);
    const item = clone.querySelector('.modal-match-left-item');
    item.setAttribute('data-id', lUid);

    const idInput = clone.querySelector('.modal-left-id');
    const textInput = clone.querySelector('.modal-left-text');
    if (idInput) idInput.value = lUid;
    if (textInput) {
        textInput.value = text;
        textInput.addEventListener('input', () => renderModalTeacherMatchingBoard('modal'));
    }
    if (gambarUrl) {
        const preview = clone.querySelector('.modal-opsi-preview');
        const icon = clone.querySelector('.modal-opsi-icon');
        const hidden = clone.querySelector('.modal-existing-img');
        const label = preview ? preview.closest('label') : null;
        if (preview) { preview.src = gambarUrl; preview.classList.remove('hidden'); }
        if (icon) icon.classList.add('hidden');
        if (hidden) hidden.value = relativeGambar;
        if (label) label.classList.add('border-blue-400', 'p-0');
    }
    container.appendChild(item);
    reindexModalMatchLefts('modal');
    renderModalTeacherMatchingBoard('modal');
}

function modalRemoveMatchLeft(btn) {
    const item = btn.closest('.modal-match-left-item');
    const isEdit = btn.closest('#edit-match-lefts-container') !== null;
    const prefix = isEdit ? 'edit' : 'modal';
    if (item) item.remove();
    reindexModalMatchLefts(prefix);
    renderModalTeacherMatchingBoard(prefix);
}

function reindexModalMatchLefts(prefix = 'modal') {
    const container = document.getElementById(`${prefix}-match-lefts-container`);
    if (!container) return;
    const items = container.querySelectorAll('.modal-match-left-item');
    items.forEach((item, idx) => {
        const badge = item.querySelector('.modal-left-badge');
        if (badge) badge.textContent = `Item ${idx + 1}`;

        const idInput = item.querySelector('.modal-left-id');
        const textInput = item.querySelector('.modal-left-text');
        const fileInput = item.querySelector('.modal-opsi-img');
        const existingInput = item.querySelector('.modal-existing-img');

        if (idInput) idInput.name = `left_items[${idx}][id]`;
        if (textInput) textInput.name = `left_items[${idx}][text]`;
        if (fileInput) fileInput.name = `left_items[${idx}][gambar]`;
        if (existingInput) existingInput.name = `left_items[${idx}][existing_gambar]`;
    });
}

// Edit Modal matching functions
function editAddMatchRight(text = '', gambarUrl = null, relativeGambar = null, customId = null) {
    const container = document.getElementById('edit-match-rights-container');
    const template  = document.getElementById('modal-match-right-template');
    const rUid = customId || ('R' + Date.now() + Math.random().toString(36).substr(2, 4));
    const clone = template.content.cloneNode(true);
    const item = clone.querySelector('.modal-match-right-item');
    item.setAttribute('data-id', rUid);
    
    const idInput = clone.querySelector('.modal-right-id');
    const textInput = clone.querySelector('.modal-right-text');
    if (idInput) idInput.value = rUid;
    if (textInput) {
        textInput.value = text;
        textInput.addEventListener('input', () => renderModalTeacherMatchingBoard('edit'));
    }
    if (gambarUrl) {
        const preview = clone.querySelector('.modal-opsi-preview');
        const icon = clone.querySelector('.modal-opsi-icon');
        const hidden = clone.querySelector('.modal-existing-img');
        const label = preview ? preview.closest('label') : null;
        if (preview) { preview.src = gambarUrl; preview.classList.remove('hidden'); }
        if (icon) icon.classList.add('hidden');
        if (hidden) hidden.value = relativeGambar;
        if (label) label.classList.add('border-blue-400', 'p-0');
    }
    container.appendChild(item);
    reindexModalMatchRights('edit');
    renderModalTeacherMatchingBoard('edit');
}

function editRemoveMatchRight(btn) {
    modalRemoveMatchRight(btn);
}

function editAddMatchLeft(text = '', gambarUrl = null, relativeGambar = null, customId = null) {
    const container = document.getElementById('edit-match-lefts-container');
    const template  = document.getElementById('modal-match-left-template');
    const lUid = customId || ('L' + Date.now() + Math.random().toString(36).substr(2, 4));
    const clone = template.content.cloneNode(true);
    const item = clone.querySelector('.modal-match-left-item');
    item.setAttribute('data-id', lUid);

    const idInput = clone.querySelector('.modal-left-id');
    const textInput = clone.querySelector('.modal-left-text');
    if (idInput) idInput.value = lUid;
    if (textInput) {
        textInput.value = text;
        textInput.addEventListener('input', () => renderModalTeacherMatchingBoard('edit'));
    }
    if (gambarUrl) {
        const preview = clone.querySelector('.modal-opsi-preview');
        const icon = clone.querySelector('.modal-opsi-icon');
        const hidden = clone.querySelector('.modal-existing-img');
        const label = preview ? preview.closest('label') : null;
        if (preview) { preview.src = gambarUrl; preview.classList.remove('hidden'); }
        if (icon) icon.classList.add('hidden');
        if (hidden) hidden.value = relativeGambar;
        if (label) label.classList.add('border-blue-400', 'p-0');
    }
    container.appendChild(item);
    reindexModalMatchLefts('edit');
    renderModalTeacherMatchingBoard('edit');
}

function editRemoveMatchLeft(btn) {
    modalRemoveMatchLeft(btn);
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

// ── INIT UI ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
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
    const edEdit = document.getElementById('edit-pertanyaan-editor');
    if (edEdit) edEdit.innerHTML = soalData.pertanyaan || '';
    document.getElementById('edit-pertanyaan').value = soalData.pertanyaan || '';

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
    document.getElementById('edit-match-rights-container').innerHTML = '';
    document.getElementById('edit-match-lefts-container').innerHTML = '';
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
    } else if (soalData.tipe === 'menjodohkan' && soalData.data_soal) {
        const ds = soalData.data_soal;
        let pairs = [];
        if (ds.left_items && ds.right_items) {
            ds.right_items.forEach(r => {
                const gUrl = r.gambar ? (r.gambar.startsWith('http') ? r.gambar : `{{ asset('storage') }}/${r.gambar}`) : null;
                editAddMatchRight(r.text, gUrl, r.gambar, r.id);
            });
            pairs = ds.correct_pairs || [];
            ds.left_items.forEach(l => {
                const gUrl = l.gambar ? (l.gambar.startsWith('http') ? l.gambar : `{{ asset('storage') }}/${l.gambar}`) : null;
                editAddMatchLeft(l.text, gUrl, l.gambar, l.id);
            });
        } else if (ds.matches) {
            // Backward compatibility for legacy matches
            ds.matches.forEach((m, idx) => {
                const rId = 'R' + idx;
                const rGUrl = m.gambar_right ? (m.gambar_right.startsWith('http') ? m.gambar_right : `{{ asset('storage') }}/${m.gambar_right}`) : null;
                editAddMatchRight(m.right, rGUrl, m.gambar_right, rId);
            });
            ds.matches.forEach((m, idx) => {
                const lId = 'L' + idx;
                const lGUrl = m.gambar_left ? (m.gambar_left.startsWith('http') ? m.gambar_left : `{{ asset('storage') }}/${m.gambar_left}`) : null;
                editAddMatchLeft(m.left, lGUrl, m.gambar_left, lId);
                pairs.push({ left: lId, right: 'R' + idx });
            });
        }
        // Minimal 2 rights and 2 lefts
        const rightContainer = document.getElementById('edit-match-rights-container');
        while (rightContainer.querySelectorAll('.modal-match-right-item').length < 2) {
            editAddMatchRight();
        }
        const leftContainer = document.getElementById('edit-match-lefts-container');
        while (leftContainer.querySelectorAll('.modal-match-left-item').length < 2) {
            editAddMatchLeft();
        }

        window._modalMatchState.edit = { pairs: pairs, selectedLeft: null, selectedRight: null };
        const jsonInput = document.getElementById('edit-correct-pairs-json');
        if (jsonInput) jsonInput.value = JSON.stringify(pairs);
        renderModalTeacherMatchingBoard('edit');
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

    if (tipe === 'menjodohkan') {
        setTimeout(() => renderModalTeacherMatchingBoard('edit'), 100);
    }
}

function editPreviewGambar(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > 2 * 1024 * 1024) {
            showNotificationModal('File Terlalu Besar', 'Ukuran gambar maksimal 2MB!', 'error');
            input.value = '';
            return;
        }
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
        const isEdit = form.id === 'form-edit-soal';
        const prefix = isEdit ? 'edit' : 'modal';
        const rightContainerId = `${prefix}-match-rights-container`;
        const leftContainerId = `${prefix}-match-lefts-container`;
        
        const rightItems = document.querySelectorAll(`#${rightContainerId} .modal-match-right-item`);
        const leftItems = document.querySelectorAll(`#${leftContainerId} .modal-match-left-item`);
        
        if (rightItems.length < 1) {
            isValid = false;
            errorMsg = 'Soal Menjodohkan minimal harus memiliki 1 pilihan jawaban (sisi kanan)!';
        } else if (leftItems.length < 1) {
            isValid = false;
            errorMsg = 'Soal Menjodohkan minimal harus memiliki 1 premis / pertanyaan (sisi kiri)!';
        } else {
            // Check right items have text or image
            let anyRightEmpty = false;
            rightItems.forEach(r => {
                const text = (r.querySelector('.modal-right-text')?.value || '').trim();
                const fileInp = r.querySelector('input[type="file"]')?.files.length || 0;
                const existing = r.querySelector('.modal-existing-img')?.value || '';
                const preview = r.querySelector('.modal-opsi-preview');
                const hasPreview = preview && !preview.classList.contains('hidden') && preview.getAttribute('src');
                if (!text && fileInp === 0 && !existing && !hasPreview) anyRightEmpty = true;
            });

            if (anyRightEmpty) {
                isValid = false;
                errorMsg = 'Semua pilihan jawaban (sisi kanan) harus memiliki teks atau gambar!';
            } else {
                // Check left items have text or image
                let anyLeftEmpty = false;
                leftItems.forEach(l => {
                    const text = (l.querySelector('.modal-left-text')?.value || '').trim();
                    const fileInp = l.querySelector('input[type="file"]')?.files.length || 0;
                    const existing = l.querySelector('.modal-existing-img')?.value || '';
                    const preview = l.querySelector('.modal-opsi-preview');
                    const hasPreview = preview && !preview.classList.contains('hidden') && preview.getAttribute('src');
                    if (!text && fileInp === 0 && !existing && !hasPreview) anyLeftEmpty = true;
                });

                if (anyLeftEmpty) {
                    isValid = false;
                    errorMsg = 'Semua premis / pertanyaan (sisi kiri) harus memiliki teks atau gambar!';
                } else {
                    let pairs = [];
                    try {
                        const raw = form.querySelector('input[name="correct_pairs_json"]')?.value || '[]';
                        pairs = (typeof raw === 'string') ? JSON.parse(raw) : raw;
                    } catch(e) { pairs = []; }

                    if (!pairs || pairs.length === 0) {
                        isValid = false;
                        errorMsg = 'Hubungkan minimal satu pasangan kunci jawaban pada Papan Kunci Pasangan!';
                    }
                }
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

