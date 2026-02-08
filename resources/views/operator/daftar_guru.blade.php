@extends('layouts.app')

@section('title', 'Data Guru')

@section('sidebar-menu')
    {{-- Kategori UTAMA: Jarak atas dikurangi drastis (mt-1) agar naik mendekati foto --}}
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 px-3 mt-1">
        Utama
    </div>
    
    <a href="{{ route('operator.landingpage') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>

    {{-- Kategori MANAJEMEN DATA: Margin dikurangi dari mt-4 jadi mt-2 --}}
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 px-3 mt-2">
        Manajemen Data
    </div>
    
    <a href="{{ route('operator.daftar_siswa') }}" class="nav-link">
        <i class="bi bi-people"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('daftar_guru2') }}" class="nav-link">
        <i class="bi bi-person-video3"></i> <span>Data Guru</span>
    </a>

    {{-- Kategori AKADEMIK: Margin dikurangi dari mt-4 jadi mt-2 --}}
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 px-3 mt-2">
        Akademik
    </div>
    
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
    <div>
        <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
                <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Manajemen Data</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-darkblue font-bold">Data Guru</span>
            </div>
        <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Manajemen Guru</h1>
    </div>

    {{-- NOTIFIKASI --}}
    <div>
        @if (session('success'))
        <div id="alert-success" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif
        @if ($errors->any())
        <div id="alert-error" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
            <i class="bi bi-exclamation-triangle-fill"></i> Gagal memproses data.
        </div>
        @endif
    </div>
</div>

{{-- 2. TOOLBAR (SEARCH & ACTION) --}}
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        
        {{-- KIRI: Pencarian --}}
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="bi bi-search text-gray-400"></i>
            </span>
            <input type="text" id="searchInput" 
                class="w-full py-2.5 pl-10 pr-4 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" 
                placeholder="Cari Nama / NIP...">
        </div>

        {{-- KANAN: Tombol Tambah --}}
        <div class="flex gap-2 w-full md:w-auto">
            <a href="{{ route('guru.create') }}" class="w-full md:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 hover:shadow-lg transition-all flex items-center justify-center gap-2 shadow-blue-200 shadow-md">
                <i class="bi bi-person-plus-fill"></i> Tambah Guru
            </a>
        </div>
    </div>
</div>

{{-- 3. TABEL DATA --}}
<div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden relative min-h-[400px]">

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center backdrop-blur-sm hidden">
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="text-sm text-gray-500 font-bold mt-3">Memuat data guru...</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 sticky top-0 z-0">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Profil</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Nama Lengkap</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">NIP / Identitas</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Akun (Username)</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="guruTableBody" class="divide-y divide-gray-50">
                {{-- Data di-load via JS --}}
            </tbody>
        </table>
    </div>
</div>

{{-- 4. MODAL HAPUS --}}
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
                            <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900">Hapus Data Guru</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="deleteMessage">
                                    Apakah Anda yakin? Data ini tidak bisa dikembalikan.
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
        const searchInput = document.getElementById("searchInput");
        const tableBody = document.getElementById("guruTableBody");
        const loadingOverlay = document.getElementById("loadingOverlay");

        // Fungsi Load Data
        function loadData(search = "") {
            loadingOverlay.classList.remove('hidden'); 
            
            fetch(`{{ route('guru.filter') }}?search=${search}`)
                .then(response => response.json())
                .then(data => {
                    let rows = "";
                    if (data.length === 0) {
                        rows = `
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="bi bi-person-x text-4xl mb-2 opacity-50"></i>
                                        <span class="text-sm font-medium">Data guru tidak ditemukan.</span>
                                    </div>
                                </td>
                            </tr>`;
                    } else {
                        data.forEach(guru => {
                            const fotoUrl = guru.foto ? `{{ asset('storage') }}/${guru.foto}` : `{{ asset('image/dummy.jpg') }}`;
                            
                            rows += `
                                <tr class="hover:bg-blue-50/20 transition-colors group border-b border-gray-50 last:border-none">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- PERUBAHAN UKURAN FOTO DI SINI --}}
                                        {{-- h-25 w-25 (64px) dan rounded-2xl (kotak tumpul) agar lebih jelas --}}
                                        <div class="h-25 w-25 rounded-2xl overflow-hidden border border-gray-200 shadow-sm relative">
                                            <img src="${fotoUrl}" alt="Foto" class="h-full w-full object-cover">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-middle">
                                        <div class="text-sm font-bold text-darkblue group-hover:text-blue-600 transition-colors">
                                            ${guru.nama_lengkap}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-middle">
                                        <span class="text-sm font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                            ${guru.nip}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-middle">
                                        <span class="text-sm text-gray-600 font-medium">
                                            <span class="text-gray-400">@</span>${guru.username}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle">
                                        <div class="flex items-center justify-center gap-3">
                                            
                                            <button onclick="window.location='{{ url('/guru') }}/${guru.id}/edit'" 
                                                    class="flex items-center gap-2 px-3 py-2 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg hover:bg-amber-100 hover:border-amber-200 transition-all shadow-sm group/btn" 
                                                    title="Edit Data">
                                                <i class="bi bi-pencil-square text-lg"></i>
                                                <span class="text-xs font-bold">Edit</span>
                                            </button>
                                            
                                            <button onclick="openDeleteModal(${guru.id}, '${guru.nama_lengkap.replace(/'/g, "\\'")}')" 
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
                    }
                    tableBody.innerHTML = rows;
                })
                .catch(error => {
                    console.error('Error:', error);
                    tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500 font-bold">Gagal memuat data server!</td></tr>`;
                })
                .finally(() => {
                    loadingOverlay.classList.add('hidden');
                });
        }

        let timeout = null;
        searchInput.addEventListener("keyup", function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                loadData(this.value);
            }, 500); 
        });

        loadData();

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

    function openDeleteModal(id, nama) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const message = document.getElementById('deleteMessage');

        form.action = `{{ url('/guru') }}/${id}`;
        message.innerHTML = `Apakah Anda yakin ingin menghapus data guru <b>"${nama}"</b>?`;
        
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endsection