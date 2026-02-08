@extends('layouts.app')

@section('title', 'Detail Akademik Siswa')

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Menu Utama</div>
    <a href="{{ route('kepsek.index') }}" class="nav-link">
        <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('kepsek.guru') }}" class="nav-link">
        <i class="bi bi-person-workspace"></i> <span>Monitoring Guru</span>
    </a>
    <a href="{{ route('kepsek.siswa') }}" class="nav-link">
        <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('kepsek.nilai') }}" class="nav-link active">
        <i class="bi bi-bar-chart-line-fill"></i> <span>Laporan Nilai</span>
    </a>
@endsection

@section('content')

    {{-- HEADER & NAVIGASI --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-sm mb-1">
                <a href="{{ route('kepsek.nilai') }}" class="hover:text-primary transition-colors">Rekapitulasi Nilai</a>
                <i class="bi bi-chevron-right text-xs"></i>
                <span class="text-darkblue font-bold">Rincian Studi</span>
            </div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Lembar Hasil Studi</h1>
        </div>
        <a href="{{ route('kepsek.nilai') }}" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold text-sm shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- INFORMASI SISWA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-[Poppins-Bold] text-darkblue uppercase tracking-wide">{{ $siswa->nama_lengkap }}</h2>
                <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-upc-scan text-gray-400"></i>
                        <span>NISN: <span class="font-bold text-darkblue">{{ $siswa->nisn ?? '-' }}</span></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center">
                <span class="px-5 py-2 rounded-lg bg-[#00415a] text-white font-[Poppins-Bold] shadow-sm text-lg">
                    Kelas {{ $siswa->kelas->kelas ?? '?' }}
                </span>
            </div>
        </div>
    </div>

    {{-- TABEL NILAI DINAMIS --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-[Poppins-Bold] text-gray-700 text-sm uppercase tracking-wider">Transkrip Nilai Akademik</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    {{-- Baris Header Utama --}}
                    <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                        <th rowspan="2" class="px-4 py-4 w-10 text-center border-r border-gray-100">No</th>
                        <th rowspan="2" class="px-6 py-4 border-r border-gray-100 min-w-[200px]">Mata Pelajaran</th>
                        
                        {{-- HEADER KUIS DINAMIS --}}
                        {{-- colspan = Jumlah max kuis + 1 kolom rata-rata --}}
                        <th colspan="{{ $maxKuis + 1 }}" class="px-4 py-2 text-center border-r border-gray-100 bg-blue-50/30 text-blue-700">
                            Kuis 
                        </th>

                        <th colspan="2" class="px-4 py-2 text-center border-r border-gray-100 bg-orange-50/30 text-orange-700">
                            Evaluasi
                        </th>
                        <th rowspan="2" class="px-4 py-4 text-center text-darkblue bg-gray-50 w-24">Nilai Akhir</th>
                    </tr>

                    {{-- Baris Sub-Header --}}
                    <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                        {{-- Generate Kolom Kuis Secara Dinamis (K1, K2, K3 ...) --}}
                        @for($i = 1; $i <= $maxKuis; $i++)
                            <th class="px-2 py-2 text-center bg-blue-50/30 w-12">K{{ $i }}</th>
                        @endfor
                        
                        <th class="px-2 py-2 text-center bg-blue-100/50 text-blue-800 border-r border-gray-100 w-16">Rata-Rata</th>
                        
                        <th class="px-4 py-2 text-center bg-orange-50/30 w-16">UTS</th>
                        <th class="px-4 py-2 text-center bg-orange-50/30 border-r border-gray-100 w-16">UAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transkrip as $index => $data)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-center text-gray-400 text-sm border-r border-gray-100">
                            {{ $loop->iteration }}
                        </td>
                        
                        <td class="px-6 py-3 border-r border-gray-100">
                            <span class="font-[Poppins-Bold] text-darkblue text-sm">{{ $data->mapel }}</span>
                        </td>

                        {{-- ISI NILAI KUIS SECARA DINAMIS --}}
                        @for($i = 0; $i < $maxKuis; $i++)
                            <td class="px-2 py-3 text-center text-sm border-r border-gray-50">
                                {{-- Cek apakah mapel ini punya nilai di urutan ke-$i --}}
                                @if(isset($data->list_kuis[$i]))
                                    <span class="text-gray-600 font-medium">{{ $data->list_kuis[$i] }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        @endfor

                        {{-- Rata-Rata Kuis --}}
                        <td class="px-2 py-3 text-center font-bold text-blue-600 bg-blue-50/10 border-r border-gray-100">
                            {{ $data->rata_kuis }}
                        </td>

                        {{-- UTS & UAS --}}
                        <td class="px-4 py-3 text-center text-orange-600 font-medium">{{ $data->uts }}</td>
                        <td class="px-4 py-3 text-center text-red-600 font-medium border-r border-gray-100">{{ $data->uas }}</td>

                        {{-- Nilai Akhir --}}
                        <td class="px-4 py-3 text-center bg-gray-50">
                            @php
                                $val = floatval($data->grade_val);
                                $color = 'text-gray-400';
                                if($val > 0) {
                                    if($val >= 90) $color = 'text-green-600';
                                    elseif($val >= 75) $color = 'text-blue-600';
                                    elseif($val >= 60) $color = 'text-orange-500';
                                    else $color = 'text-red-500';
                                }
                            @endphp
                            <span class="font-[Poppins-Bold] {{ $color }} text-base">
                                {{ $data->akhir }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- Hitung colspan: No(1) + Mapel(1) + MaxKuis + RataKuis(1) + UTS(1) + UAS(1) + Akhir(1) --}}
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