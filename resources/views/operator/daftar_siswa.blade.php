    @extends('layouts.app')

    @section('title', 'Halaman Operator')

    @section('sidebar-menu')
    <a href="{{route('operator.daftar_siswa')}}" class="menu-item active">Daftar Siswa</a>
    <a href="{{route('daftar_guru2')}}" class="menu-item">Daftar Guru</a>
    @endsection

    @section('content')
    {{-- ... (Isi HTML Anda tetap sama) ... --}}
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
        ❌ Gagal memproses data siswa
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
            @foreach ($kelasList as $kelas)
                <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto">
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
            <tbody id="siswaTableBody"></tbody>
        </table>
        <div id="loadingSpinner" style="display: none; text-align: center; margin: 10px 0;">
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
            const filterSelect = document.getElementById("kelasFilter");
            const searchInput = document.getElementById("searchInput");
            const tableBody = document.getElementById("siswaTableBody");
            const loadingSpinner = document.getElementById("loadingSpinner");
            const popup = document.querySelector('.popup-message');
            let deleteId = null;

            function loadData(kelas = "", search = "") {
                loadingSpinner.style.display = "block";
                tableBody.innerHTML = "";
                const url = `{{ route('operator.filter_siswa') }}?kelas=${kelas}&search=${search}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = "";
                        if (data.length === 0) {
                            tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Data siswa tidak ditemukan</td></tr>`;
                        } else {
                            let rows = "";
                            data.forEach(item => {
                                const kelasNama = item.kelas ? item.kelas.kelas : 'N/A';
                                rows += `
                                    <tr>
                                        <td>${item.nama_lengkap}</td>
                                        <td>${item.nisn}</td>
                                        <td>${kelasNama}</td>
                                        <td>${item.username}</td> 
                                        <td>
                                            <button type="button" onclick="window.location='{{ url('/operator/siswa') }}/${item.id}/edit'" class="table-btn">
                                                Edit <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <form id="deleteForm-${item.id}" action="{{ url('/operator/siswa') }}/${item.id}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
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
                        console.error("Error:", error);
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">Terjadi error saat memuat data!</td></tr>`;
                    })
                    .finally(() => {
                        loadingSpinner.style.display = "none";
                    });
            }

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

            loadData();
            filterSelect.addEventListener("change", function() { loadData(this.value, searchInput.value); });
            searchInput.addEventListener("keyup", function() { loadData(filterSelect.value, this.value); });
        });
    </script>