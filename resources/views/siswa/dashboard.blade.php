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
            <i class="bi bi-file-earmark-text"></i> <span>Bank Soal</span>
        </a>
    </div>
@endsection

@section('content')

    {{-- 1. HEADER & WELCOME --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <i class="bi bi-house-door"></i> Home
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Dashboard</span>
            </div>
            <h1 class="text-2xl font-[Poppins-Bold] text-darkblue tracking-tight">
                Selamat Datang, {{ explode(' ', $siswa->nama_lengkap)[0] }}! 👋
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Panel ujian siswa. Semangat belajar dan kerjakan ujian dengan jujur.
            </p>
        </div>
        
        <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl shadow-sm border border-gray-100">
            <div class="text-right hidden md:block">
                <div class="text-xs font-bold text-gray-400 uppercase">Kelas</div>
                <div class="text-sm font-[Poppins-Bold] text-darkblue">{{ $siswa->kelas->kelas ?? '-' }}</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                <i class="bi bi-person-workspace"></i>
            </div>
        </div>
    </div>

    {{-- 2. STATS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Card 1: Total Ujian --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Ujian Selesai</p>
                    <h3 class="text-3xl font-[Poppins-Bold] text-darkblue mt-2 group-hover:text-blue-600 transition-colors">{{ $totalUjian }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-journal-check"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs font-medium text-emerald-600 bg-emerald-50 w-fit px-2 py-1 rounded-lg">
                <i class="bi bi-check-circle-fill"></i> Data Terupdate
            </div>
        </div>

        {{-- Card 2: Rata-Rata Nilai --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-Rata Nilai</p>
                    <h3 class="text-3xl font-[Poppins-Bold] text-darkblue mt-2 group-hover:text-emerald-600 transition-colors">{{ number_format($rataRata, 1) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min($rataRata, 100) }}%"></div>
            </div>
        </div>

        {{-- Card 3: Ujian Terakhir --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform duration-300 group">
            <div class="flex justify-between items-start">
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ujian Terakhir</p>
                    <h3 class="text-lg font-[Poppins-Bold] text-darkblue mt-2 truncate group-hover:text-purple-600 transition-colors" title="{{ $ujianTerakhir->ujian->nama_ujian ?? '-' }}">
                        {{ $ujianTerakhir->ujian->nama_ujian ?? '-' }}
                    </h3>
                    @if($ujianTerakhir)
                        <div class="mt-1 flex items-center gap-2">
                             <span class="text-2xl font-bold {{ $ujianTerakhir->nilai >= 75 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $ujianTerakhir->nilai }}
                            </span>
                            <span class="text-xs text-gray-400">Nilai Akhir</span>
                        </div>
                    @else
                        <div class="mt-2 text-sm text-gray-400 italic">Belum ada data.</div>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. CONTENT SECTIONS --}}
    <div class="space-y-10">
        
        {{-- SECTION 1: SEDANG BERLANGSUNG --}}
        <section>
            <div class="flex items-center gap-3 mb-6">
                {{-- Icon Header --}}
                <div class="relative">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="bi bi-broadcast animate-pulse text-xl"></i>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-[Poppins-Bold] text-darkblue leading-tight">Ujian Sedang Berlangsung</h2>
                    <p class="text-xs text-gray-500">Kerjakan ujian sebelum waktu habis.</p>
                </div>
            </div>
            
            @if($sedangBerlangsung->isEmpty())
                <div class="bg-white p-8 rounded-2xl border border-dashed border-gray-200 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i class="bi bi-calendar-x text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Tidak ada ujian yang sedang berlangsung.</p>
                </div>
            @else
                <div class="bg-white border border-emerald-100 rounded-2xl shadow-[0_4px_20px_rgba(16,185,129,0.05)] overflow-hidden relative">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-emerald-50/40">
                                <tr>
                                    <th class="px-6 py-5 text-xs font-bold text-emerald-800 uppercase tracking-wider">Nama Ujian</th>
                                    <th class="px-6 py-5 text-xs font-bold text-emerald-800 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-5 text-xs font-bold text-emerald-800 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-5 text-xs font-bold text-emerald-800 uppercase tracking-wider">Waktu & Timer</th>
                                    <th class="px-6 py-5 text-xs font-bold text-emerald-800 uppercase tracking-wider text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-50">
                                @foreach($sedangBerlangsung as $ujian)
                                <tr class="hover:bg-emerald-50/20 transition-colors group">
                                    {{-- Nama Ujian --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-white border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                                                <i class="bi bi-pencil-square text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h3 class="font-[Poppins-Bold] text-darkblue text-sm group-hover:text-emerald-700 transition-colors leading-snug">
                                                        {{ $ujian->nama_ujian }}
                                                    </h3>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 animate-pulse">LIVE</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Jenis Ujian --}}
                                    <td class="px-6 py-5">
                                        <span class="inline-block text-[10px] font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200 uppercase tracking-wider">
                                            {{ $ujian->jenis_ujian }}
                                        </span>
                                    </td>

                                    {{-- Mapel & Guru --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-700 text-sm mb-1">{{ $ujian->mapel->nama_mapel }}</span>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                                <i class="bi bi-person-circle"></i>
                                                {{ $ujian->mapel->guru->nama_lengkap ?? 'Guru Mapel' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Waktu & Timer --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1.5">
                                            {{-- Timer Countdown --}}
                                            <div class="flex items-center gap-2 font-mono font-bold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg w-fit border border-red-100" 
                                                 id="timer-{{ $ujian->id }}" 
                                                 data-end="{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('Y-m-d H:i:s') }}">
                                                <i class="bi bi-stopwatch-fill animate-pulse"></i> 
                                                <span class="countdown text-sm">--:--:--</span>
                                            </div>
                                            
                                            {{-- Jam --}}
                                            <span class="text-xs text-emerald-700 font-medium flex items-center gap-1.5 ml-0.5">
                                                <i class="bi bi-clock"></i>
                                                {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <a href="{{ route('siswa.ujian.konfirmasi', $ujian->id) }}" 
                                           class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all font-bold text-sm shadow-lg hover:shadow-xl hover:-translate-y-1 ring-4 ring-emerald-50 hover:ring-emerald-100">
                                            <span>KERJAKAN</span>
                                            <i class="bi bi-arrow-right text-lg"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        {{-- SECTION 2: AKAN DATANG --}}
        <section>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-8 bg-amber-400 rounded-full"></div>
                <h2 class="text-xl font-[Poppins-Bold] text-darkblue">Jadwal Ujian Akan Datang</h2>
            </div>

            @if($akanDatang->isEmpty())
                <div class="bg-white p-8 rounded-2xl border border-dashed border-gray-200 text-center">
                    <p class="text-gray-400 italic">Belum ada jadwal ujian mendatang.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Ujian</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu Pelaksanaan</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($akanDatang as $ujian)
                                <tr class="hover:bg-amber-50/10 transition-colors">
                                    {{-- Nama Ujian --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                            <span class="text-sm font-[Poppins-Bold] text-darkblue">{{ $ujian->nama_ujian }}</span>
                                        </div>
                                    </td>

                                    {{-- Jenis --}}
                                    <td class="px-6 py-4">
                                        <span class="inline-block text-[10px] font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200 uppercase tracking-wider">
                                            {{ $ujian->jenis_ujian }}
                                        </span>
                                    </td>

                                    {{-- Mapel --}}
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-700 text-sm mb-1">{{ $ujian->mapel->nama_mapel }}</span>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                                <i class="bi bi-person-circle"></i>
                                                {{ $ujian->mapel->guru->nama_lengkap ?? 'Guru Mapel' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Waktu --}}
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-700">
                                                {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                            </span>
                                            <span class="text-xs text-amber-600 font-medium mt-1 flex items-center gap-1">
                                                <i class="bi bi-clock"></i>
                                                {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100">
                                            Terjadwal
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        {{-- SECTION 3: RIWAYAT UJIAN --}}
        <section>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-8 bg-gray-400 rounded-full"></div>
                <h2 class="text-xl font-[Poppins-Bold] text-darkblue">Riwayat Ujian</h2>
            </div>
            
            @if($telahBerlalu->isEmpty())
                <div class="bg-white p-8 rounded-2xl border border-dashed border-gray-200 text-center">
                    <p class="text-gray-400 italic">Belum ada riwayat ujian.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50/50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Ujian</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu Pelaksanaan</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Nilai</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($telahBerlalu as $hasil)
                                <tr class="hover:bg-blue-50/10 transition-colors group">
                                    {{-- Nama Ujian --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center shrink-0">
                                                <i class="bi bi-clock-history"></i>
                                            </div>
                                            <span class="text-sm font-bold text-darkblue group-hover:text-blue-600 transition-colors">
                                                {{ $hasil->ujian->nama_ujian ?? '-' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Jenis --}}
                                    <td class="px-6 py-4">
                                        <span class="inline-block text-[10px] font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200 uppercase tracking-wider">
                                            {{ $hasil->ujian->jenis_ujian }}
                                        </span>
                                    </td>

                                    {{-- Mapel --}}
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-700 text-sm mb-1">{{ $hasil->ujian->mapel->nama_mapel ?? '-' }}</span>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                                <i class="bi bi-person-circle"></i>
                                                {{ $hasil->ujian->mapel->guru->nama_lengkap ?? 'Guru Mapel' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Waktu --}}
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ $hasil->ujian->tanggal_ujian ? \Carbon\Carbon::parse($hasil->ujian->tanggal_ujian)->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                <i class="bi bi-clock"></i> 
                                                <span class="font-mono font-bold text-blue-600">
                                                    {{ \Carbon\Carbon::parse($hasil->ujian->waktu_mulai)->format('H:i') }}
                                                </span>
                                                -
                                                <span class="font-mono font-bold text-gray-600">
                                                    {{ \Carbon\Carbon::parse($hasil->ujian->waktu_selesai)->format('H:i') }}
                                                </span>
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Nilai --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-lg text-xs font-bold shadow-sm {{ $hasil->nilai >= 75 ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                            {{ $hasil->nilai }}
                                        </span>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('siswa.ujian.detail', $hasil->ujian_id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline transition-all">
                                            Lihat Detail <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

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
                const endString = timer.getAttribute('data-end'); // Format: YYYY-MM-DD HH:mm:ss
                // Pastikan format date string safe untuk browser (replace spasi dengan T)
                const endDate = new Date(endString.replace(' ', 'T'));
                
                const diff = endDate - now;
                const display = timer.querySelector('.countdown');

                if (diff <= 0) {
                    display.innerText = "Selesai";
                    timer.classList.remove('text-red-600', 'bg-red-50', 'border-red-100');
                    timer.classList.add('text-gray-500', 'bg-gray-100', 'border-gray-200');
                    timer.querySelector('.bi').classList.remove('animate-pulse');
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
    });
</script>
@endsection
