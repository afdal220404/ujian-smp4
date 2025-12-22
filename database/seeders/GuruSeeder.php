<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run()
    {
        Guru::create([
            'nama_lengkap' => 'bayu, S.T',
            'username' => 'operator',
            'password' => Hash::make('123456'),
            'role' => 'Operator',
            'nip' => '020517',
            'foto' => 'images/dummy.jpg', // path foto di public/
        ]);
    }
}
