<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'My App')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])
</head>

<body class="min-h-screen flex flex-col">
 {{-- Memanggil Sidebar --}}
        @include('layouts.navbar')
  {{-- Pembungkus untuk Konten Utama dan Footer --}}
        <div class="main-content flex-1 flex flex-col">

            {{-- Area Konten --}}
            <main class="content-area flex-grow p-6">
                @yield('content')
            </main>

            {{-- Footer SEKARANG DI SINI --}}
            <footer class="main-footer">
                &copy; 2025 SMP Negeri 4 Tilatang Kamang. All rights reserved.
            </footer>
        </div>

    </div>
  @yield('scripts')
</body>
</html>