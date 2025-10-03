<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="row g-0">
        <div class="col-md-6 g-0">
            <div class="left">
                <div class="welcome-container">
                    <p class="welcome-text">Selamat Datang</p>
                    <a href="login" class="button button1">Login</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 g-0">
            <div class = "right">
            </div>
        </div>
    </div>
    <<div class="center-logo-group">
        <div class="logo-wrapper">
            <img src="{{ asset('image/tut.png') }}" alt="Logo 1">
        </div>
        <div class="logo-wrapper">
            <img src="{{ asset('image/agam.png') }}" alt="Logo 2">
        </div>
        <div class="logo-wrapper">
            <img src="{{ asset('image/logo_sekolah.png') }}" alt="Logo 3">
        </div>
    </div>
</body>
<footer class="main-footer">
    <p>© 2025 SMP Negeri 4 Tilatang Kamang. All rights reserved.</p>
</footer>
</html>