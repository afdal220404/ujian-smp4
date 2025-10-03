<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun; // Import model Akun
use Illuminate\Support\Facades\Hash; // Import Hash

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cek dulu agar tidak ada duplikat jika seeder dijalankan berkali-kali
        Akun::firstOrCreate(
            ['username' => 'operator'], // Kriteria untuk mencari
            [
                'password_hash' => Hash::make('123456'), // Ganti password jika perlu
                'role' => 'Operator'
            ]
        );
    }
}
