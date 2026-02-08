@extends('layouts.app')

@section('title', 'Transkrip - ' . $siswa->nama_lengkap)

@section('sidebar-menu')
    <div class="mb-4 px-3">
        <a href="{{ route('guru.index') }}" 
           class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
        </a>
    </div>
    <hr class="my-3 border-gray-100 opacity-20">
    <a href="{{ route('guru.walikelas.dashboard', $kelas->id)}}" class="nav-link active">
        <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard Kelas</span>
    </a>
    <a href="{{ route('guru.walikelas.siswa', $kelas->id)}}" class="nav-link">
        <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
    </a>
@endsection

@section('content')

    {{-- HEADER & NAVIGATION --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-sm mb-1">
                <a href="{{ route('guru.walikelas.siswa', $siswa->kelas_id) }}" class="hover:text-primary">Data Siswa</a>
                <i class="bi bi-chevron-right text-xs"></i>
                <span class="text-darkblue font-bold">Transkrip Nilai</span>
            </div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Lembar Hasil Studi</h1>
        </div>
        <a href="{{ route('guru.walikelas.siswa', $siswa->kelas_id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">
            <i class="bi bi-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    {{-- KARTU PROFIL SISWA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-[100px] pointer-events-none"></div>
        
        <div class="relative z-10">
            <h2 class="text-2xl font-[Poppins-Bold] text-darkblue mb-3 uppercase tracking-wide">
                {{ $siswa->nama_lengkap }}
            </h2>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-200 text-gray-600">
                    <i class="bi bi-upc-scan text-gray-400"></i> 
                    <span>NISN: <span class="font-bold text-darkblue">{{ $siswa->nisn }}</span></span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 rounded-lg border border-blue-100 text-blue-700">
                    <i class="bi bi-door-open-fill text-blue-400"></i> 
                    <span>Kelas <span class="font-bold">{{ $siswa->kelas->kelas ?? '-' }}</span></span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL TRANSKRIP NILAI DINAMIS --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-[Poppins-Bold] text-gray-700 text-sm uppercase tracking-wider">Rincian Nilai Mata Pelajaran</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                        <th rowspan="2" class="px-6 py-4 w-10 text-center border-r border-gray-100">No</th>
                        <th rowspan="2" class="px-6 py-4 border-r border-gray-100 min-w-[200px]">Mata Pelajaran</th>
                        
                        {{-- Group Kuis (Biru) --}}
                        <th colspan="{{ $maxKuis + 1 }}" class="px-4 py-2 text-center border-r border-gray-100 bg-blue-50/30 text-blue-700">
                            Aspek Pengetahuan (Kuis)
                        </th>
                        
                        {{-- Group Evaluasi (GANTI JADI UNGU AGAR NETRAL) --}}
                        <th colspan="2" class="px-4 py-2 text-center border-r border-gray-100 bg-purple-50/50 text-purple-700">
                            Evaluasi (UTS & UAS)
                        </th>
                        
                        <th rowspan="2" class="px-6 py-4 text-center text-darkblue bg-gray-50">Nilai Akhir</th>
                        <th rowspan="2" class="px-6 py-4 text-center text-darkblue bg-gray-50">Predikat</th>
                    </tr>
                    <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                        {{-- Sub-header Kuis --}}
                        @for($i = 1; $i <= $maxKuis; $i++)
                            <th class="px-3 py-2 text-center bg-blue-50/30 w-12">K{{ $i }}</th>
                        @endfor
                        <th class="px-2 py-2 text-center bg-blue-100/50 text-blue-800 border-r border-gray-100">Rata-Rata</th>
                        
                        {{-- Sub-header Evaluasi (Ungu) --}}
                        <th class="px-4 py-2 text-center bg-purple-50/30">UTS</th>
                        <th class="px-4 py-2 text-center bg-purple-50/30 border-r border-gray-100">UAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transkrip as $index => $data)
                        {{-- Helper PHP Kecil untuk Warna Angka --}}
                        @php
                            // Fungsi sederhana untuk menentukan warna teks berdasarkan nilai
                            $getColor = function($val) {
                                $v = floatval($val);
                                if($val === '-' || $val == 0) return 'text-gray-300'; // Kosong
                                if($v >= 90) return 'text-green-600 font-bold';       // Sangat Baik
                                if($v >= 80) return 'text-blue-600 font-medium';      // Baik
                                if($v >= 70) return 'text-yellow-600 font-medium';    // Cukup
                                return 'text-red-500 font-bold';                      // Kurang
                            };
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-center text-gray-400 text-sm border-r border-gray-100">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3 border-r border-gray-100">
                                <span class="font-[Poppins-Bold] text-darkblue text-sm">{{ $data->mapel }}</span>
                            </td>

                            {{-- Looping Nilai Kuis (Warna Dinamis) --}}
                            @for($i = 0; $i < $maxKuis; $i++)
                                <td class="px-3 py-3 text-center text-sm border-r border-gray-50">
                                    @if(isset($data->list_kuis[$i]))
                                        <span class="{{ $getColor($data->list_kuis[$i]) }}">
                                            {{ $data->list_kuis[$i] }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endfor

                            {{-- Rata-rata Kuis --}}
                            <td class="px-2 py-3 text-center border-r border-gray-100 bg-blue-50/5">
                                <span class="{{ $getColor($data->rata_kuis) }}">
                                    {{ $data->rata_kuis }}
                                </span>
                            </td>

                            {{-- UTS (Warna Dinamis) --}}
                            <td class="px-4 py-3 text-center">
                                <span class="{{ $getColor($data->uts) }}">
                                    {{ $data->uts }}
                                </span>
                            </td>

                            {{-- UAS (Warna Dinamis) --}}
                            <td class="px-4 py-3 text-center border-r border-gray-100">
                                <span class="{{ $getColor($data->uas) }}">
                                    {{ $data->uas }}
                                </span>
                            </td>

                            {{-- Nilai Akhir --}}
                            <td class="px-6 py-3 text-center bg-gray-50">
                                <span class="{{ $getColor($data->akhir) }} text-base">
                                    {{ $data->akhir }}
                                </span>
                            </td>

                            {{-- Predikat Badge --}}
                            <td class="px-6 py-3 text-center bg-gray-50">
                                @php
                                    $badgeColor = match($data->predikat) {
                                        'A' => 'bg-green-100 text-green-700 border-green-200',
                                        'B' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'C' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        default => 'bg-red-100 text-red-700 border-red-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ $badgeColor }}">
                                    {{ $data->predikat }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + $maxKuis }}" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada mata pelajaran yang terdaftar untuk kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection