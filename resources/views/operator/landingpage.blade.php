@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('sidebar-menu')
    <a href="{{ route('operator.landingpage') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('operator.daftar_siswa') }}" class="nav-link">
        <i class="bi bi-people"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('operator.alumni.index') }}" class="nav-link active">
        <i class="bi bi-mortarboard-fill"></i> <span>Data Alumni</span>
    </a>
    <a href="{{ route('daftar_guru2') }}" class="nav-link">
        <i class="bi bi-person-video3"></i> <span>Data Staff</span>
    </a>
    <a href="{{ route('walikelas.index') }}" class="nav-link">
        <i class="bi bi-award"></i> <span>Set Wali Kelas</span>
    </a>
    <a href="{{ route('mapel') }}" class="nav-link">
        <i class="bi bi-book"></i> <span>Mata Pelajaran</span>
    </a>
@endsection

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
                <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Utama</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-darkblue font-bold">Dashboard</span>
            </div>
        </div>
    </div>
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Dashboard Operator</h1>
            <p class="text-gray-500 text-sm mt-1">
                Overview data sekolah dan aktivitas terbaru sistem.
            </p>
        </div>
        <div class="text-right hidden md:block">
            <div class="text-sm font-bold text-darkblue">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
    </div>

    {{-- 1. STATISTIK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card Siswa --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $jumlahSiswa }}</div>
                <div class="text-xs text-gray-400 font-bold uppercase">Total Siswa</div>
            </div>
        </div>

        {{-- Card Guru --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl">
                <i class="bi bi-person-video3"></i>
            </div>
            <div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $jumlahGuru }}</div>
                <div class="text-xs text-gray-400 font-bold uppercase">Total Staff</div>
            </div>
        </div>

        {{-- Card Kelas --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-2xl">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $jumlahKelas }}</div>
                <div class="text-xs text-gray-400 font-bold uppercase">Total Kelas</div>
            </div>
        </div>

        {{-- Card User Aktif --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:-translate-y-1 transition-transform">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                <i class="bi bi-broadcast"></i>
            </div>
            <div>
                <div class="text-2xl font-[Poppins-Bold] text-darkblue">{{ $jumlahPenggunaAktif }}</div>
                <div class="text-xs text-gray-400 font-bold uppercase">User Online</div>
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS (Desain Baru: Tinted Buttons) --}}
    <div class="mb-10">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-6 bg-darkblue rounded-full"></div>
            <h3 class="font-[Poppins-Bold] text-darkblue text-lg">Akses Cepat</h3>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            {{-- Tombol Tambah Siswa (Biru) --}}
            <a href="{{ route('tambah_siswa') }}" class="group relative overflow-hidden bg-blue-50 hover:bg-blue-600 border border-blue-100 rounded-2xl p-4 transition-all duration-300 flex items-center justify-between">
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center shadow-sm group-hover:bg-white/20 group-hover:text-white transition-colors">
                        <i class="bi bi-person-plus-fill text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-blue-900 group-hover:text-white">Tambah Siswa</span>
                </div>
                <i class="bi bi-arrow-right text-blue-300 group-hover:text-white group-hover:translate-x-1 transition-all relative z-10"></i>
            </a>

            {{-- Tombol Tambah Guru (Ungu) --}}
            <a href="{{ route('guru.create') }}" class="group relative overflow-hidden bg-purple-50 hover:bg-purple-600 border border-purple-100 rounded-2xl p-4 transition-all duration-300 flex items-center justify-between">
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-white text-purple-600 flex items-center justify-center shadow-sm group-hover:bg-white/20 group-hover:text-white transition-colors">
                        <i class="bi bi-person-video text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-purple-900 group-hover:text-white">Tambah Staff</span>
                </div>
                <i class="bi bi-arrow-right text-purple-300 group-hover:text-white group-hover:translate-x-1 transition-all relative z-10"></i>
            </a>

            {{-- Tombol Set Wali Kelas (Orange) --}}
            <a href="{{ route('walikelas.index') }}" class="group relative overflow-hidden bg-orange-50 hover:bg-orange-500 border border-orange-100 rounded-2xl p-4 transition-all duration-300 flex items-center justify-between">
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-white text-orange-600 flex items-center justify-center shadow-sm group-hover:bg-white/20 group-hover:text-white transition-colors">
                        <i class="bi bi-award-fill text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-orange-900 group-hover:text-white">Wali Kelas</span>
                </div>
                <i class="bi bi-arrow-right text-orange-300 group-hover:text-white group-hover:translate-x-1 transition-all relative z-10"></i>
            </a>

            {{-- Tombol Mapel (Hijau) --}}
            <a href="{{ route('mapel') }}" class="group relative overflow-hidden bg-green-50 hover:bg-green-600 border border-green-100 rounded-2xl p-4 transition-all duration-300 flex items-center justify-between">
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-white text-green-600 flex items-center justify-center shadow-sm group-hover:bg-white/20 group-hover:text-white transition-colors">
                        <i class="bi bi-book-half text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-green-900 group-hover:text-white">Mata Pelajaran</span>
                </div>
                <i class="bi bi-arrow-right text-green-300 group-hover:text-white group-hover:translate-x-1 transition-all relative z-10"></i>
            </a>

        </div>
    </div>

    {{-- 3. PERINGATAN DATA (Hanya muncul jika ada masalah) --}}
    @if($siswaTanpaNISN > 0 || $guruTanpaNIP > 0 || $kelasTanpaWali > 0)
    <div class="mt-8">
        <h3 class="font-[Poppins-Bold] text-darkblue text-lg mb-4">Perlu Perhatian (Validasi Data)</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            @if($siswaTanpaNISN > 0)
            <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
                    <div>
                        <div class="text-red-700 font-bold text-sm">Siswa Tanpa NISN</div>
                        <div class="text-red-500 text-xs">Lengkapi data segera</div>
                    </div>
                </div>
                <span class="bg-white text-red-600 px-3 py-1 rounded-lg font-bold shadow-sm text-sm">{{ $siswaTanpaNISN }}</span>
            </div>
            @endif

            @if($guruTanpaNIP > 0)
            <div class="bg-yellow-50 border border-yellow-100 p-4 rounded-xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-yellow-500 text-xl"></i>
                    <div>
                        <div class="text-yellow-700 font-bold text-sm">Guru Tanpa NIP</div>
                        <div class="text-yellow-600 text-xs">Cek data kepegawaian</div>
                    </div>
                </div>
                <span class="bg-white text-yellow-600 px-3 py-1 rounded-lg font-bold shadow-sm text-sm">{{ $guruTanpaNIP }}</span>
            </div>
            @endif

            @if($kelasTanpaWali > 0)
            <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <i class="bi bi-person-x-fill text-orange-500 text-xl"></i>
                    <div>
                        <div class="text-orange-700 font-bold text-sm">Kelas Tanpa Wali</div>
                        <div class="text-orange-600 text-xs">Segera tentukan wali</div>
                    </div>
                </div>
                <span class="bg-white text-orange-600 px-3 py-1 rounded-lg font-bold shadow-sm text-sm">{{ $kelasTanpaWali }}</span>
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- 2. DUA GRAFIK BERDAMPINGAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- BAGIAN KIRI: GRAFIK SISWA (Lebar 2 Kolom) --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-[Poppins-Bold] text-darkblue text-lg">Distribusi Siswa per Kelas</h3>
                <button class="text-gray-400 hover:text-blue-600"><i class="bi bi-bar-chart-fill"></i></button>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="siswaPerKelasChart"></canvas>
            </div>
        </div>

        {{-- BAGIAN KANAN: GRAFIK ROLE PEGAWAI (Lebar 1 Kolom) --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-[Poppins-Bold] text-darkblue text-lg">Komposisi Pegawai</h3>
                <button class="text-gray-400 hover:text-purple-600"><i class="bi bi-pie-chart-fill"></i></button>
            </div>
            <p class="text-xs text-gray-400 mb-6">Perbandingan peran pengguna guru & staff.</p>

            <div class="relative h-64 w-full flex items-center justify-center">
                <canvas id="userRoleChart"></canvas>
            </div>
            
         
            <div id="chartLegend" class="mt-4 flex flex-col gap-2 text-xs font-medium text-gray-700 w-full">
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. CHART SISWA (BAR) ---
        const ctxSiswa = document.getElementById('siswaPerKelasChart').getContext('2d');
        // Gradient Biru
        let gradBlue = ctxSiswa.createLinearGradient(0, 0, 0, 400);
        gradBlue.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
        gradBlue.addColorStop(1, 'rgba(59, 130, 246, 0.2)');

        new Chart(ctxSiswa, {
            type: 'bar',
            data: {
                labels: @json($chartSiswaLabels),
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: @json($chartSiswaData),
                    backgroundColor: gradBlue,
                    borderRadius: 6,
                    barThickness: 30,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // --- 2. CHART ROLE PEGAWAI (DOUGHNUT) ---
        const ctxRole = document.getElementById('userRoleChart').getContext('2d');
        const roleLabels = @json($chartRoleLabels);
        const roleData = @json($chartRoleData);
        const roleColors = [
            '#8b5cf6', // Ungu
            '#3b82f6', // Biru
            '#f97316', // Orange
            '#10b981'  // Hijau
        ];

        const legendContainer = document.getElementById('chartLegend');
        if (legendContainer && roleLabels.length > 0) {
      
            const displayOrder = ['kepala sekolah', 'guru', 'operator'];
            
            let sortedRoles = roleLabels.map((label, index) => {
                let lowerLabel = label.toString().toLowerCase();
                return {
                    label: label,
                    count: roleData[index] || 0,
                    color: roleColors[index % roleColors.length],
                    order: displayOrder.indexOf(lowerLabel) !== -1 ? displayOrder.indexOf(lowerLabel) : 99 
                };
            });

            // Sort based on the defined order
            sortedRoles.sort((a, b) => a.order - b.order);

            let legendHtml = '';
            sortedRoles.forEach((role) => {
                let formattedLabel = role.label.toString().charAt(0).toUpperCase() + role.label.toString().slice(1);
                // Special case for kepala sekolah to capitalize both words
                if (formattedLabel.toLowerCase() === 'kepala sekolah') {
                    formattedLabel = 'Kepala Sekolah';
                }
                
                legendHtml += `
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${role.color}"></span> 
                            <span>${formattedLabel}</span>
                        </div>
                        <span class="font-bold text-gray-900"> ${role.count} orang</span>
                    </div>`;
            });
            legendContainer.innerHTML = legendHtml;
        }
        
        new Chart(ctxRole, {
            type: 'doughnut',
            data: {
                labels: roleLabels,
                datasets: [{
                    data: roleData,
                    backgroundColor: roleColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Membuat lubang tengah lebih besar (Donat tipis)
                plugins: {
                    legend: { display: false }, // Kita pakai legend custom di HTML
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                let percentage = total > 0 ? ((value * 100) / total).toFixed(1) + "%" : "0%";
                                return label + ': ' + percentage;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
