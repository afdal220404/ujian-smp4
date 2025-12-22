@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">
    <i class="bi bi-card-checklist"></i> Daftar Siswa
</a>

<a href="{{route('daftar_guru2')}}" class="menu-item">
    <i class="bi bi-card-checklist"></i> Daftar Guru
</a>


@endsection



@section('content')
{{-- Kalimat Sapaan --}}
<h2 class="sapaan" style="font-size: 24px; font-weight: 600; margin-bottom: 5px;">Selamat Datang Kembali, Operator!</h2>
<p style="margin-bottom: 30px; color: #4B5563;">Siap untuk mengelola data sekolah hari ini?</p>

{{-- Widget Statistik --}}
<div class="stats-container">
    <div class="stat-card">
        <div class="stat-icon siswa"><i class="bi bi-people-fill"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $jumlahSiswa }}</div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon guru"><i class="bi bi-person-video3"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $jumlahGuru }}</div>
            <div class="stat-label">Total Guru</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon aktif"><i class="bi bi-person-check-fill"></i></div>
        <div class="stat-info">
            <div class="stat-number">{{ $jumlahPenggunaAktif }}</div>
            <div class="stat-label">Pengguna Aktif</div>
        </div>
    </div>
</div>

{{-- Menu Navigasi Baru (Model Card) --}}
<div class="menu-grid">
    <div class="menu-card">
        <a href="{{ route('tambah_siswa') }}">
            <div class="menu-icon"><i class="bi bi-person-plus-fill"></i></div>
            <div class="menu-title">Tambah Siswa Baru</div>
        </a>
    </div>
    <div class="menu-card">
        <a href="{{ route('guru.create') }}">
            <div class="menu-icon"><i class="bi bi-person-video"></i></div>
            <div class="menu-title">Tambah Guru Baru</div>
        </a>
    </div>
    <div class="menu-card">
        <a href="{{ route('walikelas.index') }}">
            <div class="menu-icon"><i class="bi bi-award-fill"></i></div>
            <div class="menu-title">Set Wali Kelas</div>
        </a>
    </div>
    <div class="menu-card">
        <a href="{{ route('mapel') }}">
            <div class="menu-icon"><i class="bi bi-book-half"></i></div>
            <div class="menu-title">Mata Pelajaran</div>
        </a>
    </div>
</div>

{{-- Bar Chart Perbandingan Siswa --}}
<div class="chart-container">
    <div class="chart-title">Distribusi Siswa per Kelas</div>
    <canvas id="siswaPerKelasChart"></canvas>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('siswaPerKelasChart').getContext('2d');

        // Data dari controller
        const labels = @json($chartLabels);
        const data = @json($chartData);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: data,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.6)', // Biru
                        'rgba(16, 185, 129, 0.6)', // Hijau
                        'rgba(245, 158, 11, 0.6)' // Oranye
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false // Menyembunyikan label 'Jumlah Siswa' di atas
                    }
                }
            }
        });
    });
</script>