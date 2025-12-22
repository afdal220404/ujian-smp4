@extends('layouts.app')

@section('title', 'Tambah Soal Ujian')

@section('sidebar-menu')
{{-- ... (Sidebar Anda tetap sama) ... --}}
<a href="{{ route('guru.index') }}" class="menu-item"><i class="bi bi-arrow-left"></i> Kembali ke Pilihan</a>
<hr class="sidebar-divider">
@if(isset($mapel))
<div class="sidebar-heading">{{ $mapel->nama_mapel }} ({{ $mapel->kelas->kelas }})</div>
<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item"><i class="bi bi-pie-chart-fill"></i> Dasbor Mapel</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item"><i class="bi bi-card-checklist"></i> Daftar Nilai Siswa</a>
@endif
<a href="#" class="menu-item active"><i class="bi bi-pencil-square"></i> Buat Ujian Baru</a>
<a href="#" class="menu-item"><i class="bi bi-archive-fill"></i> Bank Soal</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Tambahkan Soal</a>
    @if(session('ujian_temp_details'))
    <p class="sub-judul" style="text-align: center; margin-top: 10px; font-size: 22px;">
        Ujian: <strong>{{ session('ujian_temp_details')['nama_ujian'] }}</strong>
    </p>
    @endif
</div>

@if ($errors->any())
<div class="alert alert-danger mb-4">
    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
</div>
@endif

<form action="{{ route('guru.mapel.soal.store-temp', ['ujian' => $ujian->id ?? null]) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div id="soal-list-container">
        @forelse ($tempSoals as $index => $soal)
        <div class="soal-card">
            <div class="soal-card-title">
                <span>Soal Nomor <span class="soal-nomor">{{ $index + 1 }}</span></span>
            </div>
            <div class="soal-card-body">
                <div class="soal-form-left">
                    <div class="soal-group">
                        <label class="soal-label" for="soal_pertanyaan_{{ $index }}">Pertanyaan</label>
                        <textarea name="soal[{{ $index }}][pertanyaan]" id="soal_pertanyaan_{{ $index }}" class="soal-textarea" rows="4" required>{{ $soal['pertanyaan'] ?? '' }}</textarea>
                    </div>
                    <div class="soal-opsi-grid">
                        @foreach(['a','b','c','d','e'] as $opsi)
                        <div class="soal-group">
                            <label class="soal-label" for="soal_opsi_{{ $opsi }}_{{ $index }}">Opsi {{ strtoupper($opsi) }}</label>
                            <input type="text" name="soal[{{ $index }}][opsi_{{ $opsi }}]" id="soal_opsi_{{ $opsi }}_{{ $index }}" class="soal-input" value="{{ $soal['opsi_'.$opsi] ?? '' }}" required>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="soal-form-right">
                    <div class="soal-group">
                        <label class="soal-label" for="soal_gambar_{{ $index }}">Gambar (Opsional)</label>
                        <div class="soal-image-upload">
                            @php $gambarPath = $soal['gambar_path'] ?? null; @endphp

                            <span class="upload-text" style="display: {{ $gambarPath ? 'none' : 'block' }};">Klik untuk upload gambar</span>
                            <img class="image-preview"
                                src="{{ $gambarPath ? asset('storage/' . $gambarPath) : '' }}"
                                alt="Preview"
                                style="display: {{ $gambarPath ? 'block' : 'none' }};">
                        </div>
                        <input type="file" name="soal[{{ $index }}][gambar]" id="soal_gambar_{{ $index }}" class="soal-file-input" accept="image/*">
                    </div>

                    <div class="soal-group">
                        <label class="soal-label" for="soal_kunci_jawaban_{{ $index }}">Kunci Jawaban</label>
                        <select name="soal[{{ $index }}][kunci_jawaban]" id="soal_kunci_jawaban_{{ $index }}" class="soal-select" required>
                            <option value="" disabled {{ !isset($soal['kunci_jawaban']) ? 'selected' : '' }}>Pilih Kunci</option>
                            @foreach(['A','B','C','D','E'] as $huruf)
                            <option value="{{ $huruf }}" {{ ($soal['kunci_jawaban'] ?? '') == $huruf ? 'selected' : '' }}>{{ $huruf }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="soal-card-footer" style="padding: 15px; text-align: right; border-top: 1px solid #e5e7eb;">
                <button type="button" class="delete-soal-btn">Hapus</button>
            </div>
        </div>
        @empty
        @endforelse
    </div>

    <div class="soal-action-bar">
        @if(session('ujian_temp_details'))
        @php
        $mapelId = session('ujian_temp_details')['mapel_id'];
        $kembaliRoute = $ujian
        ? route('guru.mapel.ujian.edit', $ujian->id)
        : route('guru.mapel.ujian.review', $mapelId);
        @endphp
        <a href="{{ $kembaliRoute }}" class="dark-btn" style="background-color: #6B7280; border: none;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        @endif
        <button type="button" id="add-soal-btn" class="dark-btn" style="background-color: #3B82F6; border: none;">
            <i class="bi bi-plus-circle-fill"></i> Tambah Soal
        </button>
        <button type="submit" class="dark-btn" style="background-color: #10B981; border: none;">
            <i class="bi bi-save-fill"></i> Simpan Soal
        </button>
    </div>
</form>

{{-- TEMPLATE --}}
<template id="soal-card-template">
    <div class="soal-card">
        <div class="soal-card-title">
            <span>Soal Nomor <span class="soal-nomor">1</span></span>

        </div>
        <div class="soal-card-body">
            <div class="soal-form-left">
                <div class="soal-group">
                    <label class="soal-label" for="soal_pertanyaan_idx_">Pertanyaan</label>
                    <textarea name="soal[idx][pertanyaan]" id="soal_pertanyaan_idx_" class="soal-textarea" rows="4" required></textarea>
                </div>
                <div class="soal-opsi-grid">
                    @foreach(['a','b','c','d','e'] as $opsi)
                    <div class="soal-group">
                        <label class="soal-label" for="soal_opsi_{{ $opsi }}_idx_">Opsi {{ strtoupper($opsi) }}</label>
                        <input type="text" name="soal[idx][opsi_{{ $opsi }}]" id="soal_opsi_{{ $opsi }}_idx_" class="soal-input" required>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="soal-form-right">
                <div class="soal-group">
                    <label class="soal-label" for="soal_gambar_idx_">Gambar (Opsional)</label>
                    <div class="soal-image-upload">
                        <span class="upload-text">Klik untuk upload gambar</span>
                        <img class="image-preview" src="" alt="Preview" style="display: none;">
                    </div>
                    <input type="file" name="soal[idx][gambar]" id="soal_gambar_idx_" class="soal-file-input" accept="image/*">
                </div>
                <div class="soal-group">
                    <label class="soal-label" for="soal_kunci_jawaban_idx_">Kunci Jawaban</label>
                    <select name="soal[idx][kunci_jawaban]" id="soal_kunci_jawaban_idx_" class="soal-select" required>
                        <option value="" disabled selected>Pilih Kunci</option>
                        @foreach(['A','B','C','D','E'] as $huruf)
                        <option value="{{ $huruf }}">{{ $huruf }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="soal-card-footer" style="padding: 15px; text-align: right; border-top: 1px solid #e5e7eb;">
            <button type="button" class="delete-soal-btn">Hapus</button>
        </div>
    </div>
</template>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('soal-list-container');
        const template = document.getElementById('soal-card-template');
        let soalCounter = {{count($tempSoals)}};

        function reindexSoal() {
            const cards = container.querySelectorAll('.soal-card');
            cards.forEach((card, i) => {
                card.querySelector('.soal-nomor').textContent = i + 1;
            });
        }

        function addDeleteListener(card) {
            card.querySelector('.delete-soal-btn').addEventListener('click', () => {
                card.remove();
                reindexSoal();
            });
        }

        function initImageUpload(card) {
            const uploadBox = card.querySelector('.soal-image-upload');
            const fileInput = card.querySelector('.soal-file-input');
            const previewImg = card.querySelector('.image-preview');
            const uploadText = card.querySelector('.upload-text');

            uploadBox.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        uploadText.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function addSoalCard() {
            const newIndex = soalCounter++;
            const clone = template.content.cloneNode(true);
            clone.querySelectorAll('[for*="_idx_"]').forEach(el => el.htmlFor = el.htmlFor.replace('_idx_', `_${newIndex}_`));
            clone.querySelectorAll('[id*="_idx_"]').forEach(el => el.id = el.id.replace('_idx_', `_${newIndex}_`));
            clone.querySelectorAll('[name*="[idx]"]').forEach(el => el.name = el.name.replace('[idx]', `[${newIndex}]`));

            const card = clone.querySelector('.soal-card');
            container.appendChild(card);
            initImageUpload(card);
            addDeleteListener(card);
            reindexSoal();
        }

        container.querySelectorAll('.soal-card').forEach(card => {
            initImageUpload(card);
            addDeleteListener(card);
        });

        document.getElementById('add-soal-btn').addEventListener('click', addSoalCard);

        if (soalCounter === 0) addSoalCard();
    });
</script>