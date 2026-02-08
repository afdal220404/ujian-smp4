<?php

use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "--- DEBUG START ---\n";

// 1. Create Test User
$username = 'siswa_debug';
$password = 'password123';
$nisn = '9999999999';

$siswa = Siswa::updateOrCreate(
    ['username' => $username],
    [
        'nama_lengkap' => 'Siswa Debug',
        'password' => Hash::make($password),
        'nisn' => $nisn,
        'kelas_id' => 1 // Assuming class ID 1 exists
    ]
);

echo "Test User: $username / $password\n";
echo "Hash in DB: " . $siswa->password . "\n";

// 2. Attempt Login
$credentials = ['username' => $username, 'password' => $password];

try {
    $attempt = Auth::guard('siswa')->attempt($credentials);
    echo "Auth Attempt Result: " . ($attempt ? "SUCCESS" : "FAIL") . "\n";
    
    if ($attempt) {
        $user = Auth::guard('siswa')->user();
        echo "Logged in User: " . ($user ? $user->nama_lengkap : 'NULL') . "\n";
    }
} catch (\Exception $e) {
    echo "Exception during attempt: " . $e->getMessage() . "\n";
}

echo "--- DEBUG END ---\n";
