@extends('layouts.app')

@section('title', 'Profil Siswa')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link rounded-xl">
            <i class="bi bi-award"></i> <span>Ujian</span>
        </a>
        <a href="{{ route('siswa.profil') }}" class="nav-link active rounded-xl">
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
    <div class="rounded-b-[2.5rem] md:rounded-3xl px-6 sm:px-8 pt-8 pb-18 md:pb-14 text-white text-center shadow-[0_15px_35px_-5px_rgba(0,65,90,0.3)] relative overflow-hidden mb-6">
        {{-- Background School Image --}}
        <img src="{{ asset('image/foto_sekolah2.jpeg') }}" alt="SMPN 4 Tilatang Kamang" class="absolute inset-0 w-full h-full object-cover object-center transform scale-105 pointer-events-none">

        {{-- Gradient Overlay: Light Sky Blue at Top to Deep Solid Blue at Bottom --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#0284c7]/75 via-[#00415a]/90 to-[#002133] pointer-events-none"></div>

        {{-- Subtle Ambient Glows --}}
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-1/4 top-2 w-32 h-32 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>
        
        <h1 class="text-2xl sm:text-3xl font-[Poppins-Bold] !text-white tracking-tight relative z-10 drop-shadow-md">
            Profil Siswa
        </h1>
        <p class="text-xs sm:text-sm text-sky-100 mt-1 font-medium relative z-10 drop-shadow-xs">
            Informasi akun dan daftar mata pelajaran kelas kamu
        </p>
    </div>

    {{-- 2. RESPONSIVE CONTAINER (DESKTOP: 2 COLUMNS, MOBILE: 1 COLUMN) --}}
    <div class="lg:grid lg:grid-cols-12 lg:gap-6">

        {{-- LEFT COLUMN (PROFILE CARD + PENGATURAN AKUN) --}}
        <div class="lg:col-span-4 space-y-6 -mt-10 lg:-mt-12 relative z-20 mb-6 lg:mb-0">
            
            {{-- Floating Profile Hero Card --}}
            <div class="px-4 sm:px-6 md:px-0">
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 card-glow border border-white ring-1 ring-slate-900/[0.04] flex flex-col items-center text-center">
                    
                    {{-- Big User Avatar with Gradient Ring --}}
                    <div class="p-1 bg-gradient-to-tr from-sky-400 via-blue-500 to-indigo-600 rounded-full shadow-lg shadow-sky-500/20 mb-3">
                        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center text-4xl text-sky-600">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    </div>

                    {{-- Student Name --}}
                    <h2 class="text-lg sm:text-xl font-[Poppins-Bold] !text-slate-800 tracking-tight leading-snug">
                        {{ $siswa->nama_lengkap }}
                    </h2>
                    <div class="w-12 h-1 bg-gradient-to-r from-sky-400 to-blue-600 rounded-full mt-1.5 mb-3"></div>

                    {{-- Badges: Kelas & NISN --}}
                    <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 font-bold border border-sky-200/80 shadow-2xs">
                            <i class="bi bi-mortarboard-fill text-sky-500"></i> Kelas {{ $siswa->kelas->kelas ?? '-' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-mono font-bold shadow-2xs">
                            <i class="bi bi-person-badge-fill text-slate-400"></i> NISN: {{ $siswa->nisn ?? '-' }}
                        </span>
                    </div>

                </div>
            </div>

            {{-- Pengaturan Akun (Desktop / Mobile integrated) --}}
            <div class="px-4 sm:px-6 md:px-0 space-y-3">
                <h3 class="text-sm font-[Poppins-Bold] !text-slate-800 tracking-tight">
                    Pengaturan Akun
                </h3>

                {{-- Button Ganti Password --}}
                <button type="button" onclick="openGantiPasswordModal()" 
                        class="w-full py-3 px-4 rounded-2xl bg-white border-2 border-sky-500 hover:bg-sky-50 text-sky-600 font-[Poppins-Bold] text-xs uppercase tracking-wider active:scale-98 transition-all flex items-center justify-center gap-2 shadow-sm shadow-sky-500/10">
                    <i class="bi bi-shield-lock-fill text-base"></i>
                    <span>Ganti Password</span>
                </button>

                {{-- Button Logout --}}
                <button type="button" onclick="confirmLogout()" 
                        class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-rose-50 to-red-50 hover:from-rose-100 hover:to-red-100 text-rose-600 border border-rose-200 font-[Poppins-Bold] text-xs uppercase tracking-wider active:scale-98 transition-all flex items-center justify-center gap-2 shadow-2xs">
                    <i class="bi bi-box-arrow-right text-base"></i>
                    <span>Logout Akun</span>
                </button>
            </div>

        </div>

        {{-- RIGHT COLUMN (DAFTAR MATA PELAJARAN KELAS SISWA) --}}
        <div class="lg:col-span-8 space-y-4">

            {{-- Daftar Mata Pelajaran & Guru (Table) --}}
            <div class="px-4 sm:px-6 md:px-0 mb-6">
                <div class="bg-white rounded-3xl card-glow border border-slate-100/90 overflow-hidden">
                    
                    {{-- Table Header Bar --}}
                    <div style="background: linear-gradient(135deg, #00415a 0%, #005a7d 100%);" class="text-white px-5 py-4 flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg !text-white border border-white/20 shadow-2xs">
                                <i class="bi bi-book-half !text-white"></i>
                            </div>
                            <div>
                                <span class="font-[Poppins-Bold] !text-white text-sm sm:text-base uppercase tracking-wider block">
                                    Mata Pelajaran Kelas {{ $siswa->kelas->kelas ?? '-' }}
                                </span>
                                <p class="text-[11px] text-sky-100 font-medium hidden sm:block">
                                    Daftar mata pelajaran aktif dan guru pengampu kamu
                                </p>
                            </div>
                        </div>

                        <span class="text-[10px] bg-white/20 px-3 py-1 rounded-full font-mono font-bold !text-white border border-white/20 shrink-0">
                            {{ $mapels->count() }} Mapel
                        </span>
                    </div>

                    {{-- Table Content --}}
                    @if($mapels->isEmpty())
                        <div class="p-8 text-center text-slate-400 text-xs font-medium">
                            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-300 flex items-center justify-center text-2xl mx-auto mb-2">
                                <i class="bi bi-journal-x"></i>
                            </div>
                            <h4 class="font-[Poppins-Bold] !text-slate-700 text-sm">Belum Ada Data Mata Pelajaran</h4>
                            <p class="text-slate-400 text-xs mt-0.5">Mata pelajaran untuk kelas {{ $siswa->kelas->kelas ?? '-' }} belum ditambahkan.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($mapels as $index => $mapel)
                            <div class="px-5 py-4 grid grid-cols-12 items-center text-xs {{ $index % 2 == 1 ? 'bg-slate-50/50' : 'bg-white' }} hover:bg-sky-50/40 transition-colors">
                                <div class="col-span-2 sm:col-span-1 text-center font-mono font-bold text-slate-400 text-xs sm:text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="col-span-10 sm:col-span-11 pl-2">
                                    <div class="font-[Poppins-Bold] !text-slate-800 text-sm sm:text-base leading-snug">
                                        {{ $mapel->nama_mapel }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-1.5">
                                        <i class="bi bi-person text-slate-400"></i>
                                        <span>{{ $mapel->guru->nama_lengkap ?? 'Guru Belum Ditentukan' }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>

        </div>

    </div>

</div>

{{-- MODAL GANTI PASSWORD --}}
<dialog id="modal-ganti-password" class="modal rounded-3xl p-0 backdrop:bg-black/60 shadow-2xl w-full max-w-md mx-auto">
    <div class="bg-white rounded-3xl overflow-hidden">
        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #00415a 0%, #005a7d 100%);" class="px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-2 font-[Poppins-Bold] text-sm uppercase tracking-wider text-white">
                <i class="bi bi-shield-lock-fill text-base text-cyan-200"></i>
                <span class="!text-white">Ganti Password</span>
            </div>
            <button onclick="document.getElementById('modal-ganti-password').close()" class="text-white/70 hover:text-white text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('siswa.ganti_password') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password Lama</label>
                <input type="password" name="password_lama" required placeholder="Masukkan password lama" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password Baru</label>
                <input type="password" name="password_baru" required minlength="6" placeholder="Minimal 6 karakter" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_baru_confirmation" required minlength="6" placeholder="Ulangi password baru" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3">
                <button type="button" onclick="document.getElementById('modal-ganti-password').close()" 
                        class="px-4 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs uppercase hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button type="submit" 
                        style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff !important;"
                        class="px-4 py-3 rounded-xl !text-white font-bold text-xs uppercase shadow-md shadow-sky-500/25 active:scale-95 transition-all cursor-pointer">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@section('scripts')
<script>
    function openGantiPasswordModal() {
        document.getElementById('modal-ganti-password').showModal();
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false,
            customClass: { popup: 'rounded-3xl' }
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
            customClass: { popup: 'rounded-3xl' }
        });
    @endif
</script>
@endsection
