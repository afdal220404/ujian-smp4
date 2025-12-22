@extends('layouts.app')

@section('title', 'Siswa Kelas ' . $kelas->kelas)

{{-- Mengisi sidebar dengan menu konteks Wali Kelas --}}
@section('sidebar-menu')
    <a href="{{ route('guru.index') }}" class="menu-item">
        <i class="bi bi-arrow-left"></i> Menu Akses 
    </a>
    <hr class="sidebar-divider">
    <a href="{{ route('guru.walikelas.dashboard', $kelas->id) }}" class="menu-item">
        <i class="bi bi-pie-chart-fill"></i> Dasbor
    </a>
    <a href="{{ route('guru.walikelas.siswa', $kelas->id) }}" class="menu-item active">
        <i class="bi bi-people-fill"></i> Daftar Siswa
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Daftar Siswa Kelas {{ $kelas->kelas }}</a>
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
                {{-- 
                  PERUBAHAN UTAMA: 
                  - Menambahkan ondblclick untuk redirect ke route detail.
                  - Menambahkan style cursor: pointer.
                --}}
                <tr style="cursor: pointer;" 
                    ondblclick="window.location='{{ route('guru.walikelas.siswa.detail', $siswa->id) }}'">
                    
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

@push('scripts')
{{-- Script pencarian client-side ini tetap berfungsi --}}
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
                    const namaSiswa = row.cells[0].textContent.toLowerCase();
                    const nisnSiswa = row.cells[1].textContent.toLowerCase();
                    
                    if (namaSiswa.includes(searchTerm) || nisnSiswa.includes(searchTerm)) {
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
@endpush