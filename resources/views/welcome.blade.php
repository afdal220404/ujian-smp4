<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Ujian SMPN 4 Tilatang Kamang</title>
    
    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Memanggil CSS Kustom --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Definisi Warna Brand (Sama seperti Login) --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#447d9b',      /* Warna Tombol */
                        darkblue: '#00415a',     /* Warna Teks/Header */
                        lightblue: '#b3cde0',    /* Warna Aksen */
                    }
                }
            }
        }
    </script>
</head>
<body class="relative h-screen w-full flex flex-col justify-between font-[Poppins-Regular] bg-gray-50">

    {{-- 1. BACKGROUND (Sama persis dengan Login) --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('image/foto_sekolah.jpg') }}" alt="Background" class="w-full h-full object-cover">
        
        {{-- Overlay Terang (Putih & Biru Muda) --}}
        <div class="absolute inset-0 bg-gradient-to-tr from-darkblue/90 via-white/85 to-lightblue/80 backdrop-blur-[2px]"></div>
        
        {{-- Texture --}}
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-30 mix-blend-multiply"></div>
    </div>

    {{-- 2. KONTEN UTAMA --}}
    <div class="relative z-10 flex-grow flex flex-col items-center justify-center px-4 w-full max-w-7xl mx-auto mt-8 md:mt-0">
        
        {{-- === AREA LOGO === --}}
        <div class="flex items-center justify-center gap-6 md:gap-14 mb-10 pt-6">
            
            {{-- A. Logo Kiri: Tut Wuri --}}
            <div class="animate-float-slow group relative">
                {{-- Container: Background Putih Transparan --}}
                <div class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-white/60 backdrop-blur-md border border-white/80 shadow-lg transition-transform duration-300 group-hover:scale-110 overflow-hidden flex items-center justify-center">
                    <img src="{{ asset('image/tut.png') }}" alt="Tut Wuri" class="w-full h-full object-cover transform scale-110 group-hover:scale-125 transition-transform duration-500">
                </div>
            </div>

            {{-- B. Logo Tengah: Sekolah (RAJA) --}}
            <div class="relative z-20 -mt-8 md:-mt-10"> 
                {{-- Efek Glow di belakang (Warna Primary) --}}
                <div class="absolute inset-0 bg-primary rounded-full blur-2xl opacity-30 animate-pulse"></div>
                
                {{-- Container Utama --}}
                <div class="w-40 h-40 md:w-56 md:h-56 bg-white rounded-full shadow-[0_10px_40px_rgba(0,65,90,0.2)] relative transition-transform duration-500 hover:rotate-2 hover:scale-105 overflow-hidden flex items-center justify-center border-[6px] border-white/50">
                    <img src="{{ asset('image/logo_sekolah.png') }}" alt="SMPN 4 Tilatang Kamang" class="w-full h-full object-cover transform scale-110 transition-transform duration-500">
                </div>
            </div>

            {{-- C. Logo Kanan: Agam --}}
            <div class="animate-float-delayed group relative">
                <div class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-white/60 backdrop-blur-md border border-white/80 shadow-lg transition-transform duration-300 group-hover:scale-110 overflow-hidden flex items-center justify-center">
                    <img src="{{ asset('image/agam.png') }}" alt="Kab. Agam" class="w-full h-full object-cover transform scale-110 group-hover:scale-125 transition-transform duration-500">
                </div>
            </div>

        </div>
        {{-- === END AREA LOGO === --}}


        {{-- === AREA TEKS (Warna Gelap) === --}}
        <div class="text-center space-y-3 mb-12 animate-fade-in-up px-2">
            
            {{-- Badge --}}
            <span class="inline-flex items-center gap-2 py-1.5 px-5 rounded-full bg-white/80 border border-primary/20 text-primary text-xs md:text-sm font-bold tracking-widest backdrop-blur-sm shadow-sm">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                SISTEM UJIAN DIGITAL
            </span>
            
            {{-- Judul Utama (Warna Dark Blue) --}}
            <h1 class="text-4xl md:text-7xl font-[Poppins-Bold] text-darkblue drop-shadow-sm tracking-tight leading-none py-2">
                Selamat Datang
            </h1>
            
            {{-- Sub Judul (Abu-abu Gelap) --}}
            <h2 class="text-lg md:text-2xl text-gray-700 font-light tracking-wide">
                SMP Negeri 4 Tilatang Kamang
            </h2>
            
            {{-- Garis Hiasan (Warna Primary) --}}
            <div class="flex items-center justify-center gap-2 mt-6 opacity-80">
                <div class="h-1 w-12 bg-gradient-to-r from-transparent to-primary rounded-full"></div>
                <div class="h-2 w-2 bg-primary rounded-full shadow-lg"></div>
                <div class="h-1 w-12 bg-gradient-to-l from-transparent to-primary rounded-full"></div>
            </div>
        </div>

        {{-- === TOMBOL MASUK (Warna Primary) === --}}
        <div class="relative group">
            {{-- Shadow Glow Biru --}}
            <div class="absolute -inset-1 bg-primary rounded-full blur opacity-25 group-hover:opacity-60 transition duration-1000 group-hover:duration-200"></div>
            
            <a href="{{ route('login') }}" class="relative inline-flex items-center justify-center px-10 py-4 text-lg md:text-xl font-bold text-white transition-all duration-200 bg-primary font-[Poppins-Bold] rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary ring-offset-white border border-white/20 hover:bg-darkblue">
                <span class="mr-3">Masuk Aplikasi</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 transition-transform group-hover:translate-x-1 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>

    </div>

    {{-- 3. FOOTER (Warna Dark Blue) --}}
    <footer class="relative z-10 w-full py-5 text-center text-darkblue/60 text-xs md:text-sm border-t border-darkblue/10 bg-white/40 backdrop-blur-md">
        <p class="font-medium tracking-wide">
            &copy; 2025 SMP Negeri 4 Tilatang Kamang. 
            <span class="block md:inline mt-1 md:mt-0 font-light">Mewujudkan Generasi Cerdas & Berkarakter.</span>
        </p>
    </footer>

</body>
</html>
