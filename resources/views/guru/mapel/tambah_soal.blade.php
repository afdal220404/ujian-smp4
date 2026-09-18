@extends('layouts.app')

@section('title', 'Kelola Soal Ujian')

@section('sidebar-menu')
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

{{-- STEPPER --}}
<div class="max-w-3xl mx-auto mb-8">
    <div class="flex items-center justify-center">
        @php
            $prevRoute = isset($ujian) 
                ? route('guru.mapel.ujian.edit', $ujian->id) 
                : route('guru.mapel.ujian.review', isset($mapel) ? $mapel->id : session('ujian_temp_details')['mapel_id']);
        @endphp
        <a href="{{ $prevRoute }}" class="flex flex-col items-center group cursor-pointer hover:opacity-80 transition-opacity">
            <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center font-bold shadow-md z-10">
                <i class="bi bi-check-lg"></i>
            </div>
            <span class="mt-2 text-xs font-bold text-green-600 uppercase tracking-wider">Info Ujian</span>
        </a>
        <div class="w-24 h-1 bg-green-500 -mt-6 mx-2"></div>
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold shadow-lg ring-4 ring-blue-100 z-10">
                2
            </div>
            <span class="mt-2 text-xs font-bold text-blue-600 uppercase tracking-wider">Input Soal</span>
        </div>
    </div>
</div>

{{-- HEADER INFO --}}
<div class="text-center mb-8">
    <h2 class="text-2xl font-[Poppins-Bold] text-darkblue">Bank Soal Ujian</h2>
    @if(session('ujian_temp_details'))
        <p class="text-sm text-gray-500 mt-1">
            Menambahkan soal untuk: <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ session('ujian_temp_details')['nama_ujian'] }}</span>
        </p>
    @endif
</div>

@if ($errors->any())
<div class="max-w-4xl mx-auto mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl shadow-sm">
    <p class="font-bold text-sm mb-1"><i class="bi bi-exclamation-triangle-fill"></i> Terdapat Kesalahan:</p>
    <ul class="list-disc list-inside text-xs ml-4">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<form action="{{ route('guru.mapel.soal.store-temp', ['ujian' => $ujian->id ?? null]) }}" method="POST" enctype="multipart/form-data" class="max-w-5xl mx-auto" id="form-soal" novalidate>
    @csrf

    <div id="soal-list-container" class="space-y-6">
        @forelse ($tempSoals as $index => $soal)
            @include('guru.mapel.partials.soal_card', ['index' => $index, 'soal' => $soal])
        @empty
            {{-- Initial empty state handled by JS --}}
        @endforelse
    </div>

    {{-- ACTION FLOATING BAR --}}
    <div class="sticky bottom-6 mt-8 p-4 bg-white/90 backdrop-blur-md border border-gray-200 shadow-2xl rounded-2xl flex justify-between items-center z-40 max-w-5xl mx-auto">
        <div class="flex gap-3">
            <button type="button" id="add-soal-btn" class="px-5 py-2.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl font-bold hover:bg-blue-600 hover:text-white transition-all text-sm flex items-center gap-2">
                <i class="bi bi-plus-lg"></i> Tambah Soal Baru
            </button>
            <button type="button" onclick="openImportModal()" class="px-5 py-2.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-xl font-bold hover:bg-purple-600 hover:text-white transition-all text-sm flex items-center gap-2">
                <i class="bi bi-download"></i> Import dari Bank Soal
            </button>
        </div>

        <div class="flex gap-3">
             <a href="{{ $prevRoute }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all text-sm">
                Batal
            </a>
            <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all text-sm flex items-center gap-2">
                <i class="bi bi-save2-fill"></i> Simpan Semua Soal
            </button>
        </div>
    </div>

</form>

{{-- TEMPLATE UNTUK SOAL BARU (JS) --}}
<template id="soal-card-template">
    <div class="soal-card bg-white rounded-2xl shadow-[0_2px_15px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden group hover:border-blue-200 transition-all mb-6" data-index="idx">
        {{-- Header --}}
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-lg">No. <span class="soal-nomor">1</span></span>
                
                {{-- Tipe Soal Select --}}
                <select name="soal[idx][tipe]" class="soal-tipe-select bg-white border border-gray-200 text-gray-700 text-xs font-bold py-1 px-3 rounded-lg focus:outline-none focus:border-blue-500 uppercase">
                    <option value="pilihan_ganda">Pilihan Ganda</option>
                    <option value="benar_salah">Benar / Salah</option>
                    <option value="jawaban_ganda">Pilih Banyak Jawaban</option>
                    <option value="menjodohkan">Mencocokkan</option>
                </select>
            </div>
            <button type="button" class="delete-soal-btn text-gray-400 hover:text-red-500 transition-colors" title="Hapus Soal">
                <i class="bi bi-trash3-fill text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            {{-- 1. Baris Atas: Pertanyaan (Kiri) & Gambar Pendukung + Kunci PG (Kanan) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
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
                    <div contenteditable="true" class="soal-editor w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800" data-placeholder="Tulis pertanyaan disini..."></div>
                    <textarea name="soal[idx][pertanyaan]" class="soal-textarea hidden"></textarea>
                </div>

                {{-- Kanan: Gambar Pendukung & Kunci PG --}}
                <div class="lg:col-span-4 flex flex-col gap-3">
                    {{-- Upload Gambar --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Pendukung</label>
                        <div class="soal-image-upload relative w-full h-[110px] border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden group/upload">
                            <img class="image-preview absolute inset-0 w-full h-full object-contain bg-white p-2" style="display: none;">
                            <div class="upload-text text-center p-2">
                                <i class="bi bi-cloud-arrow-up-fill text-2xl text-gray-300 group-hover/upload:text-blue-500 transition-colors"></i>
                                <p class="text-[11px] text-gray-500 mt-0.5 font-medium">Upload Gambar</p>
                            </div>
                            <input type="file" name="soal[idx][gambar]" class="soal-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            <input type="hidden" name="soal[idx][existing_gambar]" class="existing-main-img">
                        </div>
                    </div>

                    {{-- Kunci Jawaban (HANYA UNTUK PILIHAN GANDA) --}}
                    <div class="key-section key-pilihan_ganda bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <label class="block text-[11px] font-bold text-blue-800 uppercase mb-1">Kunci Jawaban PG</label>
                        <div class="relative">
                            <select name="soal[idx][kunci_jawaban]" class="w-full px-3 py-1.5 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-xs font-bold text-blue-700 appearance-none cursor-pointer">
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
            </div>

            {{-- 2. Baris Bawah: Container Jawaban Dinamis (FULL WIDTH) --}}
            <div class="answers-container space-y-4 w-full">
                {{-- 1. PILIHAN GANDA (Default) --}}
                <div class="type-section type-pilihan_ganda space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        @foreach(['a','b','c','d'] as $opsi)
                        <div class="flex items-center gap-2.5 p-2 bg-slate-50/70 border border-slate-200/80 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center shrink-0 border border-blue-200 uppercase text-xs">
                                {{ $opsi }}
                            </div>
                            <input type="text" name="soal[idx][opsi_{{ $opsi }}]" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                            
                            {{-- Option Image Placeholder --}}
                            <div class="option-image-upload shrink-0 relative w-9 h-9 border border-dashed border-gray-300 rounded-lg bg-white hover:bg-gray-50 cursor-pointer overflow-hidden group">
                                <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
                                <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500">
                                    <i class="bi bi-image text-xs"></i>
                                </div>
                                <input type="file" name="soal[idx][gambar_{{ $opsi }}]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                <input type="hidden" name="soal[idx][existing_gambar_{{ $opsi }}]" class="opt-existing-img">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 2. BENAR / SALAH (DYNAMIC) --}}
                <div class="type-section type-benar_salah hidden space-y-3">
                    <div class="bg-green-50 p-3 rounded-lg text-xs text-green-700 mb-2">
                        <i class="bi bi-info-circle mr-1"></i> Klik "Tambah Pilihan" jika diperlukan. Pilih radio button untuk menandai kunci jawaban.
                    </div>
                    
                    <div class="bs-options-container space-y-2">
                        {{-- Opsi B/S akan ditambahkan lewat JS --}}
                    </div>

                    <button type="button" class="add-bs-btn text-xs font-bold text-green-600 hover:text-green-800 flex items-center gap-1 mt-2">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Pilihan
                    </button>
                </div>

                {{-- 3. JAWABAN GANDA --}}
                <div class="type-section type-jawaban_ganda hidden space-y-3">
                    <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700 mb-2">
                        <i class="bi bi-info-circle mr-1"></i> Klik "Tambah Opsi" untuk menambah pilihan jawaban. Centang kotak di kanan untuk menandai jawaban benar.
                    </div>
                    
                    <div class="jg-options-container space-y-2">
                        {{-- Opsi Jawaban Ganda akan ditambahkan lewat JS --}}
                    </div>

                    <button type="button" class="add-jg-btn text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-2">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Opsi Jawaban
                    </button>
                </div>

                {{-- 4. MENCOCOKKAN (INTERAKTIF DENGAN PAPAN GARIS PENGHUBUNG) --}}
                <div class="type-section type-menjodohkan hidden space-y-4">
                    <div class="bg-blue-50 p-3.5 rounded-xl text-xs text-blue-700 border border-blue-100 flex items-start gap-2">
                        <i class="bi bi-info-circle-fill text-blue-500 mt-0.5 shrink-0"></i>
                        <div>
                            <b>Panduan Membuat Soal Mencocokkan:</b>
                            <ol class="list-decimal list-inside mt-1 space-y-0.5 text-blue-800">
                                <li>Tambahkan item pada daftar <b>Premis (Kiri)</b> dan <b>Pilihan Jawaban (Kanan)</b>.</li>
                                <li>Tentukan kunci jawaban pada <b>Papan Kunci Pasangan</b> di bawah dengan cara mengklik item kiri lalu mengklik item kanan untuk menarik garis.</li>
                                <li>Bebas menghubungkan 1-ke-1, 1-ke-banyak, maupun membiarkan item tanpa pasangan .</li>
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
                                <button type="button" class="add-match-left-btn text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-blue-200 shadow-2xs">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Item Kiri
                                </button>
                            </div>
                            <div class="match-lefts-container space-y-2">
                                {{-- Premis Kiri akan ditambahkan lewat JS --}}
                            </div>
                        </div>

                        {{-- SISI KANAN (PILIHAN JAWABAN) --}}
                        <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="bi bi-list-check text-emerald-600 text-sm"></i> Pilihan (Sisi Kanan)
                                </span>
                                <button type="button" class="add-match-right-btn text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Pilihan Kanan
                                </button>
                            </div>
                            <div class="match-rights-container space-y-2">
                                {{-- Opsi Kanan akan ditambahkan lewat JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- PAPAN KUNCI PASANGAN (HUBUNGKAN DENGAN GARIS) --}}
                    <div class="teacher-matching-board bg-white p-4 rounded-2xl border-2 border-indigo-100 shadow-sm space-y-3">
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
                            <button type="button" class="teacher-reset-match-btn text-[11px] font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg border border-rose-200 transition-colors flex items-center gap-1">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Garis
                            </button>
                        </div>

                        {{-- Interactive Canvas --}}
                        <div class="teacher-match-canvas relative select-none p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 min-h-[160px]">
                            <svg class="absolute inset-0 w-full h-full pointer-events-none z-10 teacher-match-svg"></svg>
                            
                            <div class="flex flex-col sm:flex-row justify-between relative z-20 gap-6 sm:gap-10">
                                {{-- Sisi Kiri Board --}}
                                <div class="teacher-board-left flex-1 space-y-2.5">
                                    {{-- Dynamically populated by JS --}}
                                </div>

                                {{-- Sisi Kanan Board --}}
                                <div class="teacher-board-right flex-1 space-y-2.5">
                                    {{-- Dynamically populated by JS --}}
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="soal[idx][correct_pairs_json]" class="match-pairs-json" value="[]">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- TEMPLATE MATCH RIGHT ITEM --}}
<template id="match-right-template">
    <div class="match-right-item flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-200 shadow-2xs" data-id="RUID">
        <div class="right-label-badge w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs flex items-center justify-center shrink-0">
            A
        </div>
        <input type="hidden" name="soal[idx][right_items][ridx][id]" value="RUID" class="right-id-input">
        <input type="text" name="soal[idx][right_items][ridx][text]" class="right-text-input flex-1 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:bg-white focus:border-emerald-500 outline-none" placeholder="Tulis pilihan...">
        
        {{-- Option Image --}}
        <div class="option-image-upload shrink-0 relative w-8 h-8 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
            <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
            <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-emerald-500">
                <i class="bi bi-image text-xs"></i>
            </div>
            <input type="file" name="soal[idx][right_items][ridx][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
            <input type="hidden" name="soal[idx][right_items][ridx][existing_gambar]" class="right-existing-img">
        </div>

        <button type="button" class="remove-match-right-btn text-slate-300 hover:text-rose-500 shrink-0 p-1">
            <i class="bi bi-trash3-fill text-sm"></i>
        </button>
    </div>
</template>

{{-- TEMPLATE MATCH LEFT ITEM --}}
<template id="match-left-template">
    <div class="match-left-item flex items-center gap-2 p-2 bg-white rounded-xl border border-slate-200 shadow-2xs" data-id="LUID">
        <span class="left-num-badge px-2 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs shrink-0">
            1
        </span>
        <input type="hidden" name="soal[idx][left_items][lidx][id]" value="LUID" class="left-id-input">
        <input type="text" name="soal[idx][left_items][lidx][text]" class="left-text-input flex-1 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:bg-white focus:border-blue-500 outline-none" placeholder="Tulis premis...">
        
        {{-- Option Image --}}
        <div class="option-image-upload shrink-0 relative w-8 h-8 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
            <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
            <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500">
                <i class="bi bi-image text-xs"></i>
            </div>
            <input type="file" name="soal[idx][left_items][lidx][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
            <input type="hidden" name="soal[idx][left_items][lidx][existing_gambar]" class="left-existing-img">
        </div>

        <button type="button" class="remove-match-left-btn text-slate-300 hover:text-rose-500 shrink-0 p-1">
            <i class="bi bi-trash3-fill text-sm"></i>
        </button>
    </div>
</template>

{{-- TEMPLATE ITEM JAWABAN GANDA --}}
<template id="jg-item-template">
    <div class="jg-item flex items-center gap-3">
        <div class="jg-label w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">A</div>
        <input type="text" name="soal[idx][jg_options][jidx][text]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan Jawaban">
        
        {{-- Option Image Placeholder --}}
        <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
            <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
            <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500">
                <i class="bi bi-image text-sm"></i>
            </div>
            <input type="file" name="soal[idx][jg_options][jidx][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
            <input type="hidden" name="soal[idx][jg_options][jidx][existing_gambar]" class="jg-existing-img">
        </div>

        <div class="shrink-0 flex items-center gap-3">
            <input type="checkbox" name="soal[idx][kunci_jawaban_jg][]" value="LABEL" class="jg-checkbox w-6 h-6 text-blue-600 rounded focus:ring-blue-500 border-gray-300 cursor-pointer" title="Tandai sebagai jawaban benar">
            <button type="button" class="remove-jg-btn text-red-400 hover:text-red-600">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>
</template>

{{-- TEMPLATE ITEM BENAR SALAH --}}
<template id="bs-item-template">
    <div class="bs-item flex items-center gap-3">
        {{-- Text & Image --}}
        <div class="flex-1 flex items-center gap-2">
            <input type="text" name="soal[idx][bs_pernyataan][jidx][text]" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-green-500 outline-none text-sm" placeholder="Tulis Pernyataan...">
            
            {{-- Option Image Placeholder --}}
            <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
                <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500">
                    <i class="bi bi-image text-sm"></i>
                </div>
                <input type="file" name="soal[idx][bs_pernyataan][jidx][gambar]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                <input type="hidden" name="soal[idx][bs_pernyataan][jidx][existing_gambar]" class="bs-existing-img">
            </div>
        </div>

        {{-- Choice: Benar / Salah --}}
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl border border-gray-200 shrink-0">
            <label class="cursor-pointer px-3 py-1.5 rounded-lg transition-all text-[10px] font-bold has-[:checked]:bg-green-600 has-[:checked]:text-white text-gray-500 hover:bg-gray-200">
                <input type="radio" name="soal[idx][bs_pernyataan][jidx][correct]" value="TRUE" class="hidden">
                BENAR
            </label>
            <div class="w-px h-4 bg-gray-300"></div>
            <label class="cursor-pointer px-3 py-1.5 rounded-lg transition-all text-[10px] font-bold has-[:checked]:bg-red-600 has-[:checked]:text-white text-gray-500 hover:bg-gray-200">
                <input type="radio" name="soal[idx][bs_pernyataan][jidx][correct]" value="FALSE" class="hidden">
                SALAH
            </label>
        </div>

        <button type="button" class="remove-bs-btn text-red-300 hover:text-red-500 shrink-0">
            <i class="bi bi-x-circle-fill text-lg"></i>
        </button>
    </div>
</template>

{{-- =========================================================
     MODAL IMPORT DARI BANK SOAL
========================================================== --}}
<div id="modal-import-bank" class="fixed inset-0 z-[60] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-up">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-purple-50">
                <div>
                    <h3 class="font-[Poppins-Bold] text-lg text-purple-800">
                        <i class="bi bi-archive-fill mr-2"></i>Import dari Bank Soal
                    </h3>
                    <p class="text-xs text-purple-600 mt-0.5">Pilih butir soal yang ingin Anda masukkan ke dalam ujian ini.</p>
                </div>
                <button type="button" onclick="closeImportModal()" class="w-9 h-9 rounded-lg bg-white/50 text-purple-500 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Filter & Search --}}
            <div class="p-4 border-b border-gray-100 bg-gray-50 flex gap-4">
                <div class="relative flex-1">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="search-bank" oninput="filterBankItems()" placeholder="Cari pertanyaan..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:border-purple-500 outline-none text-sm transition-all">
                </div>
                <select id="filter-tipe-bank" onchange="filterBankItems()" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-purple-500">
                    <option value="all">Semua Tipe</option>
                    <option value="pilihan_ganda">Pilihan Ganda</option>
                    <option value="benar_salah">Benar / Salah</option>
                    <option value="jawaban_ganda">Jawaban Ganda</option>
                    <option value="menjodohkan">Menjodohkan</option>
                </select>
            </div>

            {{-- List Soal --}}
            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                <div id="bank-items-container" class="space-y-3">
                    {{-- Loading State --}}
                    <div id="bank-loading" class="py-12 flex flex-col items-center justify-center text-gray-400">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500 mb-3"></div>
                        <p class="text-sm">Memuat data bank soal...</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                <span class="text-xs font-medium text-gray-500"><span id="selected-count">0</span> soal dipilih</span>
                <div class="flex gap-3">
                    <button type="button" onclick="closeImportModal()" class="px-5 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-100 transition-colors">Batal</button>
                    <button type="button" onclick="importSelectedItems()" id="btn-import-confirm" disabled class="px-8 py-2 bg-purple-600 text-white rounded-xl text-sm font-bold hover:bg-purple-700 shadow-lg shadow-purple-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        Import Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
.animate-fade-in-up { animation: fadeInUp 0.3s ease-out; }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
{{-- =========================================================
     MODAL VALIDASI ERROR KUSTOM
========================================================== --}}
<div id="modal-validation-error" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeValidationModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div id="validation-modal-content" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 flex flex-col items-center text-center transform scale-95 opacity-0 transition-all duration-300">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-3xl mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h3 class="font-[Poppins-Bold] text-lg text-gray-800 mb-2">Periksa Kembali Soal Anda</h3>
            <p id="validation-error-msg" class="text-sm text-gray-600 mb-6 font-medium leading-relaxed">Pesan error disini.</p>
            <button type="button" onclick="closeValidationModal()" class="w-full px-5 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-bold hover:bg-red-600 hover:text-white transition-all">
                Mengerti & Perbaiki
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.formatDoc = function(button, cmd) {
        const container = button.closest('.pertanyaan-container') || button.closest('.soal-card') || button.closest('form');
        const editor = container ? container.querySelector('.soal-editor, [contenteditable="true"]') : null;
        if (!editor) return;

        editor.focus();
        document.execCommand(cmd, false, null);

        const textarea = container.querySelector('.soal-textarea, textarea[name*="pertanyaan"], textarea');
        if (textarea) {
            textarea.value = editor.innerHTML;
        }
    };

    // Sync contenteditable with hidden textarea on typing
    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('soal-editor')) {
            const container = e.target.closest('.pertanyaan-container') || e.target.parentElement;
            const textarea = container ? container.querySelector('.soal-textarea, textarea[name*="pertanyaan"], textarea') : null;
            if (textarea) {
                textarea.value = e.target.innerHTML;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('soal-list-container');
        const template = document.getElementById('soal-card-template');
        const matchRightTemplate = document.getElementById('match-right-template');
        const matchLeftTemplate = document.getElementById('match-left-template');
        const jgTemplate = document.getElementById('jg-item-template');
        const bsTemplate = document.getElementById('bs-item-template');
        let soalCounter = {{ count($tempSoals) }};
        let bankItems = [];
        let currentBankSoalIds = new Set(); // To track duplicates for import
        const formSoal = document.getElementById('form-soal');
        if (formSoal) {
            formSoal.addEventListener('submit', function(e) {
                if (!validateSemuaSoal()) {
                    e.preventDefault(); // Hentikan pengiriman jika ada yang tidak valid
                }
            });
        }

        window.closeValidationModal = function() {
            const modal = document.getElementById('modal-validation-error');
            const content = document.getElementById('validation-modal-content');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        function showValidationModal(msg) {
            document.getElementById('validation-error-msg').textContent = msg;
            const modal = document.getElementById('modal-validation-error');
            const content = document.getElementById('validation-modal-content');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function validateSemuaSoal() {
            const cards = document.querySelectorAll('#soal-list-container .soal-card');
            
            if (cards.length === 0) {
                showValidationModal("Anda belum menambahkan satu pun soal. Silakan tambah soal baru atau import dari bank soal.");
                return false;
            }

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const nomor = card.querySelector('.soal-nomor').textContent.trim();
                const type = card.querySelector('.soal-tipe-select').value;
                const editor = card.querySelector('.soal-editor');
                const textarea = card.querySelector('.soal-textarea');

                if (editor && textarea) {
                    textarea.value = editor.innerHTML;
                }

                const pertanyaanText = editor ? editor.innerText.trim() : (textarea ? textarea.value.trim() : '');

                let namaTipe = '';
                if(type === 'pilihan_ganda') namaTipe = 'Pilihan Ganda';
                if(type === 'jawaban_ganda') namaTipe = 'Pilih Banyak Jawaban';
                if(type === 'menjodohkan') namaTipe = 'Mencocokkan';
                if(type === 'benar_salah') namaTipe = 'Benar / Salah';

                // VALIDASI UMUM: Pertanyaan kosong
                if (!pertanyaanText && (!editor || !editor.querySelector('img'))) {
                    showValidationModal(`Soal No. ${nomor} (${namaTipe}): Teks pertanyaan tidak boleh kosong.`);
                    return false;
                }

                // 1. VALIDASI PILIHAN GANDA
                if (type === 'pilihan_ganda') {
                    const kunci = card.querySelector('select[name*="[kunci_jawaban]"]').value;
                    if (!kunci) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Kunci jawaban belum dipilih.`);
                        return false;
                    }
                    
                    const opsiInputs = card.querySelectorAll('.type-pilihan_ganda input[type="text"]');
                    let adaOpsiKosong = false;
                    opsiInputs.forEach(input => { if(!input.value.trim()) adaOpsiKosong = true; });
                    
                    if (adaOpsiKosong) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Semua opsi jawaban (A, B, C, D) harus diisi.`);
                        return false;
                    }
                } 
                
                // 2. VALIDASI JAWABAN GANDA
                else if (type === 'jawaban_ganda') {
                    const opsiItems = card.querySelectorAll('.jg-item input[type="text"]');
                    if (opsiItems.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Minimal harus ada satu opsi jawaban yang ditambahkan.`);
                        return false;
                    }

                    let adaOpsiKosong = false;
                    opsiItems.forEach(input => { if(!input.value.trim()) adaOpsiKosong = true; });
                    if (adaOpsiKosong) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Opsi jawaban yang ditambahkan tidak boleh kosong.`);
                        return false;
                    }

                    const checkedOpsi = card.querySelectorAll('.jg-checkbox:checked');
                    if (checkedOpsi.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Harus ada minimal satu kunci jawaban yang dicentang.`);
                        return false;
                    }
                }
                
                // 3. VALIDASI MENCOCOKKAN (MENJODOHKAN)
                else if (type === 'menjodohkan') {
                    const rightItems = card.querySelectorAll('.match-right-item');
                    const leftItems = card.querySelectorAll('.match-left-item');

                    if (rightItems.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Tambahkan minimal satu Pilihan Jawaban (Sisi Kanan).`);
                        return false;
                    }

                    if (leftItems.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Tambahkan minimal satu Premis / Pertanyaan (Sisi Kiri).`);
                        return false;
                    }

                    let adaRightKosong = false;
                    rightItems.forEach(item => {
                        const txt = item.querySelector('.right-text-input')?.value.trim();
                        const file = item.querySelector('.opt-file-input')?.files.length;
                        const exist = item.querySelector('.right-existing-img')?.value;
                        if (!txt && !file && !exist) adaRightKosong = true;
                    });
                    if (adaRightKosong) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Semua pilihan jawaban sisi kanan harus diisi teks atau gambar.`);
                        return false;
                    }

                    let adaLeftKosong = false;
                    leftItems.forEach(item => {
                        const txt = item.querySelector('.left-text-input')?.value.trim();
                        const file = item.querySelector('.opt-file-input')?.files.length;
                        const exist = item.querySelector('.left-existing-img')?.value;
                        if (!txt && !file && !exist) adaLeftKosong = true;
                    });

                    if (adaLeftKosong) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Semua premis / kategori sisi kiri harus diisi teks atau gambar.`);
                        return false;
                    }

                    let pairs = [];
                    try {
                        const rawPairs = card.querySelector('.match-pairs-json')?.value || '[]';
                        pairs = (typeof rawPairs === 'string') ? JSON.parse(rawPairs) : rawPairs;
                    } catch(e) { pairs = []; }

                    if (!pairs || pairs.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Hubungkan minimal satu pasangan kunci jawaban pada Papan Kunci Pasangan.`);
                        return false;
                    }
                }
                
                // 4. VALIDASI BENAR SALAH
                else if (type === 'benar_salah') {
                    const bsItems = card.querySelectorAll('.bs-item');
                    if (bsItems.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Minimal harus ada satu pernyataan.`);
                        return false;
                    }

                    let adaPernyataanKosong = false;
                    let adaBelumDipilih = false;
                    
                    bsItems.forEach(item => {
                        const text = item.querySelector('input[type="text"]').value.trim();
                        if (!text) adaPernyataanKosong = true;
                        
                        // Cek apakah ada radio button yg di-check dalam 1 baris pernyataan
                        const isChecked = item.querySelector('input[type="radio"]:checked');
                        if (!isChecked) adaBelumDipilih = true;
                    });

                    if (adaPernyataanKosong) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Teks pernyataan tidak boleh kosong.`);
                        return false;
                    }

                    if (adaBelumDipilih) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Semua pernyataan harus ditentukan status BENAR atau SALAH-nya.`);
                        return false;
                    }
                }
            }
            
            // Jika semua lolos validasi
            return true;
        }

        // --- CORE FUNCTIONS ---

        function bindCardEvents(card) {
            const index = card.getAttribute('data-index');
            const typeSelect = card.querySelector('.soal-tipe-select');
            
            typeSelect.addEventListener('change', () => updateCardUI(card, typeSelect.value));
            updateCardUI(card, typeSelect.value);

            card.querySelector('.delete-soal-btn').addEventListener('click', () => {
                Swal.fire({
                    title: 'Hapus Soal?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444'
                }).then((res) => { 
                    if(res.isConfirmed) { 
                        const bankId = card.querySelector('input[name*="[bank_soal_id]"]')?.value;
                        if(bankId) currentBankSoalIds.delete(parseInt(bankId));
                        card.remove(); 
                        reindexSoal(); 
                    }
                });
            });

            // Handle main image and all option images
            initImageUpload(card.querySelector('.soal-image-upload'), card.querySelector('.soal-file-input'), card.querySelector('.image-preview'), card.querySelector('.upload-text'));
            
            // Initialization for static options (Pilihan Ganda)
            card.querySelectorAll('.option-image-upload').forEach(box => {
                const input = box.querySelector('.opt-file-input');
                const preview = box.querySelector('.opt-preview');
                const btn = box.querySelector('.opt-upload-btn');
                initImageUpload(box, input, preview, btn);
            });

            // Matching Logic
            const addRightBtn = card.querySelector('.add-match-right-btn');
            if(addRightBtn) addRightBtn.addEventListener('click', () => addMatchRightItem(card));

            const addLeftBtn = card.querySelector('.add-match-left-btn');
            if(addLeftBtn) addLeftBtn.addEventListener('click', () => addMatchLeftItem(card));

            // Bind existing remove buttons in matching section
            card.querySelectorAll('.match-right-item').forEach(item => {
                const rmBtn = item.querySelector('.remove-match-right-btn');
                if (rmBtn) {
                    rmBtn.onclick = () => { item.remove(); reindexMatchRights(card); };
                }
                const txt = item.querySelector('.right-text-input');
                if (txt) {
                    txt.oninput = () => renderTeacherMatchingBoard(card);
                }
            });

            card.querySelectorAll('.match-left-item').forEach(item => {
                const rmBtn = item.querySelector('.remove-match-left-btn');
                if (rmBtn) {
                    rmBtn.onclick = () => { item.remove(); reindexMatchLefts(card); };
                }
                const txt = item.querySelector('.left-text-input');
                if (txt) {
                    txt.oninput = () => renderTeacherMatchingBoard(card);
                }
            });

            // Init Teacher Matching Canvas
            initTeacherMatching(card);

            // Jawaban Ganda Logic
            const addJGBtn = card.querySelector('.add-jg-btn');
            if(addJGBtn) addJGBtn.addEventListener('click', () => addJGItem(card));

            // Benar Salah Logic
            const addBSBtn = card.querySelector('.add-bs-btn');
            if(addBSBtn) addBSBtn.addEventListener('click', () => addBSItem(card));
        }

        function updateCardUI(card, type) {
            card.querySelectorAll('.type-section').forEach(el => el.classList.add('hidden'));
            card.querySelectorAll('.key-section').forEach(el => el.classList.add('hidden'));

            const activeSection = card.querySelector(`.type-${type}`);
            if(activeSection) activeSection.classList.remove('hidden');

            const activeKey = card.querySelector(`.key-${type}`);
            if(activeKey) activeKey.classList.remove('hidden');

            if (type === 'menjodohkan') {
                setTimeout(() => {
                    renderTeacherMatchingBoard(card);
                }, 100);
            }
        }

        function initImageUpload(box, input, preview, textElement) {
            if(!box || !input) return;
            box.addEventListener('click', (e) => {
                if (e.target !== input) input.click();
            });

            input.addEventListener('change', () => {
                const file = input.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire('File Terlalu Besar', 'Maksimal 2MB', 'error');
                        input.value = "";
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        if(textElement) textElement.style.display = 'none';
                        const card = box.closest('.soal-card');
                        if (card) renderTeacherMatchingBoard(card);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // --- MATCHING (INTERACTIVE CANVAS & 2-COLUMN INPUTS) ---
        const alphabetList = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

        const teacherPalette = [
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

        function initTeacherMatching(card) {
            const board = card.querySelector('.teacher-matching-board');
            if (!board) return;

            const jsonInput = card.querySelector('.match-pairs-json');
            let initialPairs = [];
            try {
                const raw = jsonInput.value || '[]';
                initialPairs = (typeof raw === 'string') ? JSON.parse(raw) : raw;
                if (!Array.isArray(initialPairs)) {
                    initialPairs = [];
                }
            } catch(e) { initialPairs = []; }

            card._matchPairs = initialPairs;
            card._selectedLeft = null;
            card._selectedRight = null;

            const resetBtn = card.querySelector('.teacher-reset-match-btn');
            if (resetBtn) {
                resetBtn.onclick = () => {
                    card._matchPairs = [];
                    card._selectedLeft = null;
                    card._selectedRight = null;
                    if (jsonInput) jsonInput.value = '[]';
                    renderTeacherMatchingBoard(card);
                };
            }

            renderTeacherMatchingBoard(card);
        }

        function renderTeacherMatchingBoard(card) {
            const board = card.querySelector('.teacher-matching-board');
            if (!board) return;

            const boardLeft = card.querySelector('.teacher-board-left');
            const boardRight = card.querySelector('.teacher-board-right');
            const jsonInput = card.querySelector('.match-pairs-json');

            const leftItems = card.querySelectorAll('.match-left-item');
            const rightItems = card.querySelectorAll('.match-right-item');

            const currentPairs = card._matchPairs || [];
            const validLeftIds = new Set();
            const validRightIds = new Set();

            // Render Left Column of Board
            let leftHtml = '';
            if (leftItems.length === 0) {
                leftHtml = '<p class="text-xs text-gray-400 italic p-3 text-center bg-white rounded-xl border border-dashed border-gray-200">Belum ada item premis kiri.</p>';
            } else {
                leftItems.forEach((lItem, idx) => {
                    const lId = lItem.getAttribute('data-id') || lItem.querySelector('.left-id-input')?.value;
                    validLeftIds.add(lId);
                    const lText = lItem.querySelector('.left-text-input')?.value || `Premis ${idx + 1}`;
                    const imgPreview = lItem.querySelector('.opt-preview');
                    const hasImg = imgPreview && imgPreview.style.display !== 'none' && imgPreview.src;

                    leftHtml += `
                        <button type="button" 
                                class="teacher-node-left w-full p-2.5 rounded-xl border-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-800 hover:border-blue-400 hover:shadow-xs transition-all flex items-center justify-between group"
                                data-id="${lId}" onclick="onTeacherNodeLeftClick(this)">
                            <div class="flex items-center gap-2 pr-2 min-w-0 flex-1">
                                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-bold text-[10px] shrink-0">Item ${idx+1}</span>
                                ${hasImg ? `<img src="${imgPreview.src}" class="h-6 w-6 object-cover rounded border shrink-0">` : ''}
                                <span class="truncate text-gray-700 font-medium">${lText}</span>
                            </div>
                            <div class="w-3.5 h-3.5 rounded-full bg-slate-300 group-hover:bg-blue-400 transition-colors shrink-0 teacher-dot-left" id="tdot-${lId}"></div>
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
                    const rId = rItem.getAttribute('data-id') || rItem.querySelector('.right-id-input')?.value;
                    validRightIds.add(rId);
                    const rLabel = alphabetList[idx] || `R${idx+1}`;
                    const rText = rItem.querySelector('.right-text-input')?.value || `Pilihan ${rLabel}`;
                    const imgPreview = rItem.querySelector('.opt-preview');
                    const hasImg = imgPreview && imgPreview.style.display !== 'none' && imgPreview.src;

                    rightHtml += `
                        <button type="button" 
                                class="teacher-node-right w-full p-2.5 rounded-xl border-2 border-gray-200 bg-white text-left text-xs font-medium text-gray-700 hover:border-emerald-400 hover:shadow-xs transition-all flex items-center justify-between group"
                                data-id="${rId}" onclick="onTeacherNodeRightClick(this)">
                            <div class="w-3.5 h-3.5 rounded-full bg-slate-300 group-hover:bg-emerald-400 transition-colors shrink-0 teacher-dot-right" id="tdot-${rId}"></div>
                            <div class="flex items-center gap-2 pl-2 min-w-0 flex-1 justify-end text-right">
                                <span class="truncate text-gray-700 font-medium">${rText}</span>
                                ${hasImg ? `<img src="${imgPreview.src}" class="h-6 w-6 object-cover rounded border shrink-0">` : ''}
                                <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0">${rLabel}</span>
                            </div>
                        </button>
                    `;
                });
            }
            boardRight.innerHTML = rightHtml;

            // Filter out pairs with deleted items
            card._matchPairs = currentPairs.filter(p => validLeftIds.has(p.left) && validRightIds.has(p.right));
            if (jsonInput) jsonInput.value = JSON.stringify(card._matchPairs);

            // Redraw SVG lines
            setTimeout(() => drawTeacherMatchingLines(card), 50);
        }

        window.onTeacherNodeLeftClick = function(btn) {
            const card = btn.closest('.soal-card') || btn.closest('#modal-tambah-soal') || btn.closest('#modal-edit-soal');
            if (!card) return;
            const lId = btn.dataset.id;
            if (!card._matchPairs) card._matchPairs = [];

            if (card._selectedRight) {
                const rId = card._selectedRight.dataset.id;
                card._selectedRight.classList.remove('ring-4', 'ring-emerald-200', 'border-emerald-500');
                
                // Toggle pair
                const existingIdx = card._matchPairs.findIndex(p => p.left === lId && p.right === rId);
                if (existingIdx !== -1) {
                    card._matchPairs.splice(existingIdx, 1);
                } else {
                    card._matchPairs.push({ left: lId, right: rId });
                }

                const jsonInput = card.querySelector('.match-pairs-json') || card.querySelector('input[name="correct_pairs_json"]');
                if (jsonInput) jsonInput.value = JSON.stringify(card._matchPairs);

                card._selectedRight = null;
                card._selectedLeft = null;
                drawTeacherMatchingLines(card);
                return;
            }

            if (card._selectedLeft && card._selectedLeft !== btn) {
                card._selectedLeft.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500');
            }

            if (card._selectedLeft === btn) {
                btn.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500');
                card._selectedLeft = null;
                return;
            }

            btn.classList.add('ring-4', 'ring-blue-200', 'border-blue-500');
            card._selectedLeft = btn;
        };

        window.onTeacherNodeRightClick = function(btn) {
            const card = btn.closest('.soal-card') || btn.closest('#modal-tambah-soal') || btn.closest('#modal-edit-soal');
            if (!card) return;
            const rId = btn.dataset.id;
            if (!card._matchPairs) card._matchPairs = [];

            if (card._selectedLeft) {
                const lId = card._selectedLeft.dataset.id;
                card._selectedLeft.classList.remove('ring-4', 'ring-blue-200', 'border-blue-500');
                
                // Toggle pair
                const existingIdx = card._matchPairs.findIndex(p => p.left === lId && p.right === rId);
                if (existingIdx !== -1) {
                    card._matchPairs.splice(existingIdx, 1);
                } else {
                    card._matchPairs.push({ left: lId, right: rId });
                }

                const jsonInput = card.querySelector('.match-pairs-json') || card.querySelector('input[name="correct_pairs_json"]');
                if (jsonInput) jsonInput.value = JSON.stringify(card._matchPairs);

                card._selectedLeft = null;
                card._selectedRight = null;
                drawTeacherMatchingLines(card);
                return;
            }

            if (card._selectedRight && card._selectedRight !== btn) {
                card._selectedRight.classList.remove('ring-4', 'ring-emerald-200', 'border-emerald-500');
            }

            if (card._selectedRight === btn) {
                btn.classList.remove('ring-4', 'ring-emerald-200', 'border-emerald-500');
                card._selectedRight = null;
                return;
            }

            btn.classList.add('ring-4', 'ring-emerald-200', 'border-emerald-500');
            card._selectedRight = btn;
        };

        function drawTeacherMatchingLines(card) {
            const canvas = card.querySelector('.teacher-match-canvas');
            if (!canvas) return;

            const svg = canvas.querySelector('.teacher-match-svg');
            if (!svg) return;

            svg.innerHTML = '';
            const pairs = card._matchPairs || [];
            const canvasRect = canvas.getBoundingClientRect();

            // Reset all node styling
            canvas.querySelectorAll('.teacher-node-left, .teacher-node-right').forEach(btn => {
                teacherPalette.forEach(c => btn.classList.remove(c.border, c.bg, 'shadow-xs'));
                btn.classList.add('border-gray-200', 'bg-white');
                const dot = btn.querySelector('.teacher-dot-left, .teacher-dot-right');
                if (dot) dot.style.backgroundColor = '#cbd5e1';
            });

            // Color mapping per left item
            const leftNodes = Array.from(canvas.querySelectorAll('.teacher-node-left'));
            const leftColorMap = {};
            leftNodes.forEach((node, idx) => {
                leftColorMap[node.dataset.id] = teacherPalette[idx % teacherPalette.length];
            });

            pairs.forEach(p => {
                const lNode = canvas.querySelector(`.teacher-node-left[data-id="${p.left}"]`);
                const rNode = canvas.querySelector(`.teacher-node-right[data-id="${p.right}"]`);

                if (lNode && rNode) {
                    const color = leftColorMap[p.left] || teacherPalette[0];

                    lNode.classList.remove('border-gray-200', 'bg-white');
                    lNode.classList.add(color.border, color.bg, 'shadow-xs');
                    const lDot = lNode.querySelector('.teacher-dot-left');
                    if (lDot) lDot.style.backgroundColor = color.stroke;

                    rNode.classList.remove('border-gray-200');
                    rNode.classList.add('border-slate-400', 'bg-slate-50', 'shadow-xs');
                    const rDot = rNode.querySelector('.teacher-dot-right');
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
            document.querySelectorAll('.soal-card').forEach(card => {
                if (card.querySelector('.teacher-match-canvas')) {
                    drawTeacherMatchingLines(card);
                }
            });
        });

        function addMatchRightItem(card, id = null, text = '', imgSrc = null) {
            const container = card.querySelector('.match-rights-container');
            const cardIndex = card.getAttribute('data-index');
            const rUid = id || ('R' + Date.now() + Math.random().toString(36).substr(2, 4));
            const clone = matchRightTemplate.content.cloneNode(true);

            const item = clone.querySelector('.match-right-item');
            item.setAttribute('data-id', rUid);

            const idInput = clone.querySelector('.right-id-input');
            const textInput = clone.querySelector('.right-text-input');
            const optImageBox = clone.querySelector('.option-image-upload');
            const fileInput = clone.querySelector('.opt-file-input');
            const existingInput = clone.querySelector('.right-existing-img');
            const preview = clone.querySelector('.opt-preview');
            const btn = clone.querySelector('.opt-upload-btn');

            idInput.value = rUid;
            textInput.value = text;

            if (imgSrc) {
                preview.src = imgSrc;
                preview.style.display = 'block';
                if (btn) btn.style.display = 'none';
                if (existingInput) existingInput.value = imgSrc.replace('/storage/', '');
            }
            initImageUpload(optImageBox, fileInput, preview, btn);

            item.querySelector('.remove-match-right-btn').addEventListener('click', () => {
                item.remove();
                reindexMatchRights(card);
                renderTeacherMatchingBoard(card);
            });

            textInput.addEventListener('input', () => {
                renderTeacherMatchingBoard(card);
            });

            container.appendChild(item);
            reindexMatchRights(card);
            renderTeacherMatchingBoard(card);
        }

        function reindexMatchRights(card) {
            const container = card.querySelector('.match-rights-container');
            const cardIndex = card.getAttribute('data-index');
            const items = container.querySelectorAll('.match-right-item');

            items.forEach((item, idx) => {
                const label = alphabetList[idx] || `R${idx+1}`;
                const badge = item.querySelector('.right-label-badge');
                if (badge) badge.textContent = label;

                const idInput = item.querySelector('.right-id-input');
                const textInput = item.querySelector('.right-text-input');
                const fileInput = item.querySelector('.opt-file-input');
                const existingInput = item.querySelector('.right-existing-img');

                if (idInput) idInput.name = `soal[${cardIndex}][right_items][${idx}][id]`;
                if (textInput) textInput.name = `soal[${cardIndex}][right_items][${idx}][text]`;
                if (fileInput) fileInput.name = `soal[${cardIndex}][right_items][${idx}][gambar]`;
                if (existingInput) existingInput.name = `soal[${cardIndex}][right_items][${idx}][existing_gambar]`;
            });
        }

        function addMatchLeftItem(card, id = null, text = '', imgSrc = null) {
            const container = card.querySelector('.match-lefts-container');
            const cardIndex = card.getAttribute('data-index');
            const lUid = id || ('L' + Date.now() + Math.random().toString(36).substr(2, 4));
            const clone = matchLeftTemplate.content.cloneNode(true);

            const item = clone.querySelector('.match-left-item');
            item.setAttribute('data-id', lUid);

            const idInput = clone.querySelector('.left-id-input');
            const textInput = clone.querySelector('.left-text-input');
            const optImageBox = clone.querySelector('.option-image-upload');
            const fileInput = clone.querySelector('.opt-file-input');
            const existingInput = clone.querySelector('.left-existing-img');
            const preview = clone.querySelector('.opt-preview');
            const btn = clone.querySelector('.opt-upload-btn');

            idInput.value = lUid;
            textInput.value = text;

            if (imgSrc) {
                preview.src = imgSrc;
                preview.style.display = 'block';
                if (btn) btn.style.display = 'none';
                if (existingInput) existingInput.value = imgSrc.replace('/storage/', '');
            }
            initImageUpload(optImageBox, fileInput, preview, btn);

            item.querySelector('.remove-match-left-btn').addEventListener('click', () => {
                item.remove();
                reindexMatchLefts(card);
                renderTeacherMatchingBoard(card);
            });

            textInput.addEventListener('input', () => {
                renderTeacherMatchingBoard(card);
            });

            container.appendChild(item);
            reindexMatchLefts(card);
            renderTeacherMatchingBoard(card);
        }

        function reindexMatchLefts(card) {
            const container = card.querySelector('.match-lefts-container');
            const cardIndex = card.getAttribute('data-index');
            const items = container.querySelectorAll('.match-left-item');

            items.forEach((item, idx) => {
                const badge = item.querySelector('.left-num-badge');
                if (badge) badge.textContent = idx + 1;

                const idInput = item.querySelector('.left-id-input');
                const textInput = item.querySelector('.left-text-input');
                const fileInput = item.querySelector('.opt-file-input');
                const existingInput = item.querySelector('.left-existing-img');

                if (idInput) idInput.name = `soal[${cardIndex}][left_items][${idx}][id]`;
                if (textInput) textInput.name = `soal[${cardIndex}][left_items][${idx}][text]`;
                if (fileInput) fileInput.name = `soal[${cardIndex}][left_items][${idx}][gambar]`;
                if (existingInput) existingInput.name = `soal[${cardIndex}][left_items][${idx}][existing_gambar]`;
            });
        }

        // --- JAWABAN GANDA (DYNAMIC) ---
        function addJGItem(card, text = '', isCorrect = false, imageSrc = null) {
            const container = card.querySelector('.jg-options-container');
            const cardIndex = card.getAttribute('data-index');
            const jIndex = Date.now() + Math.random().toString(36).substr(2, 5);
            const clone = jgTemplate.content.cloneNode(true);
            
            const textInput = clone.querySelector('input[type="text"]');
            const checkbox = clone.querySelector('input[type="checkbox"]');
            const optImageBox = clone.querySelector('.option-image-upload');
            
            textInput.name = textInput.name.replace('idx', cardIndex).replace('jidx', jIndex);
            textInput.value = text;
            
            checkbox.name = checkbox.name.replace('idx', cardIndex);
            checkbox.checked = isCorrect;

            // Handle images
            const input = optImageBox.querySelector('.opt-file-input');
            const preview = optImageBox.querySelector('.opt-preview');
            const btn = optImageBox.querySelector('.opt-upload-btn');
            const existingInput = clone.querySelector('.jg-existing-img');
            
            input.name = input.name.replace('idx', cardIndex).replace('jidx', jIndex);
            existingInput.name = existingInput.name.replace('idx', cardIndex).replace('jidx', jIndex);
            
            if(imageSrc) {
                preview.src = imageSrc;
                preview.style.display = 'block';
                btn.style.display = 'none';
                if(existingInput) existingInput.value = imageSrc.replace('/storage/', '');
            }
            initImageUpload(optImageBox, input, preview, btn);

            const item = clone.querySelector('.jg-item');
            item.querySelector('.remove-jg-btn').addEventListener('click', () => {
                item.remove();
                reindexJG(container);
            });

            container.appendChild(item);
            reindexJG(container);
        }

        function reindexJG(container) {
            const labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
            container.querySelectorAll('.jg-item').forEach((item, i) => {
                const label = labels[i] || `X${i}`;
                item.querySelector('.jg-label').textContent = label;
                item.querySelector('.jg-checkbox').value = label;
            });
        }

        // --- BENAR SALAH (DYNAMIC) ---
        function addBSItem(card, text = '', correctValue = 'TRUE', imageSrc = null) {
            const container = card.querySelector('.bs-options-container');
            const cardIndex = card.getAttribute('data-index');
            const jIndex = Date.now() + Math.random().toString(36).substr(2, 5);
            const clone = bsTemplate.content.cloneNode(true);
            
            const textInput = clone.querySelector('input[type="text"]');
            const radios = clone.querySelectorAll('input[type="radio"]');
            const optImageBox = clone.querySelector('.option-image-upload');
            
            textInput.name = textInput.name.replace('idx', cardIndex).replace('jidx', jIndex);
            textInput.value = text;
            
            radios.forEach(r => {
                r.name = r.name.replace('idx', cardIndex).replace('jidx', jIndex);
                if(r.value === correctValue) r.checked = true;
            });

            // Handle images
            const input = optImageBox.querySelector('.opt-file-input');
            const preview = optImageBox.querySelector('.opt-preview');
            const btn = optImageBox.querySelector('.opt-upload-btn');
            const existingInput = clone.querySelector('.bs-existing-img');
            
            input.name = input.name.replace('idx', cardIndex).replace('jidx', jIndex);
            if(existingInput) existingInput.name = existingInput.name.replace('idx', cardIndex).replace('jidx', jIndex);
            
            if(imageSrc) {
                preview.src = imageSrc;
                preview.style.display = 'block';
                if(btn) btn.style.display = 'none';
                if(existingInput) existingInput.value = imageSrc.replace('/storage/', '');
            }
            initImageUpload(optImageBox, input, preview, btn);

            const item = clone.querySelector('.bs-item');
            item.querySelector('.remove-bs-btn').addEventListener('click', () => {
                item.remove();
            });

            container.appendChild(item);
        }

        function reindexSoal() {
            container.querySelectorAll('.soal-card').forEach((card, i) => {
                card.querySelector('.soal-nomor').textContent = i + 1;
            });
        }

        function addSoalCard() {
            const newIndex = soalCounter++;
            const clone = template.content.cloneNode(true);
            
            clone.querySelectorAll('[name*="idx"]').forEach(el => {
                el.name = el.name.replace(/idx/g, newIndex);
            });

            const cardDiv = clone.querySelector('.soal-card');
            cardDiv.setAttribute('data-index', newIndex);

            container.appendChild(cardDiv);
            bindCardEvents(cardDiv);
            
            // Default states
            const type = cardDiv.querySelector('.soal-tipe-select').value;
            if(type === 'menjodohkan') { 
                addMatchRightItem(cardDiv); 
                addMatchRightItem(cardDiv); 
                addMatchLeftItem(cardDiv); 
                addMatchLeftItem(cardDiv); 
            }
            if(type === 'jawaban_ganda') { addJGItem(cardDiv); addJGItem(cardDiv); }
            if(type === 'benar_salah') { 
                addBSItem(cardDiv, 'BENAR', true); 
                addBSItem(cardDiv, 'SALAH', false); 
            }

            reindexSoal();
            cardDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return cardDiv;
        }

        // --- IMPORT MODAL LOGIC ---
        window.openImportModal = function() {
            document.getElementById('modal-import-bank').classList.remove('hidden');
            currentBankSoalIds.clear();
            document.querySelectorAll('input[name*="[bank_soal_id]"]').forEach(input => {
                if(input.value) currentBankSoalIds.add(parseInt(input.value));
            });
            if(bankItems.length === 0) fetchBankSoal(); else renderBankItems(bankItems);
        }

        window.closeImportModal = function() {
            document.getElementById('modal-import-bank').classList.add('hidden');
        }

        async function fetchBankSoal() {
            try {
                const response = await fetch("{{ route('guru.mapel.bank_soal.items', $mapel->id) }}");
                bankItems = await response.json();
                renderBankItems(bankItems);
            } catch (err) {
                console.error(err);
                document.getElementById('bank-items-container').innerHTML = '<p class="text-center text-red-500 py-8">Gagal memuat data.</p>';
            }
        }

        function renderBankItems(items) {
            const container = document.getElementById('bank-items-container');
            if(items.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-400 py-12">Tidak ada soal di Bank Soal.</p>';
                return;
            }

            container.innerHTML = items.map(item => {
                const isAlreadyAdded = currentBankSoalIds.has(item.id);
                let previewHtml = '';
                if(item.tipe === 'pilihan_ganda') {
                    previewHtml = `
                        <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] text-gray-500 bg-gray-50 p-2 rounded-lg">
                            <div class="${item.kunci_jawaban === 'A' ? 'font-bold text-blue-600' : ''}">A. ${item.opsi_a}</div>
                            <div class="${item.kunci_jawaban === 'B' ? 'font-bold text-blue-600' : ''}">B. ${item.opsi_b}</div>
                            <div class="${item.kunci_jawaban === 'C' ? 'font-bold text-blue-600' : ''}">C. ${item.opsi_c}</div>
                            <div class="${item.kunci_jawaban === 'D' ? 'font-bold text-blue-600' : ''}">D. ${item.opsi_d}</div>
                        </div>
                    `;
                } else if(item.tipe === 'benar_salah') {
                    const data = (typeof item.data_soal === 'string') ? JSON.parse(item.data_soal || '{}') : (item.data_soal || {});
                    const pernyataan = data.pernyataan || data.options || [];
                    previewHtml = `
                        <div class="mt-2 space-y-1 text-[11px] bg-gray-50 p-2 rounded-lg">
                            ${pernyataan.map(p => `
                                <div class="flex justify-between border-b border-gray-100 last:border-0 pb-1">
                                    <span class="text-gray-700">${p.text || '...'}</span>
                                    <span class="font-bold ${p.correct === 'TRUE' ? 'text-green-600' : 'text-red-600'}">${p.correct || 'TRUE'}</span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else if(item.tipe === 'jawaban_ganda') {
                    const data = (typeof item.data_soal === 'string') ? JSON.parse(item.data_soal || '{}') : (item.data_soal || {});
                    const options = data.options || [];
                    const kunci = (item.kunci_jawaban || '').split(',');
                    previewHtml = `
                        <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] bg-gray-50 p-2 rounded-lg">
                            ${options.map(opt => `
                                <div class="${kunci.includes(opt.id) ? 'font-bold text-blue-600' : 'text-gray-500'}">
                                    <i class="bi ${kunci.includes(opt.id) ? 'bi-check-square-fill' : 'bi-square'} mr-1"></i> ${opt.text || '...'}
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else if(item.tipe === 'menjodohkan') {
                    const data = (typeof item.data_soal === 'string') ? JSON.parse(item.data_soal || '{}') : (item.data_soal || {});
                    if (data.left_items || data.right_items) {
                        const lefts = data.left_items || [];
                        const rights = data.right_items || [];
                        const pairs = data.correct_pairs || [];
                        const rMap = {};
                        rights.forEach((r, i) => { rMap[r.id || ('R'+i)] = { label: alphabetList[i] || `R${i+1}`, text: r.text }; });
                        
                        previewHtml = `
                            <div class="mt-2 space-y-1.5 text-[11px] bg-gray-50 p-2 rounded-lg">
                                ${lefts.map((l, lIdx) => {
                                    const lId = l.id || ('L'+lIdx);
                                    const matched = pairs.filter(p => p.left === lId).map(p => rMap[p.right]?.label || p.right);
                                    return `
                                        <div class="flex items-center justify-between gap-2 border-b border-gray-100 last:border-0 pb-1">
                                            <span class="text-gray-700 font-medium">${l.text || '-'}</span>
                                            <span class="px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800 text-[10px]">${matched.join(', ') || 'Belum dipasangkan'}</span>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        `;
                    } else {
                        const matches = data.matches || [];
                        previewHtml = `
                            <div class="mt-2 space-y-1 text-[11px] bg-gray-50 p-2 rounded-lg">
                                ${matches.map(m => `
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600">${m.left || '...'}</span>
                                        <i class="bi bi-arrow-right text-gray-400"></i>
                                        <span class="font-bold text-gray-800">${m.right || '...'}</span>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    }
                }

                return `
                <label class="block p-4 bg-white border border-gray-100 rounded-xl transition-all ${isAlreadyAdded ? 'opacity-50 grayscale bg-gray-50 cursor-not-allowed' : 'hover:border-purple-300 hover:bg-purple-50 cursor-pointer group has-[:checked]:border-purple-500 has-[:checked]:ring-1 has-[:checked]:ring-purple-100'}">
                    <div class="flex items-start gap-4">
                        <input type="checkbox" value="${item.id}" ${isAlreadyAdded ? 'disabled' : ''} class="bank-checkbox mt-1 w-5 h-5 text-purple-600 rounded border-gray-300 focus:ring-purple-500" onchange="updateSelectedCount()">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">${item.tipe.replace('_', ' ')}</span>
                                ${isAlreadyAdded ? '<span class="text-[10px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">Sudah Ditambahkan</span>' : ''}
                            </div>
                            <div class="flex gap-3">
                                ${item.gambar ? `<img src="/storage/${item.gambar}" class="w-16 h-16 object-cover rounded-lg border border-gray-200">` : ''}
                                <p class="text-sm text-gray-800 line-clamp-3">${item.pertanyaan}</p>
                            </div>
                            ${previewHtml}
                        </div>
                    </div>
                </label>
            `}).join('');
        }

        window.updateSelectedCount = function() {
            const count = document.querySelectorAll('.bank-checkbox:checked').length;
            document.getElementById('selected-count').textContent = count;
            document.getElementById('btn-import-confirm').disabled = (count === 0);
        }

        window.importSelectedItems = function() {
            const selectedIds = Array.from(document.querySelectorAll('.bank-checkbox:checked')).map(cb => parseInt(cb.value));
            const selectedSoals = bankItems.filter(item => selectedIds.includes(item.id));

            selectedSoals.forEach(soal => {
                const card = addSoalCard();
                const ed = card.querySelector('.soal-editor');
                if (ed) ed.innerHTML = soal.pertanyaan || '';
                const tx = card.querySelector('.soal-textarea') || card.querySelector('textarea');
                if (tx) tx.value = soal.pertanyaan || '';
                const typeSelect = card.querySelector('.soal-tipe-select');
                typeSelect.value = soal.tipe;
                updateCardUI(card, soal.tipe);

                // Main Image
                if(soal.gambar) {
                    const preview = card.querySelector('.image-preview');
                    const text = card.querySelector('.upload-text');
                    const existingMain = card.querySelector('.existing-main-img');
                    
                    preview.src = `/storage/${soal.gambar}`;
                    preview.style.display = 'block';
                    if(text) text.style.display = 'none';
                    if(existingMain) existingMain.value = soal.gambar.replace('/storage/', '');
                }

                const hiddenId = document.createElement('input');
                hiddenId.type = 'hidden';
                hiddenId.name = `soal[${card.getAttribute('data-index')}][bank_soal_id]`;
                hiddenId.value = soal.id;
                card.appendChild(hiddenId);

                // Populate Fields
                if(soal.tipe === 'pilihan_ganda') {
                    ['a','b','c','d'].forEach(o => {
                        card.querySelector(`[name*="[opsi_${o}]"]`).value = soal[`opsi_${o}`] || '';
                        if(soal[`gambar_${o}`]) {
                            const box = card.querySelector(`.soal-card[data-index="${card.getAttribute('data-index')}"] .option-image-upload input[name*="[gambar_${o}]"]`).closest('.option-image-upload');
                            const prev = box.querySelector('.opt-preview');
                            const btn = box.querySelector('.opt-upload-btn');
                            const existing = box.querySelector('.opt-existing-img');
                            prev.src = `/storage/${soal[`gambar_${o}`]}`;
                            prev.style.display = 'block';
                            btn.style.display = 'none';
                            if(existing) existing.value = soal[`gambar_${o}`];
                        }
                    });
                    card.querySelector('select[name*="[kunci_jawaban]"]').value = soal.kunci_jawaban || '';
                } else if(soal.tipe === 'benar_salah') {
                    card.querySelector('.bg-blue-50').classList.replace('bg-blue-50', 'bg-green-50'); 
                    card.querySelector('.bg-green-50').innerHTML = '<i class="bi bi-info-circle mr-1"></i> Klik "Tambah Pilihan" jika diperlukan. Pilih radio button (BENAR/SALAH) untuk tiap pernyataan.';
                    card.querySelector('.bs-options-container').innerHTML = '';
                    const data = (typeof soal.data_soal === 'string') ? JSON.parse(soal.data_soal || '{}') : (soal.data_soal || {});
                    const pernyataan = data.pernyataan || data.options || [];
                    pernyataan.forEach(p => {
                        addBSItem(card, p.text, p.correct || 'TRUE', p.gambar ? `/storage/${p.gambar}` : null);
                    });
                } else if(soal.tipe === 'jawaban_ganda') {
                    card.querySelector('.jg-options-container').innerHTML = '';
                    const data = (typeof soal.data_soal === 'string') ? JSON.parse(soal.data_soal || '{}') : (soal.data_soal || {});
                    const options = data.options || [];
                    options.forEach(opt => {
                        const isSelected = (soal.kunci_jawaban || '').includes(opt.id);
                        addJGItem(card, opt.text, isSelected, opt.gambar ? `/storage/${opt.gambar}` : null);
                    });
                } else if(soal.tipe === 'menjodohkan') {
                    card.querySelector('.match-rights-container').innerHTML = '';
                    card.querySelector('.match-lefts-container').innerHTML = '';
                    const data = (typeof soal.data_soal === 'string') ? JSON.parse(soal.data_soal || '{}') : (soal.data_soal || {});
                    
                    if (data.left_items || data.right_items) {
                        const rights = data.right_items || [];
                        const lefts = data.left_items || [];
                        const pairs = data.correct_pairs || [];
                        const pairsMap = {};
                        pairs.forEach(p => {
                            if (p.left && p.right) {
                                if (!pairsMap[p.left]) pairsMap[p.left] = [];
                                pairsMap[p.left].push(p.right);
                            }
                        });

                        rights.forEach(r => {
                            addMatchRightItem(card, r.id, r.text, r.gambar ? `/storage/${r.gambar}` : null);
                        });
                        lefts.forEach(l => {
                            addMatchLeftItem(card, l.id, l.text, l.gambar ? `/storage/${l.gambar}` : null, pairsMap[l.id] || []);
                        });
                    } else {
                        const matches = data.matches || [];
                        matches.forEach((m, idx) => {
                            const rId = 'R' + idx;
                            const lId = 'L' + idx;
                            addMatchRightItem(card, rId, m.right, m.gambar_right ? `/storage/${m.gambar_right}` : null);
                            addMatchLeftItem(card, lId, m.left, m.gambar_left ? `/storage/${m.gambar_left}` : null, [rId]);
                        });
                    }
                }
            });

            closeImportModal();
            Swal.fire('Berhasil', `${selectedSoals.length} soal diimport.`, 'success');
        }

        window.filterBankItems = function() {
            const query = document.getElementById('search-bank').value.toLowerCase();
            const type = document.getElementById('filter-tipe-bank').value;
            renderBankItems(bankItems.filter(item => (item.pertanyaan.toLowerCase().includes(query) && (type === 'all' || item.tipe === type))));
        }

        container.querySelectorAll('.soal-card').forEach(card => bindCardEvents(card));
        document.getElementById('add-soal-btn').addEventListener('click', addSoalCard);
        if (soalCounter === 0) addSoalCard();
    });
</script>
@endsection