@extends('layouts.app')

@section('title', 'Dashboard ' . $mapel->nama_mapel . ' - ' . $kelas->kelas)

@section('sidebar-menu')
<a href="{{ route('guru.index') }}" class="menu-item">
    <i class="bi bi-arrow-left"></i> Kembali ke Pilihan
</a>

<hr class="sidebar-divider">

<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item active">
    <i class="bi bi-pie-chart-fill"></i> Dasboard
</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item">
    <i class="bi bi-card-checklist"></i> Daftar Nilai Siswa
</a>
@endsection

@push('styles')
{{-- Kita akan gunakan style yang sama dengan Dasbor Operator --}}
<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 30px;
        padding: 15px;
        border-radius: 50%;
        color: #fff;
    }

    .stat-icon.siswa {
        background-color: #3B82F6;
    }

    /* Biru */
    .stat-icon.kuis {
        background-color: #10B981;
    }

    /* Hijau */
    .stat-icon.uts {
        background-color: #F59E0B;
    }

    /* Oranye */
    .stat-icon.uas {
        background-color: #EF4444;
    }

    /* Merah */
    .stat-info .stat-number {
        font-size: 24px;
        font-weight: 700;
        color: #1F2937;
    }

    .stat-info .stat-label {
        font-size: 14px;
        color: #6B7280;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .menu-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border-left: 5px solid transparent;
    }

    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        border-left-color: #273F4F;
    }

    .menu-card .menu-icon {
        font-size: 40px;
        margin-bottom: 15px;
        color: #273F4F;
    }

    .menu-card .menu-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }

    .menu-card a {
        text-decoration: none;
        color: inherit;
    }

    /* ▼▼▼ TAMBAHKAN STYLE UNTUK TABEL UJIAN ▼▼▼ */
    .table-container-wrapper {
        margin-top: 30px;
        background-color: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .table-title {
        font-size: 18px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 20px;
    }

    /* Menggunakan kembali style .table-container dari page.css */
    .table-container {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container th,
    .table-container td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #E5E7EB;
    }

    .table-container th {
        background-color: #F9FAFB;
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
    }

    .table-container td {
        font-size: 14px;
        color: #374151;
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .badge-kuis {
        background-color: #DCFCE7;
        color: #166534;
    }

    .badge-uts {
        background-color: #FEF3C7;
        color: #92400E;
    }

    .badge-uas {
        background-color: #FEE2E2;
        color: #991B1B;
    }
</style>
@endpush

@section('content')

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
{{-- 1. Kalimat Sapaan --}}
<h2 class="sapaan" style="font-size: 24px; font-weight: 600; margin-bottom: 5px;">
    Dasboard {{$mapel->nama_mapel}} Kelas {{ $kelas->kelas }}
</h2>
<p style="margin-bottom: 30px; color: #4B5563;">
    Anda sedang mengelola mata pelajaran <strong>{{ $mapel->nama_mapel }}</strong> untuk kelas <strong>{{ $kelas->kelas }}</strong>.
</p>

{{-- 2. CardView Statistik (4 Kartu) --}}
<div class="stats-container">
    <div class="stat-card">
        <div class="stat-icon siswa"><i class="bi bi-people-fill"></i></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $jumlahSiswa }}</div>
            <div class="stat-label">Siswa di Kelas Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon guru "><i class="bi bi-lightbulb-fill"></i></div> {{-- <-- INI BENAR --}}
        <div class="stat-info">
            <div class="stat-number">{{ $avgKuis }}</div>
            <div class="stat-label">Rata-rata Kuis</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon aktif"><i class="bi bi-file-earmark-text-fill"></i></div> {{-- <-- INI BENAR --}}
        <div class="stat-info">
            <div class="stat-number">{{ $avgUTS }}</div>
            <div class="stat-label">Rata-rata UTS</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon uas"><i class="bi bi-award-fill"></i></div> {{-- <-- INI BENAR --}}
        <div class="stat-info">
            <div class="stat-number">{{ $avgUAS }}</div>
            <div class="stat-label">Rata-rata UAS</div>
        </div>
    </div>
</div>

{{-- 3. Tombol Menu (2 Kartu) --}}
<div class="menu-grid">
    <div class="menu-card">
        <a href="{{ route('guru.mapel.ujian.create', $mapel->id) }}">
            <div class="menu-icon"><i class="bi bi-pencil-square"></i></div>
            <div class="menu-title">Tambah Ujian Baru</div>
        </a>
    </div>
    <div class="menu-card">
        <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}">
            <div class="menu-icon"><i class="bi bi-archive-fill"></i></div>
            <div class="menu-title">Bank Soal</div>
        </a>
    </div>
</div>
<div class="table-container-wrapper">
    <h3 class="table-title" style="margin-top: 20px; margin-bottom: 10px; font-weight: 600;">Ujian yang Akan Datang</h3>
    <div class="overflow-x-auto">
        <table class="table-container">
            <thead>
                <tr>
                    <th>Nama Ujian</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Waktu Mulai</th>
                    <th>Durasi</th>
                    <th>Aksi</th> {{-- Pastikan kolom Aksi ada --}}
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarUjian as $ujian)
                <tr>
                    <td>{{ $ujian->nama_ujian }}</td>
                    <td>
                        <span class="badge {{ $ujian->badge_class }}">
                            {{ $ujian->jenis_ujian }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->isoFormat('dddd, D MMMM Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} WIB</td>
                    <td>{{ $ujian->durasi_menit }} Menit</td>
                    <td>
                        {{-- Tombol Edit --}}
                        <button type="button" class="table-btn"
                            onclick="window.location.href='{{ route('guru.mapel.ujian.edit', $ujian->id) }}'">
                            Edit <i class="bi bi-pencil-fill"></i>
                        </button>
                        <form id="deleteForm-{{ $ujian->id }}"
                            action="{{ route('guru.mapel.ujian.destroy', $ujian->id) }}"
                            method="POST"
                            style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>

                        {{-- 2. Tombol Hapus (pemicu modal) --}}
                        <button type="button" class="table-btn-red ml-3"
                            onclick="openDeleteModal({{ $ujian->id }}, '{{ str_replace("'", "\'", $ujian->nama_ujian) }}')">
                            Delete <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- Pastikan colspan adalah 6 (karena ada 6 kolom) --}}
                    <td colspan="6" class="text-center" style="padding: 20px;">
                        Belum ada ujian yang akan datang untuk mata pelajaran ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="table-container-wrapper" style="margin-top: 30px;">
    <h3 class="table-title" style="margin-top: 20px; margin-bottom: 10px; font-weight: 600;">History Ujian</h3>
    <div class="overflow-x-auto">
        <table class="table-container">
            <thead>
                <tr>
                    <th>Nama Ujian</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Selesai Pukul</th> {{-- Kolom diganti agar lebih relevan --}}
                    <th>Durasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Gunakan variabel $historyUjian yang baru --}}
                @forelse ($historyUjian as $ujian)
                <tr>
                    <td>{{ $ujian->nama_ujian }}</td>
                    <td>
                        <span class="badge {{ $ujian->badge_class }}">
                            {{ $ujian->jenis_ujian }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->isoFormat('dddd, D MMMM Y') }}</td>
                    {{-- Tampilkan waktu selesai, bukan waktu mulai --}}
                    <td>{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB</td>
                    <td>{{ $ujian->durasi_menit }} Menit</td>
                    <td>
                        {{-- Ganti tombol menjadi "Detail" atau "Lihat Hasil" --}}
                        <button type="button" class="table-btn" 
                            onclick="window.location.href='{{ route('guru.mapel.ujian.detail', $ujian->id) }}'"> 
                            Detail <i class="bi bi-eye-fill"></i>
                        </button>
                        <form id="deleteForm-{{ $ujian->id }}"
                            action="{{ route('guru.mapel.ujian.destroy', $ujian->id) }}"
                            method="POST"
                            style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>

                        {{-- 2. Tombol Hapus (pemicu modal) --}}
                        <button type="button" class="table-btn-red ml-3"
                            onclick="openDeleteModal({{ $ujian->id }}, '{{ str_replace("'", "\'", $ujian->nama_ujian) }}')">
                            Delete <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">
                        Belum ada history ujian untuk mata pelajaran ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="deleteModal" class="delete-modal" style="display: none;">
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

        // Variabel global untuk menyimpan ID yang akan dihapus
        let deleteId = null;

        /**
         * Membuka modal konfirmasi.
         * Dipanggil oleh tombol "Hapus".
         * @param {number} id - ID item yang akan dihapus.
         * @param {string} nama - Nama item (untuk ditampilkan di pesan).
         */
        window.openDeleteModal = function(id, nama) {
            deleteId = id;
            // Set pesan kustom di modal
            document.getElementById("deleteMessage").innerText = `Apakah Anda yakin ingin menghapus data "${nama}"?`;
            // Tampilkan modal
            document.getElementById("deleteModal").style.display = "flex";
        }

        /**
         * Menutup modal konfirmasi.
         * Dipanggil oleh tombol "Batal".
         */
        window.closeDeleteModal = function() {
            document.getElementById("deleteModal").style.display = "none";
        }

        /**
         * Event listener untuk tombol "Ya, Hapus".
         * Ini akan mencari form hapus yang sesuai dan men-submit-nya.
         */
        document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
            if (deleteId) {
                // Cari form dengan ID "deleteForm-..." dan submit
                const deleteForm = document.getElementById(`deleteForm-${deleteId}`);
                if (deleteForm) {
                    deleteForm.submit();
                } else {
                    console.error(`Form dengan ID deleteForm-${deleteId} tidak ditemukan.`);
                    closeDeleteModal();
                }
            }
        });

    });
</script>