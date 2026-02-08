@extends('layouts.app')

@section('title', 'Detail Ujian - ' . $ujian->nama_ujian)

@section('sidebar-menu')
    {{-- Tombol Kembali --}}
    <div class="mb-4 px-3">
        <a href="{{ route('guru.index') }}" 
           class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
        </a>
    </div>

    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Mapel</div>
    
    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link">
        <i class="bi bi-collection"></i> <span>Bank Soal</span>
    </a>
@endsection

@section('content')

    {{-- LOGIKA STATISTIK --}}
    @php
        $kumpulanNilai = $hasilUjian->pluck('nilai');
        $rataRata = $kumpulanNilai->avg() ?? 0;
        $tertinggi = $kumpulanNilai->max() ?? 0;
        $terendah = $kumpulanNilai->min() ?? 0;
    @endphp

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
        <div>
            <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-gray-500">Kelas VII</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Detail Ujian</span>
            </div>
        </div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">{{ $ujian->nama_ujian }}</h1>
            <p class="text-gray-500 mt-1">
                Laporan untuk kelas <span class="font-bold text-darkblue">{{ $mapel->kelas->kelas }}</span>.
            </p>
        </div>

        <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- 2. KARTU STATISTIK --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        {{-- Total Peserta --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-bold uppercase">Total Siswa</div>
                <div class="text-xl font-[Poppins-Bold] text-darkblue">{{ $totalSiswa }}</div>
            </div>
        </div>

        {{-- Sudah Mengerjakan --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-bold uppercase">Sudah Mengerjakan</div>
                <div class="text-xl font-[Poppins-Bold] text-green-600">{{ $sudahMengerjakan }}</div>
            </div>
        </div>

        {{-- Belum Mengerjakan --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-bold uppercase">Belum Mengerjakan</div>
                <div class="text-xl font-[Poppins-Bold] text-red-600">{{ $belumMengerjakan }}</div>
            </div>
        </div>

        {{-- Rata-Rata Nilai --}}
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="bi bi-calculator"></i>
            </div>
            <div>
                <div class="text-[10px] text-gray-400 font-bold uppercase">Rata-Rata Nilai</div>
                <div class="text-xl font-[Poppins-Bold] text-darkblue">{{ number_format($rataRata, 1) }}</div>
            </div>
        </div>
    </div>

    {{-- 3. CONTAINER TAB & TABEL --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        
        {{-- HEADER TAB --}}
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-center gap-4">
            
            {{-- Tombol Tab --}}
            <div class="flex p-1 bg-gray-200/50 rounded-xl">
                <button onclick="switchTab('sudah')" id="tab-sudah" class="px-4 py-2 rounded-lg text-sm font-bold text-darkblue bg-white shadow-sm transition-all">
                    Sudah Mengerjakan ({{ $sudahMengerjakan }})
                </button>
                <button onclick="switchTab('belum')" id="tab-belum" class="px-4 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-darkblue transition-all">
                    Belum Mengerjakan ({{ $belumMengerjakan }})
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="relative w-full md:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="bi bi-search text-gray-400"></i>
                </span>
                <input type="text" id="searchInput" class="w-full py-2 pl-9 pr-4 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg focus:border-primary focus:outline-none" placeholder="Cari nama siswa...">
            </div>
        </div>

        {{-- TABEL 1: SUDAH MENGERJAKAN --}}
        {{-- TABEL 1: SUDAH MENGERJAKAN --}}
        <div id="view-sudah" class="block">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                            <th class="px-6 py-4 w-12 text-center border-r border-gray-100">No</th>
                            <th class="px-6 py-4 border-r border-gray-100">Identitas Siswa</th>
                            
                            {{-- KOLOM BARU: STATISTIK --}}
                            <th class="px-6 py-4 text-center border-r border-gray-100">Statistik</th>
                            
                            <th class="px-6 py-4 text-center w-32 border-r border-gray-100">Status</th>
                            <th class="px-6 py-4 text-center w-32 bg-gray-50 text-darkblue">Nilai</th>
                            <th class="px-6 py-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 searchable-body">
                        @forelse ($hasilUjian as $index => $hasil)
                            @php
                                $nilai = floatval($hasil->nilai);
                                $warnaNilai = $nilai >= 75 ? 'text-green-600' : 'text-red-500';
                                $bgNilai = $nilai >= 75 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                                $namaSiswa = $hasil->siswa->nama_lengkap ?? $hasil->nama_siswa; 
                                $nisnSiswa = $hasil->siswa->nisn ?? $hasil->nisn_siswa;
                            @endphp
                            <tr class="hover:bg-green-50/10 transition-colors group cursor-pointer" ondblclick="window.location.href='{{ route('guru.mapel.ujian.siswa.detail', ['ujian' => $ujian->id, 'siswa' => $hasil->siswa_id]) }}'">
                                <td class="px-6 py-4 text-center font-medium text-gray-400 border-r border-gray-50">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 border-r border-gray-50">
                                    <div class="font-[Poppins-Bold] text-darkblue text-sm">{{ $namaSiswa }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">NISN: {{ $nisnSiswa }}</div>
                                </td>

                                {{-- ISI KOLOM BARU --}}
                                <td class="px-6 py-4 border-r border-gray-50">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Badge Benar --}}
                                        <div class="flex flex-col items-center justify-center w-10 h-10 rounded-lg bg-green-50 text-green-700 border border-green-100" title="Jawaban Benar">
                                            <span class="text-[10px] font-bold uppercase">Benar</span>
                                            <span class="text-sm font-bold">{{ $hasil->jumlah_benar }}</span>
                                        </div>
                                        {{-- Badge Salah --}}
                                        <div class="flex flex-col items-center justify-center w-10 h-10 rounded-lg bg-red-50 text-red-700 border border-red-100" title="Jawaban Salah">
                                            <span class="text-[10px] font-bold uppercase">Salah</span>
                                            <span class="text-sm font-bold">{{ $hasil->jumlah_salah }}</span>
                                        </div>
                                        {{-- Total Soal --}}
                                        <div class="ml-2 text-xs text-gray-400 font-medium">
                                            / <span class="text-gray-600 font-bold">{{ $hasil->total_soal }}</span> Soal
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center border-r border-gray-50">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-green-100 text-green-700">
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center bg-gray-50/30 border-r border-gray-50">
                                    <span class="inline-block px-3 py-1 rounded-lg text-sm font-[Poppins-Bold] border {{ $bgNilai }} {{ $warnaNilai }}">{{ $hasil->nilai }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('guru.mapel.ujian.siswa.detail', ['ujian' => $ujian->id, 'siswa' => $hasil->siswa_id]) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                       title="Lihat Detail Jawaban">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">Belum ada siswa yang mengerjakan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABEL 2: BELUM MENGERJAKAN --}}
        <div id="view-belum" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                            <th class="px-6 py-4 w-12 text-center border-r border-gray-100">No</th>
                            <th class="px-6 py-4 border-r border-gray-100">Identitas Siswa</th>
                            <th class="px-6 py-4 text-center w-32">Status</th>
                            {{-- KOLOM INGATKAN DIHAPUS --}}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 searchable-body">
                        @forelse ($siswaBelum as $index => $siswa)
                            <tr class="hover:bg-red-50/10 transition-colors group">
                                <td class="px-6 py-4 text-center font-medium text-gray-400 border-r border-gray-50">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 border-r border-gray-50">
                                    <div class="font-[Poppins-Bold] text-gray-700 text-sm">{{ $siswa->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">NISN: {{ $siswa->nisn }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                        <i class="bi bi-x-circle-fill"></i> Belum
                                    </span>
                                </td>
                                {{-- KOLOM TOMBOL DIHAPUS --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-green-600 bg-green-50/20 italic">
                                    <i class="bi bi-check-all text-2xl mb-2 block"></i>
                                    Luar biasa! Semua siswa telah mengerjakan ujian ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    // 1. Logic Tab Switcher
    function switchTab(type) {
        const viewSudah = document.getElementById('view-sudah');
        const viewBelum = document.getElementById('view-belum');
        const btnSudah = document.getElementById('tab-sudah');
        const btnBelum = document.getElementById('tab-belum');

        if(type === 'sudah') {
            viewSudah.classList.remove('hidden');
            viewBelum.classList.add('hidden');
            
            btnSudah.className = "px-4 py-2 rounded-lg text-sm font-bold text-darkblue bg-white shadow-sm transition-all";
            btnBelum.className = "px-4 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-darkblue transition-all";
        } else {
            viewSudah.classList.add('hidden');
            viewBelum.classList.remove('hidden');

            btnBelum.className = "px-4 py-2 rounded-lg text-sm font-bold text-red-600 bg-white shadow-sm transition-all";
            btnSudah.className = "px-4 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-darkblue transition-all";
        }
    }

    // 2. Logic Pencarian
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let tables = document.querySelectorAll('.searchable-body');
        
        tables.forEach(tbody => {
            let rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });
</script>
@endsection