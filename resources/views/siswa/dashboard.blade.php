@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link active rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
            <i class="bi bi-award"></i> <span>Nilai</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link rounded-xl">
            <i class="bi bi-file-earmark-text"></i> <span>Arsip Soal Siswa</span>
        </a>
    </div>
@endsection

@section('content')

    {{-- 1. PREMIUM WELCOME BANNER --}}
    <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 rounded-[2rem] p-8 sm:p-10 mb-10 shadow-xl shadow-blue-900/10 overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mt-2">
        {{-- Background Ornament --}}
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 opacity-10 pointer-events-none">
            <i class="bi bi-mortarboard-fill" style="font-size: 15rem;"></i>
        </div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/4 opacity-10 pointer-events-none">
            <i class="bi bi-book-half" style="font-size: 12rem;"></i>
        </div>

        {{-- Text Content --}}
        <div class="relative z-10 text-white">
            <div class="flex items-center gap-2 text-blue-200 text-xs sm:text-sm mb-2 font-medium tracking-wide">
                <i class="bi bi-calendar4-week"></i> 
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-[Poppins-Bold] tracking-tight mb-2">
                Halo, {{ explode(' ', $siswa->nama_lengkap)[0] }}! 👋
            </h1>
            <p class="text-blue-100 text-sm sm:text-base max-w-xl leading-relaxed">
                Selamat datang di panel ujian. Tetap fokus, kerjakan dengan jujur, dan berikan hasil yang terbaik hari ini.
            </p>
        </div>
        
        {{-- Profile Badge --}}
        <div class="relative z-10 flex items-center gap-4 bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20">
            <div class="text-right">
                <div class="text-xs font-bold text-blue-200 uppercase tracking-wider">Kelas Anda</div>
                <div class="text-lg font-[Poppins-Bold] text-white">{{ $siswa->kelas->kelas ?? '-' }}</div>
            </div>
            <div class="w-12 h-12 rounded-full bg-white text-blue-600 flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-person-workspace"></i>
            </div>
        </div>
    </div>

    {{-- 2. STATS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Card 1: Total Ujian --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full">
                    <i class="bi bi-check-circle-fill"></i> Terupdate
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-black text-slate-800 tracking-tight">{{ $totalUjian }}</h3>
                <p class="text-sm font-bold text-slate-400 mt-1">TOTAL UJIAN SELESAI</p>
            </div>
        </div>

        {{-- Card 2: Rata-Rata Kuis --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div>
                <h3 class="text-4xl font-black text-slate-800 tracking-tight">{{ number_format($rataRataKuis, 1) }}</h3>
                <p class="text-sm font-bold text-slate-400 mt-1">RATA-RATA KUIS</p>
                <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-1000" style="width: {{ min($rataRataKuis, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 3: Ujian Terakhir --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div>
                <h3 class="text-base font-[Poppins-Bold] text-slate-800 truncate mb-1" title="{{ $ujianTerakhir->ujian->nama_ujian ?? 'Belum ada' }}">
                    {{ $ujianTerakhir->ujian->nama_ujian ?? 'Belum ada ujian' }}
                </h3>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">UJIAN TERAKHIR</p>
                
                @if($ujianTerakhir)
                    @php $isUtsUasLast = in_array(strtoupper($ujianTerakhir->ujian->jenis_ujian ?? ''), ['UTS', 'UAS']); @endphp
                    @if(!$isUtsUasLast)
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black {{ $ujianTerakhir->nilai >= 75 ? 'text-emerald-500' : 'text-red-500' }}">
                                {{ $ujianTerakhir->nilai }}
                            </span>
                            <span class="text-xs font-medium text-slate-400">Skor Akhir</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-500 rounded-lg w-fit text-xs font-medium">
                            <i class="bi bi-lock-fill"></i> Skor dirahasiakan
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- 3. CONTENT SECTIONS --}}
    <div class="space-y-10">
        
        {{-- SECTION 1: SEDANG BERLANGSUNG --}}
        <section class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            {{-- Header Didalam Card --}}
            <div class="px-6 py-4 md:px-8 {{ $sedangBerlangsung->isEmpty() ? 'md:py-4' : 'md:py-7' }} border-b border-slate-100/60 flex flex-col sm:flex-row sm:items-center gap-4 bg-white relative z-10">
                <div class="relative flex items-center justify-center shrink-0">
                    <div class="absolute inset-0 bg-emerald-400 rounded-full animate-ping opacity-20"></div>
                    <div class="relative w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 text-emerald-600 flex items-center justify-center text-xl md:text-2xl shadow-inner border border-emerald-100/50">
                        <i class="bi bi-broadcast"></i>
                    </div>
                </div>
                <div>
                    {{-- Dinamika Tipografi: Gabungan Black dan Medium, Uppercase --}}
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight uppercase">
                        SEDANG <span class="font-medium text-slate-400">BERLANGSUNG</span>
                    </h2>
                    <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Ujian aktif yang harus Anda selesaikan sekarang.</p>
                </div>
            </div>
            
            {{-- Body Content --}}
            <div class="{{ $sedangBerlangsung->isEmpty() ? 'p-4' : 'p-6 md:p-8' }} bg-slate-50/30">
                @if($sedangBerlangsung->isEmpty())
                    <div class="py-6 text-center">
                        <div class="w-12 h-12 bg-white shadow-sm border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300">
                            <i class="bi bi-cup-hot text-xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-700 mb-0.5">Waktu Santai</h3>
                        <p class="text-slate-500 text-[11px]">Tidak ada ujian yang sedang berlangsung.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-5">
                        @foreach($sedangBerlangsung as $ujian)
                        <div class="bg-white border border-emerald-100/80 rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 group">
                            {{-- Aksen Hijau Kiri --}}
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500"></div>

                            <div class="flex items-start sm:items-center gap-5 w-full md:w-auto">
                                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 shadow-sm shrink-0">
                                    <i class="bi bi-pencil-square text-2xl"></i>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <h3 class="font-[Poppins-Bold] text-lg text-slate-800 group-hover:text-emerald-700 transition-colors">
                                            {{ $ujian->nama_ujian }}
                                        </h3>
                                        @if($ujian->is_susulan)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">Susulan</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 font-medium">
                                        <span class="flex items-center gap-1.5"><i class="bi bi-book text-slate-400"></i> {{ $ujian->mapel->nama_mapel }}</span>
                                        <span class="flex items-center gap-1.5"><i class="bi bi-person text-slate-400"></i> {{ $ujian->mapel->guru->nama_lengkap ?? 'Guru' }}</span>
                                    </div>
                                    
                                    {{-- Informasi Detail Tambahan --}}
                                    <div class="mt-4 pt-4 border-t border-slate-100/60 flex flex-wrap items-center gap-x-5 gap-y-3">
                                        {{-- Jenis --}}
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                                <i class="bi bi-tag"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Jenis</span>
                                                <span class="text-xs font-bold text-slate-700 leading-none">{{ $ujian->jenis_ujian }}</span>
                                            </div>
                                        </div>
                                        {{-- Tanggal --}}
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Tanggal</span>
                                                <span class="text-xs font-bold text-slate-700 leading-none">{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->isoFormat('dddd, D MMM Y') }}</span>
                                            </div>
                                        </div>
                                        {{-- Jam --}}
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                                                <i class="bi bi-clock"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Jam</span>
                                                <span class="text-xs font-bold text-slate-700 leading-none">{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB</span>
                                            </div>
                                        </div>
                                        {{-- Durasi --}}
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                                <i class="bi bi-hourglass-split"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Durasi</span>
                                                <span class="text-xs font-bold text-slate-700 leading-none">{{ $ujian->durasi_menit }} Menit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto mt-2 md:mt-0 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <div class="flex items-center gap-3 px-4 py-2.5 bg-red-50 border border-red-100 rounded-xl w-full sm:w-auto justify-center" id="timer-{{ $ujian->id }}" data-end="{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('Y-m-d H:i:s') }}">
                                    <i class="bi bi-stopwatch text-red-500 animate-pulse text-lg"></i>
                                    <div class="flex flex-col text-left">
                                        <span class="text-[10px] font-bold text-red-400 uppercase tracking-wide leading-none mb-1">Sisa Waktu</span>
                                        <span class="countdown font-mono font-bold text-red-600 leading-none tracking-tight">--:--:--</span>
                                    </div>
                                </div>
                                
                                <a href="{{ route('siswa.ujian.konfirmasi', $ujian->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-all font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5">
                                    <span>KERJAKAN</span>
                                    <i class="bi bi-arrow-right-short text-xl"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- SECTION 2: AKAN DATANG --}}
            <section class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden flex flex-col h-full">
                {{-- Header Didalam Card --}}
                <div class="px-6 py-6 border-b border-slate-100/60 flex items-center gap-4 bg-white">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100/50 text-amber-600 flex items-center justify-center text-xl shadow-inner border border-amber-100/50 shrink-0">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        {{-- Dinamika Tipografi --}}
                        <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight uppercase">
                            AKAN <span class="font-medium text-slate-400">DATANG</span>
                        </h2>
                        <p class="text-xs font-medium text-slate-500 mt-0.5">Jadwal ujian di masa mendatang.</p>
                    </div>
                </div>

                {{-- Body Content --}}
                <div class="flex-1 bg-slate-50/30">
                    @if($akanDatang->isEmpty())
                        <div class="p-10 text-center h-full flex flex-col items-center justify-center">
                            <p class="text-slate-400 text-sm font-medium">Tidak ada jadwal ujian mendatang.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100/80">
                            @foreach($akanDatang as $ujian)
                            <div class="p-6 hover:bg-slate-50/80 transition-colors flex items-start sm:items-center justify-between gap-4 flex-col sm:flex-row">
                                <div>
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <h3 class="font-[Poppins-Bold] text-slate-800 text-sm">{{ $ujian->nama_ujian }}</h3>
                                        <span class="text-[9px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-wider">{{ $ujian->jenis_ujian }}</span>
                                    </div>
                                    <div class="text-xs font-medium text-slate-500 mb-2">{{ $ujian->mapel->nama_mapel }}</div>
                                    <div class="text-xs font-bold text-amber-600 flex items-center gap-1.5 bg-amber-50 w-fit px-2.5 py-1 rounded-md border border-amber-100/50">
                                        <i class="bi bi-clock"></i>
                                        {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->isoFormat('D MMM, H:i') }} WIB
                                    </div>
                                </div>
                                <div class="px-3 py-1.5 rounded-lg text-[10px] font-bold bg-white text-slate-400 border border-slate-200 shadow-sm self-start sm:self-center tracking-wider">
                                    TERJADWAL
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- SECTION 3: RIWAYAT UJIAN --}}
            <section class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden flex flex-col h-full">
                {{-- Header Didalam Card --}}
                <div class="px-6 py-6 border-b border-slate-100/60 flex items-center gap-4 bg-white">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100/50 text-slate-500 flex items-center justify-center text-xl shadow-inner border border-slate-200/50 shrink-0">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        {{-- Dinamika Tipografi --}}
                        <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight uppercase">
                            RIWAYAT <span class="font-medium text-slate-400">UJIAN</span>
                        </h2>
                        <p class="text-xs font-medium text-slate-500 mt-0.5">Daftar ujian yang telah berlalu.</p>
                    </div>
                </div>
                
                {{-- Body Content --}}
                <div class="flex-1 bg-slate-50/30">
                    @if($telahBerlalu->isEmpty())
                        <div class="p-10 text-center h-full flex flex-col items-center justify-center">
                            <p class="text-slate-400 text-sm font-medium">Belum ada riwayat ujian.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100/80">
                            @foreach($telahBerlalu as $hasil)
                            <div class="p-6 hover:bg-slate-50/80 transition-colors flex items-center justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <h3 class="font-[Poppins-Bold] text-slate-800 text-sm truncate group-hover:text-blue-600">{{ $hasil->ujian->nama_ujian ?? '-' }}</h3>
                                    </div>
                                    <div class="text-xs font-medium text-slate-500 truncate mb-1">{{ $hasil->ujian->mapel->nama_mapel ?? '-' }}</div>
                                    <div class="text-[10px] font-medium text-slate-400 flex items-center gap-1">
                                        <i class="bi bi-calendar2-check"></i>
                                        {{ $hasil->ujian->tanggal_ujian ? \Carbon\Carbon::parse($hasil->ujian->tanggal_ujian)->locale('id')->isoFormat('D MMM Y') : '-' }}
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-3 shrink-0">
                                    @php $isUtsUasDash = in_array(strtoupper($hasil->ujian->jenis_ujian ?? ''), ['UTS', 'UAS']); @endphp
                                    @if($isUtsUasDash)
                                        <div class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white text-slate-400 border border-slate-200 shadow-sm flex items-center gap-1" title="Skor disembunyikan">
                                            <i class="bi bi-lock-fill"></i> SKOR
                                        </div>
                                    @else
                                        <div class="px-3.5 py-1 rounded-lg text-sm font-black shadow-sm {{ $hasil->nilai >= 75 ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
                                            {{ $hasil->nilai }}
                                        </div>
                                    @endif
                                    
                                    <a href="{{ route('siswa.ujian.detail', $hasil->ujian_id) }}" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 group/link">
                                        Detail <i class="bi bi-arrow-right transition-transform group-hover/link:translate-x-1"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

    </div>
@endsection

@section('scripts')
<script>
    // --- SCRIPT TIMER HITUNG MUNDUR ---
    document.addEventListener("DOMContentLoaded", function() {
        const timers = document.querySelectorAll('[id^="timer-"]');

        function updateTimers() {
            const now = new Date();

            timers.forEach(timer => {
                const endString = timer.getAttribute('data-end');
                const endDate = new Date(endString.replace(' ', 'T'));
                const diff = endDate - now;
                const display = timer.querySelector('.countdown');

                if (diff <= 0) {
                    display.innerText = "Berakhir";
                    timer.classList.replace('bg-red-50', 'bg-slate-100');
                    timer.classList.replace('border-red-100', 'border-slate-200');
                    timer.classList.replace('text-red-400', 'text-slate-400');
                    display.classList.replace('text-red-600', 'text-slate-500');
                    const icon = timer.querySelector('.bi-stopwatch');
                    if(icon) {
                        icon.classList.remove('text-red-500', 'animate-pulse');
                        icon.classList.add('text-slate-400');
                    }
                } else {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    const h = hours.toString().padStart(2, '0');
                    const m = minutes.toString().padStart(2, '0');
                    const s = seconds.toString().padStart(2, '0');
                    
                    display.innerText = `${h}:${m}:${s}`;
                }
            });
        }

        if(timers.length > 0) {
            setInterval(updateTimers, 1000);
            updateTimers(); 
        }
    });
</script>
@endsection