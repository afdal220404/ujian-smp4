<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMPN 4 Tilatang Kamang</title>
    
    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- CSS Custom --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Konfigurasi Warna Kustom Sesuai Brand --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#447d9b',      /* Warna Tombol & Footer */
                        darkblue: '#00415a',     /* Warna Sidebar/Header */
                        lightblue: '#b3cde0',    /* Warna Background Muda */
                    }
                }
            }
        }
    </script>

    <style>
        /* Override autofill browser agar cocok dengan tema */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #f9fafb inset !important; /* bg-gray-50 */
            -webkit-text-fill-color: #00415a !important; /* Teks Biru Gelap */
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="relative h-screen w-full font-[Poppins-Regular] overflow-hidden flex flex-col justify-between bg-[#F8F9FA]">

    {{-- 1. BACKGROUND --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('image/foto_sekolah.jpg') }}" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-tr from-darkblue/90 via-white/80 to-lightblue/80 backdrop-blur-[3px]"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-30 mix-blend-multiply"></div>
    </div>

    {{-- 2. KONTEN UTAMA (Centered) --}}
    {{-- Menggunakan flex-grow agar mengisi ruang kosong di tengah --}}
    <div class="relative z-10 flex-grow flex items-center justify-center w-full px-4 md:px-0 animate-fade-in-up">
        
        <div class="w-full max-w-[420px] mx-auto">
            
            {{-- HEADER: LOGO & JUDUL --}}
            <div class="flex items-center justify-center gap-5 mb-8">
                <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo Sekolah" class="w-16 h-16 md:w-20 md:h-20 object-contain rounded-3xl drop-shadow-lg hover:scale-105 transition-transform">
                
                <div class="text-left">
                    <h2 class="text-2xl md:text-3xl font-[Poppins-Bold] text-darkblue tracking-tight leading-none">
                        Sistem Ujian Digital
                    </h2>
                    <div class="h-1.5 w-24 bg-primary rounded-full my-2"></div>
                    <p class="text-sm md:text-base text-gray-600 font-medium">
                        SMPN 4 Tilatang Kamang
                    </p>
                </div>
            </div>

            {{-- KARTU LOGIN --}}
            <div class="bg-white/90 backdrop-blur-xl border border-white/60 rounded-3xl p-8 shadow-[0_20px_50px_rgba(0,65,90,0.2)] relative overflow-hidden">
                
                {{-- Hiasan Garis Atas --}}
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-lightblue via-primary to-lightblue"></div>

                {{-- Alert Error --}}
                @if(session('error'))
                    <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 flex items-center gap-3 text-sm shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm shadow-sm">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- FORM START --}}
                <form action="{{ route('login.process') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    {{-- Input Username --}}
                    <div>
                        <label class="block text-darkblue text-sm font-bold mb-2 ml-1">Username / NISN</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" name="username" placeholder="Masukkan ID Pengguna" required 
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-darkblue placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 shadow-inner">
                        </div>
                    </div>

                    {{-- Input Password --}}
                    <div>
                        <label class="block text-darkblue text-sm font-bold mb-2 ml-1">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" placeholder="Masukkan Kata Sandi" required 
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-darkblue placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 shadow-inner">
                        </div>
                    </div>

                    {{-- Tombol Login --}}
                    <button type="submit" 
                        class="w-full mt-4 py-3.5 px-4 bg-primary hover:bg-darkblue text-white font-[Poppins-Bold] rounded-xl shadow-[0_4px_14px_0_rgba(68,125,155,0.39)] hover:shadow-[0_6px_20px_rgba(0,65,90,0.23)] transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                        <span>MASUK APLIKASI</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                </form>
                {{-- FORM END --}}
            </div>
            
            {{-- Tombol Kembali --}}
            <div class="text-center mt-6">
                <a href="{{ url('/') }}" class="inline-flex items-center text-primary hover:text-darkblue transition-colors text-sm font-semibold gap-2 group">
                    <span class="group-hover:-translate-x-1 transition-transform">←</span>
                    Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>

    {{-- 3. FOOTER BARU (Menempel di Bawah) --}}
    <footer class="relative z-10 w-full py-5 text-center text-darkblue/60 text-xs md:text-sm border-t border-darkblue/10 bg-white/40 backdrop-blur-md">
        <p class="font-medium tracking-wide">
            &copy; 2025 SMP Negeri 4 Tilatang Kamang.
            <span class="block md:inline mt-1 md:mt-0 font-light">Mewujudkan Generasi Cerdas & Berkarakter.</span>
        </p>
    </footer>

</body>
</html>
