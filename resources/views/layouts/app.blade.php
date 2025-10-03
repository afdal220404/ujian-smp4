<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My App')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/page.css'])
</head>
<body class="min-h-screen flex flex-col">

  <!-- Row utama: sidebar + konten -->
  <div class="flex flex-1 min-h-0">
    @include('layouts.navbar')

    <main class="flex-1 p-6">
      @yield('content')
    </main>
  </div>

  <!-- Footer selebar layar, selalu di bawah -->
  <footer class="main-footer">
    &copy; 2025 SMP Negeri 4 Tilatang Kamang. All rights reserved.
  </footer>
   @yield('scripts')
</body>
</html>
