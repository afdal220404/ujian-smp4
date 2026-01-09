<div class="sidebar">
    {{-- Avatar Section --}}
    <div class="avatar-container">
        <div class="avatar-placeholder">
            {{-- Menggunakan operator ternary untuk cek foto, jika null pakai dummy --}}
            <img src="{{ Auth::user()->foto ? asset('storage/' . Auth::user()->foto) : asset('image/dummy.jpg') }}" 
                 alt="Foto {{ Auth::user()->nama_lengkap }}">
        </div>
    </div>

    {{-- Info Guru --}}
    <div class="teacher-info">
        <h2 class="teacher-name">{{ Auth::user()->nama_lengkap }}</h2>
        <p class="teacher-id">
            <i class="bi bi-person-badge"></i> {{ Auth::user()->nip ?? 'NIP Tidak Ada' }}
        </p>
    </div>

    {{-- Separator Line --}}
    <div class="sidebar-divider"></div>

    {{-- Menu Navigasi --}}
    <nav class="sidebar-menu">
        {{-- Menu akan diisi dari halaman yang extend --}}
        @yield('sidebar-menu')
    </nav>

    {{-- Logout Section (Di bawah) --}}
    <div class="logout-container">
        <a href="{{ route('logout') }}" class="menu-item-red">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</div>
