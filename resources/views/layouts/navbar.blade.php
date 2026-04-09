{{-- 
    ID: sidebar
    Mobile: fixed, hidden (pindah ke kiri layar -translate-x-full), z-index tinggi.
    Desktop (md): relative, translate-x-0 (selalu muncul), sticky.
--}}
<aside id="sidebar" 
     class="fixed inset-y-0 left-0 z-50 w-64 h-screen bg-[#00415a] text-white transition-transform transform -translate-x-full md:translate-x-0 md:sticky md:top-0 flex flex-col p-4 shadow-xl md:shadow-none">
    
    {{-- TOMBOL CLOSE (HANYA DI HP) --}}
    <div class="flex justify-end md:hidden mb-2">
        <button onclick="toggleSidebar()" class="text-white hover:text-red-400">
            <i class="bi bi-x-lg text-2xl"></i>
        </button>
    </div>

    @php
        $user = Auth::user();
        $role = 'Guru';
        $foto = null;
        $nip_nisn = '-';
        $roleContext = null;

        if (Auth::guard('siswa')->check()) {
            $user = Auth::guard('siswa')->user();
            $role = 'Siswa';
            $nip_nisn = $user->nisn;
        } elseif ($user) {
            $foto = $user->foto;
            $nip_nisn = $user->nip ?? '020517';
            if(isset($user->role)) $role = $user->role;

            // Context Role Khusus Guru
            if ($role === 'Guru' || $role === 'guru') {
                $routeName = request()->route() ? request()->route()->getName() : '';
                if (str_starts_with($routeName, 'guru.walikelas.')) {
                    $kelas = request()->route('kelas');
                    if (is_numeric($kelas)) $kelas = \App\Models\Kelas::find($kelas);
                    if ($kelas) {
                        $roleContext = 'Guru Wali Kelas ' . ($kelas->kelas ?? $kelas->nama_kelas ?? '');
                    }
                } elseif (str_starts_with($routeName, 'guru.mapel.')) {
                    $mapel = request()->route('mapel');
                    if (is_numeric($mapel)) $mapel = \App\Models\Mapel::with('kelas')->find($mapel);
                    if ($mapel) {
                        $namaMapel = $mapel->nama_mapel ?? '';
                        $namaKelas = $mapel->kelas->nama_kelas ?? $mapel->kelas->kelas ?? '';
                        $roleContext = 'Guru ' . $namaMapel . ' - Kelas ' . $namaKelas;
                    }
                }
            }
        }
    @endphp

    {{-- 1. BAGIAN PROFIL --}}
    <div class="relative group cursor-pointer mb-3 flex-shrink-0 flex flex-col items-center"> 
        <div class="w-20 h-20 md:w-24 md:h-24 rounded-full p-1 bg-gradient-to-tr from-white/10 to-transparent 
                    ring-4 ring-white/10 transition-all duration-500 ease-out 
                    group-hover:ring-cyan-400/50 group-hover:scale-105 shadow-2xl">
            <img src="{{ $foto ? asset('storage/' . $foto) : asset('image/dummy.jpg') }}" 
                 alt="Foto Profil"
                 class="w-full h-full rounded-full object-cover border-2 border-white/20 shadow-inner bg-white">
        </div>
        <div class="absolute bottom-1 right-16 md:right-20 w-4 h-4 md:w-5 md:h-5 bg-emerald-500 border-4 border-slate-900 rounded-full shadow-sm">
             <div class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></div>
        </div>
    </div>

    <div class="teacher-info text-center w-full flex-shrink-0">
        <h2 class="teacher-name font-bold text-white tracking-wide text-sm md:text-base truncate px-2">
            {{ $user->nama_lengkap }}
        </h2>
        <div class="flex flex-col items-center mt-1.5 space-y-2">
            <p class="inline-flex items-center justify-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/5 text-cyan-100/80 text-[10px] md:text-xs font-mono">
                <i class="bi bi-person-badge"></i> {{ $nip_nisn }}
            </p>
            @if($roleContext)
                <span class="block text-[10px] text-cyan-200/90 bg-cyan-900/40 px-2.5 py-1 rounded-md border border-cyan-500/30 uppercase tracking-widest font-bold shadow-sm">{{ $roleContext }}</span>
            @else
                <span class="block text-xs text-cyan-200/50 uppercase tracking-widest font-bold">{{ $role }}</span>
            @endif
        </div>
    </div>

    <div class="sidebar-divider w-full h-px bg-white/10 my-4 flex-shrink-0"></div>

    {{-- 2. MENU (SCROLLABLE) --}}
    <nav class="sidebar-menu w-full flex-1 overflow-hidden space-y-1">
        @yield('sidebar-menu')
    </nav>

    {{-- 3. LOGOUT --}}
    <div class="mt-auto pt-4 border-t border-white/10 w-full flex-shrink-0">
        <a href="{{ route('logout') }}" 
           class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-cyan-100/70 rounded-xl transition-all duration-300 hover:bg-white/5 hover:text-white group">
            <i class="bi bi-box-arrow-right text-lg group-hover:text-red-400 transition-colors"></i>
            <span class="tracking-wide group-hover:text-red-100 transition-colors">Logout</span>
        </a>
    </div>
</aside>

{{-- OVERLAY (Latar Gelap saat menu terbuka di HP) --}}
<div id="sidebarOverlay" onclick="toggleSidebar()" 
     class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity backdrop-blur-sm">
</div>


