@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item active">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item">Daftar Guru</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Daftar Siswa</a>
</div>

@if (session('success'))
<div id="popup-success" class="popup-message success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div id="popup-error" class="popup-message error">
    ❌ Gagal menambahkan guru
</div>

@endif
<button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn mb-5">
    Beranda <i class="bi bi-house-door-fill"></i>
</button>

<button class="dark-btn mb-5 mr-5">Naik Kelas <i class="bi bi-arrow-up-circle-fill"></i></button>
<button type="button" onclick="window.location='{{ route('tambah_siswa') }}'" class="dark-btn mb-5">
    Tambah <i class="bi bi-plus-circle-fill"></i>
</button>

<br>

<div class="filter-container mb-5">
    <div class="search-box-container">
        <i class="bi bi-search search-icon"></i>
        <input type="search" id="searchInput" class="search-input" placeholder="Cari Nama atau NISN...">
    </div>

    <select id="kelasFilter" class="select-box">
        <option value="">Semua Kelas</option>
        {{-- Loop data kelas dari controller --}}
        @foreach ($kelasList as $kelas)
        {{-- Value-nya adalah ID, teksnya adalah nama kelas --}}
        <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
        @endforeach
    </select>
</div>

<div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
    <table class="table-container">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Kelas</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="siswaTableBody">

        </tbody>
    </table>
    <div id="loadingSpinner" style="display: none; text-align: center; margin: 10px 0;">
        <div class="spinner" style="
        border: 4px solid #f3f3f3;
        border-top: 4px solid #273F4F;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: auto;
    "></div>
    </div>
</div>


<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <h3>Konfirmasi Hapus</h3>
        <p id="deleteMessage">Apakah Anda yakin ingin menghapus data ini? </p>
        <div class="modal-actions">
            <button type="button" id="confirmDeleteBtn" class="table-btn-red">Ya, Hapus</button>
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- DEKLARASI ELEMEN ---
        const filterSelect = document.getElementById("kelasFilter");
        const searchInput = document.getElementById("searchInput");
        const tableBody = document.getElementById("siswaTableBody");
        const loadingSpinner = document.getElementById("loadingSpinner");
        const popup = document.querySelector('.popup-message');
        let deleteId = null;

        // --- FUNGSI UNTUK MEMUAT DATA ---
        function loadData(kelas = "", search = "") {
            loadingSpinner.style.display = "block";
            tableBody.innerHTML = "";
            const url = `{{ route('operator.filter_siswa') }}?kelas=${kelas}&search=${search}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Data siswa tidak ditemukan</td></tr>`;
                    } else {
                        let rows = '';
                        data.forEach(item => {
                            // Cek untuk memastikan data relasi ada sebelum ditampilkan
                            const namaKelas = item.kelas ? item.kelas.kelas : 'N/A';
                            const username = item.akun ? item.akun.username : 'N/A';

                            rows += `
                            <tr>
                                <td>${item.nama_lengkap}</td>
                                <td>${item.nisn}</td>
                                <td>${namaKelas}</td>  {{-- ✅ PERBAIKAN 1 --}}
                                <td>${item.jenis_kelamin || 'N/A'}</td>
                                <td>${username}</td>
                                <td>
                                    <button type="button" onclick="window.location='{{ url('/operator/siswa') }}/${item.id}/edit'" class="table-btn">
                                        Edit <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <form id="deleteForm-${item.id}" action="{{ url('/operator/siswa') }}/${item.id}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        {{-- ✅ PERBAIKAN 2: Penyebab Utama Error --}}
                                        <button type="button" class="table-btn-red" onclick="openDeleteModal(${item.id}, '${item.nama_lengkap.replace(/'/g, "\\'")}')">
                                            Delete <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            `;
                        });
                        tableBody.innerHTML = rows;
                    }
                })
                .catch(error => {
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">Terjadi error saat memuat data! Periksa console untuk detail.</td></tr>`;
                    console.error('Error saat memuat data siswa:', error);
                })
                .finally(() => {
                    loadingSpinner.style.display = "none";
                });
        }

        // --- FUNGSI MODAL & NOTIFIKASI ---
        window.openDeleteModal = function(id, nama) {
            deleteId = id;
            document.getElementById("deleteMessage").innerText = `Apakah Anda yakin ingin menghapus siswa "${nama}"?`;
            document.getElementById("deleteModal").style.display = "flex";
        }
        window.closeDeleteModal = function() {
            document.getElementById("deleteModal").style.display = "none";
            deleteId = null;
        }
        document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
            if (deleteId) {
                document.getElementById(`deleteForm-${deleteId}`).submit();
            }
        });

        if (popup) {
            setTimeout(() => popup.classList.add('show'), 100);
            setTimeout(() => popup.classList.remove('show'), 4000);
        }

        // --- EVENT LISTENERS ---
        loadData();
        filterSelect.addEventListener("change", () => loadData(filterSelect.value, searchInput.value));
        searchInput.addEventListener("keyup", () => loadData(filterSelect.value, searchInput.value));
    });
</script>
@endsection