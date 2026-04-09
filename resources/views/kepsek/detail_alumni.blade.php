@extends('layouts.app')

@section('title', 'Detail Akademik Alumni')

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Menu Utama</div>
    <a href="{{ route('kepsek.index') }}" class="nav-link">
        <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('kepsek.guru') }}" class="nav-link">
        <i class="bi bi-person-workspace"></i> <span>Data Guru</span>
    </a>
    <a href="{{ route('kepsek.siswa') }}" class="nav-link">
        <i class="bi bi-people-fill"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('kepsek.alumni.index') }}" class="nav-link active">
        <i class="bi bi-mortarboard-fill"></i> <span>Data Alumni</span>
    </a>
    <a href="{{ route('kepsek.nilai') }}" class="nav-link">
        <i class="bi bi-bar-chart-line-fill"></i> <span>Laporan Nilai</span>
    </a>
@endsection

@section('content')

    {{-- HEADER & NAVIGASI --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-sm mb-1">
                <a href="{{ route('kepsek.alumni.index') }}" class="hover:text-primary transition-colors">Data Alumni</a>
                <i class="bi bi-chevron-right text-xs"></i>
                <span class="text-darkblue font-bold">Rincian Studi Alumni</span>
            </div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Transkrip Nilai Akademik</h1>
        </div>
        <a href="{{ route('kepsek.alumni.index') }}" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold text-sm shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Alumni
        </a>
    </div>

    {{-- INFORMASI ALUMNI --}}
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
            <div class="flex items-center gap-4">
                <div class="flex flex-col items-end border-gray-200">
                    <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Tahun Lulus</span>
                    <span class="px-5 py-2 rounded-lg bg-green-100 text-green-700 font-[Poppins-Bold] shadow-sm text-lg mt-1 border border-green-200">
                        {{ $siswa->updated_at ? $siswa->updated_at->format('Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL NILAI DINAMIS --}}
    @forelse($allTranskrips as $kData)
    {{-- TABEL NILAI DINAMIS PER KELAS --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden mb-8">
        
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-[Poppins-Bold] text-gray-700 text-sm uppercase tracking-wider">Transkrip Nilai Akademik Kelas {{ $kData['kelas']->kelas }}</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    {{-- Baris Header Utama --}}
                    <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                        <th rowspan="2" class="px-4 py-4 w-10 text-center border-r border-gray-100">No</th>
                        <th rowspan="2" class="px-6 py-4 border-r border-gray-100 min-w-[200px]">Mata Pelajaran</th>
                        
                        {{-- HEADER KUIS DINAMIS --}}
                        <th colspan="{{ $kData['maxKuis'] + 1 }}" class="px-4 py-2 text-center border-r border-gray-100 bg-blue-50/30 text-blue-700">
                            Kuis 
                        </th>

                        <th colspan="{{ $kData['maxUts'] }}" class="px-4 py-2 text-center border-r border-gray-100 bg-orange-50/30 text-orange-700">
                            UTS
                        </th>
                        <th colspan="{{ $kData['maxUas'] }}" class="px-4 py-2 text-center border-r border-gray-100 bg-rose-50/30 text-rose-700">
                            UAS
                        </th>
                        <th rowspan="2" class="px-4 py-4 text-center text-darkblue bg-gray-50 w-24">Nilai Akhir</th>
                    </tr>

                    {{-- Baris Sub-Header --}}
                    <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                        @for($i = 1; $i <= $kData['maxKuis']; $i++)
                            <th class="px-2 py-2 text-center bg-blue-50/30 w-12">K{{ $i }}</th>
                        @endfor
                        <th class="px-2 py-2 text-center bg-blue-100/50 text-blue-800 border-r border-gray-100 w-16">Rata-Rata</th>
                        
                        @for($i = 1; $i <= $kData['maxUts']; $i++)
                            <th class="px-4 py-2 text-center bg-orange-50/30 border-r border-gray-100 w-16">UTS {{ $i }}</th>
                        @endfor
                        
                        @for($i = 1; $i <= $kData['maxUas']; $i++)
                            <th class="px-4 py-2 text-center bg-rose-50/30 border-r border-gray-100 w-16">UAS {{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kData['transkrip'] as $index => $data)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-center text-gray-400 text-sm border-r border-gray-100">
                            {{ $loop->iteration }}
                        </td>
                        
                        <td class="px-6 py-3 border-r border-gray-100">
                            <span class="font-[Poppins-Bold] text-darkblue text-sm">{{ $data['mapel']->nama_mapel ?? $data['mapel'] }}</span>
                        </td>

                        {{-- ISI NILAI KUIS --}}
                        @for($i = 0; $i < $kData['maxKuis']; $i++)
                            <td class="px-2 py-3 text-center text-sm border-r border-gray-50">
                                @if(isset($data['detailKuis'][$i]))
                                    <span class="text-gray-600 font-medium">{{ $data['detailKuis'][$i] }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        @endfor

                        {{-- Rata-Rata Kuis --}}
                        @php
                            $validKuis = array_filter($data['detailKuis'], fn($v) => is_numeric($v));
                            $rataKuis = count($validKuis) > 0 ? array_sum($validKuis) / count($validKuis) : 0;
                        @endphp
                        <td class="px-2 py-3 text-center font-bold text-gray-700 bg-blue-50/10 border-r border-gray-100">
                            {{ $rataKuis > 0 ? number_format($rataKuis, 1) : '-' }}
                        </td>

                        {{-- UTS Dinamis --}}
                        @for($i = 0; $i < $kData['maxUts']; $i++)
                            <td class="px-4 py-3 text-center font-medium text-gray-700 border-r border-gray-50">
                                @if(isset($data['detailUts'][$i]))
                                    {{ $data['detailUts'][$i] }}
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        @endfor

                        {{-- UAS Dinamis --}}
                        @for($i = 0; $i < $kData['maxUas']; $i++)
                            <td class="px-4 py-3 text-center font-medium text-gray-700 border-r border-gray-100">
                                @if(isset($data['detailUas'][$i]))
                                    {{ $data['detailUas'][$i] }}
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        @endfor

                        {{-- Nilai Akhir --}}
                        <td class="px-4 py-3 text-center bg-gray-50">
                            <span class="font-[Poppins-Bold] text-gray-800 text-base">
                                {{ floatval($data['nilaiAkhir']) > 0 ? number_format($data['nilaiAkhir'], 1) : '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 4 + $kData['maxKuis'] + $kData['maxUts'] + $kData['maxUas'] }}" class="px-6 py-12 text-center text-gray-400 italic">
                            Belum ada mata pelajaran yang terdaftar untuk kelas ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="px-6 py-12 text-center text-gray-400 italic bg-white border border-gray-200 rounded-2xl shadow-sm">
        Belum ada rekam jejak akademik apapun yang diproses untuk alumni ini.
    </div>
    @endforelse

@endsection
