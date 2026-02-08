<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation; 

class SiswaImport implements ToModel, WithHeadingRow, WithValidation 
{
    /**
    * @param array $row
    */
    public function model(array $row)
    {
        // Karena kita sudah pakai validasi di bawah (rules),
        // Kita yakin data di sini sudah pasti benar & kelasnya ada.
        
        // Cari ID kelas berdasarkan Namanya
        $kelas = Kelas::where('kelas', trim($row['kelas']))->first();

        return new Siswa([
            'nama_lengkap' => $row['nama_lengkap'],
            'nisn'         => $row['nisn'],
            'kelas_id'     => $kelas->id, 
            'username'     => $row['username'],
            'password'     => Hash::make($row['password'] ?? '123456'),
        ]);
    }

    /**
     * ATURAN VALIDASI
     * Sistem akan mengecek ini dulu sebelum insert ke database
     */
    public function rules(): array
    {
        return [
            // Kolom 'nama_lengkap' wajib diisi
            'nama_lengkap' => 'required',

            // NISN wajib, harus angka, dan tidak boleh kembar di tabel siswa
            'nisn' => 'required|numeric|unique:siswas,nisn',

            // Kelas wajib diisi, DAN teks-nya harus ada di tabel 'kelas' kolom 'kelas'
            // Ini menggantikan pengecekan manual "if (!$kelas)"
            'kelas' => 'required|exists:kelas,kelas',

            // Username harus unik
            'username' => 'required|unique:siswas,username',
        ];
    }

   
    public function customValidationMessages()
    {
        return [
            // Validasi Nama
            'nama_lengkap.required' => 'Nama Lengkap wajib diisi.',

            // Validasi NISN
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.numeric'  => 'Format NISN salah. Harus berupa angka.',
            'nisn.unique'   => 'NISN ":input" sudah terdaftar di database. Cek data siswa lama atau perbaiki Excel.',

            // Validasi Username
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username ":input" sudah dipakai siswa lain. Ganti dengan yang baru.',

            // Validasi Kelas
            'kelas.required' => 'Kolom Kelas wajib diisi.',
            'kelas.exists'   => 'Kelas ":input" tidak ditemukan di database. Pastikan penulisan SAMA PERSIS (Contoh: VII A, bukan 7A).',
        ];
    }
}