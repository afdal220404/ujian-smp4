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
    <div class="flex items-center gap-2 text-gray-400 text-xs mb-2">
        <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
        <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
        <span class="text-gray-500">Wali Kelas</span>
        <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
        <span class="text-blue-600 font-bold">Dashboard Kelas</span>
    </div>
    <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Dashboard Wali Kelas</h1>
    <p class="text-gray-500 mt-1">
        Laporan perkembangan akademik kelas <span class="font-bold text-darkblue">{{ $kelas->kelas }}</span>.
    </p>
</div>

{{-- 1. KARTU STATISTIK --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

    {{-- Card: Total Siswa --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-[#00415a] to-[#006d96] p-5 rounded-2xl shadow-lg flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 group">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
        <div class="flex justify-between items-start mb-4">
            <div class="w-11 h-11 rounded-xl bg-white/15 text-white flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-people-fill"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-widest text-white/60 bg-white/10 px-2 py-0.5 rounded-full">Kelas</span>
        </div>
        <div>
            <div class="text-3xl font-[Poppins-Bold] text-white leading-none">{{ $totalSiswa }}</div>
            <div class="text-white/70 text-[10px] font-bold uppercase tracking-wider mt-1.5">Total Siswa</div>
        </div>
    </div>

    {{-- Card: Rata-rata Kelas --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 p-5 rounded-2xl shadow-lg flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 group">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
        <div class="flex justify-between items-start mb-4">
            <div class="w-11 h-11 rounded-xl bg-white/15 text-white flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-award-fill"></i>
            </div>
        </div>
        <div>
            <div class="text-3xl font-[Poppins-Bold] text-white leading-none">{{ number_format($rataRataKelas, 1) }}</div>
            <div class="text-white/70 text-[10px] font-bold uppercase tracking-wider mt-1.5">Rata-rata Kelas</div>
        </div>
    </div>

    {{-- Card: Rata Kuis --}}
    <div class="relative overflow-hidden bg-white border border-blue-100 p-5 rounded-2xl shadow-sm flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 group">
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-blue-600 rounded-b-2xl"></div>
        <div class="flex justify-between items-start mb-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                <i class="bi bi-pencil-fill"></i>
            </div>
        </div>
        <div>
            <div class="text-3xl font-[Poppins-Bold] text-blue-600 leading-none">{{ number_format($rataKuis, 1) }}</div>
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mt-1.5">Rata-rata Kuis</div>
        </div>
    </div>

    {{-- Card: Rata UTS --}}
    <div class="relative overflow-hidden bg-white border border-yellow-100 p-5 rounded-2xl shadow-sm flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 group">
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-yellow-400 to-amber-500 rounded-b-2xl"></div>
        <div class="flex justify-between items-start mb-4">
            <div class="w-11 h-11 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl shadow-sm">
                <i class="bi bi-journal-text"></i>
            </div>
        </div>
        <div>
            <div class="text-3xl font-[Poppins-Bold] text-yellow-600 leading-none">{{ number_format($rataUTS, 1) }}</div>
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mt-1.5">Rata-rata UTS</div>
        </div>
    </div>

    {{-- Card: Rata UAS --}}
    <div class="relative overflow-hidden bg-white border border-red-100 p-5 rounded-2xl shadow-sm flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 group">
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-red-400 to-rose-500 rounded-b-2xl"></div>
        <div class="flex justify-between items-start mb-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xl shadow-sm">
                <i class="bi bi-file-earmark-check-fill"></i>
            </div>
        </div>
        <div>
            <div class="text-3xl font-[Poppins-Bold] text-red-500 leading-none">{{ number_format($rataUAS, 1) }}</div>
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mt-1.5">Rata-rata UAS</div>
        </div>
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
    <div class="bg-white p-6 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-[Poppins-Bold] text-darkblue">Sebaran Nilai Akhir</h3>
            <a href="{{ route('guru.walikelas.siswa', $kelas->id) }}" class="text-[10px] font-bold px-3 py-1 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-colors flex items-center gap-1 shadow-sm">
                Detail <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div id="chart-sebaran" class="w-full h-[320px] flex justify-center flex-1"></div>
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
                        <th class="px-6 py-3">Mata Pelajaran</th>
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
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($s->mapel_bermasalah as $mp)
                                <span class="px-2 py-1 rounded bg-red-50 border border-red-100 text-red-600 text-[10px] font-medium flex items-center gap-1">
                                    {{ $mp->nama_mapel }} <span class="font-bold bg-white px-1 rounded-sm shadow-sm">{{ $mp->nilai }}</span>
                                </span>
                                @endforeach
                            </div>
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
                        <th class="px-6 py-3 text-center">Nilai Akhir</th>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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

        // Chart Sebaran (ApexCharts)
        var elSebaran = document.querySelector("#chart-sebaran");
        if (elSebaran) {
            var dataSebaran = sebaranRaw;
            var isDataEmpty = !dataSebaran || dataSebaran.reduce((a, b) => a + b, 0) === 0;
            
            var seriesData = isDataEmpty ? [1] : dataSebaran;
            var colorsSebaran = isDataEmpty ? ['#e5e7eb'] : ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'];
            var labelsSebaran = isDataEmpty ? ['Data Kosong'] : ['Sangat Baik (85-100)', 'Baik (75-84)', 'Cukup (70-74)', 'Kurang (<70)'];

            var optionsSebaran = {
                series: seriesData,
                labels: labelsSebaran,
                chart: { type: 'donut', height: 320, fontFamily: 'Poppins-Regular, sans-serif' },
                colors: colorsSebaran,
                dataLabels: { enabled: false },
                legend: { 
                    position: 'bottom', 
                    fontSize: '12px', 
                    offsetY: 0, 
                    itemMargin: { horizontal: 10, vertical: 5 }
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        if (isDataEmpty) return '<div class="px-2 py-1 text-xs">Data Kosong</div>';
                        let val = series[seriesIndex];
                        let total = w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0);
                        let percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                        let label = w.globals.labels[seriesIndex];
                        let color = w.globals.colors[seriesIndex];
                        return '<div class="px-3 py-2 shadow-lg bg-white rounded-lg border border-gray-100 flex items-center gap-2">' +
                               '<span class="w-2.5 h-2.5 rounded-full" style="background-color: ' + color + '"></span>' +
                               '<span class="text-xs font-bold text-gray-700">' + label.split(' (')[0] + ':</span>' +
                               '<span class="text-xs text-gray-600">' + percent + '%</span>' +
                               '</div>';
                    }
                },
                plotOptions: { 
                    pie: { 
                        donut: { 
                            size: '65%', 
                            labels: { 
                                show: true, 
                                name: {
                                    show: true,
                                    formatter: function (val) {
                                        return val ? val.split(' (')[0] : '';
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    formatter: function (val) {
                                        return val + ' Siswa';
                                    }
                                },
                                total: { 
                                    show: true, 
                                    label: 'Total Siswa', 
                                    fontSize: '12px', 
                                    fontWeight: 700,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0) + ' Siswa';
                                    }
                                } 
                            } 
                        } 
                    } 
                }
            };
            new ApexCharts(elSebaran, optionsSebaran).render();
        }
    });
</script>
@endsection