@extends('layouts.app')

@section('title', 'Buat Ujian Baru')

@section('sidebar-menu')
{{-- Tombol Kembali --}}
<div class="mb-4 px-3">
    <a href="{{ route('guru.index') }}"
        class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
        <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
    </a>
</div>

    
    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.siswa') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.bank_soal.*') ? 'active' : '' }}">
        <i class="bi bi-archive-fill"></i> <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.mapel.arsip_soal_siswa.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.arsip_soal_siswa.*') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> <span>Arsip Soal Siswa</span>
    </a>
@endsection

@section('content')

{{-- 1. STEPPER (Indikator Langkah) --}}
<div class="max-w-3xl mx-auto mb-8">
    <div class="flex items-center justify-center">
        {{-- Step 1 (Aktif) --}}
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold shadow-lg ring-4 ring-blue-100 z-10">
                1
            </div>
            <span class="mt-2 text-xs font-bold text-blue-600 uppercase tracking-wider">Info Ujian</span>
        </div>
        
        {{-- Garis Penghubung --}}
        <div class="w-24 h-1 bg-gray-200 -mt-6 mx-2"></div>
        
        {{-- Step 2 (Non-Aktif) --}}
        <div class="flex flex-col items-center opacity-40">
            <div class="w-10 h-10 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center font-bold z-10">
                2
            </div>
            <span class="mt-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Input Soal</span>
        </div>
    </div>
</div>

{{-- 2. HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-[Poppins-Bold] text-darkblue tracking-tight">
            {{ isset($ujian) ? 'Edit Konfigurasi Ujian' : 'Buat Ujian Baru' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Mapel: <span class="font-bold text-blue-600">{{ $mapel->nama_mapel }}</span> • 
            Kelas: <span class="font-bold text-gray-700">{{ $mapel->kelas->kelas }}</span>
        </p>
    </div>
</div>

{{-- Alert Error --}}
@if ($errors->any())
<div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm">
    <p class="font-bold text-sm mb-1">Terjadi Kesalahan:</p>
    <ul class="list-disc list-inside text-xs">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

{{-- 3. FORM CARD --}}
<div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden">
    <div class="h-1 w-full bg-gradient-to-r from-blue-500 to-cyan-400"></div>

    <form action="{{ isset($ujian) ? route('guru.mapel.ujian.update', ['mapel' => $mapel->id, 'ujian' => $ujian->id]) : route('guru.mapel.ujian.store', $mapel->id) }}" method="POST" class="p-6 md:p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            {{-- Nama Ujian --}}
            <div class="col-span-1 md:col-span-2 group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Ujian / Judul</label>
                <input type="text" name="nama_ujian" 
                       value="{{ old('nama_ujian', $ujianDetails['nama_ujian'] ?? '') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-bold text-gray-700 placeholder-gray-400"
                       placeholder="Contoh: Kuis Bab 1..." required>
            </div>

            {{-- Jenis Ujian --}}
            <div class="group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jenis Evaluasi</label>
                <div class="relative">
                    <select name="jenis_ujian" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all appearance-none font-medium text-gray-700 cursor-pointer" required>
                        <option value="" disabled {{ !isset($ujianDetails['jenis_ujian']) ? 'selected' : '' }}>-- Pilih Jenis --</option>
                        <option value="Kuis" {{ (old('jenis_ujian', $ujianDetails['jenis_ujian'] ?? '') == 'Kuis') ? 'selected' : '' }}>Kuis (Latihan)</option>
                        <option value="UTS" {{ (old('jenis_ujian', $ujianDetails['jenis_ujian'] ?? '') == 'UTS') ? 'selected' : '' }}>UTS (Tengah Semester)</option>
                        <option value="UAS" {{ (old('jenis_ujian', $ujianDetails['jenis_ujian'] ?? '') == 'UAS') ? 'selected' : '' }}>UAS (Akhir Semester)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-400">
                        <i class="bi bi-chevron-down"></i>
                    </div>
                </div>
            </div>

            {{-- Tanggal --}}
            <div class="group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal_ujian" 
                       value="{{ old('tanggal_ujian', $ujianDetails['tanggal_ujian'] ?? '') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium text-gray-700" required>
            </div>

            {{-- Waktu Mulai --}}
            <div class="group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Waktu Mulai</label>
                <input type="time" id="waktu_mulai" name="waktu_mulai" 
                       value="{{ old('waktu_mulai', $ujianDetails['waktu_mulai'] ?? '') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-mono font-medium text-gray-700" required>
            </div>

            {{-- Waktu Selesai --}}
            <div class="group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Waktu Selesai</label>
                <input type="time" id="waktu_selesai" name="waktu_selesai" 
                       value="{{ old('waktu_selesai', $ujianDetails['waktu_selesai'] ?? '') }}" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-mono font-medium text-gray-700" required>
            </div>

            {{-- Durasi Otomatis --}}
            <div class="col-span-1 md:col-span-2 bg-blue-50/50 rounded-xl p-4 border border-blue-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="bi bi-stopwatch-fill text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-bold uppercase">Estimasi Durasi</p>
                        <p class="text-xs text-blue-400">Dihitung otomatis dari waktu mulai & selesai</p>
                    </div>
                </div>
                <input type="text" id="durasi" class="bg-transparent border-none text-right font-bold text-xl text-blue-700 focus:ring-0 w-40" 
                       value="{{ $ujianDetails['durasi_menit'] ?? '0' }} Menit" readonly>
            </div>
            
        </div>

        {{-- Divider --}}
        <div class="border-t border-gray-100 my-6"></div>

        {{-- Info Soal --}}
        <div class="flex justify-between items-center mb-6">
            <div class="text-sm text-gray-500">
                Jumlah soal saat ini: <span class="font-bold text-gray-800">{{ $jumlahSoal }} Butir</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse md:flex-row justify-between gap-4">
            
            {{-- Tombol Simpan Draft/Final --}}
            <button type="submit" name="action" value="simpan_ujian" 
                    class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50 hover:border-gray-400 transition-all text-sm flex items-center justify-center gap-2">
                <i class="bi bi-save"></i> 
                {{ isset($ujian) ? 'Simpan Perubahan' : 'Simpan Sebagai Draft' }}
            </button>

            {{-- Tombol Lanjut (Primary) --}}
            <button type="submit" name="action" value="tambah_soal" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 transition-all text-sm flex items-center justify-center gap-2 shadow-blue-200 shadow-md">
                <span>Lanjut Kelola Soal</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const waktuMulaiEl = document.getElementById('waktu_mulai');
        const waktuSelesaiEl = document.getElementById('waktu_selesai');
        const durasiEl = document.getElementById('durasi');

        function hitungDurasi() {
            const waktuMulai = waktuMulaiEl.value;
            const waktuSelesai = waktuSelesaiEl.value;

            if (waktuMulai && waktuSelesai) {
                const tgl = '1970-01-01T';
                const mulai = new Date(tgl + waktuMulai + ':00');
                const selesai = new Date(tgl + waktuSelesai + ':00');

                if (selesai < mulai) {
                    durasiEl.value = 'Waktu Salah!';
                    durasiEl.classList.add('text-red-500');
                    return;
                } else {
                    durasiEl.classList.remove('text-red-500');
                }

                const selisihMs = selesai.getTime() - mulai.getTime();
                const selisihMenit = selisihMs / 60000;

                durasiEl.value = selisihMenit + " Menit";
            }
        }

        waktuMulaiEl.addEventListener('change', hitungDurasi);
        waktuSelesaiEl.addEventListener('change', hitungDurasi);
        hitungDurasi();
    });
</script>
@endsection