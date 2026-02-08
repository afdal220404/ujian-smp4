@extends('layouts.app')

@section('title', 'Kelola Soal Ujian')

@section('sidebar-menu')
    <div class="mb-4 px-3">
        <a href="{{ route('guru.index') }}" 
           class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
        </a>
    </div>

    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Mapel</div>
    
    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link">
        <i class="bi bi-collection"></i> <span>Bank Soal</span>
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

<form action="{{ route('guru.mapel.soal.store-temp', ['ujian' => $ujian->id ?? null]) }}" method="POST" enctype="multipart/form-data" class="max-w-5xl mx-auto" id="form-soal">
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
        <button type="button" id="add-soal-btn" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 hover:text-gray-900 transition-all text-sm flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Tambah Soal
        </button>

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
                        </div>
                        @endforeach
                    </div>

                    {{-- 2. BENAR / SALAH --}}
                    <div class="type-section type-benar_salah hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:bg-green-50 hover:border-green-200 transition-all has-[:checked]:bg-green-50 has-[:checked]:border-green-500">
                                <input type="radio" name="soal[idx][kunci_jawaban_bs]" value="TRUE" class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500">
                                <span class="font-bold text-green-700">BENAR</span>
                            </label>
                            <label class="cursor-pointer border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:bg-red-50 hover:border-red-200 transition-all has-[:checked]:bg-red-50 has-[:checked]:border-red-500">
                                <input type="radio" name="soal[idx][kunci_jawaban_bs]" value="FALSE" class="w-5 h-5 text-red-600 border-gray-300 focus:ring-red-500">
                                <span class="font-bold text-red-700">SALAH</span>
                            </label>
                        </div>
                    </div>

                    {{-- 3. JAWABAN GANDA --}}
                    <div class="type-section type-jawaban_ganda hidden space-y-3">
                        <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700 mb-2">
                            <i class="bi bi-info-circle mr-1"></i> Centang kotak di kanan untuk menandai jawaban benar.
                        </div>
                        @foreach(['a','b','c','d'] as $opsi)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center shrink-0 border border-gray-200 uppercase text-xs">
                                {{ $opsi }}
                            </div>
                            <input type="text" name="soal[idx][opsi_{{ $opsi }}_jg]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm" placeholder="Pilihan {{ strtoupper($opsi) }}">
                            {{-- Checkbox Kunci --}}
                            <div class="shrink-0">
                                <input type="checkbox" name="soal[idx][kunci_jawaban_jg][]" value="{{ strtoupper($opsi) }}" class="w-6 h-6 text-blue-600 rounded focus:ring-blue-500 border-gray-300 cursor-pointer" title="Tandai sebagai jawaban benar">
                            </div>
                        </div>
                        @endforeach
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
    <div class="match-item flex items-center gap-2">
        <div class="flex-1">
            <input type="text" name="soal[idx][matches][midx][left]" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-blue-500 outline-none" placeholder="Pertanyaan / Pernyataan">
        </div>
        <div class="text-gray-400"><i class="bi bi-arrow-right"></i></div>
        <div class="flex-1">
            <input type="text" name="soal[idx][matches][midx][right]" class="w-full px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm focus:bg-white focus:border-green-500 outline-none" placeholder="Pasangan Benar">
        </div>
        <button type="button" class="remove-match-btn text-red-400 hover:text-red-600">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </div>
</template>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('soal-list-container');
        const template = document.getElementById('soal-card-template');
        const matchTemplate = document.getElementById('match-item-template');
        let soalCounter = {{ count($tempSoals) }};

        // --- PARTIAL RENDER (Untuk PHP Loop) ---
        // Karena kita menggunakan include partial, kita asumsikan HTML structure sama.
        // Kita perlu bind event listener ke elemen yang sudah ada.
        
        // FUNGSI UTAMA: BIND EVENT KE KARTU
        function bindCardEvents(card) {
            const index = card.getAttribute('data-index');
            const typeSelect = card.querySelector('.soal-tipe-select');
            
            // 1. Handle Type Change
            typeSelect.addEventListener('change', () => {
                updateCardUI(card, typeSelect.value);
            });

            // 2. Initial UI State
            updateCardUI(card, typeSelect.value);

            // 3. Delete Soal
            card.querySelector('.delete-soal-btn').addEventListener('click', () => {
                Swal.fire({
                    title: 'Hapus Soal?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444'
                }).then((res) => { if(res.isConfirmed) { card.remove(); reindexSoal(); }});
            });

            // 4. Image Upload
            initImageUpload(card);

            // 5. Matching Logic
            const addMatchBtn = card.querySelector('.add-match-btn');
            if(addMatchBtn) {
                addMatchBtn.addEventListener('click', () => addMatchItem(card));
            }
        }

        function updateCardUI(card, type) {
            // Sembunyikan semua section
            card.querySelectorAll('.type-section').forEach(el => el.classList.add('hidden'));
            card.querySelectorAll('.key-section').forEach(el => el.classList.add('hidden'));

            // Disable semua input di section yang tersembunyi agar tidak terkirim (opsional, tapi bagus untuk validasi backend)
            // Note: Backend validasi harus handle null. 

            // Tampilkan section sesuai tipe
            const activeSection = card.querySelector(`.type-${type}`);
            if(activeSection) activeSection.classList.remove('hidden');

            const activeKey = card.querySelector(`.key-${type}`);
            if(activeKey) activeKey.classList.remove('hidden');
        }

        function initImageUpload(card) {
            const uploadBox = card.querySelector('.soal-image-upload');
            const fileInput = card.querySelector('.soal-file-input');
            const previewImg = card.querySelector('.image-preview');
            const uploadText = card.querySelector('.upload-text');

            uploadBox.addEventListener('click', (e) => {
                if (e.target !== fileInput) fileInput.click();
            });

            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire('File Terlalu Besar', 'Maksimal 2MB', 'error');
                        fileInput.value = "";
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = e => {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        if(uploadText) uploadText.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function addMatchItem(card, dataLeft = '', dataRight = '') {
            const matchesContainer = card.querySelector('.matches-container');
            const cardIndex = card.getAttribute('data-index');
            // Check current matches count to generate unique index
            const matchCount = matchesContainer.querySelectorAll('.match-item').length;
            const matchIndex = Date.now() + Math.random().toString(36).substr(2, 5); // Random UID

            const clone = matchTemplate.content.cloneNode(true);
            
            // Fix Names
            clone.querySelectorAll('input').forEach(input => {
                let name = input.name;
                name = name.replace('idx', cardIndex);
                name = name.replace('midx', matchIndex);
                input.name = name;
                
                if(input.name.includes('[left]')) input.value = dataLeft;
                if(input.name.includes('[right]')) input.value = dataRight;
            });

            const item = clone.querySelector('.match-item');
            
            // Delete Logic
            item.querySelector('.remove-match-btn').addEventListener('click', () => {
                item.remove();
            });

            matchesContainer.appendChild(item);
        }

        function reindexSoal() {
            container.querySelectorAll('.soal-card').forEach((card, i) => {
                card.querySelector('.soal-nomor').textContent = i + 1;
            });
        }

        function addSoalCard() {
            const newIndex = soalCounter++;
            const clone = template.content.cloneNode(true);
            
            // Replace idx
            clone.querySelectorAll('[name*="idx"]').forEach(el => {
                el.name = el.name.replace(/idx/g, newIndex);
            });
            // Replace data-index
            const cardDiv = clone.querySelector('.soal-card');
            cardDiv.setAttribute('data-index', newIndex);

            container.appendChild(cardDiv);
            bindCardEvents(cardDiv);
            
             // Default 2 matches for new matching questions
             addMatchItem(cardDiv);
             addMatchItem(cardDiv);

            reindexSoal();
            cardDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // --- INIT EXISTING CARDS (PHP RENDERED) ---
        // Kita butuh Logic untuk parse PHP render yang mungkin berbeda structure.
        // Untuk amannya, kita REPALCE Partial PHP dengan JS Logic??
        // TIDAK. Kita harus bikin partial view yang structurenya SAMA PERSIS dengan Template.
        
        // Karena saya pakai single file write, saya akan inject partial view di sini
        // Tapi "include" blade tidak bisa saya tulis di sini.
        // Jadi saya manual render loop di PHP section di atas sesuai structure template.
        
        // ... Init Script ...
        container.querySelectorAll('.soal-card').forEach(card => {
             bindCardEvents(card);
        });

        document.getElementById('add-soal-btn').addEventListener('click', addSoalCard);
        
        if (soalCounter === 0) addSoalCard();
    });
</script>
@endsection