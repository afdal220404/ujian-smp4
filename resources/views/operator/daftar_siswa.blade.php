@extends('layouts.app')

@section('title', 'Data Siswa')

@section('sidebar-menu')
    <a href="{{ route('operator.landingpage') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('operator.daftar_siswa') }}" class="nav-link">
        <i class="bi bi-people"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('operator.alumni.index') }}" class="nav-link active">
        <i class="bi bi-mortarboard-fill"></i> <span>Data Alumni</span>
    </a>
    <a href="{{ route('daftar_guru2') }}" class="nav-link">
        <i class="bi bi-person-video3"></i> <span>Data Staff</span>
    </a>
    <a href="{{ route('walikelas.index') }}" class="nav-link">
        <i class="bi bi-award"></i> <span>Set Wali Kelas</span>
    </a>
    <a href="{{ route('mapel') }}" class="nav-link">
        <i class="bi bi-book"></i> <span>Mata Pelajaran</span>
    </a>
@endsection

@section('content')

{{-- 1. HEADER & BREADCRUMB --}}
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    {{-- Breadcrumb & Title --}}
    <div>
        <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
            <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-blue-600 font-bold">Manajemen Data</span>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-darkblue font-bold">Data Siswa</span>
        </div>
        <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Manajemen Siswa</h1>
    </div>

    {{-- ALERT NOTIFIKASI STANDAR --}}
    <div> 
        {{-- 1. Alert Sukses --}}
        @if (session('success'))
        <div id="alert-success" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down mb-2 md:mb-0">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif

        {{-- 2. Alert Error Validasi Umum --}}
        @if ($errors->any())
        <div id="alert-error" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down mb-2">
            <i class="bi bi-exclamation-triangle-fill"></i> Gagal memproses data.
        </div>
        @endif

        {{-- 3. ALERT SYSTEM ERROR (INI YANG HILANG DI KODE ANDA) --}}
        {{-- Wajib ada untuk menangkap error database / catch(\Exception $e) --}}
        @if (session('error'))
        <div id="alert-system-error" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
            <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
        </div>
        @endif
    </div>
</div>


{{-- === 1.B. AREA ERROR IMPORT (FULL WIDTH) === --}}
{{-- Diletakkan DI LUAR header agar memanjang penuh dari kiri ke kanan --}}
@if (session('import_errors'))
<div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl animate-fade-in-down">
    <div class="flex items-start gap-3">
        <div class="p-2 bg-red-100 rounded-full text-red-600 shrink-0">
            <i class="bi bi-file-earmark-x-fill text-xl"></i>
        </div>
        <div class="w-full">
            <h3 class="font-bold text-red-800 text-sm mb-2">Gagal Import Data</h3>
            
            {{-- List Scrollable --}}
            <div class="bg-white p-3 rounded-lg border border-red-100 max-h-40 overflow-y-auto">
                <ul class="list-disc list-inside text-xs text-red-600 space-y-1 font-mono">
                    @foreach (session('import_errors') as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
            </div>
            <p class="text-[10px] text-red-400 mt-2">*Silakan perbaiki file Excel Anda sesuai pesan di atas lalu upload ulang.</p>
        </div>
    </div>
</div>
@endif

{{-- 2. TOOLBAR (SEARCH, FILTER, ACTION) --}}
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
            
            {{-- KIRI: Pencarian & Filter --}}
            <div class="flex flex-col md:flex-row gap-3 flex-grow">
                {{-- Search Box (Kode Lama) --}}
                <div class="relative w-full md:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="bi bi-search text-gray-400"></i></span>
                    <input type="text" id="searchInput" class="w-full py-2.5 pl-10 pr-4 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:border-blue-500 outline-none" placeholder="Cari Nama / NISN...">
                </div>

                {{-- Filter Kelas (Kode Lama) --}}
                <div class="relative w-full md:w-48">
                    <select id="kelasFilter" class="w-full py-2.5 pl-10 pr-8 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-xl outline-none appearance-none cursor-pointer">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
                        @endforeach
                    </select>
                    <i class="bi bi-filter absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            {{-- KANAN: Tombol Aksi (TAMBAHAN IMPORT) --}}
            <div class="flex gap-2">
                
                {{-- TOMBOL IMPORT BARU --}}
                <button onclick="openImportModal()" class="px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 hover:shadow-lg transition-all flex items-center gap-2 shadow-green-200 shadow-md">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> <span class="hidden lg:inline">Import Excel</span>
                </button>

                <a href="{{ route('operator.kenaikan_kelas') }}" class="px-4 py-2.5 bg-orange-50 text-orange-600 border border-orange-100 rounded-xl text-sm font-bold hover:bg-orange-100 transition-all flex items-center gap-2">
                    <i class="bi bi-arrow-up-circle-fill"></i> <span class="hidden lg:inline">Naik Kelas</span>
                </a>
                
                <a href="{{ route('tambah_siswa') }}" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 hover:shadow-lg transition-all flex items-center gap-2 shadow-blue-200 shadow-md">
                    <i class="bi bi-plus-lg"></i> <span class="hidden lg:inline">Tambah</span>
                </a>
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT EXCEL (Letakkan di bagian bawah file, sebelum @endsection) --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-100">
                    
                    <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="bi bi-file-earmark-excel-fill text-green-600 text-lg"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg font-bold leading-6 text-gray-900">Import Data Siswa</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            {{-- Update teks ekstensi file --}}
                                            Upload file Excel (.xlsx) untuk menambahkan siswa secara massal.
                                        </p>
                                        
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-4 flex justify-between items-center">
                                            <span class="text-xs text-gray-500 font-bold">Belum punya format?</span>
                                            <a href="{{ route('siswa.template') }}" class="text-xs font-bold text-green-600 hover:underline flex items-center gap-1">
                                                {{-- Update ikon menjadi Excel --}}
                                                <i class="bi bi-file-earmark-excel-fill"></i> Download Template .xlsx
                                            </a>
                                        </div>

                                        {{-- Input File --}}
                                        <input type="file" name="file" required accept=".xlsx, .xls"
                                            class="block w-full text-sm text-gray-500 ... (style tetap sama) ...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-green-500 sm:w-auto transition-colors">
                                Upload & Proses
                            </button>
                            <button type="button" onclick="closeImportModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

{{-- 3. TABEL DATA --}}
<div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden relative min-h-[300px]">

    {{-- Loading Overlay (Hidden by default) --}}
    <div id="loadingOverlay" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center backdrop-blur-sm hidden">
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="text-sm text-gray-500 font-bold mt-3">Memuat data...</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 sticky top-0 z-0">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Siswa</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">NISN</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Kelas</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Akun (Username)</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="siswaTableBody" class="divide-y divide-gray-50">
                {{-- Data akan di-inject lewat JS --}}
            </tbody>
        </table>
    </div>
</div>

{{-- 4. MODAL HAPUS (Desain Baru) --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

            {{-- Modal Panel --}}
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="bi bi-exclamation-triangle-fill text-red-600 text-lg"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900" id="modal-title">Hapus Data Siswa</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="deleteMessage">
                                    Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <form id="deleteForm" method="POST" class="inline-block w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            Ya, Hapus
                        </button>
                    </form>
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
    document.addEventListener("DOMContentLoaded", function() {
        const filterSelect = document.getElementById("kelasFilter");
        const searchInput = document.getElementById("searchInput");
        const tableBody = document.getElementById("siswaTableBody");
        const loadingOverlay = document.getElementById("loadingOverlay");

        // Fungsi Load Data
        function loadData(kelas = "", search = "") {
            loadingOverlay.classList.remove('hidden');
            
            const url = `{{ route('operator.filter_siswa') }}?kelas=${kelas}&search=${search}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = "";
                    if (data.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="bi bi-folder-x text-4xl mb-2 opacity-50"></i>
                                        <span class="text-sm font-medium">Data siswa tidak ditemukan.</span>
                                    </div>
                                </td>
                            </tr>`;
                    } else {
                        let rows = "";
                        data.forEach(item => {
                            const kelasNama = item.kelas ? item.kelas.kelas : 'N/A';
                            
                            // Avatar dihapus, langsung menampilkan nama
                            rows += `
                                <tr class="hover:bg-blue-50/20 transition-colors group border-b border-gray-50 last:border-none">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-darkblue group-hover:text-blue-600 transition-colors">
                                            ${item.nama_lengkap}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                            ${item.nisn}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-gray-200 text-gray-600 shadow-sm">
                                            <i class="bi bi-bookmark-fill text-blue-400"></i> ${kelasNama}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                        @${item.username}
                                    </td> 
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        
                                        <div class="flex items-center justify-center gap-3">
                                            
                                            <button onclick="window.location='{{ url('/operator/siswa') }}/${item.id}/edit'" 
                                                    class="flex items-center gap-2 px-3 py-2 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg hover:bg-amber-100 hover:border-amber-200 transition-all shadow-sm group/btn" 
                                                    title="Edit Data">
                                                <i class="bi bi-pencil-square text-lg"></i>
                                                <span class="text-xs font-bold">Edit</span>
                                            </button>
                                            
                                            <button onclick="openDeleteModal(${item.id}, '${item.nama_lengkap.replace(/'/g, "\\'")}')" 
                                                    class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 hover:border-red-200 transition-all shadow-sm group/btn" 
                                                    title="Hapus Data">
                                                <i class="bi bi-trash3 text-lg"></i>
                                                <span class="text-xs font-bold">Hapus</span>
                                            </button>

                                        </div>

                                    </td>
                                </tr>
                            `;
                        });
                        tableBody.innerHTML = rows;
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500 font-bold">Gagal memuat data server!</td></tr>`;
                })
                .finally(() => {
                    loadingOverlay.classList.add('hidden');
                });
        }

        // Event Listeners
        filterSelect.addEventListener("change", function() { loadData(this.value, searchInput.value); });
        
        // Debounce Search
        let timeout = null;
        searchInput.addEventListener("keyup", function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                loadData(filterSelect.value, this.value);
            }, 500); 
        });

        // Load Awal
        loadData();

        // Notifikasi Auto Close
        const alerts = document.querySelectorAll('[id^="alert-"]');
        if (alerts.length > 0) {
            setTimeout(() => {
                alerts.forEach(el => {
                    el.style.transition = "opacity 0.5s ease";
                    el.style.opacity = "0";
                    setTimeout(() => el.remove(), 500);
                });
            }, 4000);
        }
    });

    // Fungsi Modal (Global Scope)
    function openDeleteModal(id, nama) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const message = document.getElementById('deleteMessage');

        // Set Action URL dinamis
        form.action = `{{ url('/operator/siswa') }}/${id}`;
        
        // Update Pesan
        message.innerHTML = `Apakah Anda yakin ingin menghapus data siswa <b>"${nama}"</b>?<br>Data yang dihapus tidak dapat dikembalikan.`;
        
        // Tampilkan Modal
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

</script>
@endsection
