@extends('layouts.app')

@section('title', 'Detail Ujian ' . $ujian->nama_ujian)

@section('sidebar-menu')
<a href="{{ route('guru.index') }}" class="menu-item">
    <i class="bi bi-arrow-left"></i> Kembali ke Pilihan
</a>
<hr class="sidebar-divider">
<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item active">
    <i class="bi bi-pie-chart-fill"></i> Dasbor
</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item">
    <i class="bi bi-card-checklist"></i> Daftar Nilai Siswa
</a>
@endsection

@section('content')

{{-- 1. Judul Halaman (CardView) --}}
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul" style="font-size: 24px;">Detail Hasil Ujian</a>
    <p class="sub-judul" style="text-align: center; margin-top: 15px; font-size: 18px;">
        <strong>{{ $ujian->nama_ujian }}</strong>
    </p>
    <p style="text-align: center; color: #4B5563; margin-top: 5px;">
        {{ $mapel->nama_mapel }} - Kelas {{ $kelas->kelas }}
    </p>
</div>

{{-- 2. Tombol Aksi (Kembali) --}}
<div class="filter-container mb-5">
    <button type="button" onclick="window.location='{{ route('guru.mapel.dashboard', $mapel->id) }}'" class="dark-btn">
        <i class="bi bi-arrow-left"></i> Kembali 
    </button>
</div>

{{-- 3. Tabel Nilai Siswa --}}
<div class="table-container-wrapper">
    <h3 class="table-title">Daftar Nilai Siswa</h3>
    <div class="overflow-x-auto">
        <table class="table-container">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Siswa</th>
                    <th>NISN</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hasilUjian as $index => $hasil)
                <tr style="cursor: pointer;" ondblclick="window.location.href='{{ route('guru.mapel.ujian.siswa.detail', ['ujian' => $ujian->id, 'siswa' => $hasil->siswa_id]) }}'">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $hasil->nama_siswa }}</td>
                    <td>{{ $hasil->nisn_siswa }}</td>
                    {{-- Tampilkan nilai dummy --}}
                    <td style="font-weight: 600; color: #1F2937;">{{ $hasil->nilai }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px;">
                        Belum ada siswa di kelas ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection