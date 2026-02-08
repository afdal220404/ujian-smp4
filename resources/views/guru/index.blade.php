<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Guru - {{ $guru->nama_lengkap }}</title>
    {{-- Baris untuk Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('image/favicon.ico') }}">
    
    {{-- (Opsional) Jika Anda punya file PNG untuk kualitas lebih baik di mobile --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('image/logo_sekolah2.png') }}">

    {{-- Aset & Library --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    {{-- Memuat CSS Global --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])

    {{-- Konfigurasi Warna Brand --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#447d9b',
                        darkblue: '#00415a',
                        lightblue: '#b3cde0',
                    },
                    fontFamily: {
                        sans: ['Poppins-Regular', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        @font-face {
            font-family: 'Poppins-Regular';
            src: url('/font/Poppins/Poppins-Regular.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Poppins-Bold';
            src: url('/font/Poppins/Poppins-Bold.ttf') format('truetype');
        }

        body {
            font-family: 'Poppins-Regular', sans-serif;
            background-color: rgba(179, 205, 224, 0.3);
        }
        
        .font-bold-custom {
            font-family: 'Poppins-Bold', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-slate-800">

    {{-- 1. NAVBAR --}}
    <nav class="bg-darkblue sticky top-0 z-50 border-b border-white/10 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                {{-- Logo & Judul --}}
                <div class="flex items-center gap-4">
                    <div class="p-1.5 bg-white/10 rounded-xl backdrop-blur-sm border border-white/20 shadow-inner">
                        <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo" class="h-10 w-10 object-contain hover:scale-110 transition-transform duration-300">
                    </div>
                    <div class="flex flex-col text-white">
                        <span class="font-bold-custom text-lg leading-tight tracking-wide">Dashboard Guru</span>
                        <span class="text-xs text-blue-200 opacity-90">SMPN 4 Tilatang Kamang</span>
                    </div>
                </div>

                {{-- Tombol Logout --}}
                <a href="{{ route('logout') }}" 
                   class="group flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium text-white bg-white/10 border border-white/20 hover:bg-red-500 hover:border-red-500 hover:shadow-red-500/30 transition-all duration-300">
                    <i class="bi bi-power text-lg group-hover:scale-110 transition-transform"></i>
                    <span class="hidden md:inline">Keluar</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- 2. KONTEN UTAMA --}}
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- A. HERO PROFILE SECTION --}}
        <div class="relative bg-white/80 backdrop-blur-xl rounded-3xl p-8 md:p-10 shadow-[0_10px_40px_-10px_rgba(0,65,90,0.15)] overflow-hidden border border-white/60 animate-fade-in-down">
            
            <div class="absolute top-0 right-0 w-full h-2 bg-gradient-to-r from-darkblue via-primary to-lightblue"></div>
            <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-blue-50 blur-3xl opacity-60 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-8">
                
                <div class="flex-shrink-0 relative group">
                    <div class="absolute -inset-1 bg-gradient-to-tr from-primary to-darkblue rounded-full blur opacity-20 group-hover:opacity-40 transition duration-500"></div>
                    <div class="relative w-28 h-28 md:w-32 md:h-32 rounded-full border-4 border-white shadow-xl overflow-hidden bg-gray-100">
                        @if($guru->foto)
                            <img src="{{ asset('storage/' . $guru->foto) }}" alt="Foto Profil" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('image/dummy.jpg') }}" alt="Default" class="w-full h-full object-cover opacity-90">
                        @endif
                    </div>
                    <div class="absolute bottom-1 right-1 w-6 h-6 bg-green-500 border-4 border-white rounded-full" title="Aktif"></div>
                </div>

                <div class="text-center md:text-left flex-grow space-y-3 pt-2">
                    <div>
                        <h1 class="text-2xl md:text-4xl font-bold-custom text-darkblue drop-shadow-sm">
                            Halo, {{ $guru->nama_lengkap }}! 👋
                        </h1>
                        <p class="text-gray-500 text-sm md:text-base font-medium">
                            Selamat datang di Sistem Ujian Digital.
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-2">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-darkblue text-sm font-semibold border border-blue-100 shadow-sm">
                            <i class="bi bi-person-badge text-primary text-lg"></i>
                            <div class="flex flex-col text-left leading-none gap-0.5">
                                <span class="text-[10px] text-gray-400 uppercase tracking-wider">NIP</span>
                                <span>{{ $guru->nip ?? '-' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-darkblue text-sm font-semibold border border-blue-100 shadow-sm">
                            <i class="bi bi-mortarboard text-primary text-lg"></i>
                            <div class="flex flex-col text-left leading-none gap-0.5">
                                <span class="text-[10px] text-gray-400 uppercase tracking-wider">Peran</span>
                                <span>{{ $guru->role ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- B. DASHBOARD GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LOGIKA BARU: Jika Guru ADALAH Wali Kelas, tampilkan kolom ini --}}
            @if ($waliKelasTugas)
            <div class="lg:col-span-1 space-y-5">
                <div class="flex items-center gap-3 px-1">
                    <div class="w-1.5 h-8 bg-yellow-500 rounded-full shadow-sm"></div>
                    <h3 class="text-xl font-bold-custom text-darkblue">Wali Kelas</h3>
                </div>

                <a href="{{ route('guru.walikelas.dashboard', $waliKelasTugas->kelas->id) }}" 
                   class="group block bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-yellow-400 hover:shadow-[0_10px_30px_rgba(234,179,8,0.15)] hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                    
                    <div class="absolute top-0 right-0 w-24 h-24 bg-yellow-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    
                    <div class="relative z-10 flex flex-col items-center text-center space-y-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg shadow-yellow-500/20 transform group-hover:rotate-6 transition-transform duration-300">
                             <i class="bi bi-journal-x text-3xl text-yellow-700"></i>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-bold-custom text-gray-800">Kelas {{ $waliKelasTugas->kelas->kelas }}</h4>
                            <p class="text-sm text-gray-500 font-medium">Kelola Siswa & Laporan</p>
                        </div>
                        
                        <div class="w-full py-2.5 bg-yellow-50 text-yellow-700 text-sm font-bold rounded-xl group-hover:bg-yellow-500 group-hover:text-white transition-all shadow-sm">
                            Masuk Kelas <i class="bi bi-arrow-right ml-1"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            {{-- LOGIKA BARU: Kolom Mapel menjadi Full Width (col-span-3) jika tidak ada Wali Kelas --}}
            <div class="{{ $waliKelasTugas ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-5">
                <div class="flex items-center gap-3 px-1">
                    <div class="w-1.5 h-8 bg-primary rounded-full shadow-sm"></div>
                    <h3 class="text-xl font-bold-custom text-darkblue">Mata Pelajaran</h3>
                </div>

                @if ($mapelTugas->isEmpty())
                    <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-10 text-center border-2 border-dashed border-blue-200">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-300">
                            <i class="bi bi-journal-x text-3xl"></i>
                        </div>
                        <p class="text-darkblue font-medium">Belum ada mata pelajaran yang ditugaskan.</p>
                    </div>
                @else
                    {{-- Grid Mapel juga menyesuaikan: Jika full width, bisa tampil 3 kolom (lg:grid-cols-3), jika tidak 2 kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 {{ $waliKelasTugas ? '' : 'xl:grid-cols-3' }} gap-5">
                        @foreach ($mapelTugas as $mapel)
                            <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" 
                               class="group relative bg-white rounded-2xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 hover:border-primary/50 hover:shadow-[0_10px_30px_rgba(68,125,155,0.15)] hover:-translate-y-1 transition-all duration-300 flex items-center gap-4 overflow-hidden">
                                
                                <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                                <div class="w-14 h-14 flex-shrink-0 bg-lightblue/20 text-darkblue rounded-2xl flex items-center justify-center text-2xl group-hover:bg-primary group-hover:text-white transition-colors duration-300 shadow-sm">
                                    <i class="bi bi-journal-bookmark-fill"></i>
                                </div>

                                <div class="flex-grow min-w-0 z-10">
                                    <h4 class="font-bold-custom text-gray-800 text-lg truncate group-hover:text-primary transition-colors">
                                        {{ $mapel->nama_mapel }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 group-hover:bg-white group-hover:text-primary group-hover:shadow-sm transition-all">
                                            <i class="bi bi-building mr-1.5"></i> Kelas {{ $mapel->kelas->kelas }}
                                        </span>
                                    </div>
                                </div>

                                <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white transition-all duration-300 transform group-hover:translate-x-1 shadow-sm">
                                    <i class="bi bi-chevron-right text-xs font-bold"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </main>

    {{-- 3. FOOTER --}}
    <footer class="bg-white/40 backdrop-blur-md border-t border-white/20 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="font-medium tracking-wide">
            &copy; 2025 SMP Negeri 4 Tilatang Kamang. 
            <span class="block md:inline mt-1 md:mt-0 font-light">Mewujudkan Generasi Cerdas & Berkarakter.</span>
        </p>
        </div>
    </footer>

</body>
</html>