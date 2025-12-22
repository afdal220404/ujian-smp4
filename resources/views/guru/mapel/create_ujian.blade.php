@extends('layouts.app')

@section('title', 'Buat Ujian Baru')

@section('sidebar-menu')
    {{-- Ini adalah menu konteks Guru Mapel --}}
    <a href="{{ route('guru.index') }}" class="menu-item">
        <i class="bi bi-arrow-left"></i> Kembali ke Pilihan
    </a>
    <hr class="sidebar-divider">
    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item">
        <i class="bi bi-pie-chart-fill"></i> Dasbor Mapel
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item">
        <i class="bi bi-card-checklist"></i> Daftar Nilai Siswa
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Detail Ujian</a>
    <p class="sub-judul" style="text-align: center; margin-top: 10px;">
        Mata Pelajaran: <strong>{{ $mapel->nama_mapel }} ({{ $mapel->kelas->kelas }})</strong>
    </p>
</div>

{{-- Tampilkan error/sukses --}}
@if ($errors->any())
    <div class="alert alert-danger mb-4">
        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

{{-- Form ini sekarang memiliki DUA tombol submit --}}
<div class="ujian-form-container">
    <form action="{{ isset($ujian) ? route('guru.mapel.ujian.update', ['mapel' => $mapel->id, 'ujian' => $ujian->id]) : route('guru.mapel.ujian.store', $mapel->id) }}" method="POST">
        @csrf
        
        {{-- Input data ujian (sama seperti sebelumnya, sudah diisi oleh $ujianDetails) --}}
        <div class="ujian-form-group">
            <label class="ujian-form-label" for="nama_ujian">Nama Ujian</label>
            <input type="text" id="nama_ujian" name="nama_ujian" class="ujian-form-input" value="{{ old('nama_ujian', $ujianDetails['nama_ujian'] ?? '') }}" required>
        </div>
        
        <div class="ujian-form-row">
            <div class="ujian-form-group" style="flex: 1;">
                <label class="ujian-form-label" for="jenis_ujian">Jenis Ujian</label>
                <select id="jenis_ujian" name="jenis_ujian" class="ujian-form-select" required>
                    <option value="" disabled {{ !isset($ujianDetails['jenis_ujian']) ? 'selected' : '' }}>Pilih Jenis Ujian</option>
                    <option value="Kuis" {{ (old('jenis_ujian', $ujianDetails['jenis_ujian'] ?? '') == 'Kuis') ? 'selected' : '' }}>Kuis</option>
                    <option value="UTS" {{ (old('jenis_ujian', $ujianDetails['jenis_ujian'] ?? '') == 'UTS') ? 'selected' : '' }}>UTS</option>
                    <option value="UAS" {{ (old('jenis_ujian', $ujianDetails['jenis_ujian'] ?? '') == 'UAS') ? 'selected' : '' }}>UAS</option>
                </select>
            </div>
            <div class="ujian-form-group" style="flex: 1;">
                <label class="ujian-form-label" for="tanggal_ujian">Hari dan Tanggal</label>
                <input type="date" id="tanggal_ujian" name="tanggal_ujian" class="ujian-form-input" value="{{ old('tanggal_ujian', $ujianDetails['tanggal_ujian'] ?? '') }}" required>
            </div>
        </div>

        <div class="ujian-form-row">
            <div class="ujian-form-group" style="flex: 1;">
                <label class="ujian-form-label" for="waktu_mulai">Waktu Mulai</label>
                <input type="time" id="waktu_mulai" name="waktu_mulai" class="ujian-form-input" value="{{ old('waktu_mulai', $ujianDetails['waktu_mulai'] ?? '') }}" required>
            </div>
            <div class="ujian-form-group" style="flex: 1;">
                <label class="ujian-form-label" for="waktu_selesai">Waktu Selesai</label>
                <input type="time" id="waktu_selesai" name="waktu_selesai" class="ujian-form-input" value="{{ old('waktu_selesai', $ujianDetails['waktu_selesai'] ?? '') }}" required>
            </div>
            <div class="ujian-form-group" style="flex: 1;">
                <label class="ujian-form-label" for="durasi">Durasi (Menit)</label>
                <input type="text" id="durasi" name="durasi" class="ujian-form-input" value="{{ $ujianDetails['durasi_menit'] ?? '' }} Menit" placeholder="Akan terisi otomatis" disabled>
            </div>
        </div>
        
        <div class="ujian-form-group">
            <label class="ujian-form-label">Jumlah Soal Ditambahkan</label>
            <input type="text" class="ujian-form-input" value="{{ $jumlahSoal }} Soal" disabled>
        </div>

        {{-- Dua Tombol Aksi --}}
        <div class="ujian-form-action" style="justify-content: space-between;">
            <button type="submit" name="action" value="simpan_ujian" class="dark-btn" style="background-color: #10B981; border: none;">
                <i class="bi bi-check-circle-fill"></i> {{ isset($ujian) ? 'Update Ujian' : 'Simpan Ujian' }}
            </button>
            <button type="submit" name="action" value="tambah_soal" class="dark-btn">
                Kelola Soal <i class="bi bi-arrow-right-circle-fill"></i>
            </button>
        </div>
    </div>
</form>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const waktuMulaiEl = document.getElementById('waktu_mulai');
        const waktuSelesaiEl = document.getElementById('waktu_selesai');
        const durasiEl = document.getElementById('durasi');

        function hitungDurasi() {
            const waktuMulai = waktuMulaiEl.value;
            const waktuSelesai = waktuSelesaiEl.value;

            if (waktuMulai && waktuSelesai) {
                // Buat objek Date palsu untuk menghitung selisih
                const tgl = '1970-01-01T';
                const mulai = new Date(tgl + waktuMulai + ':00');
                const selesai = new Date(tgl + waktuSelesai + ':00');

                if (selesai < mulai) {
                    durasiEl.value = 'Waktu selesai harus setelah waktu mulai';
                    return;
                }

                // Hitung selisih dalam milidetik, lalu ubah ke menit
                const selisihMs = selesai.getTime() - mulai.getTime();
                const selisihMenit = selisihMs / 60000;

                durasiEl.value = selisihMenit + " Menit";
            }
        }

        // Panggil fungsi saat input berubah
        waktuMulaiEl.addEventListener('change', hitungDurasi);
        waktuSelesaiEl.addEventListener('change', hitungDurasi);

        // Panggil sekali saat memuat jika ada old value
        hitungDurasi();
    });
</script>
