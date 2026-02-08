@extends('layouts.app')

@section('title', 'Dashboard ' . $mapel->nama_mapel . ' - ' . $kelas->kelas)

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

    {{-- 1. HEADER & BREADCRUMB --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-gray-500">Kelas {{ $kelas->kelas }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Dashboard</span>
            </div>
            <h1 class="text-2xl font-[Poppins-Bold] text-darkblue tracking-tight">
                Overview Pembelajaran
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola ujian dan pantau perkembangan siswa kelas <span class="font-bold text-gray-700">{{ $kelas->kelas }}</span>.
            </p>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
    <div id="alert-success" class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
        <i class="bi bi-check-circle-fill text-lg"></i> {{ session('success') }}
    </div>
    @endif

    {{-- 2. STATISTIK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        {{-- Card 1: Siswa --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Siswa</p>
                    <h3 class="text-2xl font-[Poppins-Bold] text-gray-800 mt-1">{{ $jumlahSiswa }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>

        {{-- Card 2: Rata-rata Kuis --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-rata Kuis</p>
                    <h3 class="text-2xl font-[Poppins-Bold] text-gray-800 mt-1">{{ number_format($avgKuis, 1) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
            </div>
        </div>

        {{-- Card 3: Rata-rata UTS --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-rata UTS</p>
                    <h3 class="text-2xl font-[Poppins-Bold] text-gray-800 mt-1">{{ number_format($avgUTS, 1) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
            </div>
        </div>

        {{-- Card 4: Rata-rata UAS --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-rata UAS</p>
                    <h3 class="text-2xl font-[Poppins-Bold] text-gray-800 mt-1">{{ number_format($avgUAS, 1) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. QUICK ACTIONS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        {{-- Action 1: Buat Ujian --}}
        <a href="{{ route('guru.mapel.ujian.create', $mapel->id) }}" 
           class="group relative overflow-hidden rounded-2xl p-6 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
           style="background: linear-gradient(135deg, #00415a 0%, #447d9b 100%); color: white;">
            
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-white opacity-10 blur-2xl transition-all group-hover:opacity-20"></div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl shadow-inner border border-white/20"
                        style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-plus-lg text-2xl font-bold text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-[Poppins-Bold] text-lg leading-tight tracking-wide text-white">Buat Ujian Baru</h3>
                        <p class="text-sm opacity-90 text-blue-100">Jadwalkan ujian untuk siswa</p>
                    </div>
                </div>
                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white opacity-0 transform translate-x-4 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0"
                    style="background-color: rgba(255, 255, 255, 0.2);">
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- Action 2: Bank Soal --}}
        <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" 
           class="group relative overflow-hidden rounded-2xl bg-white border border-gray-200 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#447d9b]/50 hover:shadow-lg">
            
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#b3cde0]/30 text-[#00415a] transition-colors group-hover:bg-[#447d9b] group-hover:text-white">
                        <i class="bi bi-collection-fill text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-[Poppins-Bold] text-lg leading-tight text-[#00415a] group-hover:text-[#447d9b] transition-colors">Bank Soal</h3>
                        <p class="text-sm text-gray-500">Kelola kumpulan soal ujian</p>
                    </div>
                </div>
                <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 transform transition-all duration-300 group-hover:bg-[#447d9b] group-hover:text-white">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- 4. TABEL UJIAN SEDANG BERLANGSUNG (LIVE) --}}
    @if($ongoingUjian->count() > 0)
    <div class="bg-white border border-emerald-200 rounded-2xl shadow-[0_4px_20px_rgba(16,185,129,0.05)] overflow-hidden relative mb-8">
        <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="bi bi-broadcast animate-pulse"></i>
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-emerald-800">Ujian Sedang Berlangsung</h3>
                    <p class="text-xs text-emerald-600">Ujian ini sedang aktif dikerjakan siswa.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-emerald-50/30">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-emerald-700 uppercase tracking-wider">Nama Ujian</th>
                        <th class="px-6 py-4 text-xs font-bold text-emerald-700 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold text-emerald-700 uppercase tracking-wider">Waktu & Timer</th>
                        <th class="px-6 py-4 text-xs font-bold text-emerald-700 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @foreach ($ongoingUjian as $ujian)
                    <tr class="hover:bg-emerald-50/20 transition-colors">
                        {{-- Nama Ujian --}}
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-darkblue">{{ $ujian->nama_ujian }}</span>
                            <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">LIVE</span>
                        </td>

                        {{-- Jenis Ujian --}}
                        <td class="px-6 py-4">
                            @php
                                $badgeColor = match($ujian->jenis_ujian) {
                                    'Kuis' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'UTS' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'UAS' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badgeColor }}">
                                {{ $ujian->jenis_ujian }}
                            </span>
                        </td>

                        {{-- Waktu & Timer --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                {{-- Timer Hitung Mundur --}}
                                <div class="flex items-center gap-2 font-mono font-bold text-red-500 bg-red-50 px-3 py-1.5 rounded-lg w-fit mb-1" 
                                     id="timer-{{ $ujian->id }}" 
                                     data-end="{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('Y-m-d H:i:s') }}">
                                    <i class="bi bi-stopwatch"></i> <span class="countdown">--:--:--</span>
                                </div>
                                {{-- Detail Jam Mulai - Selesai --}}
                                <span class="text-xs text-emerald-700 font-medium flex items-center gap-1">
                                    <i class="bi bi-clock"></i>
                                    {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                             <div class="flex items-center justify-center gap-2">
                                <button onclick="window.location.href='{{ route('guru.mapel.ujian.detail', $ujian->id) }}'"
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all text-xs font-bold flex items-center gap-2">
                                    <i class="bi bi-eye-fill"></i> Pantau
                                </button>
                                <button onclick="openTimeModal({{ $ujian->id }}, '{{ str_replace("'", "\'", $ujian->nama_ujian) }}', '{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }}')"
                                        class="px-3 py-2 bg-white border border-emerald-200 text-emerald-600 rounded-lg hover:bg-emerald-50 transition-all text-xs font-bold flex items-center gap-2"
                                        title="Tambah Waktu / Edit Durasi">
                                    <i class="bi bi-clock-history"></i> Atur Waktu
                                </button>
                             </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 5. TABEL UJIAN AKAN DATANG --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden relative mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800">Jadwal Akan Datang</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Nama Ujian</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Waktu Pelaksanaan</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($upcomingUjian as $ujian)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-darkblue">{{ $ujian->nama_ujian }}</span>
                        </td>
                        
                        {{-- Jenis Ujian (BARU) --}}
                        <td class="px-6 py-4">
                            @php
                                $badgeColor = match($ujian->jenis_ujian) {
                                    'Kuis' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'UTS' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'UAS' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badgeColor }}">
                                {{ $ujian->jenis_ujian }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                {{-- Tanggal (BAHASA INDONESIA) --}}
                                <span class="text-sm font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </span>
                                {{-- Jam Mulai - Selesai --}}
                                <span class="text-xs text-blue-500 font-bold mt-1 flex items-center gap-1">
                                    <i class="bi bi-clock"></i> 
                                    {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                Terjadwal
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="window.location.href='{{ route('guru.mapel.ujian.edit', $ujian->id) }}'"
                                        class="px-3 py-2 bg-amber-50 border border-amber-100 text-amber-600 rounded-lg hover:bg-amber-100 transition-all shadow-sm text-xs font-bold"
                                        title="Edit Jadwal/Soal">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <button onclick="openDeleteModal({{ $ujian->id }}, '{{ str_replace("'", "\'", $ujian->nama_ujian) }}')"
                                        class="px-3 py-2 bg-red-50 border border-red-100 text-red-600 rounded-lg hover:bg-red-100 transition-all shadow-sm text-xs font-bold"
                                        title="Batalkan Ujian">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            <span class="text-sm italic">Tidak ada jadwal ujian dalam waktu dekat.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. TABEL RIWAYAT UJIAN (HISTORY) --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800">Riwayat Ujian</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Nama Ujian</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Waktu Pelaksanaan</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Durasi</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($historyUjian as $ujian)
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        
                        {{-- Nama Ujian --}}
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-darkblue group-hover:text-blue-600 transition-colors">
                                {{ $ujian->nama_ujian }}
                            </span>
                        </td>

                        {{-- Jenis Badge --}}
                        <td class="px-6 py-4">
                            @php
                                $badgeColor = match($ujian->jenis_ujian) {
                                    'Kuis' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'UTS' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'UAS' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badgeColor }}">
                                {{ $ujian->jenis_ujian }}
                            </span>
                        </td>

                        {{-- Waktu (BAHASA INDONESIA) --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </span>
                                <span class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <i class="bi bi-clock"></i> 
                                    <span class="font-mono font-bold text-blue-600">
                                        {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }}
                                    </span>
                                    <span class="text-gray-400 mx-1">-</span>
                                    <span class="font-mono font-bold text-gray-600">
                                        {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }}
                                    </span>
                                    WIB
                                </span>
                            </div>
                        </td>

                        {{-- Durasi --}}
                        <td class="px-6 py-4">
                            <span class="text-sm font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                {{ $ujian->durasi_menit }} menit
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="window.location.href='{{ route('guru.mapel.ujian.detail', $ujian->id) }}'"
                                        class="px-3 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm text-xs font-bold flex items-center gap-2"
                                        title="Lihat Detail">
                                    <i class="bi bi-eye-fill"></i> Detail
                                </button>

                                {{-- Form Hapus Hidden --}}
                                <form id="deleteForm-{{ $ujian->id }}"
                                      action="{{ route('guru.mapel.ujian.destroy', $ujian->id) }}"
                                      method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                {{-- Tombol Hapus --}}
                                <button onclick="openDeleteModal({{ $ujian->id }}, '{{ str_replace("'", "\'", $ujian->nama_ujian) }}')"
                                        class="px-3 py-2 bg-red-50 border border-red-100 text-red-600 rounded-lg hover:bg-red-100 hover:border-red-200 transition-all shadow-sm text-xs font-bold flex items-center gap-2"
                                        title="Hapus Ujian">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="bi bi-clipboard-x text-2xl opacity-50"></i>
                                </div>
                                <span class="text-sm font-medium">Belum ada riwayat ujian yang dibuat.</span>
                                <p class="text-xs mt-1">Klik "Buat Ujian Baru" untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Form Hapus (Hidden) dan Modal Hapus --}}
    @foreach($upcomingUjian->merge($historyUjian)->merge($ongoingUjian) as $ujian)
    <form id="deleteForm-{{ $ujian->id }}"
          action="{{ route('guru.mapel.ujian.destroy', $ujian->id) }}"
          method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endforeach

    {{-- Modal Hapus Code --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-trash3-fill text-red-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900">Hapus Ujian</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" id="deleteMessage">
                                        Apakah Anda yakin? Data nilai siswa terkait ujian ini juga akan terhapus.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" id="confirmDeleteBtn" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            Ya, Hapus
                        </button>
                        <button type="button" onclick="closeDeleteModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Update Waktu --}}
    <div id="timeModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeTimeModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100">
                    <form id="timeForm" action="" method="POST">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="bi bi-clock-history text-blue-600 text-lg"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900">Atur Waktu Ujian</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Sesuaikan waktu selesai untuk ujian <b id="timeModalNama" class="text-gray-800"></b>.
                                        </p>
                                        
                                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-4">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Selesai Saat Ini</label>
                                            <div class="text-xl font-mono font-bold text-gray-800" id="timeModalCurrent">--:--</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Tambahan Waktu (Menit)</label>
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="adjustTimeInput(-10)" class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-bold border border-red-100">-10m</button>
                                                <input type="number" name="tambahan_menit" id="timeInput" value="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-center font-bold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <button type="button" onclick="adjustTimeInput(10)" class="px-3 py-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 font-bold border border-green-100">+10m</button>
                                                <button type="button" onclick="adjustTimeInput(30)" class="px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-bold border border-blue-100">+30m</button>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-2">* Masukkan nilai negatif untuk mengurangi waktu.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:w-auto transition-colors">
                                Simpan Perubahan
                            </button>
                            <button type="button" onclick="closeTimeModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // --- MODAL DELETE SCRIPT ---
    let deleteId = null;

    function openDeleteModal(id, nama) {
        deleteId = id;
        document.getElementById("deleteMessage").innerHTML = `Apakah Anda yakin ingin menghapus ujian <b>"${nama}"</b>?<br><span class="text-xs text-red-500 mt-1 block">Tindakan ini tidak dapat dibatalkan.</span>`;
        document.getElementById("deleteModal").classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.add('hidden');
        deleteId = null;
    }

    document.getElementById("confirmDeleteBtn").addEventListener("click", function() {
        if (deleteId) {
            document.getElementById(`deleteForm-${deleteId}`).submit();
        }
    });

    // --- MODAL TIME SCRIPT ---
    function openTimeModal(id, nama, selesai) {
        document.getElementById("timeModalNama").innerText = nama;
        document.getElementById("timeModalCurrent").innerText = selesai + " WIB";
        document.getElementById("timeInput").value = 0;
        
        // Set Action URL
        // Route: /ujian/{id}/update-waktu
        const url = "{{ route('guru.mapel.ujian.update_waktu', ':id') }}".replace(':id', id);
        document.getElementById("timeForm").action = url;

        document.getElementById("timeModal").classList.remove('hidden');
    }

    function closeTimeModal() {
        document.getElementById("timeModal").classList.add('hidden');
    }

    function adjustTimeInput(min) {
        const input = document.getElementById("timeInput");
        let val = parseInt(input.value) || 0;
        input.value = val + min;
    }

    // Auto Close Alerts
    const alerts = document.querySelectorAll('[id^="alert-"]');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(el => {
                el.style.transition = "opacity 0.5s ease";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);
    }

    // --- SCRIPT TIMER HITUNG MUNDUR ---
    document.addEventListener("DOMContentLoaded", function() {
        const timers = document.querySelectorAll('[id^="timer-"]');

        function updateTimers() {
            const now = new Date();

            timers.forEach(timer => {
                const endString = timer.getAttribute('data-end'); // Format: YYYY-MM-DD HH:mm:ss
                // Pastikan format date string bisa diparsing di semua browser (ganti spasi dengan T)
                const endDate = new Date(endString.replace(' ', 'T'));
                
                const diff = endDate - now;

                const display = timer.querySelector('.countdown');

                if (diff <= 0) {
                    display.innerText = "Selesai";
                    timer.classList.remove('text-red-500', 'bg-red-50');
                    timer.classList.add('text-gray-500', 'bg-gray-100');
                } else {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    // Format 00:00:00
                    const h = hours.toString().padStart(2, '0');
                    const m = minutes.toString().padStart(2, '0');
                    const s = seconds.toString().padStart(2, '0');
                    
                    display.innerText = `${h}:${m}:${s}`;
                }
            });
        }

        if(timers.length > 0) {
            setInterval(updateTimers, 1000);
            updateTimers(); // Jalankan langsung
        }
        
        // Auto Close Alert
        const alert = document.getElementById('alert-success');
        if(alert) setTimeout(() => alert.style.display = 'none', 4000);
    });
</script>
@endsection