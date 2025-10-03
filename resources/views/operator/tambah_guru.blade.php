@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{ route('daftar_guru2') }}" class="menu-item">Daftar Guru</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">@if(isset($guru)) Edit Guru @else Tambah Guru @endif</a>
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
            ❌ Gagal menambahkan guru
        </div>
        @endif
        <form class="form-container" action="@if(isset($guru)) {{ route('guru.update', $guru->id) }} @else {{ route('guru.store') }} @endif" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($guru))
                @method('PUT')
            @endif
            <div class="form-group">
                <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" value="{{ old('nama_lengkap', $guru->nama_lengkap ?? '') }}" required>
            </div>
            <div class="form-group">
                <select name="role" required>
                    <option disabled {{ !isset($guru) ? 'selected' : '' }}>role</option>
                    <option value="Kepala Sekolah" {{ (old('role', $guru->role ?? '') == 'Kepala Sekolah') ? 'selected' : '' }}>Kepala Sekolah</option>
                    <option value="Operator" {{ (old('role', $guru->role ?? '') == 'Operator') ? 'selected' : '' }}>Operator</option>
                    <option value="Guru" {{ (old('role', $guru->role ?? '') == 'Guru') ? 'selected' : '' }}>Guru</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="nip" placeholder="NIP" value="{{ old('nip', $guru->nip ?? '') }}" required>
            </div>
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" value="{{old('username', isset($guru) ? $guru->akun->username : '') }}" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="password">
                <p>@if(isset($guru)) Kosongkan jika tidak ingin mengubah password @else Password Default @endif</p>
            </div>
            <div class="form-row">
                <div class="form-group photo-group">
                    <label for="photo" class="upload-btn">
                        <i class="bi bi-upload"></i> Pilih Foto
                    </label>
                    <input type="file" id="photo" name="foto" accept="image/*" onchange="previewImage()" hidden>
                    <img id="preview" src="@if(isset($guru) && $guru->foto) {{ asset('storage/'.$guru->foto) }} @else # @endif" alt="Preview Foto" class="preview-image" @if(isset($guru) && $guru->foto) style="display: block;" @else style="display: none;" @endif>
                </div>
                <div class="form-action">
                    <button type="submit" class="dark-btn">@if(isset($guru)) Update @else Tambah @endif</button>
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
<script>
    function previewImage() {
        const file = document.getElementById('photo').files[0];
        const preview = document.getElementById('preview');
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = 'block';
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "#";
            preview.style.display = 'none';
        }
    }
</script>