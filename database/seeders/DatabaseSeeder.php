<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Baris di bawah ini mencoba membuat user default dan menyebabkan error
        // \App\Models\User::factory(10)->create(); 

        // Anda bisa biarkan atau hapus baris di bawah ini juga
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Pastikan hanya seeder yang Anda butuhkan yang dipanggil
        $this->call([
            GuruSeeder::class,
            // Jika Anda punya KelasSeeder, tambahkan di sini
        ]);
    }
}