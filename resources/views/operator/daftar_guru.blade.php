@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item active">Daftar Guru</a>
@endsection

@section('content')
{{-- ... (Isi HTML Anda tetap sama) ... --}}
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Daftar Guru</a>
</div>

@if (session('success'))
<div id="popup-success" class="popup-message success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div id="popup-error" class="popup-message error">
    ❌ Gagal menghapus guru
</div>
@endif

{{-- Tombol-tombol Aksi --}}
<div class="filter-container mb-5">
    <button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn">
        Beranda <i class="bi bi-house-door-fill"></i>
    </button>
    <button type="button" onclick="window.location='{{ route('guru.create') }}'" class="dark-btn">
        Tambah <i class="bi bi-plus-circle-fill"></i>
    </button>
    {{-- Form Pencarian Live --}}
    <div class="search-box-container">
        <i class="bi bi-search search-icon"></i>
        <input type="search" id="searchInput" class="search-input" placeholder="Cari Nama atau NIP...">
    </div>
</div>

<div class="overflow-x-auto">
    <table class="table-container">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nama Guru</th>
                <th>NIP</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="guruTableBody">
            {{-- Data akan dimuat di sini oleh JavaScript --}}
        </tbody>
    </table>
    <div id="loadingSpinner" style="display: none; text-align: center; margin: 20px 0;">
        <div class="spinner"></div>
    </div>
</div>

<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <h3>Konfirmasi Hapus</h3>
        <p id="deleteMessage">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="modal-actions">
            <button type="button" id="confirmDeleteBtn" class="table-btn-red">Ya, Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
        </div>
    </div>
</div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ... (Elemen dan fungsi modal tetap sama) ...
        const searchInput = document.getElementById("searchInput");
        const tableBody = document.getElementById("guruTableBody");
        const loadingSpinner = document.getElementById("loadingSpinner");
        const popup = document.querySelector('.popup-message');
        let deleteId = null;
        
        window.openDeleteModal = function(id, nama) {
            deleteId = id;
            document.getElementById("deleteMessage").innerText = `Apakah Anda yakin ingin menghapus data "${nama}"?`;
            document.getElementById("deleteModal").style.display = "flex";
        }

        window.closeDeleteModal = function() {
            document.getElementById("deleteModal").style.display = "none";
        }
        
        document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
            if (deleteId) {
                document.getElementById(`deleteForm-${deleteId}`).submit();
            }
        });

        function loadData(search = "") {
            loadingSpinner.style.display = "block";
            tableBody.innerHTML = "";
            fetch(`{{ route('guru.filter') }}?search=${search}`)
                .then(response => response.json())
                .then(data => {
                    let rows = "";
                    if (data.length === 0) {
                        rows = `<tr><td colspan="5" class="text-center">Data guru tidak ditemukan</td></tr>`;
                    } else {
                        data.forEach(guru => {
                            const fotoUrl = guru.foto ? `{{ asset('storage') }}/${guru.foto}` : `{{ asset('image/dummy.jpg') }}`;
                            rows += `
                                <tr>
                                    <td><img src="${fotoUrl}" alt="Foto Guru" class="w-30 h-30 rounded"></td>
                                    <td>${guru.nama_lengkap}</td>
                                    <td>${guru.nip}</td>
                                    <td>${guru.username}</td> {{-- ✅ PERBAIKAN: Langsung panggil guru.username --}}
                                    <td>
                                        <form id="deleteForm-${guru.id}" action="{{ url('/guru') }}/${guru.id}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button type="button" onclick="window.location='{{ url('/guru') }}/${guru.id}/edit'" class="table-btn">
                                            Edit <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button type="button" class="table-btn-red" onclick="openDeleteModal(${guru.id}, '${guru.nama_lengkap.replace(/'/g, "\\'")}')">
                                            Delete <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    tableBody.innerHTML = rows;
                })
                .catch(error => {
                    console.error('Error memuat data:', error);
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Gagal memuat data.</td></tr>`;
                })
                .finally(() => {
                    loadingSpinner.style.display = "none";
                });
        }

        searchInput.addEventListener("keyup", function() { loadData(this.value); });
        if (popup) {
            setTimeout(() => popup.classList.add('show'), 100);
            setTimeout(() => popup.classList.remove('show'), 4000);
        }
        loadData();
    });
</script>