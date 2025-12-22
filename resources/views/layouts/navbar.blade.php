<div class="sidebar">
   <div class="avatar-placeholder">
    <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="User Avatar"> 
         alt="foto {{ auth()->user()->nama }}" 
         class="rounded-full w-20 h-20 mx-auto">
</div>
     <!-- Info Guru -->
   <div class="teacher-info mb-8">
        {{-- Panggil properti 'name' dan 'nip' dari user yang login --}}
        <h2 class="teacher-name">{{ Auth::user()->nama_lengkap}}</h2>
        <p class="teacher-id">{{ Auth::user()->nip }}</p>
    </div>
    <nav class="flex flex-col space-y-4">
        {{-- Menu akan diisi dari halaman yang extend --}}
        @yield('sidebar-menu')
    </nav>

    <nav class="flex-grow flex flex-col justify-end">
    <a href="{{route('logout')}}" class="menu-item-red">Logout</a>
    </nav> 
    
</div>
