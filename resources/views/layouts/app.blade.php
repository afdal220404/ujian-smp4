<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'My App')</title>
  
  {{-- CDN --}}

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  {{-- Baris untuk Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('image/favicon.ico') }}">
    
    {{-- (Opsional) Jika Anda punya file PNG untuk kualitas lebih baik di mobile --}}
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('image/logo_sekolah2.png') }}">
  {{-- Vite Resources --}}
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])
</head>

<body class="bg-slate-80 font-[Poppins-Regular]">

    {{-- WRAPPER UTAMA: Flex Row --}}
    <div class="flex min-h-screen relative">

      @include('layouts.navbar') 

        {{-- 2. KONTEN UTAMA --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden h-screen overflow-y-auto scroll-smooth">
            
            {{-- HEADER MOBILE (Hanya muncul di HP) --}}
            <div class="md:hidden bg-white border-b border-gray-200 p-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo" class="w-8 h-8">
                    <span class="font-bold text-darkblue text-sm">Ujian Digital</span>
                </div>
                
                {{-- TOMBOL HAMBURGER --}}
                <button onclick="toggleSidebar()" class="text-darkblue focus:outline-none p-2 rounded-lg hover:bg-gray-100">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>

            {{-- AREA KONTEN --}}
            <main class="flex-1 p-4 md:p-8">
                @yield('content')
            </main>

            {{-- FOOTER --}}
            @include('layouts.footer')

        </div>
    </div>

    {{-- SCRIPT TOGGLE SIDEBAR --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            // Toggle Class Translate (Keluar/Masuk layar)
            if (sidebar.classList.contains('-translate-x-full')) {
                // Buka Sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                // Tutup Sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>
    
    @yield('scripts')
</body>
</html>