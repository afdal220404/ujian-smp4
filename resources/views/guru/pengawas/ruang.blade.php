<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ruang Pengawas - {{ $ujian->nama_ujian }} | SMPN 4 Tilatang Kamang</title>
    
    <link rel="icon" type="image/x-icon" href="{{ asset('image/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('image/logo_sekolah2.png') }}">

    {{-- Aset & Library --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])

    <style>
        @font-face {
            font-family: 'Poppins-Regular';
            src: url('/font/Poppins/Poppins-Regular.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Poppins-Bold';
            src: url('/font/Poppins/Poppins-Bold.ttf') format('truetype');
        }

        body {
            font-family: 'Poppins-Regular', sans-serif;
            background-color: rgba(179, 205, 224, 0.3);
        }
        
        .font-bold-custom {
            font-family: 'Poppins-Bold', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-slate-800 antialiased">

    {{-- FLOATING TOAST CONTAINER (TEMPLATE ALERT ASLI APLIKASI) --}}
    <div id="toast-container" class="fixed top-24 right-4 sm:right-6 z-50 flex flex-col gap-3 max-w-sm sm:max-w-md w-full pointer-events-none"></div>

    {{-- 1. NAVBAR PENGAWAS --}}
    <nav class="bg-[#00415a] sticky top-0 z-50 border-b border-white/10 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                {{-- Logo & Identitas --}}
                <div class="flex items-center gap-4">
                    <div class="p-2 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                        <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo" class="h-10 w-10 object-contain">
                    </div>
                    <div class="flex flex-col text-white">
                        <div class="flex items-center gap-2">
                            <span class="font-bold-custom text-lg tracking-wide">Ruang Pengawas Ujian</span>
                            <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                Live Proctor
                            </span>
                        </div>
                        <span class="text-xs text-blue-200 opacity-90">SMP Negeri 4 Tilatang Kamang</span>
                    </div>
                </div>

                {{-- Action Nav --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('guru.index') }}" 
                       class="group flex items-center gap-2 px-5 py-2.5 rounded-full text-xs md:text-sm font-bold text-white bg-white/10 border border-white/20 hover:bg-white hover:text-[#00415a] transition-all duration-300 shadow-sm">
                        <i class="bi bi-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>

            </div>
        </div>
    </nav>

    {{-- 2. KONTEN UTAMA --}}
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- FLASH ALERTS --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-2xl text-emerald-900 shadow-sm flex items-center justify-between border border-emerald-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-check-lg text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl text-red-900 shadow-sm flex items-center justify-between border border-red-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-base"></i>
                    </div>
                    <p class="text-sm font-semibold">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-1">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        {{-- A. PREMIUM HERO HEADER CARD (INFO UJIAN LENGKAP + DIGITAL STANDOUT TIMER + CONTROL ACTIONS) --}}
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_10px_40px_-10px_rgba(0,65,90,0.12)] border border-white/80 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#00415a] via-[#447d9b] to-[#10b981]"></div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch gap-6 lg:gap-8">
                
                {{-- Left Side: Identitas & Metadata Lengkap Ujian --}}
                <div class="flex-1 flex flex-col justify-between space-y-4">
                    
                    {{-- Badges Header Row --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-blue-50 to-cyan-50 text-blue-700 rounded-xl text-xs font-bold border border-blue-200 shadow-2xs">
                            <i class="bi bi-card-checklist text-blue-600"></i> {{ $ujian->jenis_ujian }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-200 shadow-2xs">
                            <i class="bi bi-building text-indigo-600"></i> Kelas {{ $kelas->kelas ?? '-' }}
                        </span>
                        @if($ujian->is_susulan)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-xl text-xs font-bold border border-amber-300 shadow-2xs">
                                <i class="bi bi-clock-history text-amber-600"></i> Ujian Susulan
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-medium border border-slate-200">
                            <i class="bi bi-shield-check text-[#00415a]"></i> Pengawas: <b class="text-slate-800 ml-0.5">{{ Auth::user()->nama_lengkap }}</b>
                        </span>
                    </div>
                    
                    {{-- Judul Ujian --}}
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold-custom text-[#00415a] tracking-tight leading-snug">
                            {{ $ujian->nama_ujian }}
                        </h1>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 flex items-center gap-2">
                            <span>Mata Pelajaran: <b class="text-[#00415a] font-bold">{{ $mapel->nama_mapel }}</b></span>
                        </p>
                    </div>

                    {{-- Mini Cards Metadata --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        
                        {{-- 1. Tanggal Pelaksanaan --}}
                        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 flex items-center gap-3 shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-blue-100/80 text-[#00415a] flex items-center justify-center text-base flex-shrink-0">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider truncate">Tanggal</p>
                                <p class="text-xs font-bold text-gray-800 truncate">{{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>

                        {{-- 2. Jadwal Ujian --}}
                        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 flex items-center gap-3 shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-cyan-100/80 text-cyan-800 flex items-center justify-center text-base flex-shrink-0">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider truncate">Jadwal</p>
                                <p class="text-xs font-mono font-bold text-gray-800 truncate" id="header-jadwal-text">{{ $start->format('H:i') }} - {{ $end->format('H:i') }} WIB</p>
                            </div>
                        </div>

                        {{-- 3. Durasi Pengerjaan --}}
                        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-3 flex items-center gap-3 shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100/80 text-emerald-800 flex items-center justify-center text-base flex-shrink-0">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider truncate">Durasi Ujian</p>
                                <p class="text-xs font-bold text-gray-800 truncate" id="header-durasi-text">{{ $ujian->durasi_menit }} Menit</p>
                            </div>
                        </div>

                    </div>

                    {{-- 4. KONTROL AKSES BROWSER WEB (PER SISWA) --}}
                    <div class="bg-gradient-to-r from-slate-50 via-blue-50/40 to-slate-50 border border-slate-200/90 rounded-2xl p-3.5 sm:p-4 flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#00415a] text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                                <i class="bi bi-display"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs sm:text-sm text-[#00415a]">Izin Ujian Dari Komputer</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-sky-100 text-sky-800 border border-sky-300">
                                        Kontrol Per Siswa
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    Secara default seluruh siswa wajib menggunakan HP (Aplikasi Mobile). Gunakan tombol pada kolom <b>"Izin Akses Ujian"</b> di tabel bawah jika ada siswa yang mengerjakan via komputer lab sekolah.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right Side: Standout Digital Timer Widget & Control Center --}}
                <div class="flex flex-col justify-between gap-3 lg:w-84 flex-shrink-0">
                    
                    @if($isPreExam)
                    {{-- Widget Mode Pra-Ujian Standout Sunset Amber --}}
                    <div class="relative overflow-hidden p-5 rounded-2xl text-white shadow-xl flex-1 flex flex-col justify-between" 
                         style="background: linear-gradient(135deg, #7c2d12 0%, #b45309 50%, #d97706 100%); border: 1.5px solid rgba(251, 191, 36, 0.5); box-shadow: 0 10px 25px -5px rgba(180, 83, 9, 0.4);">
                        
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-hourglass-split animate-spin text-amber-200 text-sm"></i>
                                <span class="text-[11px] font-bold text-amber-100 uppercase tracking-wider">Pra-Ujian (Menunggu)</span>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full" 
                                  style="background: rgba(0, 0, 0, 0.25); color: #fde68a; border: 1px solid rgba(255, 255, 255, 0.2);">
                                Mulai {{ $start->format('H:i') }}
                            </span>
                        </div>

                        <div class="rounded-xl p-3 text-center my-1" 
                             style="background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.4);">
                            <span class="text-[10px] text-amber-200 uppercase font-bold tracking-widest block mb-0.5">Waktu Menuju Mulai</span>
                            <div class="flex items-center justify-center gap-1.5 font-mono text-3xl md:text-4xl font-bold text-white tracking-wider" 
                                 style="text-shadow: 0 0 12px rgba(255, 255, 255, 0.4);">
                                <span id="cd-hours">00</span>:<span id="cd-minutes">00</span>:<span id="cd-seconds">00</span>
                            </div>
                        </div>

                        <div class="text-[10px] text-amber-100/90 text-center pt-0.5">
                            Otomatis aktif begitu countdown selesai
                        </div>
                    </div>
                    @elseif($isFinished)
                    {{-- Widget Mode Selesai Standout Dark Slate --}}
                    <div class="relative overflow-hidden p-5 rounded-2xl text-white shadow-xl flex-1 flex flex-col justify-between" 
                         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1.5px solid #334155; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.4);">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-check2-all text-emerald-400 text-lg"></i>
                            <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Ujian Telah Selesai</span>
                        </div>
                        <div class="rounded-xl p-3 text-center my-1" 
                             style="background: rgba(0, 0, 0, 0.35); border: 1px solid rgba(255, 255, 255, 0.08);">
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest block mb-0.5">Batas Waktu Berakhir</span>
                            <span class="text-2xl font-bold text-white font-mono block">{{ $end->format('H:i') }} WIB</span>
                        </div>
                    </div>
                    @else
                    {{-- Widget Mode Sedang Berlangsung (Ultra Standout Neon Digital Clock) --}}
                    <div class="relative overflow-hidden p-5 rounded-2xl text-white shadow-xl flex-1 flex flex-col justify-between" 
                         style="background: linear-gradient(135deg, #002233 0%, #003b52 50%, #00506e 100%); border: 1.5px solid rgba(16, 185, 129, 0.4); box-shadow: 0 10px 25px -5px rgba(0, 34, 51, 0.4), 0 0 15px rgba(16, 185, 129, 0.12);">
                        
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-80"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400" style="box-shadow: 0 0 8px #34d399;"></span>
                                </span>
                                <span class="text-[11px] font-bold text-emerald-300 uppercase tracking-wider">Sedang Berlangsung</span>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full" 
                                  style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                                Live Monitor
                            </span>
                        </div>

                        {{-- Digital Inset Clock Box --}}
                        <div class="rounded-xl p-3 text-center my-1" 
                             style="background: rgba(0, 0, 0, 0.45); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.5);">
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest block mb-0.5">Sisa Waktu Ujian</span>
                            <span id="ongoing-countdown" class="font-mono text-3xl md:text-4xl font-bold tracking-wider block" 
                                  style="color: #10b981; text-shadow: 0 0 14px rgba(16, 185, 129, 0.65);">
                                --:--:--
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-blue-200/80 pt-1">
                            <span>Selesai: <b id="timer-selesai-text" class="text-white font-mono">{{ $end->format('H:i') }} WIB</b></span>
                            <span>Durasi: <b id="timer-durasi-text" class="text-white">{{ $ujian->durasi_menit }} Menit</b></span>
                        </div>
                    </div>
                    @endif

                    {{-- Action Buttons (Atur Waktu, Selesaikan, & Refresh) --}}
                    <div class="flex items-center gap-2">
                        @if(!$isFinished)
                        <button type="button" onclick="openTimeModal()" 
                                class="flex-1 py-2.5 px-3 text-white rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 transform active:scale-95"
                                style="background: linear-gradient(135deg, #00415a 0%, #005a7d 100%);">
                            <i class="bi bi-stopwatch-fill text-sm text-cyan-300"></i>
                            <span>Atur Waktu</span>
                        </button>

                        <button type="button" onclick="openFinishModal()" 
                                class="py-2.5 px-3 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-xl font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5 border border-red-200"
                                title="Selesaikan Pelaksanaan Ujian Sekarang">
                            <i class="bi bi-stop-circle-fill text-sm"></i>
                            <span>Selesaikan</span>
                        </button>
                        @endif

                        <button type="button" onclick="manualRefresh()" 
                                class="py-2.5 px-3 bg-white text-[#00415a] hover:bg-[#00415a] hover:text-white rounded-xl font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1"
                                style="border: 1.5px solid #cbd5e1;"
                                title="Muat Ulang Data Pemantauan">
                            <i id="refresh-icon" class="bi bi-arrow-clockwise text-base"></i>
                            <span class="hidden sm:inline">Refresh</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>

        {{-- B. 4 KARTU STATISTIK SISWA --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- 1. Total Peserta --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Siswa</span>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="kpi-total" class="text-2xl md:text-3xl font-bold-custom text-[#00415a]">{{ $totalSiswa }}</span>
                    <span class="text-xs font-medium text-gray-400">Peserta</span>
                </div>
            </div>

            {{-- 2. Sedang Mengerjakan --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Mengerjakan</span>
                    <div class="w-11 h-11 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="kpi-sedang" class="text-2xl md:text-3xl font-bold-custom text-cyan-600">{{ $jumlahSedang }}</span>
                    <span class="text-xs font-medium text-gray-400">Siswa</span>
                </div>
            </div>

            {{-- 3. Selesai Mengerjakan --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Selesai</span>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="kpi-selesai" class="text-2xl md:text-3xl font-bold-custom text-emerald-600">{{ $jumlahSelesai }}</span>
                    <span class="text-xs font-medium text-gray-400">Siswa</span>
                </div>
            </div>

            {{-- 4. Belum Mulai --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Belum Mulai</span>
                    <div class="w-11 h-11 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center text-xl">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span id="kpi-belum" class="text-2xl md:text-3xl font-bold-custom text-gray-600">{{ $jumlahBelum }}</span>
                    <span class="text-xs font-medium text-gray-400">Siswa</span>
                </div>
            </div>

        </div>

        {{-- C. TABEL PEMANTAUAN PESERTA UJIAN (PROPORTIONAL & LIVE SYNC) --}}
        <div class="bg-white rounded-3xl shadow-[0_4px_20px_rgba(0,65,90,0.06)] border border-gray-100 overflow-hidden">
            
            {{-- Toolbar Filter & Search --}}
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 bg-gray-50/60">
                <div class="flex items-center gap-3">
                    <div>
                        <h3 class="text-lg font-bold-custom text-[#00415a]">Daftar Pengerjaan Siswa</h3>
                        <p class="text-xs text-gray-500">Pantau kehadiran pengerjaan, atur izin akses komputer lab, dan reset ujian siswa</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Search Input --}}
                    <div class="relative flex-grow sm:flex-grow-0 sm:w-72">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama atau NISN..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>

                    {{-- Filter Status --}}
                    <div class="relative">
                        <select id="statusFilter" class="px-4 py-2.5 bg-white border border-gray-200 rounded-2xl text-xs font-bold text-gray-700 outline-none cursor-pointer focus:ring-2 focus:ring-blue-500 appearance-none pr-9">
                            <option value="all" id="opt-all">Semua Status ({{ $totalSiswa }})</option>
                            <option value="Sedang Mengerjakan" id="opt-sedang">Sedang Mengerjakan ({{ $jumlahSedang }})</option>
                            <option value="Selesai" id="opt-selesai">Selesai ({{ $jumlahSelesai }})</option>
                            <option value="Belum Mulai" id="opt-belum">Belum Mulai ({{ $jumlahBelum }})</option>
                        </select>
                        <i class="bi bi-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>

            {{-- Table View with Fixed Proportions & Single-Line Layout --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap divide-y divide-gray-100" id="monitoringTable">
                    <thead class="bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">No</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Nama Siswa</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">NISN</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Izin Akses Ujian</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Status Pengerjaan</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Mulai</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Selesai</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="monitoringTableBody">
                        @forelse($daftarMonitoring as $index => $row)
                            <tr class="hover:bg-blue-50/30 transition-colors student-row" 
                                id="row-student-{{ $row->id }}"
                                data-student-id="{{ $row->id }}" 
                                data-status="{{ $row->status }}" 
                                data-name="{{ strtolower($row->nama_lengkap) }}" 
                                data-nisn="{{ $row->nisn }}">
                                
                                <td class="py-3.5 px-4 text-center text-xs text-gray-400 font-semibold whitespace-nowrap align-middle">{{ $index + 1 }}</td>
                                
                                <td class="py-3.5 px-4 whitespace-nowrap align-middle">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[#00415a] text-sm leading-tight">{{ $row->nama_lengkap }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-xs font-mono text-gray-600 font-bold whitespace-nowrap align-middle">
                                    {{ $row->nisn }}
                                </td>

                                {{-- Kolom Izin Akses Web/Mobile Per Siswa --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap align-middle" id="web-access-cell-{{ $row->id }}">
                                    @if($row->allow_web)
                                        <button type="button" 
                                                onclick="toggleSiswaWeb('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                id="btn-web-{{ $row->id }}"
                                                class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                                title="Klik untuk mengunci kembali ke Aplikasi Mobile">
                                            <i class="bi bi-display text-emerald-600 text-sm"></i>
                                            <span>Web Diizinkan</span>
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="toggleSiswaWeb('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                id="btn-web-{{ $row->id }}"
                                                class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-semibold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                                title="Klik untuk mengizinkan siswa mengerjakan via Browser Web Komputer">
                                            <i class="bi bi-phone text-slate-500 text-sm"></i>
                                            <span>Khusus Mobile</span>
                                        </button>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap align-middle" id="status-cell-{{ $row->id }}">
                                    @if($row->status == 'Pelanggaran')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $row->keterangan_pelanggaran ?? 'Pelanggaran keluar aplikasi terdeteksi' }}">
                                            <i class="bi bi-exclamation-triangle-fill text-rose-500"></i> Pelanggaran
                                        </span>
                                    @elseif($row->status == 'Waktu Habis')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="bi bi-clock-history text-amber-500"></i> Waktu Habis
                                        </span>
                                    @elseif($row->status == 'Selesai')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="bi bi-check-circle-fill text-emerald-500"></i> Selesai
                                        </span>
                                    @elseif($row->status == 'Ujian Dijeda')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 animate-pulse" title="Ujian sedang dijeda oleh Pengawas (Izin keluar ruangan)">
                                            <i class="bi bi-pause-circle-fill text-amber-600"></i> Dijeda (Izin)
                                        </span>
                                    @elseif($row->status == 'Sesi Terkunci')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-50 text-orange-700 border border-orange-200 animate-pulse" title="Sesi terputus. Siswa butuh izin buka kunci pengawas untuk masuk kembali.">
                                            <i class="bi bi-lock-fill text-orange-500"></i> Sesi Terkunci
                                        </span>
                                    @elseif($row->status == 'Sedang Mengerjakan')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span> Mengerjakan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            <i class="bi bi-dash-circle text-gray-400"></i> Belum Mulai
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap align-middle" id="mulai-cell-{{ $row->id }}">
                                    @if($row->waktu_mulai != '-')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono font-medium text-gray-700">{{ $row->waktu_mulai }} WIB</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap align-middle" id="selesai-cell-{{ $row->id }}">
                                    @if($row->waktu_selesai != '-')
                                        <span class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-mono font-bold">{{ $row->waktu_selesai }} WIB</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap align-middle" id="action-cell-{{ $row->id }}">
                                    @if(!$row->can_restart || $row->status == 'Belum Mulai')
                                        <span class="text-[11px] text-gray-400 italic whitespace-nowrap">Menunggu Siswa</span>
                                    @elseif($row->is_paused && !$isFinished)
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" 
                                                    onclick="togglePauseSiswa('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                    id="btn-pause-{{ $row->id }}"
                                                    class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all transform active:scale-95 cursor-pointer"
                                                    title="Lanjutkan pengerjaan ujian siswa">
                                                <i class="bi bi-play-fill text-sm"></i>
                                                <span>Lanjutkan</span>
                                            </button>
                                            <button type="button" 
                                                    onclick="confirmRestart('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                    class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all cursor-pointer"
                                                    title="Ulangi Ujian Siswa (Reset pengerjaan)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </div>
                                    @elseif($row->is_locked_reentry && !$isFinished)
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" 
                                                    onclick="unlockSiswaReentry('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                    id="btn-unlock-{{ $row->id }}"
                                                    class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-xs transition-all transform active:scale-95 cursor-pointer"
                                                    title="Buka kunci agar siswa dapat melanjutkan ujian kembali">
                                                <i class="bi bi-unlock-fill"></i>
                                                <span>Buka Kunci</span>
                                            </button>
                                            <button type="button" 
                                                    onclick="confirmRestart('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                    class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all cursor-pointer"
                                                    title="Ulangi Ujian Siswa (Reset pengerjaan)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </div>
                                    @elseif($row->status == 'Sedang Mengerjakan' && !$isFinished)
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" 
                                                    onclick="togglePauseSiswa('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                    id="btn-pause-{{ $row->id }}"
                                                    class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                                    title="Jeda sesi ujian siswa saat izin keluar ruangan">
                                                <i class="bi bi-pause-fill text-sm"></i>
                                                <span>Jeda</span>
                                            </button>
                                            <button type="button" 
                                                    onclick="confirmRestart('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                    class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all cursor-pointer"
                                                    title="Ulangi Ujian Siswa (Reset pengerjaan)">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </div>
                                    @elseif($row->can_restart && !$isFinished)
                                        <button type="button" 
                                                onclick="confirmRestart('{{ $row->id }}', '{{ str_replace("'", "\'", $row->nama_lengkap) }}')"
                                                class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                                title="Ulangi Ujian Siswa (Reset pengerjaan agar siswa bisa mengulang)">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                            <span>Ulangi Ujian</span>
                                        </button>
                                    @elseif($row->can_restart && $isFinished)
                                        <span class="text-[11px] text-gray-400 italic whitespace-nowrap">Ujian Berakhir</span>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic whitespace-nowrap">Menunggu Siswa</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-row">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic whitespace-nowrap">
                                    Belum ada data peserta untuk ujian ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-gray-50/80 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500">
                <span>Total <b id="footer-total">{{ $totalSiswa }}</b> siswa terdaftar pada sesi ini</span>
                <span class="text-[11px] text-gray-400 italic flex items-center gap-1">
                    <i class="bi bi-shield-lock-fill text-emerald-600"></i> Nilai dan kunci jawaban dirahasiakan penuh dari ruang pengawas
                </span>
            </div>

        </div>

    </main>

    {{-- 3. MODAL ULANGI UJIAN SISWA --}}
    <div id="restartModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeRestartModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl border border-gray-100 text-center space-y-5">
                
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-3xl border border-amber-200 shadow-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold-custom text-gray-900">Buka Akses Ujian Siswa?</h3>
                    <p class="text-xs md:text-sm text-gray-500 leading-relaxed">
                        Akses pengerjaan untuk <b id="restartStudentName" class="text-gray-800"></b> akan dibuka kembali. <b>Seluruh jawaban yang sudah dipilih sebelumnya tetap tersimpan</b> dan otomatis terisi, sehingga siswa dapat langsung melanjutkan.
                    </p>
                </div>

                <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200/80 text-[11px] text-amber-800 text-left flex items-start gap-2.5">
                    <i class="bi bi-info-circle-fill text-amber-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <span>Sangat berguna jika siswa mengalami kendala perangkat, laptop mati, atau gangguan jaringan di tengah-tengah ujian.</span>
                </div>

                <form id="restartForm" action="" method="POST" class="pt-2 flex gap-3">
                    @csrf
                    <button type="button" onclick="closeRestartModal()" 
                            class="flex-1 py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-bold text-xs transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 py-3 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-amber-500/25 transition-all">
                        Ya, Buka Akses Ujian
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- 4. MODAL ATUR / TAMBAH WAKTU UJIAN --}}
    <div id="timeModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeTimeModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 md:p-8 max-w-lg w-full shadow-2xl border border-gray-100 space-y-6">
                
                <div class="flex items-center gap-3.5 border-b border-gray-100 pb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                        <i class="bi bi-stopwatch-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold-custom text-[#00415a]">Atur & Tambah Waktu Ujian</h3>
                        <p class="text-xs text-gray-500 font-medium">Sesuaikan batas waktu pengerjaan untuk seluruh peserta di ruangan</p>
                    </div>
                </div>

                <form action="{{ route('guru.pengawas.update_waktu', $ujian->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 flex justify-between items-center">
                        <div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Waktu Selesai Saat Ini</span>
                            <span class="text-lg font-mono font-bold text-gray-800" id="modal-waktu-selesai">{{ $end->format('H:i') }} WIB</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Durasi Total</span>
                            <span class="text-lg font-bold text-[#00415a]" id="modal-durasi-total">{{ $ujian->durasi_menit }} Menit</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Penyesuaian Waktu (Menit)</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="adjustTime(-10)" class="px-3.5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold text-xs border border-red-200 transition-all">-10m</button>
                            <input type="number" name="tambahan_menit" id="tambahanMenitInput" value="15" 
                                   class="flex-1 px-4 py-2.5 bg-white border border-gray-300 rounded-xl text-center font-bold text-gray-800 text-base focus:ring-2 focus:ring-blue-500 outline-none" required>
                            <button type="button" onclick="adjustTime(10)" class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-xl font-bold text-xs border border-emerald-200 transition-all">+10m</button>
                            <button type="button" onclick="adjustTime(30)" class="px-3.5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl font-bold text-xs border border-blue-200 transition-all">+30m</button>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2">* Masukkan nilai positif untuk menambah menit (misal: 15), atau negatif untuk memajukan waktu.</p>
                    </div>

                    <div class="pt-3 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeTimeModal()" 
                                class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-bold text-xs transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="py-2.5 px-6 bg-[#00415a] hover:bg-[#447d9b] text-white rounded-2xl font-bold text-xs shadow-md transition-all">
                            Simpan Perubahan Waktu
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- 5. MODAL SELESAIKAN UJIAN OLEH PENGAWAS --}}
    <div id="finishModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeFinishModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl border border-gray-100 text-center space-y-5">
                
                <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mx-auto text-3xl border border-red-200 shadow-sm">
                    <i class="bi bi-stop-circle-fill"></i>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold-custom text-gray-900">Selesaikan Ujian Sekarang?</h3>
                    <p class="text-xs md:text-sm text-gray-500 leading-relaxed">
                        Pelaksanaan ujian <b class="text-gray-800">{{ $ujian->nama_ujian }}</b> akan dihentikan saat ini juga. Siswa yang masih mengerjakan tidak akan dapat melanjutkan lagi.
                    </p>
                </div>

                <div class="p-3.5 bg-red-50 rounded-2xl border border-red-200/80 text-[11px] text-red-800 text-left flex items-start gap-2.5">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 text-sm mt-0.5 flex-shrink-0"></i>
                    <span>Pastikan seluruh siswa di ruangan telah mengumpulkan atau batas pelaksanaan ujian memang ingin diakhiri lebih awal oleh pengawas.</span>
                </div>

                <form action="{{ route('guru.pengawas.force_finish', $ujian->id) }}" method="POST" class="pt-2 flex gap-3">
                    @csrf
                    <button type="button" onclick="closeFinishModal()" 
                            class="flex-1 py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-bold text-xs transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 py-3 px-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold text-xs shadow-lg shadow-red-500/25 transition-all">
                        Ya, Selesaikan Ujian
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT & REAL-TIME POLLING --}}
    <script>
        // 1. MODAL RESTART / ULANGI
        function confirmRestart(siswaId, namaSiswa) {
            document.getElementById('restartStudentName').innerText = namaSiswa;
            let url = "{{ route('guru.pengawas.restart', ['ujian' => $ujian->id, 'siswa' => ':siswa_id']) }}";
            document.getElementById('restartForm').action = url.replace(':siswa_id', siswaId);
            document.getElementById('restartModal').classList.remove('hidden');
        }

        function closeRestartModal() {
            document.getElementById('restartModal').classList.add('hidden');
        }

        // 2. MODAL TIME
        function openTimeModal() {
            document.getElementById('timeModal').classList.remove('hidden');
        }

        function closeTimeModal() {
            document.getElementById('timeModal').classList.add('hidden');
        }

        // 3. MODAL FINISH
        function openFinishModal() {
            document.getElementById('finishModal').classList.remove('hidden');
        }

        function closeFinishModal() {
            document.getElementById('finishModal').classList.add('hidden');
        }

        function adjustTime(delta) {
            const input = document.getElementById('tambahanMenitInput');
            let current = parseInt(input.value) || 0;
            input.value = current + delta;
        }

        // 4. SEARCH & FILTER TABLE
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');

        function filterTable() {
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedStatus = statusFilter ? statusFilter.value : 'all';
            const rows = document.querySelectorAll('.student-row');

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const nisn = row.getAttribute('data-nisn') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = name.includes(query) || nisn.includes(query);
                const matchesStatus = (selectedStatus === 'all' || status === selectedStatus);

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (statusFilter) statusFilter.addEventListener('change', filterTable);

        // 5. MANUAL REFRESH (ANIMATED)
        function manualRefresh() {
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.classList.add('animate-spin');
            fetchLiveData().finally(() => {
                setTimeout(() => {
                    if (icon) icon.classList.remove('animate-spin');
                }, 500);
            });
        }

        // 6. REAL-TIME LIVE DATA POLLING (TANPA REFRESH HALAMAN)
        const liveDataUrl = "{{ route('guru.pengawas.live_data', $ujian->id) }}";
        let isPolling = false;

        async function fetchLiveData() {
            if (isPolling) return;
            isPolling = true;

            try {
                const response = await fetch(liveDataUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;
                const result = await response.json();
                if (!result.success) return;

                // A. Update KPI Counters
                const kpiTotal = document.getElementById('kpi-total');
                const kpiSedang = document.getElementById('kpi-sedang');
                const kpiSelesai = document.getElementById('kpi-selesai');
                const kpiBelum = document.getElementById('kpi-belum');
                const footerTotal = document.getElementById('footer-total');

                if (kpiTotal) kpiTotal.innerText = result.totalSiswa;
                if (kpiSedang) kpiSedang.innerText = result.jumlahSedang;
                if (kpiSelesai) kpiSelesai.innerText = result.jumlahSelesai;
                if (kpiBelum) kpiBelum.innerText = result.jumlahBelum;
                if (footerTotal) footerTotal.innerText = result.totalSiswa;

                // B. Update Filter Dropdown Options
                const optAll = document.getElementById('opt-all');
                const optSedang = document.getElementById('opt-sedang');
                const optSelesai = document.getElementById('opt-selesai');
                const optBelum = document.getElementById('opt-belum');

                if (optAll) optAll.innerText = `Semua Status (${result.totalSiswa})`;
                if (optSedang) optSedang.innerText = `Sedang Mengerjakan (${result.jumlahSedang})`;
                if (optSelesai) optSelesai.innerText = `Selesai (${result.jumlahSelesai})`;
                if (optBelum) optBelum.innerText = `Belum Mulai (${result.jumlahBelum})`;

                // C. Update Table Rows Secara Real-Time
                if (Array.isArray(result.data)) {
                    result.data.forEach(siswa => {
                        const row = document.getElementById(`row-student-${siswa.id}`);
                        if (!row) return;

                        // Update row data-status
                        row.setAttribute('data-status', siswa.status);

                        // 1. Update Status Cell
                        const statusCell = document.getElementById(`status-cell-${siswa.id}`);
                        if (statusCell) {
                            if (siswa.status === 'Pelanggaran') {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200" title="${siswa.keterangan_pelanggaran || 'Pelanggaran keluar aplikasi terdeteksi'}">
                                        <i class="bi bi-exclamation-triangle-fill text-rose-500"></i> Pelanggaran
                                    </span>
                                `;
                            } else if (siswa.status === 'Waktu Habis') {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="bi bi-clock-history text-amber-500"></i> Waktu Habis
                                    </span>
                                `;
                            } else if (siswa.status === 'Selesai') {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="bi bi-check-circle-fill text-emerald-500"></i> Selesai
                                    </span>
                                `;
                            } else if (siswa.status === 'Ujian Dijeda') {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 animate-pulse" title="Ujian sedang dijeda oleh Pengawas (Izin keluar ruangan)">
                                        <i class="bi bi-pause-circle-fill text-amber-600"></i> Dijeda (Izin)
                                    </span>
                                `;
                            } else if (siswa.status === 'Sesi Terkunci') {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-50 text-orange-700 border border-orange-200 animate-pulse" title="Sesi terputus. Siswa butuh izin buka kunci pengawas untuk masuk kembali.">
                                        <i class="bi bi-lock-fill text-orange-500"></i> Sesi Terkunci
                                    </span>
                                `;
                            } else if (siswa.status === 'Sedang Mengerjakan') {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span> Mengerjakan
                                    </span>
                                `;
                            } else {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        <i class="bi bi-dash-circle text-gray-400"></i> Belum Mulai
                                    </span>
                                `;
                            }
                        }

                        // 2. Update Web Access Cell (Per Siswa)
                        const webCell = document.getElementById(`web-access-cell-${siswa.id}`);
                        if (webCell) {
                            const cleanName = siswa.nama_lengkap.replace(/'/g, "\\'");
                            if (siswa.allow_web) {
                                webCell.innerHTML = `
                                    <button type="button" 
                                            onclick="toggleSiswaWeb('${siswa.id}', '${cleanName}')"
                                            id="btn-web-${siswa.id}"
                                            class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                            title="Klik untuk mengunci kembali ke Aplikasi Mobile">
                                        <i class="bi bi-display text-emerald-600 text-sm"></i>
                                        <span>Web Diizinkan</span>
                                    </button>
                                `;
                            } else {
                                webCell.innerHTML = `
                                    <button type="button" 
                                            onclick="toggleSiswaWeb('${siswa.id}', '${cleanName}')"
                                            id="btn-web-${siswa.id}"
                                            class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-semibold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                            title="Klik untuk mengizinkan siswa mengerjakan via Browser Web Komputer">
                                        <i class="bi bi-phone text-slate-500 text-sm"></i>
                                        <span>Khusus Mobile</span>
                                    </button>
                                `;
                            }
                        }

                        // 3. Update Mulai Cell
                        const mulaiCell = document.getElementById(`mulai-cell-${siswa.id}`);
                        if (mulaiCell) {
                            if (siswa.waktu_mulai && siswa.waktu_mulai !== '-') {
                                mulaiCell.innerHTML = `<span class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono font-medium text-gray-700">${siswa.waktu_mulai}</span>`;
                            } else {
                                mulaiCell.innerHTML = `<span class="text-gray-300">-</span>`;
                            }
                        }

                        // 4. Update Selesai Cell
                        const selesaiCell = document.getElementById(`selesai-cell-${siswa.id}`);
                        if (selesaiCell) {
                            if (siswa.waktu_selesai && siswa.waktu_selesai !== '-') {
                                selesaiCell.innerHTML = `<span class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-mono font-bold">${siswa.waktu_selesai}</span>`;
                            } else {
                                selesaiCell.innerHTML = `<span class="text-gray-300">-</span>`;
                            }
                        }

                        // 5. Update Action Cell (Jeda/Lanjutkan, Buka Kunci & Ulangi Ujian Button)
                        const actionCell = document.getElementById(`action-cell-${siswa.id}`);
                        if (actionCell) {
                            const cleanName = siswa.nama_lengkap.replace(/'/g, "\\'");
                            if (!siswa.can_restart || siswa.status === 'Belum Mulai') {
                                actionCell.innerHTML = `<span class="text-[11px] text-gray-400 italic whitespace-nowrap">Menunggu Siswa</span>`;
                            } else if (siswa.is_paused && !result.isFinished) {
                                actionCell.innerHTML = `
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" 
                                                onclick="togglePauseSiswa('${siswa.id}', '${cleanName}')"
                                                id="btn-pause-${siswa.id}"
                                                class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all transform active:scale-95 cursor-pointer"
                                                title="Lanjutkan pengerjaan ujian siswa">
                                            <i class="bi bi-play-fill text-sm"></i>
                                            <span>Lanjutkan</span>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmRestart('${siswa.id}', '${cleanName}')"
                                                class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all cursor-pointer"
                                                title="Ulangi Ujian Siswa (Reset pengerjaan)">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                `;
                            } else if (siswa.is_locked_reentry && !result.isFinished) {
                                actionCell.innerHTML = `
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" 
                                                onclick="unlockSiswaReentry('${siswa.id}', '${cleanName}')"
                                                id="btn-unlock-${siswa.id}"
                                                class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-xs transition-all transform active:scale-95 cursor-pointer"
                                                title="Buka kunci agar siswa dapat melanjutkan ujian kembali">
                                            <i class="bi bi-unlock-fill"></i>
                                            <span>Buka Kunci</span>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmRestart('${siswa.id}', '${cleanName}')"
                                                class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all cursor-pointer"
                                                title="Ulangi Ujian Siswa (Reset pengerjaan)">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                `;
                            } else if (siswa.status === 'Sedang Mengerjakan' && !result.isFinished) {
                                actionCell.innerHTML = `
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" 
                                                onclick="togglePauseSiswa('${siswa.id}', '${cleanName}')"
                                                id="btn-pause-${siswa.id}"
                                                class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                                title="Jeda sesi ujian siswa saat izin keluar ruangan">
                                            <i class="bi bi-pause-fill text-sm"></i>
                                            <span>Jeda</span>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmRestart('${siswa.id}', '${cleanName}')"
                                                class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all cursor-pointer"
                                                title="Ulangi Ujian Siswa (Reset pengerjaan)">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                `;
                            } else if (siswa.can_restart && !result.isFinished) {
                                actionCell.innerHTML = `
                                    <button type="button" 
                                            onclick="confirmRestart('${siswa.id}', '${cleanName}')"
                                            class="inline-flex items-center justify-center whitespace-nowrap gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-300 hover:border-amber-600 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                            title="Ulangi Ujian Siswa (Reset pengerjaan agar siswa bisa mengulang)">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        <span>Ulangi Ujian</span>
                                    </button>
                                `;
                            } else if (siswa.can_restart && result.isFinished) {
                                actionCell.innerHTML = `<span class="text-[11px] text-gray-400 italic whitespace-nowrap">Ujian Berakhir</span>`;
                            } else {
                                actionCell.innerHTML = `<span class="text-[11px] text-gray-400 italic whitespace-nowrap">Menunggu Siswa</span>`;
                            }
                        }
                    });
                }

                // D. Re-apply current filter & search
                filterTable();

            } catch (err) {
                console.error('Error fetching live data:', err);
            } finally {
                isPolling = false;
            }
        }

        // Jalankan Live Polling otomatis setiap 4 detik
        setInterval(fetchLiveData, 4000);

        // 7. COUNTDOWN TIMERS
        @if($isPreExam)
            // Pre-Exam Countdown: Refresh page when reaches 00:00
            let secondsLeft = {{ (int) $detikKeMulai }};

            function updatePreExamCountdown() {
                if (secondsLeft <= 0) {
                    window.location.reload();
                    return;
                }

                const hours = Math.floor(secondsLeft / 3600);
                const minutes = Math.floor((secondsLeft % 3600) / 60);
                const seconds = secondsLeft % 60;

                const pad = (n) => String(n).padStart(2, '0');
                const cdH = document.getElementById('cd-hours');
                const cdM = document.getElementById('cd-minutes');
                const cdS = document.getElementById('cd-seconds');

                if (cdH) cdH.innerText = pad(hours);
                if (cdM) cdM.innerText = pad(minutes);
                if (cdS) cdS.innerText = pad(seconds);

                secondsLeft--;
            }

            updatePreExamCountdown();
            setInterval(updatePreExamCountdown, 1000);
        @endif

        @if(!$isPreExam && !$isFinished)
            // Ongoing Exam Remaining Time Countdown
            let ongoingSeconds = {{ (int) $detikSisaUjian }};

            function updateOngoingCountdown() {
                if (ongoingSeconds <= 0) {
                    const el = document.getElementById('ongoing-countdown');
                    if (el) el.innerText = "00:00:00 (Waktu Habis)";
                    return;
                }

                const hours = Math.floor(ongoingSeconds / 3600);
                const minutes = Math.floor((ongoingSeconds % 3600) / 60);
                const seconds = ongoingSeconds % 60;

                const pad = (n) => String(n).padStart(2, '0');
                const el = document.getElementById('ongoing-countdown');
                if (el) {
                    el.innerText = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                }

                ongoingSeconds--;
            }

            updateOngoingCountdown();
            setInterval(updateOngoingCountdown, 1000);
        @endif

        // 8. TOGGLE IZIN AKSES BROWSER WEB PER SISWA
        async function toggleSiswaWeb(siswaId, namaSiswa) {
            const btn = document.getElementById(`btn-web-${siswaId}`);
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50');
            }

            try {
                const url = `{{ url('/guru/pengawas/ujian/' . $ujian->id . '/siswa') }}/${siswaId}/toggle-web-access`;
                const response = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        "Accept": "application/json"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const cleanName = namaSiswa.replace(/'/g, "\\'");
                    const webCell = document.getElementById(`web-access-cell-${siswaId}`);
                    if (webCell) {
                        if (data.allow_web) {
                            webCell.innerHTML = `
                                <button type="button" 
                                        onclick="toggleSiswaWeb('${siswaId}', '${cleanName}')"
                                        id="btn-web-${siswaId}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                        title="Klik untuk mengunci kembali ke Aplikasi Mobile">
                                    <i class="bi bi-display text-emerald-600"></i>
                                    <span>Web Diizinkan</span>
                                </button>
                            `;
                        } else {
                            webCell.innerHTML = `
                                <button type="button" 
                                        onclick="toggleSiswaWeb('${siswaId}', '${cleanName}')"
                                        id="btn-web-${siswaId}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-semibold shadow-2xs transition-all transform active:scale-95 cursor-pointer"
                                        title="Klik untuk mengizinkan siswa mengerjakan via Browser Web Komputer">
                                    <i class="bi bi-phone text-slate-500"></i>
                                    <span>Khusus Mobile</span>
                                </button>
                            `;
                        }
                    }

                    showAppToast(data.message, 'success');
                } else {
                    showAppToast(data.message || 'Gagal mengubah izin akses web siswa.', 'error');
                }
            } catch (error) {
                console.error('Error toggling siswa web access:', error);
                showAppToast('Terjadi gangguan jaringan saat menghubungi server.', 'error');
            } finally {
                const refreshedBtn = document.getElementById(`btn-web-${siswaId}`);
                if (refreshedBtn) {
                    refreshedBtn.disabled = false;
                    refreshedBtn.classList.remove('opacity-50');
                }
            }
        }

        // 9. BUKA KUNCI MASUK ULANG (RE-ENTRY UNLOCK)
        async function unlockSiswaReentry(siswaId, namaSiswa) {
            const btn = document.getElementById(`btn-unlock-${siswaId}`);
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50');
            }

            try {
                const url = `{{ url('/guru/pengawas/ujian/' . $ujian->id . '/siswa') }}/${siswaId}/unlock-reentry`;
                const response = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        "Accept": "application/json"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showAppToast(data.message, 'success');
                    fetchLiveData(); // Refresh status langsung
                } else {
                    showAppToast(data.message || 'Gagal membuka kunci masuk ulang siswa.', 'error');
                }
            } catch (error) {
                console.error('Error unlocking siswa reentry:', error);
                showAppToast('Terjadi gangguan jaringan saat menghubungi server.', 'error');
            } finally {
                const refreshedBtn = document.getElementById(`btn-unlock-${siswaId}`);
                if (refreshedBtn) {
                    refreshedBtn.disabled = false;
                    refreshedBtn.classList.remove('opacity-50');
                }
            }
        }

        // 10. TOGGLE JEDA / LANJUTKAN UJIAN SISWA (STOP & CONTINUE)
        async function togglePauseSiswa(siswaId, namaSiswa) {
            const btn = document.getElementById(`btn-pause-${siswaId}`);
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50');
            }

            try {
                const url = `{{ url('/guru/pengawas/ujian/' . $ujian->id . '/siswa') }}/${siswaId}/toggle-pause`;
                const response = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        "Accept": "application/json"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showAppToast(data.message, 'success');
                    fetchLiveData(); // Refresh status live tabel seketika
                } else {
                    showAppToast(data.message || 'Gagal mengubah status jeda ujian siswa.', 'error');
                }
            } catch (error) {
                console.error('Error toggling pause siswa:', error);
                showAppToast('Terjadi gangguan jaringan saat menghubungi server.', 'error');
            } finally {
                const refreshedBtn = document.getElementById(`btn-pause-${siswaId}`);
                if (refreshedBtn) {
                    refreshedBtn.disabled = false;
                    refreshedBtn.classList.remove('opacity-50');
                }
            }
        }

        // 11. NATIVE TOAST NOTIFICATION COMPONENT (TEMPLATE ALERT ASLI APLIKASI)
        function showAppToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `p-4 rounded-2xl shadow-xl flex items-center justify-between border transition-all duration-300 transform translate-x-12 opacity-0 pointer-events-auto ${
                type === 'success' 
                    ? 'bg-emerald-50 border-emerald-300 text-emerald-900 border-l-4 border-l-emerald-500' 
                    : 'bg-red-50 border-red-300 text-red-900 border-l-4 border-l-red-500'
            }`;

            const icon = type === 'success' 
                ? '<div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs"><i class="bi bi-check-lg text-lg font-bold"></i></div>'
                : '<div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs"><i class="bi bi-exclamation-triangle-fill text-sm"></i></div>';

            toast.innerHTML = `
                <div class="flex items-center gap-3 pr-2">
                    ${icon}
                    <p class="text-xs sm:text-sm font-semibold leading-snug">${message}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 p-1 ml-2 cursor-pointer flex-shrink-0">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            `;

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-12', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });

            // Auto dismiss after 3.5 seconds
            setTimeout(() => {
                toast.classList.remove('translate-x-0', 'opacity-100');
                toast.classList.add('translate-x-12', 'opacity-0');
                setTimeout(() => toast.remove(), 350);
            }, 3500);
        }
    </script>

</body>
</html>
