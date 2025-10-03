<div class="sidebar">
   <div class="avatar-placeholder">
    <img src="{{ asset('storage/'.Auth::user()->guru->foto) }}" 
         alt="foto {{ auth()->user()->nama }}" 
         class="rounded-full w-20 h-20 mx-auto">
</div>
     <!-- Info Guru -->
   <div class="teacher-info mb-8">
        {{-- Panggil properti 'name' dan 'nip' dari user yang login --}}
        <h2 class="teacher-name">{{ Auth::user()->guru->nama_lengkap}}</h2>
        <p class="teacher-id">NIP: {{ Auth::user()->guru->nip }}</p>
    </div>
    <nav class="flex flex-col space-y-4">
        {{-- Menu akan diisi dari halaman yang extend --}}
        @yield('sidebar-menu')
    </nav>
</div>
