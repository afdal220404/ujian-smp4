@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')

{{-- Tambahkan CDN ApexCharts --}}
@section('header')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Menu Utama</div>
    <a href="{{ route('kepsek.index') }}" class="nav-link">
        <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('kepsek.guru') }}" class="nav-link">
        <i class="bi bi-person-workspace"></i> <span>Data Guru</span>
    </a>
    <a href="{{ route('kepsek.siswa') }}" class="nav-link">
        <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('kepsek.alumni.index') }}" class="nav-link active">
        <i class="bi bi-mortarboard-fill"></i> <span>Data Alumni</span>
    </a>
    <a href="{{ route('kepsek.nilai') }}" class="nav-link">
        <i class="bi bi-bar-chart-line-fill"></i> <span>Laporan Nilai</span>
    </a>
@endsection 

@section('content')

    {{-- 1. HEADER --}}
    <div class="mb-8">
        <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Selamat Datang, Bapak/Ibu Kepala Sekolah</h1>
        <p class="text-gray-500">Ringkasan data akademik & visualisasi performa kelas.</p>
    </div>

    {{-- 2. STATISTIK UTAMA (GLOBAL) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {{-- Card Guru --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 rounded-xl bg-blue-50 text-primary flex items-center justify-center text-2xl"><i class="bi bi-person-workspace"></i></div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Total Guru</div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $totalGuru ?? 0 }}</div>
            </div>
        </div>
        {{-- Card Siswa --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl"><i class="bi bi-people"></i></div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Total Siswa</div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $totalSiswa ?? 0 }}</div>
            </div>
        </div>
        {{-- Card Kelas --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-2xl"><i class="bi bi-building"></i></div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Total Kelas</div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $totalKelas ?? 0 }}</div>
            </div>
        </div>
        {{-- Card Ujian --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-14 h-14 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Total Ujian</div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $totalUjian ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- 3. ANALISIS PER KELAS DENGAN FILTER --}}
    <div class="space-y-8 mb-10">
        {{-- Card Judul & Filter --}}
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="w-2 h-12 bg-gradient-to-b from-[#00415a] to-blue-500 rounded-full"></div>
                <div>
                    <h2 class="text-3xl md:text-4xl font-black text-[#00415a] uppercase tracking-tight">Analisis Data Per Tingkat </h2>
                    <p class="text-gray-500 text-sm mt-1">Pilih kelas di bawah ini untuk melihat detail visualisasi nilai siswa.</p>
                </div>
            </div>
            
            {{-- Dropdown Filter --}}
            <div class="relative w-full md:w-56">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="bi bi-funnel-fill text-[#00415a]"></i>
                </div>
                <select id="classFilter" class="w-full pl-10 pr-10 py-2.5 bg-blue-50/50 border border-blue-100/50 rounded-xl text-sm font-bold text-[#00415a] focus:ring-4 focus:ring-[#00415a]/10 focus:border-[#00415a] shadow-sm appearance-none cursor-pointer transition-all hover:bg-blue-50">
                    @if(isset($dataKelas) && count($dataKelas) > 0)
                        @foreach($dataKelas as $kelasData)
                            <option value="{{ $kelasData->nama_kelas }}">Kelas {{ $kelasData->nama_kelas }}</option>
                        @endforeach
                    @else
                        <option value="">Tidak ada kelas</option>
                    @endif
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <i class="bi bi-chevron-down text-[#00415a] font-bold"></i>
                </div>
            </div>
        </div>

        @if(isset($dataKelas) && count($dataKelas) > 0)
            @foreach($dataKelas as $kelas)
            
            {{-- CONTAINER KELAS WRAPPER --}}
            <div class="class-card bg-blue-50 rounded-3xl p-6 border border-gray-200 shadow-[0_4px_20px_rgba(0,0,0,0.03)] relative overflow-hidden transition-all duration-300" data-kelas="{{ $kelas->nama_kelas }}">
                
                {{-- Label Kelas --}}
                <div class="absolute top-0 left-0 bg-[#00415a] text-white px-6 py-2 rounded-br-2xl font-[Poppins-Bold] text-sm shadow-md z-10">
                    Kelas {{ $kelas->nama_kelas }}
                </div>

                {{-- A. KARTU NILAI (BARIS 1) --}}
                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 pb-8 border-b border-gray-100">
                    
                    {{-- Kuis --}}
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-md transition-shadow flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Rata Kuis</span>
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600"><i class="bi bi-pencil-square text-lg"></i></div>
                        </div>
                        <div class="text-3xl font-[Poppins-Bold] text-gray-800">{{ $kelas->kuis }}</div>
                    </div>

                    {{-- UTS --}}
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-md transition-shadow flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Rata UTS</span>
                            <div class="p-2 bg-orange-50 rounded-lg text-orange-600"><i class="bi bi-file-text text-lg"></i></div>
                        </div>
                        <div class="text-3xl font-[Poppins-Bold] text-gray-800">{{ $kelas->uts }}</div>
                    </div>

                    {{-- UAS --}}
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-md transition-shadow flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Rata UAS</span>
                            <div class="p-2 bg-red-50 rounded-lg text-red-600"><i class="bi bi-award text-lg"></i></div>
                        </div>
                        <div class="text-3xl font-[Poppins-Bold] text-gray-800">{{ $kelas->uas }}</div>
                    </div>

                    {{-- Nilai Akhir --}}
                    <div class="relative p-5 rounded-2xl shadow-lg hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between h-32 overflow-hidden"
                         style="background: linear-gradient(135deg, #00415a 0%, #447d9b 100%);">
                        <div class="absolute right-0 top-0 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                        <div class="flex justify-between items-start relative z-10">
                            <span class="text-[10px] font-bold uppercase text-blue-100 bg-white/10 px-2 py-0.5 rounded backdrop-blur-sm">Rata Nilai akhir</span>
                            <div class="p-2 bg-white/20 rounded-lg text-white backdrop-blur-sm"><i class="bi bi-graph-up-arrow text-lg"></i></div>
                        </div>
                        <div class="text-4xl font-[Poppins-Bold] text-white relative z-10">{{ $kelas->akhir }}</div>
                    </div>
                </div>

                {{-- B. BAGIAN GRAFIK VISUALISASI --}}
                
                {{-- BARIS 2: Top 5 Siswa & Sebaran Grade (Side by Side) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    
                    {{-- Grafik Siswa --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <i class="bi bi-trophy-fill text-yellow-500"></i> Top 5 Siswa Terbaik
                        </h4>
                        <div id="chart-siswa-{{ $kelas->id }}" class="w-full h-[250px]"></div>
                    </div>

                    {{-- Grafik Sebaran --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                            <i class="bi bi-pie-chart-fill text-purple-500"></i> Sebaran Nilai Siswa
                        </h4>
                        <div id="chart-sebaran-{{ $kelas->id }}" class="w-full h-[250px] flex justify-center"></div>
                    </div>
                </div>

                {{-- BARIS 3: Grafik Mapel (Full Width) --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-primary"></i> Rata-Rata Nilai per Mata Pelajaran
                    </h4>
                    {{-- Container Full Width --}}
                    <div id="chart-mapel-{{ $kelas->id }}" class="w-full h-[350px]"></div>
                </div>

            </div>
            @endforeach
        @else
            <div class="bg-white p-8 rounded-2xl text-center border border-gray-200 border-dashed">
                <h3 class="text-gray-800 font-bold">Belum ada data</h3>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            @if(isset($dataKelas) && count($dataKelas) > 0)
                @foreach($dataKelas as $kelas)
                    
                    // --- 1. CHART SISWA (Horizontal Bar) ---
                    var labelsSiswa = @json($kelas->siswa_labels);
                    var valuesSiswa = @json($kelas->siswa_values);
                    if (!labelsSiswa || labelsSiswa.length === 0) { labelsSiswa = ['Data Kosong']; valuesSiswa = [0]; }

                    var optionsSiswa = {
                        series: [{ name: 'Nilai Rata-rata', data: valuesSiswa }],
                        chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'Poppins-Regular, sans-serif' },
                        colors: ['#10B981'], 
                        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
                        dataLabels: { enabled: true, offsetX: -6, style: { fontSize: '10px', colors: ['#fff'] } },
                        xaxis: { categories: labelsSiswa, max: 100, labels: { show: false } },
                        grid: { show: false },
                        tooltip: { theme: 'light' }
                    };
                    var elSiswa = document.querySelector("#chart-siswa-{{ $kelas->id }}");
                    if(elSiswa) new ApexCharts(elSiswa, optionsSiswa).render();


                    // --- 2. CHART SEBARAN (Donut) ---
                    var dataSebaran = @json($kelas->sebaran_data);
                    if (!dataSebaran || dataSebaran.reduce((a, b) => a + b, 0) === 0) {
                         dataSebaran = [1]; var colorsSebaran = ['#e5e7eb']; var labelsSebaran = ['Data Kosong'];
                    } else {
                         var colorsSebaran = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'];
                         var labelsSebaran = ['Sangat Baik (85-100)', 'Baik (75-84)', 'Cukup (70-74)', 'Kurang (<70)'];
                    }

                    var optionsSebaran = {
                        series: dataSebaran,
                        labels: labelsSebaran,
                        chart: { type: 'donut', height: 250, fontFamily: 'Poppins-Regular, sans-serif' },
                        colors: colorsSebaran,
                        dataLabels: { enabled: false },
                        legend: { position: 'right', fontSize: '12px', offsetY: 0, itemMargin: { vertical: 5 } },
                        tooltip: {
                            enabled: true,
                            custom: function({series, seriesIndex, dataPointIndex, w}) {
                                if (!dataSebaran || dataSebaran.reduce((a, b) => a + b, 0) === 0) return '<div class="px-2 py-1 text-xs">Data Kosong</div>';
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
                    var elSebaran = document.querySelector("#chart-sebaran-{{ $kelas->id }}");
                    if(elSebaran) new ApexCharts(elSebaran, optionsSebaran).render();


                    // --- 3. CHART MAPEL (GROUPED BAR - FULL WIDTH) ---
                    var labelsMapel = @json($kelas->chart_labels);
                    var dataKuis = @json($kelas->data_kuis);
                    var dataUTS = @json($kelas->data_uts);
                    var dataUAS = @json($kelas->data_uas);

                    if (!labelsMapel || labelsMapel.length === 0) {
                        labelsMapel = ['Matematika', 'B. Inggris', 'IPA']; dataKuis = [0, 0, 0]; dataUTS = [0, 0, 0]; dataUAS = [0, 0, 0];
                    }

                    var optionsMapel = {
                        series: [
                            { name: 'Rata Kuis', data: dataKuis },
                            { name: 'Rata UTS', data: dataUTS },
                            { name: 'Rata UAS', data: dataUAS }
                        ],
                        chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'Poppins-Regular, sans-serif' },
                        colors: ['#3B82F6', '#F59E0B', '#EF4444'],
                        plotOptions: { bar: { horizontal: false, columnWidth: '60%', borderRadius: 3 } },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 2, colors: ['transparent'] },
                        xaxis: { 
                            categories: labelsMapel, 
                            labels: { 
                                style: { fontSize: '12px', colors: '#6B7280' },
                                rotate: -45, // Rotasi jika label terlalu panjang
                                trim: false, // Jangan potong text
                                hideOverlappingLabels: false // Tampilkan semua label
                            } 
                        },
                        yaxis: { title: { text: 'Nilai' }, max: 100 },
                        fill: { opacity: 1 },
                        legend: { position: 'top', horizontalAlign: 'right' },
                        tooltip: { y: { formatter: function (val) { return val } } }
                    };
                    var elMapel = document.querySelector("#chart-mapel-{{ $kelas->id }}");
                    if(elMapel) new ApexCharts(elMapel, optionsMapel).render();

                @endforeach
            @endif
            
            // ----------------------------------------------------
            // FILTER KELAS LOGIC
            // ----------------------------------------------------
            var classFilter = document.getElementById('classFilter');
            if(classFilter) {
                // Function to apply filter
                function applyClassFilter() {
                    var selectedClass = classFilter.value;
                    var classCards = document.querySelectorAll('.class-card');
                    
                    classCards.forEach(function(card) {
                        if(card.getAttribute('data-kelas') === selectedClass) {
                            card.style.display = 'block';
                            // Trigger reflow to restart animation
                            void card.offsetWidth;
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        } else {
                            card.style.display = 'none';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(10px)';
                        }
                    });
                }

                // Listen for changes
                classFilter.addEventListener('change', applyClassFilter);
                
                // Apply immediately on load
                applyClassFilter();
            }
        });
    </script>
@endsection
