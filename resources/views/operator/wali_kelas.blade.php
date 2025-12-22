@extends('layouts.app')

@section('title', 'Halaman Wali Kelas')

@section('sidebar-menu')
{{-- Sesuaikan dengan menu aktif Anda --}}
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item">Daftar Guru</a>
{{-- ... menu lain ... --}}
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Set Wali Kelas</a>
</div>

{{-- Tampilkan notifikasi --}}
@if (session('success'))
<div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger mb-3">{{ session('error') }}</div>
@endif

<form action="{{ route('walikelas.store') }}" method="POST">
    @csrf

    <div class="filter-container mb-5">
        <button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn">
            Beranda <i class="bi bi-house-door-fill"></i>
        </button>
        <button type="submit" class="dark-btn">
            Simpan <i class="bi bi-check-square-fill"></i>
        </button>
    </div>

    <div class="overflow-x-auto">
        <div class="form-card">
            <table class="table-container">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Guru Wali</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasList as $kelas)
                    <tr>
                        <td>{{ $kelas->kelas }}</td>
                        <td>
                            <div class="form-group">
                                {{-- Nama input menggunakan array agar mudah diproses --}}
                                <select name="wali_kelas[{{ $kelas->id }}]" class="select2-guru"> {{-- <-- TAMBAHKAN KELAS INI --}}
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach($gurus as $guru)
                                    <option value="{{ $guru->id }}"
                                        {{ ($waliKelasData[$kelas->id] ?? null) == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama_lengkap }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center">Data kelas tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inisialisasi Select2 pada semua elemen dengan kelas 'select2-guru'
        // Kode ini menggunakan jQuery ($)
        $('.select2-guru').select2({
            placeholder: "Cari dan pilih guru...",
            allowClear: true
        });

        // ... (Kode notifikasi Anda yang sudah ada) ...
        const popup = document.querySelector('.popup-message');
        if (popup) {
            setTimeout(() => popup.classList.add('show'), 100);
            setTimeout(() => {
                popup.classList.remove('show');
            }, 4000);
        }
    });
</script>