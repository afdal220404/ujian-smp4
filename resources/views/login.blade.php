<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-oranye-200 min-h-screen flex flex-col justify-between">

    <!-- Kontainer utama (login box) -->
    <div class="flex justify-center items-center min-h-screen bg-[#F8F9FA]">
        <div class="w-3/4 bg-white rounded-2xl overflow-hidden shadow-2xl flex">

            <!-- Left -->
            <div class="left-login relative">
                <div class="absolute inset-0 bg-black bg-opacity-50 rounded-l-2xl"></div>
                <div class="relative z-10 text-center">
                    <h2 class="text-welcome">Selamat Datang</h2>
                    <p class="text-white text-lg">Silakan login untuk melanjutkan</p>
                </div>
            </div>

            <!-- Right Section -->
            <div class="w-2/3 flex flex-col items-center justify-center p-12">
                <h2 class="text-2xl font-semibold mb-6 self-start text-gray-700">Masukkan Data Anda</h2>

                <!-- Logo -->
                <div class="w-40 h-40 mb-6 flex items-center justify-center">
                    <img src="{{ asset('image/logo_sekolah2.png') }}" alt="Logo Sekolah" class="object-contain">
                </div>

                <form action="{{ route('login.process') }}" method="POST" class="form-login">
                    @csrf
                    <label class="label-input">Username</label>
                    <input type="text" name="username" placeholder="Masukkan Username Anda" class="input-field" required>
                    @error('username')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <label class="label-input">Password</label>
                    <input type="password" name="password" placeholder="Masukkan Password Anda" class="input-field" required>
                    @error('password')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    @error('login') <!-- Untuk error custom -->
                        <div class="text-red-500 text-sm mt-3">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="button w-full">Login</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        &copy; 2025 SMP Negeri 4 Tilatang Kamang. All rights reserved.
    </footer>
</body>

</html>