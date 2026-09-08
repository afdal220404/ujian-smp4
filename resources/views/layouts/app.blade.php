<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>@yield('title', 'Ujian Digital')</title>
  
  {{-- CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  
  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('image/favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('image/logo_sekolah2.png') }}">
  
  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- Tailwind CSS CDN (Memastikan seluruh style langsung aktif di Mobile/Emulator/PC tanpa tergantung Vite) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#0284c7',
            darkblue: '#00415a',
            lightblue: '#b3cde0',
          }
        }
      }
    }
  </script>

  {{-- CSS Fallback --}}
  <link rel="stylesheet" href="{{ asset('css/page.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  {{-- Vite Resources --}}
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&display=swap');

    /* Global Typography & Android Fallback */
    * {
        font-family: 'Plus Jakarta Sans', 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    body {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        -webkit-tap-highlight-color: transparent;
        -webkit-font-smoothing: antialiased;
    }

    .font-\[Poppins-Bold\],
    [class*="font-[Poppins-Bold]"],
    .font-poppins-bold {
        font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif !important;
        font-weight: 700 !important;
    }

    .font-\[Poppins-Regular\],
    [class*="font-[Poppins-Regular]"],
    .font-poppins {
        font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif !important;
        font-weight: 400 !important;
    }
    
    /* Modern Glassmorphism & Mesh Background Glows */
    .bg-mesh-student {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.08) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
            radial-gradient(at 50% 100%, rgba(14, 165, 233, 0.05) 0px, transparent 50%);
    }

    /* Floating Card Soft Glow */
    .card-glow {
        box-shadow: 0 10px 30px -5px rgba(2, 132, 199, 0.07), 0 4px 12px rgba(15, 23, 42, 0.03);
    }
    .card-glow:hover {
        box-shadow: 0 15px 35px -5px rgba(2, 132, 199, 0.12), 0 6px 16px rgba(15, 23, 42, 0.04);
    }

    /* Hide scrollbar for clean horizontal tabs */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>

@php
    $isSiswa = Auth::guard('siswa')->check();
    $siswaUser = $isSiswa ? Auth::guard('siswa')->user() : null;
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp

<body class="{{ $isSiswa ? 'bg-mesh-student' : 'bg-slate-100' }} text-slate-800 antialiased selection:bg-sky-500 selection:text-white min-h-screen">

    {{-- WRAPPER UTAMA --}}
    <div class="flex min-h-screen relative">

      {{-- BACKDROP OVERLAY UNTUK SIDEBAR MOBILE (HANYA NON-SISWA) --}}
      @if(!$isSiswa)
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden md:hidden transition-opacity"></div>
      @endif

      @include('layouts.navbar') 

        {{-- KONTEN UTAMA --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden h-screen overflow-y-auto scroll-smooth">
            
            {{-- HEADER MOBILE UNTUK NON-SISWA (GURU/OPERATOR) --}}
            @if(!$isSiswa)
            <div class="md:hidden bg-[#00415a] text-white border-b border-white/10 px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-md">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 bg-white/10 rounded-xl border border-white/20 shadow-inner">
                        <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-white text-sm tracking-wide leading-tight">SMPN 4 Tilatang Kamang</span>
                        <span class="text-[10px] text-cyan-200/80 font-medium">Sistem Ujian Digital</span>
                    </div>
                </div>
                
                <button onclick="toggleSidebar()" class="text-white focus:outline-none p-2 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 transition-all" aria-label="Buka Menu">
                    <i class="bi bi-list text-2xl leading-none"></i>
                </button>
            </div>
            @endif

            {{-- AREA KONTEN --}}
            <main class="flex-1 {{ $isSiswa ? 'p-0 md:p-8 pb-28 md:pb-8' : 'p-3.5 sm:p-6 md:p-8' }}">
                @yield('content')
            </main>

            {{-- FOOTER NON-SISWA --}}
            @if(!$isSiswa)
              @include('layouts.footer')
            @endif

        </div>
    </div>

    {{-- 3. MODERN ANDROID BOTTOM NAVIGATION BAR KHUSUS SISWA --}}
    @if($isSiswa)
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white/90 backdrop-blur-xl border-t border-slate-200/70 z-50 shadow-[0_-8px_30px_rgba(15,23,42,0.06)] px-4 py-2 flex items-center justify-around">
        
        {{-- Tab 1: Dashboard --}}
        @php $isDashboardActive = str_starts_with($currentRoute, 'siswa.dashboard'); @endphp
        <a href="{{ route('siswa.dashboard') }}" 
           class="flex flex-col items-center justify-center py-1 px-4 rounded-2xl transition-all duration-300 {{ $isDashboardActive ? 'text-sky-600 font-bold scale-105' : 'text-slate-500 hover:text-sky-600 font-medium' }}">
            <div class="w-12 h-8 flex items-center justify-center rounded-full {{ $isDashboardActive ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-md shadow-sky-500/30' : '' }} transition-all duration-300">
                <i class="bi {{ $isDashboardActive ? 'bi-grid-fill' : 'bi-grid' }} text-lg"></i>
            </div>
            <span class="text-[11px] tracking-tight mt-0.5 {{ $isDashboardActive ? 'font-bold text-sky-600' : '' }}">Dashboard</span>
        </a>

        {{-- Tab 2: Ujian --}}
        @php $isNilaiActive = str_starts_with($currentRoute, 'siswa.nilai') || str_starts_with($currentRoute, 'siswa.ujian.detail'); @endphp
        <a href="{{ route('siswa.nilai') }}" 
           class="flex flex-col items-center justify-center py-1 px-4 rounded-2xl transition-all duration-300 {{ $isNilaiActive ? 'text-sky-600 font-bold scale-105' : 'text-slate-500 hover:text-sky-600 font-medium' }}">
            <div class="w-12 h-8 flex items-center justify-center rounded-full {{ $isNilaiActive ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-md shadow-sky-500/30' : '' }} transition-all duration-300">
                <i class="bi {{ $isNilaiActive ? 'bi-award-fill' : 'bi-award' }} text-lg"></i>
            </div>
            <span class="text-[11px] tracking-tight mt-0.5 {{ $isNilaiActive ? 'font-bold text-sky-600' : '' }}">Ujian</span>
        </a>

        {{-- Tab 3: Profil --}}
        @php $isProfilActive = str_starts_with($currentRoute, 'siswa.profil'); @endphp
        <a href="{{ route('siswa.profil') }}" 
           class="flex flex-col items-center justify-center py-1 px-4 rounded-2xl transition-all duration-300 {{ $isProfilActive ? 'text-sky-600 font-bold scale-105' : 'text-slate-500 hover:text-sky-600 font-medium' }}">
            <div class="w-12 h-8 flex items-center justify-center rounded-full {{ $isProfilActive ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-md shadow-sky-500/30' : '' }} transition-all duration-300">
                <i class="bi {{ $isProfilActive ? 'bi-person-circle' : 'bi-person' }} text-lg"></i>
            </div>
            <span class="text-[11px] tracking-tight mt-0.5 {{ $isProfilActive ? 'font-bold text-sky-600' : '' }}">Profil</span>
        </a>

    </nav>
    @endif

    {{-- SCRIPT TOGGLE SIDEBAR & LOGOUT CONFIRMATION --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!sidebar) return;
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
            }
        }

        function confirmLogout() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Keluar dari Akun?',
                    text: 'Anda akan keluar dari sesi akun siswa.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('logout') }}";
                    }
                });
            } else {
                if (confirm('Yakin ingin keluar dari akun siswa?')) {
                    window.location.href = "{{ route('logout') }}";
                }
            }
        }
    </script>
    
    {{-- POPUP PERINGATAN: WAJIB APLIKASI MOBILE UJIAN DIGITAL --}}
    @if(session('blocked_web_access'))
    @php
        $blockInfo = session('blocked_web_access');
        $namaUjian = is_array($blockInfo) ? ($blockInfo['nama_ujian'] ?? 'Ujian') : 'Ujian';
        $jenisUjian = is_array($blockInfo) ? ($blockInfo['jenis_ujian'] ?? 'Sesi Ujian') : 'Sesi Ujian';
    @endphp
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    title: '<span style="font-family: Poppins, sans-serif; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Akses Khusus Aplikasi Mobile</span>',
                    html: `
                        <div class="text-center px-2">
                            <p style="font-size: 0.875rem; color: #334155; line-height: 1.5; margin-bottom: 0.75rem;">
                                Sesi <b>{{ $jenisUjian }}: {{ $namaUjian }}</b> diwajibkan dikerjakan melalui <b>Aplikasi Mobile Ujian Digital SMPN 4</b> demi menjaga integritas dan ketertiban ujian.
                            </p>
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #64748b;">
                                <div style="display: flex; align-items: start; gap: 0.5rem;">
                                    <i class="bi bi-info-circle-fill text-sky-500" style="font-size: 1rem; margin-top: 1px;"></i>
                                    <div>
                                        <b style="color: #1e293b;">Terkendala Perangkat HP?</b><br>
                                        Silakan lapor kepada <b>Guru Pengawas</b> di ruangan agar izin akses komputer/lab sekolah dapat diaktifkan.
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Saya Mengerti',
                    confirmButtonColor: '#00415a',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl p-6 border border-slate-100',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5 shadow-md'
                    }
                });
            } else {
                alert('Peringatan: Ujian {{ $namaUjian }} hanya dapat diakses melalui Aplikasi Mobile Ujian Digital SMPN 4.');
            }
        });
    </script>
    @endif

    {{-- POPUP PERINGATAN: SESI TERKUNCI (RE-ENTRY LOCK) --}}
    @if(session('reentry_locked'))
    @php
        $lockInfo = session('reentry_locked');
        $ujianId = is_array($lockInfo) ? ($lockInfo['ujian_id'] ?? 0) : 0;
        $namaUjian = is_array($lockInfo) ? ($lockInfo['nama_ujian'] ?? 'Ujian') : 'Ujian';
        $jenisUjian = is_array($lockInfo) ? ($lockInfo['jenis_ujian'] ?? 'Sesi Ujian') : 'Sesi Ujian';
    @endphp
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    iconColor: '#f97316',
                    title: '<span style="font-family: Poppins, sans-serif; font-size: 1.25rem; font-weight: 700; color: #0f172a;">🔒 Sesi Ujian Terkunci</span>',
                    html: `
                        <div class="text-center px-2">
                            <p style="font-size: 0.875rem; color: #334155; line-height: 1.5; margin-bottom: 0.75rem;">
                                Sesi pengerjaan <b>{{ $jenisUjian }}: {{ $namaUjian }}</b> Anda sempat terputus atau terdeteksi keluar dari aplikasi.
                            </p>
                            <div style="background-color: #fff7ed; border: 1px solid #ffedd5; border-radius: 1rem; padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #9a3412;">
                                <div style="display: flex; align-items: start; gap: 0.5rem;">
                                    <i class="bi bi-shield-lock-fill text-amber-600" style="font-size: 1.15rem; margin-top: 1px;"></i>
                                    <div>
                                        <b style="color: #7c2d12;">Petunjuk Masuk Kembali:</b><br>
                                        Silakan <b>angkat tangan dan melapor ke Guru Pengawas</b> di ruangan agar kunci login dibuka.
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-arrow-repeat mr-1"></i> Coba Masuk Lagi',
                    cancelButtonText: 'Tutup',
                    confirmButtonColor: '#00415a',
                    cancelButtonColor: '#94a3b8',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl p-6 border border-slate-100',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md',
                        cancelButton: 'rounded-xl font-medium px-5 py-2.5'
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch("{{ url('/siswa/ujian/' . $ujianId . '/check-reentry') }}");
                            const data = await res.json();
                            if (data.unlocked) {
                                window.location.href = "{{ url('/siswa/ujian/' . $ujianId . '/kerjakan') }}";
                            } else {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Belum Dibuka',
                                    text: 'Pengawas ruangan belum membuka kunci sesi Anda. Harap melapor langsung ke Pengawas.',
                                    confirmButtonColor: '#00415a',
                                    customClass: {
                                        popup: 'rounded-3xl shadow-xl',
                                        confirmButton: 'rounded-xl font-bold px-6 py-2'
                                    }
                                });
                            }
                        } catch (e) {
                            window.location.href = "{{ url('/siswa/ujian/' . $ujianId . '/kerjakan') }}";
                        }
                    }
                });
            } else {
                alert('Sesi Ujian Terkunci. Silakan melapor ke Pengawas Ruangan untuk membuka kunci masuk kembali.');
            }
        });
    </script>
    @endif

    @yield('scripts')
</body>
</html>