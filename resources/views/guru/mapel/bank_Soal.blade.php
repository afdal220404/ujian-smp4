@extends('layouts.app')

@section('title', 'Bank Soal ' . $mapel->nama_mapel)


<style>
    
</style>


@section('sidebar-menu')
<a href="{{ route('guru.index') }}" class="menu-item">
    <i class="bi bi-arrow-left"></i> Kembali ke Pilihan
</a>
<hr class="sidebar-divider">
<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item active">
    <i class="bi bi-pie-chart-fill"></i> Dasbor
</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item">
    <i class="bi bi-card-checklist"></i> Daftar Nilai Siswa
</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul" style="font-size: 24px;">Bank Soal</a>
    <p style="text-align: center; color: #4B5563; margin-top: 5px;">
        {{ $mapel->nama_mapel }}
    </p>
</div>

@if (session('success'))
<div id="popup-success" class="popup-message success">{{ session('success') }}</div>
@endif
@if (session('error'))
<div id="popup-error" class="popup-message error">{{ session('error') }}</div>
@endif

<form action="{{ route('guru.mapel.bank_soal.store', $mapel->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="table-container-wrapper">


        <div class="overflow-x-auto">
            <table class="table-container">
                {{-- Header Tabel --}}
                <thead>
                    <tr>
                        <th style="width: 50%;">Nama File & File</th>
                        <th style="width: 20%;">Visibilitas</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>

                {{-- Body Tabel (Tempat Data Existing & Baru) --}}
                <tbody id="file-list-container">
                    {{-- 1. File Yang Sudah Ada (Existing) --}}
                    @forelse ($bankSoals as $file)
                    <tr class="file-row existing-file" data-file-id="{{ $file->id }}">
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-file-earmark-pdf-fill file-icon" style="color: #EF4444; font-size: 18px;"></i>
                                {{-- Input Nama File --}}
                                <input type="text" name="existing[{{ $file->id }}][nama]" class="file-name-input" value="{{ old('existing.' . $file->id . '.nama', $file->nama) }}" required style="padding: 8px; border: 1px solid #D1D5DB; border-radius: 4px; width: 100%;">
                            </div>
                        </td>
                        <td>
                            {{-- Input Visibilitas --}}
                            <select name="existing[{{ $file->id }}][visibilitas]" class="file-visibility-select" style="padding: 8px; border: 1px solid #D1D5DB; border-radius: 4px; width: 100%;">
                                <option value="Public" {{ $file->visibilitas == 'Public' ? 'selected' : '' }}>Public</option>
                                <option value="Private" {{ $file->visibilitas == 'Private' ? 'selected' : '' }}>Private</option>
                        </td>
                        <td>
                            <div class="file-settings-group">
                                {{-- Tombol View/Download --}}
                                <a href="{{ $file->file_url }}" target="_blank" class="table-btn">cek<i class="bi bi-eye-fill"></i></a>

                                {{-- Tombol Pemicu Modal (Ganti Tombol Submit) --}}
                                <button type="button"
                                    class="table-btn-red"
                                    onclick="openDeleteModal({{ $file->id }}, '{{ str_replace("'", "\'", $file->nama) }}')">
                                     Hapus
                                     <i class="bi bi-trash-fill"></i>
                                </button>

                                {{-- TIDAK ADA FORM DELETE DI SINI --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" id="no-files-message" style="text-align: center; color: #6B7280; padding: 20px;">Belum ada file di bank soal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tombol Aksi di Bawah Tabel --}}
    <div class="file-list-action" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 0;">
        {{-- Tombol Tambah --}}
        <button type="button" id="add-file-btn" class="dark-btn"><i class="bi bi-plus-circle-fill"></i> Tambah File Baru</button>

        {{-- Tombol Simpan Perubahan --}}
        <button type="submit" class="dark-btn" style="background-color: #10B981;">Simpan</button>
    </div>
</form>

<form id="globalDeleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- TEMPLATE UNTUK BARIS FILE BARU (Penting untuk JS) --}}
<template id="new-file-template">
    <tr class="file-row new-file">
        <td>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <input type="file" name="new_files[idx][file]" class="file-upload-input" accept="application/pdf" required style="padding: 8px; border: 1px solid #D1D5DB; border-radius: 4px;">
                <input type="text" name="new_files[idx][nama]" class="file-name-input" placeholder="Nama File (cth: Latihan Bab 3)" required style="padding: 8px; border: 1px solid #D1D5DB; border-radius: 4px;">
            </div>
        </td>
        <td>
            <select name="new_files[idx][visibilitas]" class="file-visibility-select" style="padding: 8px; border: 1px solid #D1D5DB; border-radius: 4px; width: 100%;">
                <option value="Private" selected>Private</option>
                <option value="Public">Public</option>
            </select>
        </td>
        <td>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="remove-new-file-btn delete-btn"> Hapus <i class="bi bi-trash-fill"></i></button>
            </div>
        </td>
    </tr>
</template>

<div id="deleteModal" class="delete-modal" style="display: none;">
    <div class="delete-modal-content">
        <h3>Konfirmasi Hapus</h3>
        <p id="deleteMessage">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="modal-actions">
            <button type="button" id="confirmDeleteBtn" class="table-btn-red" style="background-color: #EF4444; color: white;">Ya, Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
        </div>
    </div>
</div>
@endsection

<script>
    // Variabel global untuk menyimpan ID yang akan dihapus
    let deleteId = null;

    // ===========================================
    // UTILITY FUNCTIONS (Didefinisikan secara global untuk Blade 'onclick')
    // ===========================================

    window.openDeleteModal = function(id, nama) {
        deleteId = id;
        document.getElementById("deleteMessage").innerText = `Apakah Anda yakin ingin menghapus file "${nama}"?`;
        document.getElementById("deleteModal").style.display = "flex";
    }

    window.closeDeleteModal = function() {
        document.getElementById("deleteModal").style.display = "none";
    }

    // ===========================================
    // MAIN INITIALIZATION (Hanya berjalan setelah DOM siap)
    // ===========================================
    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById('file-list-container');
        const template = document.getElementById('new-file-template');
        const addButton = document.getElementById('add-file-btn');
        const noFilesMessage = document.getElementById('no-files-message');

        // Inisialisasi variabel yang bergantung pada DOM di sini
        const confirmBtn = document.getElementById("confirmDeleteBtn");

        // Inisialisasi fileCounter hanya sekali
        let fileCounter = 0;
        const existingRows = container.querySelectorAll('.existing-file').length;
        fileCounter = existingRows;

        // --- FUNGSI UTAMA ---

        function updateCounter() {
            const existingCount = container.querySelectorAll('.existing-file').length;
            const newCount = container.querySelectorAll('.new-file').length;

            if (noFilesMessage) {
                // Tampilkan pesan kosong jika tidak ada row sama sekali
                const emptyRow = document.querySelector('#no-files-message').closest('tr');
                if (emptyRow) {
                    if (existingCount + newCount > 0) {
                        emptyRow.style.display = 'none';
                    } else {
                        emptyRow.style.display = 'table-row';
                    }
                }
            }
        }

        function addFileRow() {
            const newIndex = fileCounter++;
            const clone = template.content.cloneNode(true);
            const newRow = clone.querySelector('.file-row');

            clone.querySelectorAll('[name*="[idx]"]').forEach(el => {
                el.name = el.name.replace('[idx]', `[${newIndex}]`);
            });

            const removeBtn = clone.querySelector('.remove-new-file-btn');
            removeBtn.addEventListener('click', function() {
                newRow.remove();
                updateCounter();
            });

            container.appendChild(newRow);
            updateCounter();
        }

        // --- LISTENERS ---

        // FIX: Listener untuk tombol konfirmasi MODAL
        if (confirmBtn) {
            confirmBtn.addEventListener("click", function() {
                if (deleteId) {
                    const deleteForm = document.getElementById(`globalDeleteForm`);

                    // Set action URL pada form global sebelum submit
                    const deleteUrl = "{{ url('guru/bank-soal') }}" + '/' + deleteId;
                    deleteForm.action = deleteUrl;

                    deleteForm.submit();
                }
            });
        }

        // Listener untuk tombol Tambah
        addButton.addEventListener('click', addFileRow);

        // Panggil updateCounter saat halaman dimuat
        updateCounter();
    });
</script>