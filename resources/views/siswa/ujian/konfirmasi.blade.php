@extends('layouts.app')

@section('title', 'Persiapan Ujian')

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
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="text-center mb-6">
        <h1 class="text-2xl font-[Poppins-Bold] text-darkblue mb-1">Persiapan Ujian</h1>
        <p class="text-gray-500 text-sm">Harap baca instruksi dengan seksama sebelum memulai.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Blue Header Strip --}}
        <div class="h-1.5 bg-blue-600 w-full"></div>

        <div class="p-6 md:p-8">
            {{-- Informasi Utama --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-100 pb-6 mb-6">
                <div>
                     <span class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider border border-blue-100 mb-2">
                        <i class="bi bi-journal-check me-1"></i> {{ $ujian->mapel->nama_mapel }}
                    </span>
                    <h2 class="text-2xl font-[Poppins-Bold] text-gray-800">{{ $ujian->nama_ujian }}</h2>
                    <p class="text-gray-500 mt-1 flex items-center gap-2 text-sm">
                        <i class="bi bi-person-circle text-gray-400"></i>
                        {{ $ujian->mapel->guru->nama_lengkap ?? 'Guru Mapel' }}
                    </p>
                </div>
                
                {{-- Countdown / Info Waktu --}}
                <div class="flex items-center gap-4 bg-gray-50 p-3 rounded-xl border border-gray-100 shadow-sm">
                    <div class="text-center min-w-[80px]">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">DURASI</p>
                        @php
                            $start = \Carbon\Carbon::parse($ujian->waktu_mulai);
                            $end = \Carbon\Carbon::parse($ujian->waktu_selesai);
                            $diff = $start->diffInMinutes($end);
                        @endphp
                        <p class="text-lg font-[Poppins-Bold] text-darkblue leading-none">{{ $diff }} <span class="text-xs font-normal text-gray-500">mnt</span></p>
                    </div>
                    <div class="w-px h-6 bg-gray-200"></div>
                    <div class="text-center min-w-[80px]">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">SOAL</p>
                        <p class="text-lg font-[Poppins-Bold] text-darkblue leading-none">{{ $ujian->soals->count() }} <span class="text-xs font-normal text-gray-500">btr</span></p>
                    </div>
                </div>
            </div>

            {{-- Detail Waktu & Tata Tertib --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kolom Kiri: Waktu --}}
                <div class="space-y-4">
                    <h3 class="font-[Poppins-Bold] text-darkblue text-base mb-3 flex items-center gap-2">
                        <i class="bi bi-calendar-event text-blue-600"></i> Waktu Pelaksanaan
                    </h3>
                    <div class="bg-blue-50/40 border border-blue-100 rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-play-circle-fill text-blue-600 text-lg"></i>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">MULAI</p>
                                <p class="font-[Poppins-Bold] text-gray-800 text-sm">
                                    {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} WIB
                                </p>
                            </div>
                        </div>
                        <div class="h-8 w-px bg-blue-200/50 mx-2"></div>
                        <div class="flex items-center gap-3">
                            <i class="bi bi-stop-circle-fill text-red-500 text-lg"></i>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">SELESAI</p>
                                <p class="font-[Poppins-Bold] text-gray-800 text-sm">
                                    {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Tata Tertib --}}
                <div>
                     <h3 class="font-[Poppins-Bold] text-darkblue text-base mb-3 flex items-center gap-2">
                        <i class="bi bi-shield-exclamation text-amber-500"></i> Tata Tertib Ujian
                    </h3>
                    <ul class="bg-gray-50/50 rounded-xl border border-gray-100 p-3 space-y-2">
                        @foreach([
                            'Berdoalah sebelum mengerjakan soal.',
                            'Waktu berjalan otomatis saat tombol "Mulai" ditekan.',
                            'Dilarang menutup/minimize browser saat ujian.',
                            'Dilarang berpindah Tab (Ujian auto-submit).',
                            'Pastikan koneksi internet stabil.'
                        ] as $index => $rule)
                        <li class="flex items-start gap-2 text-xs text-gray-600 leading-tight">
                            <i class="bi bi-check-circle-fill text-blue-400 text-[10px] mt-0.5"></i>
                            <span>{{ $rule }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Warning Box --}}
            <div class="mt-6 p-3 bg-amber-50 border border-amber-100 rounded-lg flex items-start gap-3 shadow-inner">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg mt-0.5"></i>
                <div>
                    <h4 class="font-bold text-amber-800 text-xs uppercase tracking-wide mb-1">Peringatan Keamanan</h4>
                    <p class="text-xs text-amber-700 leading-snug">
                        Sistem dilengkapi dengan keamanan anti-kecurangan. 
                        <strong>Meninggalkan halaman / keluar dari fullscreen</strong> akan dianggap menyelesaikan ujian.
                    </p>
                </div>
            </div>

            {{-- Action Button --}}
            <div class="mt-6 pt-6 border-t border-gray-100 flex justify-center">
                <form id="start-exam-form" action="{{ route('siswa.ujian.mulai', $ujian->id) }}" method="POST">
                    @csrf
                    <button type="button" onclick="confirmStart()" class="group relative inline-flex items-center justify-center gap-3 px-10 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all shadow-md hover:shadow-blue-500/30 hover:-translate-y-0.5 w-full md:w-auto">
                        <span class="text-base font-[Poppins-Bold]">MULAI MENGERJAKAN</span>
                        <i class="bi bi-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
            </div>
            
            {{-- Custom Confirmation Modal --}}
            <dialog id="start-confirm-modal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md backdrop:bg-black/50 bg-white m-auto">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-6 animate-pulse">
                        <i class="bi bi-info-circle-fill text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-[Poppins-Bold] text-gray-800 mb-3">Siap Memulai?</h3>
                    <p class="text-gray-600 leading-relaxed mb-6 text-sm">
                        Anda akan diarahkan ke halaman ujian. Di halaman selanjutnya, Anda wajib masuk ke mode layar penuh.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="document.getElementById('start-confirm-modal').close()" 
                                class="px-6 py-3 rounded-xl border-2 border-gray-100 text-gray-600 font-bold hover:bg-gray-50 hover:border-gray-200 transition-all">
                            Batal
                        </button>
                        {{-- Tombol ini hanya submit form biasa, tanpa fullscreen force --}}
                        <button onclick="document.getElementById('start-exam-form').submit()" 
                                class="px-6 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all hover:-translate-y-1">
                            Ya, Lanjut
                        </button>
                    </div>
                </div>
            </dialog>

            <script>
                function confirmStart() {
                    document.getElementById('start-confirm-modal').showModal();
                }
            </script>
            
            <div class="text-center mt-4">
                 <a href="{{ route('siswa.dashboard') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600">
                    Batal & Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>
</div>
@endsection