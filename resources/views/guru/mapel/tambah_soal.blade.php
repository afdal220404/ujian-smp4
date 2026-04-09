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

        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Kiri: Pertanyaan & Konten --}}
            <div class="lg:col-span-8 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pertanyaan</label>
                    <textarea name="soal[idx][pertanyaan]" class="soal-textarea w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm text-gray-800" rows="3" placeholder="Tulis pertanyaan disini..." required></textarea>
                </div>

                {{-- Container Jawaban Dinamis --}}
                <div class="answers-container space-y-4">
                    {{-- 1. PILIHAN GANDA (Default) --}}
                    <div class="type-section type-pilihan_ganda space-y-3">
                        @foreach(['a','b','c','d'] as $opsi)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">
                                {{ $opsi }}
                            </div>
                            <input type="text" name="soal[idx][opsi_{{ $opsi }}]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                            
                            {{-- Option Image Placeholder --}}
                            <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                                <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
                                <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400 group-hover:text-blue-500">
                                    <i class="bi bi-image text-sm"></i>
                                </div>
                                <input type="file" name="soal[idx][gambar_{{ $opsi }}]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                <input type="hidden" name="soal[idx][existing_gambar_{{ $opsi }}]" class="opt-existing-img">
                            </div>
                        </div>
                        @endforeach
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

                    {{-- 4. MENCOCOKKAN --}}
                    <div class="type-section type-menjodohkan hidden space-y-4">
                        <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700">
                            <i class="bi bi-info-circle mr-1"></i> Buat pasangan pertanyaan (kiri) dan jawaban (kanan) yang sesuai.
                        </div>
                        
                        <div class="matches-container space-y-2">
                            {{-- Baris Match Item Template --}}
                        </div>

                        <button type="button" class="add-match-btn text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 mt-2">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Pasangan
                        </button>
                    </div>
                </div>
            </div>

            {{-- Kanan: Gambar & Kunci (Sidebar) --}}
            <div class="lg:col-span-4 space-y-4">
                
                {{-- Upload Gambar --}}
                <div class="bg-white">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Gambar Pendukung</label>
                    <div class="soal-image-upload relative w-full h-40 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-blue-400 transition-all cursor-pointer flex flex-col items-center justify-center overflow-hidden group/upload">
                        <img class="image-preview absolute inset-0 w-full h-full object-contain bg-white p-2" style="display: none;">
                        <div class="upload-text text-center p-4">
                            <i class="bi bi-cloud-arrow-up-fill text-3xl text-gray-300 group-hover/upload:text-blue-500 transition-colors"></i>
                            <p class="text-xs text-gray-500 mt-2 font-medium">Upload Gambar</p>
                        </div>
                        <input type="file" name="soal[idx][gambar]" class="soal-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                        <input type="hidden" name="soal[idx][existing_gambar]" class="existing-main-img">
                    </div>
                </div>

                {{-- Kunci Jawaban (HANYA UNTUK PILIHAN GANDA) --}}
                <div class="key-section key-pilihan_ganda bg-blue-50 rounded-xl p-4 border border-blue-100">
                    <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Kunci Jawaban</label>
                    <div class="relative">
                        <select name="soal[idx][kunci_jawaban]" class="w-full px-3 py-2 bg-white border border-blue-200 rounded-lg focus:border-blue-500 outline-none text-sm font-bold text-blue-700 appearance-none cursor-pointer">
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

            </div>
        </div>
    </div>
</template>

{{-- TEMPLATE ITEM MATCHING --}}
<template id="match-item-template">
    <div class="match-item flex flex-col gap-2 p-3 bg-gray-50 border border-gray-100 rounded-xl">
        <div class="flex items-center gap-3">
            {{-- Left Side --}}
            <div class="flex-1 flex items-center gap-2">
                <input type="text" name="soal[idx][matches][midx][left]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none" placeholder="Pernyataan Kiri">
                
                {{-- Left Image --}}
                <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                    <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
                    <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400">
                        <i class="bi bi-image text-sm"></i>
                    </div>
                    <input type="file" name="soal[idx][matches][midx][gambar_left]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                </div>
            </div>

            <div class="text-gray-400"><i class="bi bi-arrow-right-circle-fill"></i></div>

            {{-- Right Side --}}
            <div class="flex-1 flex items-center gap-2">
                <input type="text" name="soal[idx][matches][midx][right]" class="w-full px-3 py-2 bg-white border border-green-200 rounded-lg text-sm focus:border-green-500 outline-none" placeholder="Jawaban Kanan">
                
                {{-- Right Image --}}
                <div class="option-image-upload shrink-0 relative w-10 h-10 border border-dashed border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer overflow-hidden group">
                    <img class="opt-preview absolute inset-0 w-full h-full object-cover" style="display: none;">
                    <div class="opt-upload-btn absolute inset-0 flex items-center justify-center text-gray-400">
                        <i class="bi bi-image text-sm"></i>
                    </div>
                    <input type="file" name="soal[idx][matches][midx][gambar_right]" class="opt-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                </div>
            </div>

            <input type="hidden" name="soal[idx][matches][midx][existing_gambar_left]" class="match-existing-img-left">
            <input type="hidden" name="soal[idx][matches][midx][existing_gambar_right]" class="match-existing-img-right">
            <button type="button" class="remove-match-btn text-red-300 hover:text-red-500 shrink-0">
                <i class="bi bi-x-circle-fill text-lg"></i>
            </button>
        </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('soal-list-container');
        const template = document.getElementById('soal-card-template');
        const matchTemplate = document.getElementById('match-item-template');
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
                const pertanyaan = card.querySelector('.soal-textarea').value.trim();

                let namaTipe = '';
                if(type === 'pilihan_ganda') namaTipe = 'Pilihan Ganda';
                if(type === 'jawaban_ganda') namaTipe = 'Pilih Banyak Jawaban';
                if(type === 'menjodohkan') namaTipe = 'Mencocokkan';
                if(type === 'benar_salah') namaTipe = 'Benar / Salah';

                // VALIDASI UMUM: Pertanyaan kosong
                if (!pertanyaan) {
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
                    const matchItems = card.querySelectorAll('.match-item');
                    if (matchItems.length === 0) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Minimal harus ada satu pasangan jawaban.`);
                        return false;
                    }

                    let adaPasanganKosong = false;
                    matchItems.forEach(item => {
                        const left = item.querySelector('input[name*="[left]"]').value.trim();
                        const right = item.querySelector('input[name*="[right]"]').value.trim();
                        if (!left || !right) adaPasanganKosong = true;
                    });

                    if (adaPasanganKosong) {
                        showValidationModal(`Soal No. ${nomor} (${namaTipe}): Teks pernyataan (kiri) dan jawaban (kanan) tidak boleh ada yang kosong.`);
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
            const addMatchBtn = card.querySelector('.add-match-btn');
            if(addMatchBtn) addMatchBtn.addEventListener('click', () => addMatchItem(card));

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
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // --- MATCHING ---
        function addMatchItem(card, dataLeft = '', dataRight = '') {
            const matchesContainer = card.querySelector('.matches-container');
            const cardIndex = card.getAttribute('data-index');
            const matchIndex = Date.now() + Math.random().toString(36).substr(2, 5);
            const clone = matchTemplate.content.cloneNode(true);
            
            clone.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace('idx', cardIndex).replace('midx', matchIndex);
                if(input.name.includes('[left]')) input.value = dataLeft;
                if(input.name.includes('[right]')) input.value = dataRight;
            });

            const item = clone.querySelector('.match-item');
            item.querySelector('.remove-match-btn').addEventListener('click', () => item.remove());
            matchesContainer.appendChild(item);
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
                // Extract clean path from /storage/path
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

        // --- MATCHING (DYNAMIC) ---
        function addMatchItem(card, left = '', right = '', imgLeft = null, imgRight = null) {
            const container = card.querySelector('.matches-container');
            const cardIndex = card.getAttribute('data-index');
            const mIndex = Date.now() + Math.random().toString(36).substr(2, 5);
            const clone = matchTemplate.content.cloneNode(true);
            
            const inputs = clone.querySelectorAll('input[type="text"]');
            inputs[0].name = `soal[${cardIndex}][matches][${mIndex}][left]`;
            inputs[0].value = left;
            inputs[1].name = `soal[${cardIndex}][matches][${mIndex}][right]`;
            inputs[1].value = right;

            // Handle Images
            clone.querySelectorAll('.option-image-upload').forEach((box, i) => {
                const isLeft = i === 0;
                const input = box.querySelector('.opt-file-input');
                const preview = box.querySelector('.opt-preview');
                const btn = box.querySelector('.opt-upload-btn');
                const existingInput = clone.querySelector(isLeft ? '.match-existing-img-left' : '.match-existing-img-right');
                
                const nameKey = isLeft ? 'gambar_left' : 'gambar_right';
                const src = isLeft ? imgLeft : imgRight;

                input.name = `soal[${cardIndex}][matches][${mIndex}][${nameKey}]`;
                if(existingInput) {
                    existingInput.name = `soal[${cardIndex}][matches][${mIndex}][existing_${nameKey}]`;
                }

                if(src) {
                    preview.src = src;
                    preview.style.display = 'block';
                    if(btn) btn.style.display = 'none';
                    if(existingInput) existingInput.value = src.replace('/storage/', '');
                }
                initImageUpload(box, input, preview, btn);
            });

            const item = clone.querySelector('.match-item');
            item.querySelector('.remove-match-btn').addEventListener('click', () => item.remove());
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
            if(type === 'menjodohkan') { addMatchItem(cardDiv); addMatchItem(cardDiv); }
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
            // Refresh current IDs from existing inputs
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
                // Parse options and matches for preview
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
                card.querySelector('textarea').value = soal.pertanyaan;
                const typeSelect = card.querySelector('.soal-tipe-select');
                typeSelect.value = soal.tipe;
                updateCardUI(card, soal.tipe);

                // Main Image
                if(soal.gambar) {
                    const preview = card.querySelector('.image-preview');
                    const text = card.querySelector('.upload-text');
                    
                    // 👇 TAMBAHKAN BARIS INI 👇
                    const existingMain = card.querySelector('.existing-main-img');
                    
                    preview.src = `/storage/${soal.gambar}`;
                    preview.style.display = 'block';
                    if(text) text.style.display = 'none';
                    
                    // 👇 TAMBAHKAN BARIS INI 👇
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
                    const pernyataan = data.pernyataan || data.options || []; // Support both formats
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
                    card.querySelector('.matches-container').innerHTML = '';
                    const data = (typeof soal.data_soal === 'string') ? JSON.parse(soal.data_soal || '{}') : (soal.data_soal || {});
                    const matches = data.matches || [];
                    matches.forEach(m => {
                        addMatchItem(card, m.left, m.right, 
                                    m.gambar_left ? `/storage/${m.gambar_left}` : null,
                                    m.gambar_right ? `/storage/${m.gambar_right}` : null);
                    });
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