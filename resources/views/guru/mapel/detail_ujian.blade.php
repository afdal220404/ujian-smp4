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

    
    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.siswa') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.bank_soal.*') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.mapel.arsip_soal_siswa.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.arsip_soal_siswa.*') ? 'active' : '' }}">
        <i class="bi bi-folder2-open"></i> <span>Arsip Soal Siswa</span>
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

        <div class="flex flex-col sm:flex-row items-center gap-2">
            @if($ujian->is_susulan)
            <div class="px-4 py-2 bg-orange-50 border border-orange-200 rounded-xl text-sm font-bold text-orange-600 flex items-center gap-2">
                <i class="bi bi-info-circle-fill"></i> Ujian Susulan
            </div>
            @endif
            <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2 w-full sm:w-auto justify-center">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-xl shadow-sm flex items-center gap-3">
        <i class="bi bi-check-circle-fill text-xl"></i>
        <p class="font-bold text-sm">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm flex items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-xl"></i>
        <p class="font-bold text-sm">{{ session('error') }}</p>
    </div>
    @endif

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
        <div id="view-sudah" class="block">
            
            {{-- Tombol Analisis Soal (Hanya muncul jika ada yang sudah mengerjakan) --}}
            @if($sudahMengerjakan > 0)
            <div class="p-4 bg-blue-50/50 border-b border-blue-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-sm text-gray-600">Terdapat <span class="font-bold text-blue-600">{{ $sudahMengerjakan }}</span> siswa yang sudah menyelesaikan ujian.</p>
                <a href="{{ route('guru.mapel.ujian.analisis_soal', $ujian->id) }}" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all flex items-center gap-2 shadow-sm">
                    <i class="bi bi-bar-chart-line"></i> Lihat Analisis Soal
                </a>
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                            <th class="px-6 py-4 w-12 text-center border-r border-gray-100">No</th>
                            <th class="px-6 py-4 border-r border-gray-100">Identitas Siswa</th>
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
                                    <div class="flex items-center gap-2">
                                        <div class="font-[Poppins-Bold] text-darkblue text-sm">{{ $namaSiswa }}</div>
                                        @if($hasil->ujian_id != $ujian->id)
                                            <span class="px-2 py-0.5 bg-orange-100 text-orange-600 text-[9px] font-bold rounded uppercase border border-orange-200 shadow-sm leading-none" title="Mengerjakan melalui Ujian Susulan">
                                                <i class="bi bi-clock-history"></i> Susulan
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">NISN: {{ $nisnSiswa }}</div>
                                </td>

                                {{-- ISI KOLOM BARU --}}
                                <td class="px-6 py-4 border-r border-gray-50">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="flex flex-col items-center justify-center w-10 h-10 rounded-lg bg-green-50 text-green-700 border border-green-100" title="Jawaban Benar">
                                            <span class="text-[10px] font-bold uppercase">Benar</span>
                                            <span class="text-sm font-bold">{{ $hasil->jumlah_benar }}</span>
                                        </div>
                                        <div class="flex flex-col items-center justify-center w-10 h-10 rounded-lg bg-red-50 text-red-700 border border-red-100" title="Jawaban Salah">
                                            <span class="text-[10px] font-bold uppercase">Salah</span>
                                            <span class="text-sm font-bold">{{ $hasil->jumlah_salah }}</span>
                                        </div>
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
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Detail --}}
                                    <a href="{{ route('guru.mapel.ujian.siswa.detail', ['ujian' => $ujian->id, 'siswa' => $hasil->siswa_id]) }}" 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                    title="Lihat Detail Jawaban">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    {{-- TOMBOL RESTART BARU (Hanya Muncul Jika Waktu Ujian Belum Habis) --}}
                                    @if(\Carbon\Carbon::now('Asia/Jakarta') <= \Carbon\Carbon::parse($ujian->waktu_selesai))
                                    <button type="button" 
                                            onclick="event.stopPropagation(); confirmRestart('{{ $hasil->siswa_id }}', '{{ str_replace("'", "\'", $namaSiswa) }}')"
                                            class="relative z-10 inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white transition-all shadow-sm"
                                            title="Restart Ujian (Izinkan Mengulang)">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    @endif
                                </div>
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
            
            {{-- Tombol Modal Ujian Susulan --}}
            @if(!$ujian->is_susulan && count($siswaBelum) > 0)
            <div class="p-4 bg-orange-50/50 border-b border-orange-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-sm text-gray-600">Terdapat <span class="font-bold text-red-600">{{ count($siswaBelum) }}</span> siswa yang belum mengerjakan.</p>
                
                @if($isIndukOngoing)
                    <div class="flex flex-col items-center sm:items-end">
                        <div class="px-5 py-2.5 bg-gray-100 text-gray-400 border border-gray-200 rounded-xl text-xs font-bold flex items-center gap-2 cursor-not-allowed" title="Ujian utama masih sedang berlangsung">
                            <i class="bi bi-lock-fill"></i> Ujian Utama Sedang Berlangsung
                        </div>
                        <span class="text-[10px] text-orange-600 font-bold mt-1 uppercase tracking-wider italic">* Ujian susulan dapat dibuat setelah ujian selesai</span>
                    </div>
                @elseif($hasActiveSusulan)
                    <div class="px-5 py-2.5 bg-gray-100 text-gray-400 border border-gray-200 rounded-xl text-xs font-bold flex items-center gap-2 cursor-not-allowed" title="Masih ada ujian susulan lain yang sedang berjalan">
                        <i class="bi bi-hourglass-split"></i> Ujian Susulan Berjalan
                    </div>
                @else
                    <button onclick="openSusulanModal()" class="px-5 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 transition-all flex items-center gap-2 shadow-sm">
                        <i class="bi bi-calendar-plus"></i> Buat Ujian Susulan
                    </button>
                @endif
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                            <th class="px-6 py-4 w-12 text-center border-r border-gray-100">No</th>
                            <th class="px-6 py-4 border-r border-gray-100">Identitas Siswa</th>
                            <th class="px-6 py-4 text-center w-32">Status</th>
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

    {{-- MODAL BUAT UJIAN SUSULAN --}}
    <div id="modal-susulan" class="fixed inset-0 z-[100] hidden">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeSusulanModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl my-auto transform transition-all">
                <form action="{{ route('guru.mapel.ujian.susulan.store', $ujian->id) }}" method="POST">
                    @csrf
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-orange-50 rounded-t-2xl">
                        <div>
                            <h3 class="font-[Poppins-Bold] text-lg text-orange-800"><i class="bi bi-calendar-plus mr-2"></i>Buat Ujian Susulan</h3>
                            <p class="text-xs text-orange-600 mt-0.5">Ujian Asal: {{ $ujian->nama_ujian }}</p>
                        </div>
                        <button type="button" onclick="closeSusulanModal()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/50 text-orange-500 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Kolom Kiri: Form Detail Ujian --}}
                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Ujian Susulan</label>
                                <input type="text" name="nama_ujian" value="Ujian Susulan - {{ $ujian->nama_ujian }}" required class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-orange-500 focus:ring-1 focus:ring-orange-100 outline-none transition-all">
                            </div>
                            <div class="mb-5">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Pelaksanaan</label>
                                <input type="date" name="tanggal_ujian" id="modal_tanggal_ujian" required class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-orange-500 outline-none transition-all">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Mulai</label>
                                    <input type="time" name="waktu_mulai" id="modal_waktu_mulai" required class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-orange-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Selesai</label>
                                    <input type="time" name="waktu_selesai" id="modal_waktu_selesai" required class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-orange-500 outline-none transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durasi Ujian</label>
                                <input type="text" id="modal_durasi" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 outline-none cursor-not-allowed" placeholder="Otomatis dihitung...">
                            </div>
                        </div>
                        
                        {{-- Kolom Kanan: Pilih Siswa --}}
                        <div class="flex flex-col h-[300px]">
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Pilih Peserta Susulan</label>
                                <label class="flex items-center gap-2 text-xs font-bold text-blue-600 cursor-pointer hover:text-blue-700">
                                    <input type="checkbox" id="check-all-susulan" class="w-4 h-4 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                                    Pilih Semua
                                </label>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 flex-1 overflow-y-auto">
                                @if(count($siswaBelum) == 0)
                                    <div class="h-full flex flex-col items-center justify-center text-gray-400">
                                        <i class="bi bi-check-circle text-3xl mb-2"></i>
                                        <p class="text-sm">Tidak ada siswa tersisa.</p>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($siswaBelum as $siswa)
                                        <label class="flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-lg cursor-pointer group hover:border-blue-300 transition-all">
                                            <input type="checkbox" name="peserta_ids[]" value="{{ $siswa->id }}" class="susulan-cb w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors truncate">{{ $siswa->nama_lengkap }}</p>
                                                <p class="text-[10px] text-gray-400 font-mono">NISN: {{ $siswa->nisn }}</p>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl border-t border-gray-100">
                        <button type="button" onclick="closeSusulanModal()" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-all text-sm">Batal</button>
                        <button type="submit" class="px-8 py-2.5 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 shadow-lg shadow-orange-200 transition-all text-sm flex items-center gap-2">
                            <i class="bi bi-send-check-fill"></i> Simpan & Buat
                        </button>
                    </div>
                </form>
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

    // 3. Logic Modal Susulan
    function openSusulanModal() {
        document.getElementById('modal-susulan').classList.remove('hidden');
    }
    function closeSusulanModal() {
        document.getElementById('modal-susulan').classList.add('hidden');
    }

    // 4. Hitung Durasi Otomatis
    const tanggalInp = document.getElementById('modal_tanggal_ujian');
    const waktuMulaiEl = document.getElementById('modal_waktu_mulai');
    const waktuSelesaiEl = document.getElementById('modal_waktu_selesai');
    const durasiEl = document.getElementById('modal_durasi');

    function hitungDurasi() {
        if (tanggalInp.value && waktuMulaiEl.value && waktuSelesaiEl.value) {
            // Gabungkan tanggal dan jam untuk objek Date
            const mulaiStr = tanggalInp.value + ' ' + waktuMulaiEl.value;
            const selesaiStr = tanggalInp.value + ' ' + waktuSelesaiEl.value;
            
            const mulai = new Date(mulaiStr);
            const selesai = new Date(selesaiStr);

            if (selesai <= mulai) {
                durasiEl.value = 'Waktu Selesai tidak valid!';
                durasiEl.classList.add('text-red-500');
                return;
            } else {
                durasiEl.classList.remove('text-red-500');
            }

            const selisihMs = selesai.getTime() - mulai.getTime();
            const selisihMenit = Math.floor(selisihMs / 60000);
            durasiEl.value = selisihMenit + " Menit";
        }
    }

    tanggalInp.addEventListener('change', hitungDurasi);
    waktuMulaiEl.addEventListener('change', hitungDurasi);
    waktuSelesaiEl.addEventListener('change', hitungDurasi);

    // 5. Logic Check All Peserta
    const checkAllSusulan = document.getElementById('check-all-susulan');
    const susulanCbs = document.querySelectorAll('.susulan-cb');
    
    if(checkAllSusulan) {
        checkAllSusulan.addEventListener('change', function() {
            susulanCbs.forEach(cb => cb.checked = this.checked);
        });
    }

    susulanCbs.forEach(cb => {
        cb.addEventListener('change', function() {
            if(!this.checked && checkAllSusulan) checkAllSusulan.checked = false;
            
            if(document.querySelectorAll('.susulan-cb:checked').length === susulanCbs.length) {
                if(checkAllSusulan) checkAllSusulan.checked = true;
            }
        });
    });
</script>
{{-- Hidden Form untuk Restart --}}
<form id="form-restart-ujian" method="POST" style="display: none;">
    @csrf
</form>

<script>
    function confirmRestart(siswaId, namaSiswa) {
        // Menggunakan modal konfirmasi custom yang profesional
        showConfirmModal(
            'Restart Ujian Siswa?',
            'Seluruh pengerjaan "' + namaSiswa + '" akan dihapus secara permanen. Siswa akan diizinkan untuk masuk dan mengerjakan ulang ujian ini dari awal.',
            function() {
                const form = document.getElementById('form-restart-ujian');
                // Set action route secara dinamis
                let url = "{{ route('guru.mapel.ujian.siswa.restart', ['ujian' => $ujian->id, 'siswa' => ':siswa_id']) }}";
                form.action = url.replace(':siswa_id', siswaId);
                
                showLoadingModal(); // Tampilkan loading saat proses hapus
                form.submit();
            },
            'Ya, Izinkan Mengulang',
            'bg-orange-600',
            'hover:bg-orange-700'
        );
    }
</script>

{{-- =========================================================
     3 MODAL CUSTOM UNIVERSAL (NOTIFIKASI, KONFIRMASI, LOADING)
========================================================== --}}

{{-- 1. Modal Notifikasi (Sukses, Error, Info) --}}
<div id="modal-notification" class="fixed inset-0 z-[110] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeNotificationModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 text-left">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="notification-modal-content">
            <div class="p-6 text-center">
                <div id="notif-icon-container" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border-[6px]">
                    <i id="notif-icon" class="text-2xl"></i>
                </div>
                <h3 id="notif-title" class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Title</h3>
                <p id="notif-message" class="text-sm text-gray-500 mb-6 font-medium">Message here.</p>
                <button type="button" id="notif-btn" onclick="closeNotificationModal()" class="px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal Konfirmasi (Restart, Hapus, dll) --}}
<div id="modal-custom-confirm" class="fixed inset-0 z-[105] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeConfirmModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 text-left">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="confirm-modal-content">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-orange-50 flex items-center justify-center mx-auto mb-4 border-[6px] border-orange-100">
                    <i class="bi bi-question-circle-fill text-2xl text-orange-500"></i>
                </div>
                <h3 id="confirm-title" class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Konfirmasi</h3>
                <p id="confirm-message" class="text-sm text-gray-500 mb-6 font-medium">Apakah Anda yakin?</p>
                <div class="flex gap-3 justify-center">
                    <button type="button" onclick="closeConfirmModal()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors w-full">
                        Batal
                    </button>
                    <button type="button" id="confirm-action-btn" class="px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. Modal Loading --}}
<div id="modal-loading" class="fixed inset-0 z-[120] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center transform scale-95 opacity-0 transition-all duration-300" id="loading-modal-content">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mb-4"></div>
            <h3 class="text-lg font-bold text-gray-800">Memproses...</h3>
            <p class="text-sm text-gray-500 mt-1 text-center font-medium">Mohon tunggu sebentar, sistem sedang memproses permintaan Anda.</p>
        </div>
    </div>
</div>

<script>
// =========================================================================
// FUNGSI PENGENDALI MODAL CUSTOM UNIVERSAL
// =========================================================================
function showNotificationModal(title, message, type = 'error', callback = null) {
    const modal = document.getElementById('modal-notification');
    const content = document.getElementById('notification-modal-content');
    const iconContainer = document.getElementById('notif-icon-container');
    const icon = document.getElementById('notif-icon');
    const btn = document.getElementById('notif-btn');
    
    document.getElementById('notif-title').innerText = title;
    document.getElementById('notif-message').innerText = message;

    iconContainer.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border-[6px]';
    icon.className = 'text-2xl';
    btn.className = 'px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2';

    if (type === 'error') {
        iconContainer.classList.add('bg-red-50', 'border-red-100');
        icon.classList.add('bi', 'bi-exclamation-circle-fill', 'text-red-500');
        btn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-200');
    } else if (type === 'success') {
        iconContainer.classList.add('bg-green-50', 'border-green-100');
        icon.classList.add('bi', 'bi-check-circle-fill', 'text-green-500');
        btn.classList.add('bg-green-600', 'hover:bg-green-700', 'shadow-green-200');
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    btn.onclick = () => {
        closeNotificationModal();
        if (callback) setTimeout(callback, 300);
    };
}

function closeNotificationModal() {
    const modal = document.getElementById('modal-notification');
    const content = document.getElementById('notification-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function showConfirmModal(title, message, onConfirm, confirmText = 'Ya', confirmBg = 'bg-blue-600', confirmHover = 'hover:bg-blue-700') {
    const modal = document.getElementById('modal-custom-confirm');
    const content = document.getElementById('confirm-modal-content');
    const btn = document.getElementById('confirm-action-btn');

    document.getElementById('confirm-title').innerText = title;
    document.getElementById('confirm-message').innerText = message;
    btn.innerText = confirmText;
    btn.className = `px-5 py-2.5 text-white rounded-xl text-sm font-bold shadow-lg transition-all w-full flex items-center justify-center gap-2 ${confirmBg} ${confirmHover}`;

    btn.onclick = () => {
        closeConfirmModal();
        if (onConfirm) onConfirm();
    };

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeConfirmModal() {
    const modal = document.getElementById('modal-custom-confirm');
    const content = document.getElementById('confirm-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function showLoadingModal() {
    const modal = document.getElementById('modal-loading');
    const content = document.getElementById('loading-modal-content');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function hideLoadingModal() {
    const modal = document.getElementById('modal-loading');
    const content = document.getElementById('loading-modal-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
</script>
@endsection