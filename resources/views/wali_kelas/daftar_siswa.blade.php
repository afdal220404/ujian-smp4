@extends('layouts.app')

@section('title', 'Halaman Guru Mapel')

@section('sidebar-menu')
<a href="" class="menu-item active">Daftar Siswa</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Daftar Siswa Kelas VII</a>
</div>
<button type="button" onclick="window.location='{{ route('landingpage4') }}'" class="dark-btn mb-5">
    Beranda <i class="bi bi-house-door-fill"></i>
</button>
<div class="overflow-x-auto">
    <table class="table-container">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Rata Rata Kuis</th>
                <th>Rata Rata UTS</th>
                <th>Rata Rata UAS</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Budi Santoso</td>
                <td>1920048</td>
                <td>80</td>
                <td>88</td>
                <td>92</td>
                <td>
                    <button type="button" onclick="window.location='{{ route('tambah_siswa') }}'" class="table-btn ">
                        Detail
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>

@endsection