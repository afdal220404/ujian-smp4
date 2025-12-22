<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Guru - {{ $guru->nama_lengkap }}</title>

    {{-- Memuat Aset CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])

    <style>
        /* Mengatur body */
        body {
            font-family: 'Poppins-Regular', sans-serif;
            background-color: rgb(179, 205, 224, 0.3);
            /* Latar belakang abu-abu sangat muda */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Header Minimalis (DITAMBAHKAN) */
        .guru-top-bar {
            background: #273F4F;
            color: #fff;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .guru-top-bar .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .guru-top-bar .logo {
            height: 35px;
        }

        .guru-top-bar .app-name {
            font-family: 'Poppins-Bold';
            font-size: 20px;
        }

        .guru-top-bar .logout-btn {
            font-family: 'Poppins-Regular', sans-serif;
            padding: 8px 16px;
            background: #E74C3C;
            color: #f3f3f3;
            font-weight: bold;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
        }

        .guru-top-bar .logout-btn:hover {
            background: #c0392b;
        }

        /* 2. Konten Utama */
        .main-container {
            width: 100%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            flex-grow: 1;
        }

        /* 3. "Hero Section" Profil Guru (DIUBAH) */
        .guru-profile-hero {
            background: #FFFFFF;
            /* Diberi background putih */
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            /* Diberi shadow */
            padding: 30px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        /* 4. Menerapkan CSS Avatar Anda */
        .avatar-placeholder {
            width: 180px;
            height: 200px;
            border-radius: 50%;
            overflow: hidden;
            border: 6px solid #E5E7EB;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            flex-shrink: 0;
            /* Mencegah foto mengecil */
        }

        .avatar-placeholder:hover {
            transform: scale(1.05);
            /* Efek hover lebih halus */
        }

        .avatar-placeholder .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Perbaikan dari 'object-cover;' */
        }

        .guru-profile-hero .guru-sapaan {
            font-size: 16px;
            font-weight: 500;
            color: #4B5563;
        }

        .guru-profile-hero .guru-nama {
            font-size: 28px;
            font-weight: 700;
            color: #1F2937;
            margin: 0;
        }

        .guru-profile-hero .guru-nip {
            font-size: 16px;
            color: #6B7280;
            margin-top: 2px;
        }

        /* 5. Judul Sesi Pilihan */
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
        }

        /* 6. Kartu Peran (Desain tetap sama, sudah bagus) */
        .role-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .role-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            padding: 25px;
            gap: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            border-left: 5px solid transparent;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .role-card.wali-kelas:hover {
            border-left-color: #3B82F6;
        }

        .role-card.mapel:hover {
            border-left-color: #10B981;
        }

        .role-card .role-icon {
            font-size: 30px;
            padding: 18px;
            border-radius: 50%;
            color: #fff;
        }

        .role-card.wali-kelas .role-icon {
            background-color: #3B82F6;
        }

        .role-card.mapel .role-icon {
            background-color: #10B981;
        }

        .role-card .role-info {
            flex-grow: 1;
        }

        .role-card .role-title {
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
        }

        .role-card .role-subtitle {
            font-size: 14px;
            color: #6B7280;
        }

        .role-card .role-arrow {
            font-size: 24px;
            color: #9CA3AF;
        }


        /* 7. Footer (DIUBAH) */
        .main-footer {
            text-align: center;
            font-size: 12px;
            padding: 15px;
            background: #273F4F;
            color: #fff;
            /* Kunci untuk menempel di bawah: */
            margin-top: auto;
        }

        z
    </style>
</head>

<body>

    <header class="guru-top-bar">
        <div class="logo-container">
            <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo" class="logo">
            <span class="app-name">SMPN 4 TIKAM</span>
        </div>
        <a href="{{ route('logout') }}" class="logout-btn">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </header>

    <main class="main-container">

        <section class="guru-profile-hero">
            <div class="avatar-placeholder">
                <img src="{{ asset('storage/' . ($guru->foto ?? 'image/dummy.jpg')) }}"
                    alt="Foto {{ $guru->nama_lengkap }}"
                    class="avatar-img">
            </div>
            <div class="guru-info">
                <p class="guru-sapaan">Selamat datang!</p>
                <h2 class="guru-nama">{{ $guru->nama_lengkap }}</h2>
                <p class="guru-nip">{{ $guru->nip }}</p>
            </div>
        </section>

        <section class="roles-section">

            {{-- 1. BAGIAN WALI KELAS --}}
            @if ($waliKelasTugas)
            <h3 class="section-title">Akses Wali Kelas</h3>
            <div class="role-card-grid" style="margin-bottom: 30px;">
                <a href="{{ route('guru.walikelas.dashboard', $waliKelasTugas->kelas_id) }}" class="role-card wali-kelas">
                    <div class="role-icon"><i class="bi bi-award-fill"></i></div>
                    <div class="role-info">
                        <div class="role-title">Wali Kelas</div>
                        <div class="role-subtitle"> Kelas {{ $waliKelasTugas->kelas->kelas }}</div>
                    </div>
                    <div class="role-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
            </div>
            @endif

            {{-- 2. BAGIAN GURU MATA PELAJARAN --}}
            <h3 class="section-title">Akses Mata Pelajaran</h3>

            @if ($mapelTugas->isEmpty())
            <p>Anda belum ditugaskan untuk mengajar mata pelajaran apapun.</p>
            @else
            <div class="role-card-grid">
                @foreach ($mapelTugas as $mapel)
                <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="role-card mapel">
                    <div class="role-icon"><i class="bi bi-book-half"></i></div>
                    <div class="role-info">
                        <div class="role-title">{{ $mapel->nama_mapel }}</div>
                        <div class="role-subtitle">Kelas {{ $mapel->kelas->kelas }}</div>
                    </div>
                    <div class="role-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                @endforeach
            </div>
            @endif
        </section>
    </main>

    <footer class="main-footer">
        &copy; 2025 SMP Negeri 4 Tilatang Kamang. All rights reserved.
    </footer>

    {{-- Memuat Aset JS --}}
    @stack('scripts')
</body>

</html>