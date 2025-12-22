@extends('layouts.app')

@section('title', 'Daftar Siswa ' . $mapel->nama_mapel . ' - ' . $kelas->kelas)


@section('sidebar-menu')
    <a href="{{ route('guru.index') }}" class="menu-item">
        <i class="bi bi-arrow-left"></i> Kembali ke Pilihan
    </a>

    <hr class="sidebar-divider">

    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item ">
        <i class="bi bi-pie-chart-fill"></i> Dasboard
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item active">
        <i class="bi bi-card-checklist"></i> Daftar Nilai Siswa
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Daftar Nilai {{ $mapel->nama_mapel }} ({{ $kelas->kelas }})</a>
</div>

{{-- Kotak pencarian --}}
<div class="filter-container mb-5">
    <div class="search-box-container">
        <i class="bi bi-search search-icon"></i>
        <input type="search" id="searchInput" class="search-input" placeholder="Cari Nama atau NISN...">
    </div>
</div>

<div class="overflow-x-auto">
    <table class="table-container">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Rata-rata Kuis</th>
                <th>Rata-rata UTS</th>
                <th>Rata-rata UAS</th>
                <th>Rata-rata Akhir</th>
            </tr>
        </thead>
        <tbody id="siswaTableBody">
            @forelse ($siswas as $siswa)
            <tr style="cursor: pointer;">

                <td>{{ $siswa->nama_lengkap }}</td>
                <td>{{ $siswa->nisn }}</td>

                {{--
                      Saat ini data nilai belum ada. Kita gunakan placeholder '-'.
                      Nanti, Anda akan mengganti ini dengan query nilai rata-rata.
                    --}}
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">
                    Belum ada data siswa di kelas ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

{{-- Menggunakan script pencarian client-side yang sama --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            const tableBody = document.getElementById("siswaTableBody");
            const rows = tableBody.getElementsByTagName("tr");

            searchInput.addEventListener("keyup", function() {
                const searchTerm = searchInput.value.toLowerCase();

                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    if (row.cells.length > 1) {
                        const infoSiswa = row.cells[0].textContent.toLowerCase();

                        if (infoSiswa.includes(searchTerm)) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    }
                }
            });
        }
    });
</script>