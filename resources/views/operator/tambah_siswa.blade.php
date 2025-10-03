@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item">Daftar Guru</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">@if(isset($siswa)) Edit Siswa @else Tambah Siswa @endif</a>
</div>

<button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn mb-5">
    Beranda <i class="bi bi-house-door-fill"></i>
</button>

<div class="overflow-x-auto">
    <div class="form-card">
        {{-- Notifikasi error --}}
        @if ($errors->any())
        <div class="alert alert-danger mb-3 p-3 rounded">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div id="popup-success" class="popup-message success">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div id="popup-error" class="popup-message error">
            ❌ Gagal menambahkan Siswa
        </div>
        @endif
        <form class="form-container" action="@if(isset($siswa)) {{ route('siswa.update', $siswa->id) }} @else {{ route('siswa.store') }} @endif" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($siswa))
                @method('PUT')
            @endif
            <div class="form-group">
                <input type="text" name="nama" placeholder="Nama" value="{{ old('nama', $siswa->nama ?? '') }}" required>
            </div>
            <div class="form-group">
                <input type="text" name="nisn" placeholder="NISN" value="{{ old('nisn', $siswa->nisn ?? '') }}" required>
            </div>
            <div class="form-group">
                <select name="kelas" required>
                    <option disabled {{ !isset($siswa) ? 'selected' : '' }}>Kelas</option>
                    <option value="VII" {{ (old('kelas', $siswa->kelas ?? '') == 'VII') ? 'selected' : '' }}>VII</option>
                    <option value="VIII" {{ (old('kelas', $siswa->kelas ?? '') == 'VIII') ? 'selected' : '' }}>VIII</option>
                    <option value="IX" {{ (old('kelas', $siswa->kelas ?? '') == 'IX') ? 'selected' : '' }}>IX</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" value="{{ old('username', $siswa->username ?? '') }}" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder=" ">
                 <p>@if(isset($siswa))*Kosongkan jika tidak ingin mengubah password @else Password Default @endif</p>
            </div>
            <div class="form-row">
                <div class="form-action">
                    <button type="submit" class="dark-btn">@if(isset($siswa)) Update @else Tambah @endif</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const popup = document.querySelector('.popup-message');
        if (popup) {
            // Tampilkan
            setTimeout(() => popup.classList.add('show'), 100);

            // Sembunyikan setelah 4 detik
            setTimeout(() => {
                popup.classList.remove('show');
            }, 4000);
        }
    });
</script>