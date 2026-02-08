@extends('layouts.app')

@section('title', 'Jawaban Siswa - ' . $siswa->nama_lengkap)

@section('sidebar-menu')
{{-- Tombol Kembali --}}
<div class="mb-4 px-3">
    <a href="{{ route('guru.index') }}"
        class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
        <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
    </a>
</div>

<div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Menu Mapel</div>

<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link active">
    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link">
    <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
</a>
<a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link">
    <i class="bi bi-collection"></i> <span>Bank Soal</span>
</a>
@endsection

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
    <div>
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-gray-500">Kelas VII</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Detail Ujian</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Detail Jawaban</span>
            </div>
        </div>
        <h1 class="text-3xl font-[Poppins-Bold] text-darkblue"></h1>
    </div>

    <a href="{{ route('guru.mapel.ujian.detail', $ujian->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
{{-- 2. INFO SISWA & SKOR --}}
<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex-grow text-center md:text-left">
            <h1 class="text-2xl font-bold text-darkblue">{{ $siswa->nama_lengkap }}</h1>
            <div class="flex flex-wrap justify-center md:justify-start items-center gap-3 mt-2">
                <span class="px-2.5 py-1 bg-gray-100 rounded text-xs text-gray-600 font-medium">NISN: {{ $siswa->nisn }}</span>
                <span class="px-2.5 py-1 bg-gray-100 rounded text-xs text-gray-600 font-medium">Kelas: {{ $siswa->kelas->kelas ?? '-' }}</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold">{{ $ujian->nama_ujian }}</span>
            </div>
        </div>

        {{-- Skor Circle --}}
        <div class="flex items-center gap-4 border-l-0 md:border-l border-gray-100 md:pl-6">
            {{-- Letakkan di dalam kartu Info Siswa --}}
            <div class="flex flex-wrap justify-center md:justify-start items-center gap-6 mt-4 pt-4 border-t border-gray-50">

                {{-- Waktu Mulai --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <i class="bi bi-play-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none">Mulai</p>
                        <p class="text-xs font-semibold text-gray-700">
                            {{ $hasilUjian ? \Carbon\Carbon::parse($hasilUjian->waktu_mulai)->format('H:i') : '-' }} WIB
                        </p>
                    </div>
                </div>

                {{-- Waktu Selesai --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                        <i class="bi bi-stop-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none">Selesai</p>
                        <p class="text-xs font-semibold text-gray-700">
                            {{ $hasilUjian ? \Carbon\Carbon::parse($hasilUjian->waktu_selesai)->format('H:i') : '-' }} WIB
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-right hidden md:block">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Nilai Akhir</div>
                <div class="text-xs text-gray-400">Skor Total</div>
            </div>
            <div class="relative w-16 h-16 flex items-center justify-center rounded-full border-4 {{ $nilai >= 75 ? 'border-green-100 bg-green-50 text-green-600' : 'border-red-100 bg-red-50 text-red-600' }}">
                <span class="text-xl font-bold">{{ round($nilai) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- 3. DAFTAR SOAL (GRID 2 KOLOM) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($daftarSoal as $soal)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative group hover:shadow-md transition-all flex flex-col h-full">

        {{-- Header Kartu: Nomor & Status --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-500 font-bold text-xs flex items-center justify-center shadow-sm">
                    {{ $loop->iteration }}
                </span>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Soal</span>
            </div>

            {{-- Status Badge --}}
            @if($soal->status_jawaban)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-green-100 text-green-700 text-[10px] font-bold">
                <i class="bi bi-check-lg"></i> Benar
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-red-100 text-red-700 text-[10px] font-bold">
                <i class="bi bi-x-lg"></i> Salah
            </span>
            @endif
        </div>

        <div class="p-5 flex-grow flex flex-col">
            {{-- Pertanyaan & Gambar --}}
            <div class="mb-5 flex-grow">

                {{-- PERBAIKAN TAMPILAN GAMBAR --}}
                @if(isset($soal->gambar) && !empty($soal->gambar))
                <div class="mb-4 w-full bg-gray-50 rounded-lg border border-gray-100 p-2 flex justify-center items-center">
                    {{-- Gunakan max-h agar tidak terlalu tinggi, dan w-auto agar rasio terjaga --}}
                    <img src="{{ asset('storage/' . $soal->gambar) }}"
                        alt="Soal"
                        class="max-h-60 max-w-full h-auto object-contain rounded shadow-sm hover:scale-105 transition-transform cursor-pointer"
                        onclick="window.open(this.src, '_blank')">
                </div>
                @endif

                {{-- Teks Pertanyaan --}}
                <div class="text-gray-800 text-sm font-medium leading-relaxed">
                    {!! nl2br(e($soal->pertanyaan)) !!}
                </div>
            </div>

            {{-- Pilihan Ganda (A-E) --}}
            <div class="grid grid-cols-1 gap-2 mt-auto">
                @foreach(['A', 'B', 'C', 'D'] as $opt)
                @php
                $propKey = 'opsi_' . strtolower($opt);
                $textOpsi = $soal->$propKey ?? '-';

                $wrapperClass = "border-gray-100 bg-white text-gray-500 hover:bg-gray-50";
                $badgeClass = "bg-gray-100 text-gray-400";
                $icon = "";

                // LOGIKA WARNA (Sama seperti sebelumnya)
                if ($soal->jawaban_siswa == $opt) {
                if ($soal->status_jawaban) {
                // Siswa BENAR
                $wrapperClass = "border-green-500 bg-green-50 text-green-900 ring-1 ring-green-500 shadow-sm";
                $badgeClass = "bg-green-200 text-green-800";
                $icon = "<i class='bi bi-check-circle-fill text-green-600 text-sm ml-auto'></i>";
                } else {
                // Siswa SALAH
                $wrapperClass = "border-red-500 bg-red-50 text-red-900 ring-1 ring-red-500 shadow-sm";
                $badgeClass = "bg-red-200 text-red-800";
                $icon = "<i class='bi bi-x-circle-fill text-red-500 text-sm ml-auto'></i>";
                }
                } elseif ($soal->kunci_jawaban == $opt && !$soal->status_jawaban) {
                // KUNCI BENAR (Saat siswa salah)
                $wrapperClass = "border-green-400 bg-white text-green-700 border-dashed border-2";
                $badgeClass = "bg-green-100 text-green-600";
                $icon = "<span class='text-[10px] font-bold text-green-600 bg-green-100 px-1.5 py-0.5 rounded ml-auto'>Kunci</span>";
                }
                @endphp

                <div class="flex items-center gap-3 p-2.5 rounded-lg border text-xs transition-all {{ $wrapperClass }}">
                    <div class="w-6 h-6 rounded flex items-center justify-center font-bold flex-shrink-0 {{ $badgeClass }}">
                        {{ $opt }}
                    </div>
                    <div class="font-medium flex-grow break-words leading-snug">
                        {{ $textOpsi }}
                    </div>
                    {!! $icon !!}
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection