@extends('layouts.app')

@section('title', 'Halaman Kepala Sekolah')

@section('sidebar-menu')
    <a href="" class="menu-item ">Daftar Nilai</a>
    <a href="" class="menu-item">Daftar Siswa</a>
    <a href="" class="menu-item active">Daftar Guru</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul text-xl font-bold">Detail Nilai Siswa</a>
</div>

<!-- Informasi Siswa -->
<div class="bg-gray-100 rounded-lg p-3 mb-5">
    <div class="bg-gray-100 rounded-lg p-3">
  <div class="flex items-center mb-1">
    <span class="w-28 font-semibold">Nama</span>
    <span class="mx-2">:</span>
    <span id="student-name">Budi Santoso</span>
  </div>
  <div class="flex items-center mb-1">
    <span class="w-28 font-semibold">NISN</span>
    <span class="mx-2">:</span>
    <span id="student-nisn">1920048</span>
  </div>
  <div class="flex items-center">
    <span class="w-28 font-semibold">Rata-rata</span>
    <span class="mx-2">:</span>
    <span id="student-average">87</span>
  </div>
</div>


    <!-- Tombol Aksi -->
<div class="flex gap-4">
    <button class="bg-gray-500 text-white px-3 py-1 text-sm rounded-md hover:bg-gray-600">Kelas 7</button>
    <button class="bg-gray-500 text-white px-3 py-1 text-sm rounded-md hover:bg-gray-600">Kelas 8</button>
    <button class="bg-gray-500 text-white px-3 py-1 text-sm rounded-md hover:bg-gray-600">Kelas 9</button>

</div>
</div>

<!-- Tabel Nilai -->
<div class="overflow-x-auto">
    <table class="table-container">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mata Pelajaran</th>
                        <th>Kuis 1</th>
                        <th>Kuis 2</th>
                        <th>Kuis 3</th>
                        <th>UTS</th>
                        <th>UAS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Budi Santoso</td>
                        <td>1920048</td>
                        <td>1920048</td>   
                        <td>1920048</td>
                        <td>1920048</td>
                        <td>1920048</td>
                        <td>1920048</td>
                    </tr>
                </tbody>
            </table>
</div>
@endsection
