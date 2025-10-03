@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item">Daftar Guru</a>
<a href="{{route('logout')}}" class="menu-item">Logout</a>
@endsection

@section('content')
<h2 class="sapaan">SELAMAT DATANG<br>BAPAK BAYU S.T</h2>
<div class="menu-container">
    <div class="menu-buttons">
        <button type="button" onclick="window.location='{{ route('tambah_siswa') }}'" class="menu-btn">
            Tambah Siswa Baru
        </button>
        <button type="button" onclick="window.location='{{ route('tambah_guru') }}'" class="menu-btn">
            Tambah Guru Baru
        </button>
        <button type="button" onclick="window.location='{{ route('wali_kelas') }}'" class="menu-btn">
            Wali Kelas
        </button>
        <button type="button" onclick="window.location='{{ route('mapel') }}'" class="menu-btn">
            Mata Pelajaran
        </button>
    </div>
</div>

@endsection