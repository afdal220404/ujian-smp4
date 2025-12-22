@extends('layouts.app')

@section('title', 'Detail Jawaban ' . $siswa->nama_lengkap)

@push('styles')
<style>
    /* Style untuk card header nilai */
 
</style>
@endpush

@section('sidebar-menu')
<a href="{{ route('guru.index') }}" class="menu-item">
    <i class="bi bi-arrow-left"></i> Kembali ke Pilihan
</a>
<hr class="sidebar-divider">
<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="menu-item active">
    <i class="bi bi-pie-chart-fill"></i> Dasbor
</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="menu-item">
    <i class="bi bi-card-checklist"></i> Daftar Nilai Siswa
</a>
@endsection

@section('content')

{{-- 1. Judul Halaman (CardView) --}}
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul" style="font-size: 24px;">Detail Jawaban Siswa</a>
    <p class="sub-judul" style="text-align: center; margin-top: 15px; font-size: 18px;">
        <strong>{{ $siswa->nama_lengkap }}</strong> ({{ $siswa->nisn }})
    </p>
    <p style="text-align: center; color: #4B5563; margin-top: 5px;">
        Ujian: {{ $ujian->nama_ujian }}
    </p>
</div>

{{-- 2. Card Nilai dan Jumlah Benar --}}
<div class="nilai-card-container">
    <div class="nilai-card">
        <div class="nilai-label">Nilai Akhir</div>
        <div class="nilai-angka {{ $nilaiSiswa >= 70 ? 'nilai-angka-benar' : 'nilai-angka-salah' }}">
            {{ $nilaiSiswa }}
        </div>
    </div>
    <div class="nilai-card">
        <div class="nilai-label">Jawaban Benar</div>
        <div class="nilai-angka" style="color: #1F2937;">
            {{ $jumlahBenar }} <span style="font-size: 20px; color: #6B7280;">/ {{ $jumlahTotalSoal }} Soal</span>
        </div>
    </div>
</div>

{{-- 3. Daftar Soal dan Jawaban --}}
<div class="soal-grid-container">
    
    @foreach ($detailJawaban as $index => $item)
    <div class="soal-review-card"> {{-- Kartu ini sekarang akan otomatis masuk ke grid --}}
        <div class="soal-review-header">
            Soal Nomor {{ $index + 1 }}
        </div>
        <div class="soal-review-body">
            @if($item->soal->gambar)
                <img src="{{ asset('storage/' . $item->soal->gambar) }}" alt="Gambar Soal" style="max-width: 400px; border-radius: 8px; margin-bottom: 20px;">
            @endif
            <div class="soal-review-pertanyaan">{!! nl2br(e($item->soal->pertanyaan)) !!}</div>
            
            <ul class="soal-review-opsi-list">
                @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                    @php
                        $opsiText = $item->soal->{'opsi_' . strtolower($opsi)};
                        $kunciJawaban = $item->soal->kunci_jawaban;
                        $jawabanSiswa = $item->jawaban_siswa;
                        
                        $class = '';
                        if ($opsi == $jawabanSiswa && $opsi == $kunciJawaban) {
                            $class = 'opsi-benar'; // Siswa memilih ini, dan ini benar
                        } elseif ($opsi == $jawabanSiswa && $opsi != $kunciJawaban) {
                            $class = 'opsi-salah'; // Siswa memilih ini, dan ini salah
                        } elseif ($opsi == $kunciJawaban) {
                            $class = 'opsi-kunci-benar'; // Ini kunci benar (tapi siswa tdk pilih)
                        }
                    @endphp
                    <li class="soal-review-opsi {{ $class }}">
                        <strong>{{ $opsi }}.</strong> {{ $opsiText }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach
    
</div>
@endsection