@extends('layouts.app')

@section('title', 'Ujian & Nilai Siswa')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link active rounded-xl">
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
<div class="max-w-7xl mx-auto md:p-4">

    {{-- 1. HERO APP BAR WITH SCHOOL PHOTO & BLUE GRADIENT OVERLAY --}}
    <div class="rounded-b-[2.5rem] md:rounded-3xl px-6 sm:px-8 pt-8 pb-12 text-white shadow-[0_15px_35px_-5px_rgba(0,65,90,0.3)] relative overflow-hidden mb-6">
        {{-- Background School Image --}}
        <img src="{{ asset('image/foto_sekolah2.jpeg') }}" alt="SMPN 4 Tilatang Kamang" class="absolute inset-0 w-full h-full object-cover object-center transform scale-105 pointer-events-none">

        {{-- Gradient Overlay: Light Sky Blue at Top to Deep Solid Blue at Bottom --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#0284c7]/75 via-[#00415a]/90 to-[#002133] pointer-events-none"></div>

        {{-- Subtle Ambient Glows --}}
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute left-1/4 bottom-0 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xl text-[11px] font-semibold text-sky-100 border border-white/20 mb-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.3)]">
                <i class="bi bi-award-fill text-amber-300"></i>
                <span>Akademik & Riwayat Nilai</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-[Poppins-Bold] !text-white tracking-tight leading-tight drop-shadow-md">
                Riwayat Ujian
            </h1>
            <p class="text-xs sm:text-sm text-sky-100 mt-1 font-medium drop-shadow-xs">
                Pantau seluruh rekap hasil ujian dan capaian belajarmu
            </p>
        </div>
    </div>

    {{-- 2. KELAS SWITCHER PILLS & SEARCH (RESPONSIVE FLEX/GRID ON PC) --}}
    <div class="px-4 sm:px-6 md:px-0 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        {{-- Kelas Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar" id="shared-kelas-tabs">
            @foreach($allKelasShared as $kidx => $kentry)
                @if($kentry['is_unlocked'])
                    <button onclick="switchKelas({{ $kidx }})"
                            id="shared-tab-{{ $kidx }}"
                            style="{{ $kentry['is_current'] ? 'background-color: #00415a; color: #ffffff !important;' : 'background-color: #ffffff; color: #334155 !important;' }}"
                            class="shared-kelas-btn px-5 py-2.5 rounded-xl text-xs sm:text-sm font-[Poppins-Bold] transition-all shrink-0 border border-slate-200 shadow-sm cursor-pointer">
                        {{ $kentry['kelas']->nama_kelas ?? ('Kelas ' . $kentry['tingkat']) }}
                    </button>
                @else
                    <button type="button" 
                            onclick="showLockedClassAlert('{{ $kentry['tingkat'] }}')"
                            id="shared-tab-{{ $kidx }}"
                            class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-[Poppins-Bold] transition-all shrink-0 bg-slate-100/90 text-slate-400 border border-slate-200/80 cursor-not-allowed flex items-center gap-1.5 shadow-2xs hover:bg-slate-200/70"
                            title="Kelas {{ $kentry['tingkat'] }} Terkunci">
                        <i class="bi bi-lock-fill text-slate-400 text-xs"></i>
                        <span>{{ $kentry['kelas']->nama_kelas ?? ('Kelas ' . $kentry['tingkat']) }}</span>
                    </button>
                @endif
            @endforeach
        </div>

        {{-- Search Bar --}}
        <form action="{{ route('siswa.nilai') }}" method="GET" class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="bi bi-search text-sky-500 text-xs"></i>
            </div>
            <input type="text" name="search" value="{{ $keyword }}" placeholder="Cari mata pelajaran atau ujian..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-2xl bg-white border border-slate-200/80 text-xs sm:text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            @if($keyword)
                <a href="{{ route('siswa.nilai') }}" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-red-500">
                    <i class="bi bi-x-circle-fill text-base"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- 3. DAFTAR MATA PELAJARAN (DESKTOP: 2-COLUMN GRID, MOBILE: 1-COLUMN) --}}
    <div class="px-4 sm:px-6 md:px-0 mb-8" id="view-card">
        @foreach($allKelasShared as $cidx => $centry)
        <div id="card-panel-{{ $cidx }}" class="card-kelas-panel {{ !$centry['is_current'] ? 'hidden' : '' }}">
            
            @if(!$centry['is_unlocked'])
                {{-- Locked State Screen --}}
                <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-100 text-center card-glow max-w-md mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3 shadow-inner">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <h3 class="font-[Poppins-Bold] !text-slate-700 text-lg">Kelas {{ $centry['tingkat'] }} Terkunci</h3>
                    <p class="text-slate-400 text-xs mt-1.5 leading-relaxed">
                        Anda belum mencapai atau menempuh jenjang Kelas {{ $centry['tingkat'] }}. Riwayat ujian kelas ini belum tersedia.
                    </p>
                </div>
            @else
                @php $mapelPanel = collect($centry['mapels']); @endphp

                @if($mapelPanel->isEmpty())
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                        <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-300 flex items-center justify-center text-3xl mx-auto mb-3">
                            <i class="bi bi-journal-x"></i>
                        </div>
                        <h3 class="font-[Poppins-Bold] !text-slate-700 text-base">Belum Ada Data</h3>
                        <p class="text-slate-400 text-xs mt-1">Tidak ada mata pelajaran atau riwayat ujian untuk kelas ini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($mapelPanel as $mapel)
                        @php 
                            $ujianList = $mapel->ujian_selesai ?? collect();
                        @endphp
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-md hover:border-sky-200 transition-all duration-300 flex flex-col justify-between gap-3 group">
                            
                            {{-- Card Header: Subject Info with Standard Clean Book Icon --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0 border border-sky-100 group-hover:scale-105 transition-transform shadow-2xs">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <h3 class="font-[Poppins-Bold] !text-slate-800 text-base leading-snug group-hover:!text-sky-600 transition-colors truncate">
                                                {{ $mapel->nama_mapel }}
                                            </h3>
                                        </div>
                                        <p class="text-xs text-slate-500 font-medium truncate flex items-center gap-1.5">
                                            <i class="bi bi-person-fill text-slate-400"></i>
                                            <span>{{ $mapel->guru->nama_lengkap ?? 'Guru Belum Ditentukan' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Collapsible Section --}}
                            <div class="pt-3 border-t border-slate-100/90">
                                <button type="button" 
                                        onclick="toggleUjianDetail('ujian-list-{{ $mapel->id }}-{{ $cidx }}', this)"
                                        class="w-full flex items-center justify-between text-xs font-[Poppins-Bold] text-slate-600 hover:text-sky-600 transition-colors py-1">
                                    <span class="flex items-center gap-2">
                                        <i class="bi bi-card-checklist text-sky-500"></i>
                                        <span>Riwayat Ujian</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-600 border border-sky-100">
                                            {{ $ujianList->count() }} Ujian
                                        </span>
                                    </span>
                                    <div class="w-6 h-6 rounded-lg bg-slate-100 group-hover:bg-sky-50 flex items-center justify-center text-slate-400 group-hover:text-sky-600 transition-all">
                                        <i class="bi bi-chevron-down text-[11px] transition-transform duration-200"></i>
                                    </div>
                                </button>

                                {{-- Accordion Body: List of Exams --}}
                                <div id="ujian-list-{{ $mapel->id }}-{{ $cidx }}" class="mt-3 space-y-2.5 hidden">
                                    @if($ujianList->isEmpty())
                                        <div class="p-4 bg-slate-50 rounded-xl text-center text-slate-400 text-xs font-medium">
                                            Belum ada ujian yang diselesaikan untuk mata pelajaran ini.
                                        </div>
                                    @else
                                        @foreach($ujianList as $uj)
                                        @php
                                            $hasil = $uj->hasilUjians ? $uj->hasilUjians->first() : null;
                                            $isUtsUas = in_array(strtoupper($uj->jenis_ujian ?? ''), ['UTS', 'UAS']);
                                            $jenisUjian = strtoupper($uj->jenis_ujian ?? 'UAS');
                                            $tagClass = 'bg-rose-50 text-rose-600 border-rose-100';
                                            if (str_contains($jenisUjian, 'KUIS')) $tagClass = 'bg-violet-50 text-violet-600 border-violet-100';
                                            elseif (str_contains($jenisUjian, 'UTS')) $tagClass = 'bg-amber-50 text-amber-600 border-amber-100';
                                        @endphp
                                        <div class="p-3 bg-slate-50/80 hover:bg-white rounded-xl border border-slate-100 shadow-2xs transition-all flex items-center justify-between gap-2 text-xs">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <span class="font-[Poppins-Bold] !text-slate-800 truncate">{{ $uj->nama_ujian }}</span>
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase {{ $tagClass }} border shrink-0">
                                                        {{ $uj->jenis_ujian }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1">
                                                    <i class="bi bi-calendar4"></i>
                                                    {{ \Carbon\Carbon::parse($uj->waktu_mulai)->locale('id')->isoFormat('D MMM Y') }}
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                @if($isUtsUas)
                                                    <span class="px-2 py-1 bg-white rounded-lg border border-slate-200 text-[10px] font-bold text-slate-400 flex items-center gap-1">
                                                        <i class="bi bi-lock-fill"></i> Skor
                                                    </span>
                                                @elseif($hasil)
                                                    <span class="px-2.5 py-1 rounded-xl text-xs font-[Poppins-Bold] {{ $hasil->nilai >= 75 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} shadow-2xs">
                                                        {{ $hasil->nilai }}
                                                    </span>
                                                @endif

                                                <a href="{{ route('siswa.ujian.detail', $uj->id) }}" 
                                                   class="w-7 h-7 rounded-xl bg-sky-500 hover:bg-sky-600 text-white flex items-center justify-center transition-all shadow-sm shadow-sky-500/20 active:scale-90" 
                                                   title="Lihat Detail Ujian">
                                                    <i class="bi bi-eye text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                        </div>
                        @endforeach
                    </div>
                @endif
            @endif

        </div>
        @endforeach
    </div>

</div>
@endsection

@section('scripts')
<script>
    function switchKelas(index) {
        document.querySelectorAll('.shared-kelas-btn').forEach((btn, idx) => {
            if (idx === index) {
                btn.style.backgroundColor = '#00415a';
                btn.style.color = '#ffffff';
                btn.classList.add('shadow-md');
            } else {
                btn.style.backgroundColor = '#ffffff';
                btn.style.color = '#334155';
                btn.classList.remove('shadow-md');
            }
        });

        document.querySelectorAll('.card-kelas-panel').forEach((panel, idx) => {
            if (idx === index) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
    }

    function showLockedClassAlert(tingkat) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Kelas ' + tingkat + ' Terkunci',
                text: 'Anda belum mencapai atau menempuh jenjang Kelas ' + tingkat + '.',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'Mengerti',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl',
                    confirmButton: 'rounded-xl font-bold px-5 py-2.5'
                }
            });
        } else {
            alert('Kelas ' + tingkat + ' terkunci karena Anda belum menempuh jenjang ini.');
        }
    }

    function toggleUjianDetail(id, btn) {
        const el = document.getElementById(id);
        const icon = btn.querySelector('.bi-chevron-down, .bi-chevron-up');
        if (el) {
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                if (icon) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-up');
                }
            } else {
                el.classList.add('hidden');
                if (icon) {
                    icon.classList.remove('bi-chevron-up');
                    icon.classList.add('bi-chevron-down');
                }
            }
        }
    }
</script>
@endsection
