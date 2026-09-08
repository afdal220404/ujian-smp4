@extends('layouts.app')

@section('title', 'Persiapan Ujian')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link active rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
            <i class="bi bi-award"></i> <span>Ujian</span>
        </a>
        <a href="{{ route('siswa.profil') }}" class="nav-link rounded-xl">
            <i class="bi bi-person-circle"></i> <span>Profil</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link rounded-xl">
            <i class="bi bi-file-earmark-text"></i> <span>Arsip Soal Siswa</span>
        </a>
    </div>
@endsection

@section('content')
@php
    $start = \Carbon\Carbon::parse($ujian->waktu_mulai);
    $end = \Carbon\Carbon::parse($ujian->waktu_selesai);
    $diff = $start->diffInMinutes($end);
@endphp

<div class="max-w-4xl mx-auto md:p-4">

    {{-- 1. LUXURIOUS HERO APP BAR WITH SCHOOL PHOTO & BLUE GRADIENT OVERLAY --}}
    <div style="background: linear-gradient(135deg, #00415a 0%, #002b3d 100%);" class="rounded-b-[2.5rem] md:rounded-3xl px-6 sm:px-8 pt-8 pb-16 text-white shadow-[0_15px_35px_-5px_rgba(0,65,90,0.3)] relative overflow-hidden">
        {{-- Background School Image --}}
        <img src="{{ asset('image/foto_sekolah2.jpeg') }}" alt="SMPN 4 Tilatang Kamang" class="absolute inset-0 w-full h-full object-cover object-center transform scale-105 pointer-events-none opacity-40">
        <div class="absolute inset-0 bg-gradient-to-b from-[#0284c7]/75 via-[#00415a]/90 to-[#002133] pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Back Button --}}
            <a href="{{ route('siswa.dashboard') }}" 
               class="inline-flex items-center gap-1.5 text-xs !text-white font-bold mb-3 hover:bg-white/25 active:scale-95 transition-all bg-white/15 px-3.5 py-1.5 rounded-full border border-white/20 shadow-2xs backdrop-blur-xl">
                <i class="bi bi-arrow-left !text-white"></i>
                <span class="!text-white">Kembali ke Dashboard</span>
            </a>

            <h1 class="text-2xl sm:text-3xl font-[Poppins-Bold] !text-white tracking-tight leading-tight drop-shadow-sm">
                Persiapan Ujian
            </h1>
            <p class="text-xs sm:text-sm text-sky-100 mt-1 font-medium">
                Harap periksa rincian sesi dan baca tata tertib sebelum mulai
            </p>
        </div>
    </div>

    {{-- 2. FLOATING MAIN CONFIRMATION CARD --}}
    <div class="px-4 sm:px-6 md:px-0 -mt-10 relative z-20 mb-8">
        <div class="bg-white rounded-3xl p-6 sm:p-8 card-glow border border-slate-100/90 shadow-sm">
            
            {{-- Subject & Exam Details --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div class="flex items-start gap-4 min-w-0">
                    <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff !important;" class="w-14 h-14 rounded-2xl text-white flex items-center justify-center text-2xl shrink-0 shadow-md">
                        <i class="bi bi-journal-text text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-sky-50 text-sky-700 border border-sky-200 mb-1">
                            {{ $ujian->mapel->nama_mapel ?? 'Mapel' }}
                        </span>
                        <h2 class="text-xl sm:text-2xl font-[Poppins-Bold] text-slate-800 leading-snug truncate">{{ $ujian->nama_ujian }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5 font-medium">
                            <i class="bi bi-person-fill text-slate-400"></i>
                            <span>{{ $ujian->mapel->guru->nama_lengkap ?? 'Guru Pengampu' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Badges Duration & Question Count --}}
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <div class="flex-1 sm:flex-initial bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-100 rounded-2xl p-3 text-center min-w-[90px] shadow-2xs">
                        <p class="text-[9px] font-bold text-sky-600 uppercase tracking-wider">Durasi</p>
                        <p class="text-lg font-[Poppins-Bold] text-sky-950 leading-tight mt-0.5">{{ $diff }} <span class="text-xs font-medium text-sky-600">menit</span></p>
                    </div>
                    <div class="flex-1 sm:flex-initial bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-3 text-center min-w-[90px] shadow-2xs">
                        <p class="text-[9px] font-bold text-indigo-600 uppercase tracking-wider">Jumlah Soal</p>
                        <p class="text-lg font-[Poppins-Bold] text-indigo-950 leading-tight mt-0.5">{{ $ujian->soals->count() }} <span class="text-xs font-medium text-indigo-600">butir</span></p>
                    </div>
                </div>
            </div>

            {{-- Schedule & Instructions Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-6">
                {{-- Left: Schedule Details --}}
                <div class="space-y-3">
                    <h3 class="font-[Poppins-Bold] text-slate-800 text-sm flex items-center gap-2">
                        <i class="bi bi-clock-history text-sky-600"></i>
                        <span>Jadwal Pengerjaan</span>
                    </h3>

                    <div class="bg-gradient-to-br from-sky-50/50 via-white to-blue-50/50 rounded-2xl p-4 border border-sky-100 space-y-3 shadow-2xs">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Waktu Mulai:</span>
                            <span class="font-[Poppins-Bold] text-slate-800 font-mono">{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('H:i') }} WIB</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Batas Selesai:</span>
                            <span class="font-[Poppins-Bold] text-rose-600 font-mono">{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('H:i') }} WIB</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-sky-100">
                            <span class="text-slate-500 font-medium">Status Tipe:</span>
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-white border border-sky-200 text-sky-700 shadow-2xs">
                                {{ $ujian->jenis_ujian }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right: Instructions Checklist --}}
                <div class="space-y-3">
                    <h3 class="font-[Poppins-Bold] text-slate-800 text-sm flex items-center gap-2">
                        <i class="bi bi-shield-check text-emerald-600"></i>
                        <span>Tata Tertib Ujian</span>
                    </h3>

                    <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-100 space-y-2.5 shadow-2xs">
                        @foreach([
                            'Berdoalah terlebih dahulu sebelum mulai mengerjakan.',
                            'Waktu berjalan otomatis saat tombol Mulai ditekan.',
                            'Dilarang membuka aplikasi lain selama ujian berlangsung.',
                            'Jawaban tersimpan otomatis setiap kali memilih opsi.'
                        ] as $rule)
                        <div class="flex items-start gap-2.5 text-xs text-slate-600">
                            <i class="bi bi-check-circle-fill text-emerald-500 text-xs shrink-0 mt-0.5"></i>
                            <span>{{ $rule }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- CTA Button Section --}}
            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col items-center">
                <form id="start-exam-form" action="{{ route('siswa.ujian.mulai', $ujian->id) }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="button" onclick="confirmStart()" 
                            style="{{ !empty($isResume) ? 'background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);' : 'background: linear-gradient(135deg, #059669 0%, #0d9488 100%);' }} color: #ffffff !important;"
                            class="w-full sm:w-auto px-12 py-4 rounded-2xl !text-white font-[Poppins-Bold] text-sm uppercase tracking-wider shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2.5 cursor-pointer">
                        <span class="!text-white">{{ !empty($isResume) ? 'Lanjutkan Pengerjaan Ujian' : 'Mulai Mengerjakan Ujian' }}</span>
                        <i class="bi {{ !empty($isResume) ? 'bi-play-circle-fill' : 'bi-arrow-right-circle-fill' }} text-lg !text-white"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

{{-- MODAL KONFIRMASI MULAI --}}
<dialog id="start-confirm-modal" class="rounded-3xl shadow-2xl p-0 w-full max-w-md backdrop:bg-slate-900/60 m-auto">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full {{ !empty($isResume) ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner">
            <i class="bi {{ !empty($isResume) ? 'bi-play-circle-fill' : 'bi-patch-question-fill' }}"></i>
        </div>
        <h3 class="text-xl font-[Poppins-Bold] text-slate-800 mb-2">
            {{ !empty($isResume) ? 'Lanjutkan Ujian?' : 'Siap Mulai Ujian?' }}
        </h3>
        <p class="text-slate-500 leading-relaxed mb-6 text-xs sm:text-sm">
            @if(!empty($isResume))
                Jawaban sebelumnya sudah tersimpan otomatis. Anda akan melanjutkan menjawab sisa soal.
            @else
                Waktu pengerjaan akan langsung berjalan saat Anda menekan tombol Lanjut.
            @endif
        </p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="document.getElementById('start-confirm-modal').close()" 
                    class="px-4 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs uppercase hover:bg-slate-50 transition-all cursor-pointer">
                Batal
            </button>
            <button onclick="document.getElementById('start-exam-form').submit()" 
                    style="{{ !empty($isResume) ? 'background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);' : 'background: linear-gradient(135deg, #059669 0%, #0d9488 100%);' }} color: #ffffff !important;"
                    class="px-4 py-3 rounded-xl !text-white font-bold text-xs uppercase shadow-md active:scale-95 transition-all cursor-pointer">
                <span class="!text-white">Ya, Lanjut</span>
            </button>
        </div>
    </div>
</dialog>

<script>
    function confirmStart() {
        document.getElementById('start-confirm-modal').showModal();
    }
</script>
@endsection