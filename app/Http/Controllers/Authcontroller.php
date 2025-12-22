<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Illuminate\Support\Facades\Session;
use App\Models\Akun;

class Authcontroller extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function loginProcess(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user(); // Ini akan menjadi objek Guru
            $role = $user->role;

            // Logika redirect berdasarkan role
            if ($role === 'Operator') {
                return redirect()->intended(route('operator.landingpage'));
            } elseif ($role === 'Guru') {
                return redirect()->intended(route('landingpage2'));
            } elseif ($role === 'Kepala Sekolah') {
                return redirect()->intended(route('landingpage'));
            }

            return redirect('/');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request) // Tambahkan Request $request
    {
        Auth::logout();

        // PERBAIKAN 3: Praktik terbaik untuk keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    public function tesSesi(Request $request)
    {
        // Cek apakah session dengan kunci 'test_key' sudah ada
        if ($request->session()->has('test_key')) {

            // JIKA SUDAH ADA, berarti Laravel berhasil MEMBACA session yang disimpan sebelumnya.
            dd('SUKSES! Sistem Sesi Bekerja Dengan Baik. Laravel berhasil membaca data.');
        } else {

            // JIKA BELUM ADA, kita akan mencoba MEMBUAT session baru.
            $request->session()->put('test_key', 'data_tes_123');
            $request->session()->save(); // Paksa untuk menyimpan session

            return 'Langkah 1 Selesai: Sesi baru saja dibuat. Sekarang, silakan REFRESH halaman ini (tekan F5).';
        }
    }
}
