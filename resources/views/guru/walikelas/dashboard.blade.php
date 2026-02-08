@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas ' . $kelas->kelas)

@section('sidebar-menu')
<div class="mb-4 px-3">
    <a href="{{ route('guru.index') }}"
        class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
        <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
    </a>
</div>
<hr class="my-3 border-gray-100 opacity-20">
<a href="{{ route('guru.walikelas.dashboard', $kelas->id)}}" class="nav-link active">
    <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard Kelas</span>
</a>
<a href="{{ route('guru.walikelas.siswa', $kelas->id)}}" class="nav-link">
    <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
</a>
<a href="{{ route('guru.walikelas.rekap_nilai', $kelas->id)}}" class="nav-link">
    <i class="bi bi-table"></i> <span>Leger Nilai</span>
</a>
@endsection

@section('content')

{{-- HEADER --}}
<div class="mb-8">
    <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Dashboard Wali Kelas</h1>
    <p class="text-gray-500 mt-1">
        Laporan perkembangan akademik kelas <span class="font-bold text-darkblue">{{ $kelas->kelas }}</span>.
    </p>
</div>

{{-- 1. KARTU STATISTIK BARU (5 GRID) --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

    {{-- Card: Total Siswa --}}
    <div class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-all">
        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-2">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Total Siswa</div>
        <div class="text-2xl font-[Poppins-Bold] text-darkblue mt-1">{{ $totalSiswa }}</div>
    </div>

    {{-- Card: Rata-rata Kelas --}}
    <div class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-all">
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-2">
            <i class="bi bi-bar-chart-fill"></i>
        </div>
        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Rata-rata Total</div>
        <div class="text-2xl font-[Poppins-Bold] text-darkblue mt-1">{{ number_format($rataRataKelas, 1) }}</div>
    </div>

    {{-- Card: Rata Kuis --}}
    <div class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border-l-4 border-l-blue-400 border-gray-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-all">
        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Rata-rata Kuis</div>
        <div class="text-2xl font-[Poppins-Bold] text-blue-600 mt-1">{{ number_format($rataKuis, 1) }}</div>
    </div>

    {{-- Card: Rata UTS --}}
    <div class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border-l-4 border-l-yellow-400 border-gray-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-all">
        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Rata-rata UTS</div>
        <div class="text-2xl font-[Poppins-Bold] text-yellow-600 mt-1">{{ number_format($rataUTS, 1) }}</div>
    </div>

    {{-- Card: Rata UAS --}}
    <div class="bg-white p-5 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border-l-4 border-l-red-400 border-gray-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-all">
        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Rata-rata UAS</div>
        <div class="text-2xl font-[Poppins-Bold] text-red-600 mt-1">{{ number_format($rataUAS, 1) }}</div>
    </div>
</div>

{{-- 2. AREA GRAFIK --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Chart Performa Mapel (Lebar) --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100">
        <h3 class="font-[Poppins-Bold] text-darkblue mb-6">Analisis Mata Pelajaran</h3>
        <div class="relative h-64 w-full">
            <canvas id="mapelChart"></canvas>
        </div>
    </div>

    {{-- Chart Sebaran (Kecil) --}}
    <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100">
        <h3 class="font-[Poppins-Bold] text-darkblue mb-4">Sebaran Nilai Akhir</h3>
        <div class="relative h-40 w-full flex justify-center mb-4">
            <canvas id="sebaranChart"></canvas>
        </div>
        {{-- Legend Manual --}}
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="flex items-center gap-1 text-gray-500"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>Sangat Baik</div>
            <div class="flex items-center gap-1 text-gray-500"><span class="w-2 h-2 rounded-full bg-blue-400"></span>Baik</div>
            <div class="flex items-center gap-1 text-gray-500"><span class="w-2 h-2 rounded-full bg-yellow-400"></span>Cukup</div>
            <div class="flex items-center gap-1 text-gray-500"><span class="w-2 h-2 rounded-full bg-red-400"></span>Kurang</div>
        </div>
    </div>
</div>

{{-- 3. DUA TABEL: PERLU BIMBINGAN (Kiri) & TOP SISWA (Kanan) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- TABEL SISWA PERLU BIMBINGAN (BARU) --}}
    <div class="bg-white border border-red-100 rounded-2xl shadow-[0_4px_20px_rgba(254,202,202,0.15)] overflow-hidden">
        <div class="px-6 py-4 border-b border-red-50 bg-red-50/30 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                <h3 class="font-[Poppins-Bold] text-red-700 text-sm uppercase tracking-wide">Perlu Bimbingan (< 70)</h3>
            </div>
            <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-xs font-bold">{{ $siswaBermasalah->count() }} Siswa</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-white text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Nama Siswa</th>
                        <th class="px-6 py-3 text-center">Rata-Rata</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($siswaBermasalah as $s)
                    <tr class="hover:bg-red-50/20 transition-colors">
                        <td class="px-6 py-3">
                            <div class="font-bold text-gray-700 text-sm">{{ $s->nama_lengkap }}</div>
                            <div class="text-[10px] text-gray-400">{{ $s->nisn }}</div>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="px-2 py-1 rounded bg-red-100 text-red-600 font-bold text-xs">
                                {{ number_format($s->hasilUjians->avg('nilai'), 1) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('guru.walikelas.siswa.detail', $s->id) }}" class="text-xs font-bold text-primary hover:underline">Cek Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-emerald-500">
                                <i class="bi bi-check-circle-fill text-2xl mb-2"></i>
                                <span class="text-sm font-bold">Aman! Tidak ada siswa di bawah KKM.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL TOP SISWA --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="bi bi-trophy-fill text-yellow-500"></i>
                <h3 class="font-[Poppins-Bold] text-darkblue text-sm uppercase tracking-wide">Top 5 Berprestasi</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-white text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 w-10">#</th>
                        <th class="px-6 py-3">Nama Siswa</th>
                        <th class="px-6 py-3 text-center">Rata-Rata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($topSiswas as $s)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="px-6 py-3 font-bold text-gray-400 text-xs">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-bold text-darkblue text-sm">{{ $s->nama_lengkap }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="font-bold text-green-600 text-xs">
                                {{ number_format($s->hasilUjians->avg('nilai'), 1) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400 italic text-xs">Belum ada data nilai.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapelRaw = @json($dataMapel);
        const sebaranRaw = @json(array_values($sebaran));

        // Chart Mapel
        const mapelCanvas = document.getElementById('mapelChart');
        if (mapelCanvas) {
            new Chart(mapelCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: mapelRaw.map(d => d.label),
                    datasets: [{
                            label: 'Kuis',
                            data: mapelRaw.map(d => d.kuis),
                            backgroundColor: '#3B82F6',
                            borderRadius: 3
                        },
                        {
                            label: 'UTS',
                            data: mapelRaw.map(d => d.uts),
                            backgroundColor: '#F59E0B',
                            borderRadius: 3
                        },
                        {
                            label: 'UAS',
                            data: mapelRaw.map(d => d.uas),
                            backgroundColor: '#EF4444',
                            borderRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 8,
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Chart Sebaran
        const sebaranCanvas = document.getElementById('sebaranChart');
        if (sebaranCanvas) {
            new Chart(sebaranCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],
                    datasets: [{
                        data: sebaranRaw,
                        backgroundColor: ['#34d399', '#60a5fa', '#facc15', '#f87171'],
                        borderWidth: 0,
                        cutout: '70%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
@endsection