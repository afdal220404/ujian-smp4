@extends('layouts.app')

@section('title', 'Bank Soal ' . $mapel->nama_mapel)

@section('sidebar-menu')
    {{-- Tombol Kembali --}}
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
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-gray-500">Kelas VII</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Bank Soal</span>
            </div>
        </div>
        
        {{-- TOMBOL KEMBALI --}}
        <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">
            <i class="bi bi-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-[Poppins-Bold] text-darkblue tracking-tight">
                Bank Soal & Materi
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola file materi dan soal untuk mapel <span class="font-bold text-blue-600">{{ $mapel->nama_mapel }}</span>
            </p>
        </div>
        <div class="hidden md:block">
            <span class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-bold border border-blue-100">
                <i class="bi bi-folder2-open"></i> Total: <span id="total-files-badge">{{ count($bankSoals) }}</span> File
            </span>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
    <div id="alert-success" class="mb-6 px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl shadow-sm text-sm font-bold flex items-center gap-3">
        <i class="bi bi-check-circle-fill text-lg"></i> {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-100 text-red-700 rounded-xl shadow-sm text-sm font-bold flex items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-lg"></i> {{ session('error') }}
    </div>
    @endif

    {{-- FORM UTAMA --}}
    <form action="{{ route('guru.mapel.bank_soal.store', $mapel->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden relative">
            
            {{-- Header Tabel --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 grid grid-cols-12 gap-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                <div class="col-span-12 md:col-span-6">Nama File & Dokumen</div>
                <div class="col-span-6 md:col-span-3">Visibilitas</div>
                <div class="col-span-6 md:col-span-3 text-center">Aksi</div>
            </div>

            {{-- Container List File --}}
            <div id="file-list-container" class="divide-y divide-gray-50">
                
                {{-- 1. LOOP FILE EXISTING --}}
                @forelse ($bankSoals as $file)
                <div class="file-row existing-file p-4 hover:bg-blue-50/30 transition-colors grid grid-cols-12 gap-4 items-center group" data-file-id="{{ $file->id }}">
                    
                    {{-- Kolom Nama & Ikon --}}
                    <div class="col-span-12 md:col-span-6 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center shrink-0 border border-red-100">
                            <i class="bi bi-file-earmark-pdf-fill text-xl"></i>
                        </div>
                        <div class="w-full">
                            <label class="text-[10px] text-gray-400 font-bold uppercase mb-1 block">Nama File</label>
                            <input type="text" name="existing[{{ $file->id }}][nama]" 
                                   value="{{ old('existing.' . $file->id . '.nama', $file->nama) }}" 
                                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none text-sm font-medium transition-all" 
                                   required>
                            <a href="{{ $file->file_url }}" target="_blank" class="text-[10px] text-blue-500 hover:underline mt-1 inline-block">
                                <i class="bi bi-link-45deg"></i> Lihat File Asli
                            </a>
                        </div>
                    </div>

                    {{-- Kolom Visibilitas --}}
                    <div class="col-span-6 md:col-span-3">
                        <label class="text-[10px] text-gray-400 font-bold uppercase mb-1 block md:hidden">Visibilitas</label>
                        <div class="relative">
                            <select name="existing[{{ $file->id }}][visibilitas]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm appearance-none cursor-pointer">
                                <option value="Public" {{ $file->visibilitas == 'Public' ? 'selected' : '' }}>Public (Siswa Bisa Lihat)</option>
                                <option value="Private" {{ $file->visibilitas == 'Private' ? 'selected' : '' }}>Private (Hanya Guru)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                <i class="bi bi-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Aksi --}}
                    <div class="col-span-6 md:col-span-3 flex justify-end md:justify-center gap-2">
                        <a href="{{ $file->file_url }}" target="_blank" 
                           class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all border border-transparent hover:border-blue-200"
                           title="Lihat File">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        <button type="button" 
                                onclick="openDeleteModal({{ $file->id }}, '{{ str_replace("'", "\'", $file->nama) }}')"
                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition-all border border-red-100"
                                title="Hapus File">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </div>
                </div>
                @empty
                {{-- Empty State (Akan disembunyikan via JS jika ada file baru) --}}
                <div id="no-files-message" class="py-12 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <i class="bi bi-folder2-open text-3xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada file materi atau soal.</p>
                    <p class="text-xs text-gray-400 mt-1">Klik "Tambah File Baru" untuk mengupload.</p>
                </div>
                @endforelse

            </div>
        </div>

        {{-- ACTION BAR (STATIS) --}}
        <div class="mt-6 flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-gray-200 gap-4">
            
            {{-- Tombol Tambah --}}
            <button type="button" id="add-file-btn" class="w-full md:w-auto px-5 py-2.5 bg-gray-50 text-gray-700 rounded-xl font-bold hover:bg-blue-50 hover:text-blue-600 transition-all text-sm flex items-center justify-center gap-2 border border-gray-200">
                <i class="bi bi-plus-lg"></i> Tambah File Baru
            </button>

            {{-- Tombol Simpan --}}
            <button type="submit" class="w-full md:w-auto px-8 py-2.5 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/30 transition-all text-sm flex items-center justify-center gap-2">
                <i class="bi bi-save2-fill"></i> Simpan Perubahan
            </button>
        </div>

    </form>

    {{-- FORM DELETE GLOBAL (Hidden) --}}
    <form id="globalDeleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- TEMPLATE FILE BARU (Hidden) --}}
    <template id="new-file-template">
        <div class="file-row new-file p-4 bg-blue-50/50 border-l-4 border-blue-500 grid grid-cols-12 gap-4 items-center animate-fade-in-down">
            
            {{-- Kolom Upload --}}
            <div class="col-span-12 md:col-span-6 flex items-start gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="bi bi-cloud-arrow-up-fill text-xl"></i>
                </div>
                <div class="w-full space-y-2">
                    <input type="file" name="new_files[idx][file]" 
                           class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition-all" 
                           accept="application/pdf" required>
                    <input type="text" name="new_files[idx][nama]" 
                           class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm transition-all placeholder-gray-400" 
                           placeholder="Masukkan Nama File (Contoh: Materi Bab 1)" required>
                </div>
            </div>

            {{-- Kolom Visibilitas --}}
            <div class="col-span-6 md:col-span-3">
                <div class="relative">
                    <select name="new_files[idx][visibilitas]" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:border-blue-500 outline-none text-sm appearance-none cursor-pointer">
                        <option value="Private" selected>Private (Default)</option>
                        <option value="Public">Public</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Kolom Aksi --}}
            <div class="col-span-6 md:col-span-3 flex justify-end md:justify-center">
                <button type="button" class="remove-new-file-btn px-3 py-2 bg-white border border-gray-200 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-all text-xs font-bold flex items-center gap-2">
                    <i class="bi bi-x-lg"></i> Batal
                </button>
            </div>
        </div>
    </template>

    {{-- MODAL HAPUS --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-trash3-fill text-red-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900">Hapus File?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" id="deleteMessage">
                                        Apakah Anda yakin? File ini akan dihapus permanen dari server.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" id="confirmDeleteBtn" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            Ya, Hapus
                        </button>
                        <button type="button" onclick="closeDeleteModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    let deleteId = null;

    // --- UTILITY MODAL ---
    function openDeleteModal(id, nama) {
        deleteId = id;
        document.getElementById("deleteMessage").innerHTML = `Apakah Anda yakin ingin menghapus file <b>"${nama}"</b>?`;
        document.getElementById("deleteModal").classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.add('hidden');
        deleteId = null;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const container = document.getElementById('file-list-container');
        const template = document.getElementById('new-file-template');
        const addButton = document.getElementById('add-file-btn');
        const noFilesMessage = document.getElementById('no-files-message');
        const confirmBtn = document.getElementById("confirmDeleteBtn");

        let fileCounter = {{ count($bankSoals) }};

        // Update Tampilan Kosong/Isi
        function updateCounter() {
            const rowCount = container.querySelectorAll('.file-row').length;
            if (noFilesMessage) {
                if (rowCount > 0) {
                    noFilesMessage.style.display = 'none';
                } else {
                    noFilesMessage.style.display = 'flex';
                }
            }
        }

        // Tambah Baris File Baru
        function addFileRow() {
            const newIndex = fileCounter++;
            const clone = template.content.cloneNode(true);
            const newRow = clone.querySelector('.file-row');

            // Replace Index
            clone.querySelectorAll('[name*="[idx]"]').forEach(el => {
                el.name = el.name.replace('[idx]', `[${newIndex}]`);
            });

            // Listener Tombol Batal
            const removeBtn = clone.querySelector('.remove-new-file-btn');
            removeBtn.addEventListener('click', function() {
                newRow.remove();
                updateCounter();
            });

            // Insert ke container (sebelum pesan kosong)
            if (noFilesMessage) {
                container.insertBefore(newRow, noFilesMessage);
            } else {
                container.appendChild(newRow);
            }
            
            updateCounter();
            
            // Scroll ke elemen baru
            newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // --- EVENT LISTENERS ---
        
        // Modal Confirm Delete
        if (confirmBtn) {
            confirmBtn.addEventListener("click", function() {
                if (deleteId) {
                    const deleteForm = document.getElementById(`globalDeleteForm`);
                    // Set action URL dinamis
                    deleteForm.action = "{{ url('guru/bank-soal') }}" + '/' + deleteId;
                    deleteForm.submit();
                }
            });
        }

        addButton.addEventListener('click', addFileRow);

        // Auto Close Alert
        const alertSuccess = document.getElementById('alert-success');
        if(alertSuccess) setTimeout(() => alertSuccess.style.display = 'none', 4000);

        updateCounter();
    });
</script>
@endsection