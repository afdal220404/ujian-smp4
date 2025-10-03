@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item active">Daftar Guru</a>
@endsection

@section('content')
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
    <div class="search-box-container" style="width: 300px; margin-left: auto;">
        <i class="bi bi-search search-icon"></i>
        <input type="search" id="searchInput" class="search-input" placeholder="Cari Nama atau NIP...">
    </div>
</div>

{{-- Tabel untuk Menampilkan Data --}}
<div class="overflow-x-auto">
    <table class="table-container">
        <thead>
            <tr>
                <th>Nama Guru</th>
                <th>NIP</th>
                <th>Foto</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="guruTableBody">
            {{-- Data guru akan dimuat lewat AJAX --}}
        </tbody>
    </table>
    {{-- Indikator Loading --}}
    <div id="loadingSpinner" style="display: none; text-align: center; padding: 20px;">
        <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #273F4F; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: auto;"></div>
    </div>
</div>
@endsection

@section('scripts')
{{-- Modal Konfirmasi Hapus --}}
<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <h3>Konfirmasi Hapus</h3>
        <p id="deleteMessage">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="modal-actions">
            <form id="deleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="table-btn-red">Ya, Hapus</button>
            </form>
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk modal hapus
    function openDeleteModal(id, nama) {
        const modal = document.getElementById("deleteModal");
        const message = document.getElementById("deleteMessage");
        const form = document.getElementById("deleteForm");

        message.innerText = `Apakah Anda yakin ingin menghapus guru "${nama}"?`;
        form.action = `/guru/${id}`;
        modal.style.display = "flex";
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
    }

    // Event listener utama untuk semua fungsionalitas halaman
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput");
        const tableBody = document.getElementById("guruTableBody");
        const loadingSpinner = document.getElementById("loadingSpinner");
        const popup = document.querySelector('.popup-message');

        // Fungsi utama untuk memuat data
        function loadData(search = "") {
            loadingSpinner.style.display = "block";

            // =================================================================
            // KUNCI PERBAIKAN: Baris ini membersihkan tabel sebelum data baru dimasukkan.
            // Ini adalah baris yang paling penting untuk mengatasi bug Anda.
            tableBody.innerHTML = "";
            // =================================================================

            const url = `{{ route('guru.filter') }}?search=${search}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Data guru tidak ditemukan.</td></tr>`;
                    } else {
                        // Variabel untuk menampung semua baris HTML baru
                        let rows = '';
                        data.forEach(guru => {
                            let fotoUrl = guru.foto ? `{{ asset('storage') }}/${guru.foto}` : `{{ asset('images/default.png') }}`;

                            // Tambahkan baris baru ke variabel 'rows'
                            rows += `
                                <tr>
                                    <td>${guru.nama_lengkap}</td>
                                    <td>${guru.nip}</td>
                                    <td>
                                        <img src="${fotoUrl}" alt="Foto ${fotoUrl}" width="80" height="80" style="border-radius: 8px; object-fit: cover;">
                                    </td>
                                    <td>${guru.akun.username}</td>
                                    <td>
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
                        // Masukkan semua baris baru ke tabel sekaligus (lebih efisien)
                        tableBody.innerHTML = rows;
                    }
                })
                .catch(error => {
                    console.error('Error memuat data:', error);
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center">Gagal memuat data.</td></tr>`;
                })
                .finally(() => {
                    loadingSpinner.style.display = "none";
                });
        }

        // Event listener untuk input pencarian
        searchInput.addEventListener("keyup", function() {
            loadData(this.value);
        });

        // Logika untuk notifikasi pop-up
        if (popup) {
            setTimeout(() => popup.classList.add('show'), 100);
            setTimeout(() => popup.classList.remove('show'), 4000);
        }

        // Muat data pertama kali saat halaman dibuka
        loadData();
    });
</script>
@endsection