@extends('layouts.app')

@section('title', 'Dasbor Wali Kelas ' . $kelas->kelas)

{{-- 
    Ini adalah "Layout Tipe 2".
    Perhatikan bagaimana @section('sidebar-menu') diisi dengan
    menu-menu yang relevan dengan konteks Wali Kelas.
--}}
@section('sidebar-menu')
    <a href="{{ route('guru.index')}}" class="menu-item">
        <i class="bi bi-arrow-left"></i> Menu Akses 
    </a>
    
    <hr class="sidebar-divider">
    
    <a href="#" class="menu-item active">
        <i class="bi bi-pie-chart-fill"></i> Dashboard
    </a>
    <a href="{{ route('guru.walikelas.siswa', $kelas->id)}}" class="menu-item">
        <i class="bi bi-people-fill"></i> Daftar Siswa
    </a>
@endsection

@push('styles')
<style>
    /* Style untuk Widget Statistik (CardView) */
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background-color: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 20px; }
    .stat-icon { font-size: 36px; padding: 15px; border-radius: 50%; color: #fff; }
    .stat-icon.siswa { background-color: #3B82F6; } /* Biru */
    .stat-icon.nilai { background-color: #10B981; } /* Hijau */
    .stat-icon.absen { background-color: #F59E0B; } /* Oranye */
    .stat-info .stat-number { font-size: 28px; font-weight: 700; color: #1F2937; }
    .stat-info .stat-label { font-size: 14px; color: #6B7280; }
    
    /* Style untuk Chart */
    .chart-container {
        background-color: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-top: 30px;
    }
    .chart-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #1F2937; }
</style>
@endpush

@section('content')

    <h2 class="sapaan" style="font-size: 24px; font-weight: 600; margin-bottom: 5px;">
        Selamat datang, {{ $guru->nama_lengkap }}.
    </h2>
    <h2 class="sapaan" style="font-size: 24px; font-weight: 600; margin-bottom: 5px;">
        Wali Kelas {{ $kelas->kelas }}
    </h2>
    
    {{-- 2. CardView Statistik --}}
    <div class="stats-container mt-5">
        <div class="stat-card">
            <div class="stat-icon siswa"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
                <div class="stat-number">{{ $jumlahSiswa }}</div>
                <div class="stat-label">Jumlah Siswa</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon nilai"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-info">
                <div class="stat-number">85.4</div>
                <div class="stat-label">Rata-rata Nilai Kelas (Contoh)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon absen"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-info">
                <div class="stat-number">98%</div>
                <div class="stat-label">Kehadiran Hari Ini (Contoh)</div>
            </div>
        </div>
    </div>

    {{-- 3. CardView Besar untuk Chart --}}
    <div class="chart-container">
        <div class="chart-title">Ringkasan Kinerja Siswa (Contoh)</div>
        <canvas id="waliKelasChart"></canvas>
    </div>
@endsection

@push('scripts')
{{-- Library Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data chart diambil dari controller
        const labels = @json($chartLabels);
        const data = @json($chartData);

        const ctx = document.getElementById('waliKelasChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut', // Tipe chart: donat
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: data,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.7)', // Hijau
                        'rgba(239, 68, 68, 0.7)'   // Merah
                    ],
                    borderColor: ['#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>
@endpush