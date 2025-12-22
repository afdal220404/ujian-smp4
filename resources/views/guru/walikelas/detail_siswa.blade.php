@extends('layouts.app')

@section('title', 'Detail Nilai - ' . $siswa->nama_lengkap)

{{-- Sidebar tetap dalam konteks Wali Kelas --}}
@section('sidebar-menu')
    <a href="{{ route('guru.index') }}" class="menu-item">
        <i class="bi bi-arrow-left"></i> Menu Akses 
    </a>
    <hr class="sidebar-divider">
    
    <a href="{{ route('guru.walikelas.dashboard', $kelas->id) }}" class="menu-item">
        <i class="bi bi-pie-chart-fill"></i> Dasbor
    </a>
    {{-- Tandai 'Daftar Siswa' sebagai aktif karena ini adalah sub-halamannya --}}
    <a href="{{ route('guru.walikelas.siswa', $kelas->id) }}" class="menu-item active">
        <i class="bi bi-people-fill"></i> Daftar Siswa
    </a>
@endsection

@section('content')

<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5 header-card-container">
    
    {{-- Tombol Kembali (akan diposisikan secara 'absolute') --}}
    <a href="{{ route('guru.walikelas.siswa', $kelas->id) }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    
    {{-- Judul Halaman (akan 'center' dengan sendirinya) --}}
    <a class="judul">Detail Nilai Mata Pelajaran</a>
    
</div>

<div class="siswa-detail-card">
    {{-- Info Kiri: Nama dan NISN --}}
    <div class="info-kiri">
        <h2 class="nama-siswa">{{ $siswa->nama_lengkap }}</h2>
        <span class="nisn-siswa">{{ $siswa->nisn }}</span>
    </div>
    
    {{-- Info Kanan: Kelas --}}
    <div class="info-kanan">
        <div class="label">Kelas</div>
        <div class="kelas-info">{{ $siswa->kelas->kelas }}</div>
    </div>
</div>



<div class="overflow-x-auto">
    <table class="table-container">
        <thead>
            <tr>
                <th>Mata Pelajaran</th>
                <th>Rata-rata Kuis</th>
                <th>Nilai UTS</th>
                <th>Nilai UAS</th>
                <th>Rata-rata Mapel</th>
            </tr>
        </thead>
        <tbody id="detailNilaiTableBody">
            @forelse ($mapels as $mapel)
                <tr>
                    <td>{{ $mapel->nama_mapel }}</td>
                    
                    {{-- 
                      Saat ini data nilai belum ada. Kita gunakan placeholder '-'.
                      Nanti, Anda akan mengganti ini dengan query nilai spesifik
                      untuk $siswa->id dan $mapel->id
                    --}}
                    <td>-</td> {{-- Placeholder untuk Kuis --}}
                    <td>-</td> {{-- Placeholder untuk UTS --}}
                    <td>-</td> {{-- Placeholder untuk UAS --}}
                    <td>-</td> {{-- Placeholder untuk Rata-rata Mapel --}}
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        Belum ada data mata pelajaran untuk kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection