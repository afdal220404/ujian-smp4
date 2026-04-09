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
<a href="{{ route('guru.walikelas.rekap_nilai', $kelas->id)}}" class="nav-link">
    <i class="bi bi-table"></i> <span>Leger Nilai</span>
</a>
@endsection

@section('content')
    {{-- HEADER & NAVIGATION --}}
<div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
    <div>
        {{-- Breadcrumb Baru (Style sesuai permintaan) --}}
        <div class="flex items-center gap-2 text-gray-400 text-xs mb-2">
            <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors">
                <i class="bi bi-house-door"></i> Home
            </a>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-gray-500">Wali Kelas</span>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-blue-600 font-bold">Data Siswa</span>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-blue-600 font-bold">Detail Nilai</span>
        </div>
        <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Lembar Hasil Ujian</h1>
        <p class="text-gray-500 mt-1">
            Rincian nilai akademik siswa</span>
        </p>
    </div>

    {{-- Tombol Kembali (Dipertahankan) --}}
    <a href="{{ route('guru.walikelas.siswa', $siswa->kelas_id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">
        <i class="bi bi-arrow-left mr-1"></i> Kembali
    </a>
</div>

    {{-- FILTER KELAS HISTORIS --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-2 mr-2">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                <i class="bi bi-filter"></i>
            </div>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Histori Kelas:</span>
        </div>
        
        <div class="flex flex-wrap gap-2">
            @php
                $listKelasFilter = [
                    ['id' => 1, 'nama' => 'VII'],
                    ['id' => 2, 'nama' => 'VIII'],
                    ['id' => 3, 'nama' => 'IX'],
                ];
            @endphp

            @foreach($listKelasFilter as $kf)
                @php
                    $hasData = in_array($kf['id'], $availableKelasIds);
                    $isActive = $activeKelasId == $kf['id'];
                @endphp
                
                @if($hasData)
                    <a href="{{ route('guru.walikelas.siswa.detail', ['siswa' => $siswa->id, 'kelas_id' => $kf['id']]) }}" 
                       class="px-4 py-2 rounded-xl border text-sm font-bold transition-all flex items-center gap-2
                       {{ $isActive 
                          ? 'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-200' 
                          : 'bg-white border-gray-200 text-gray-600 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50/50' }}">
                       <i class="bi {{ $isActive ? 'bi-check-circle-fill' : 'bi-circle' }} text-[10px]"></i>
                       Kelas {{ $kf['nama'] }}
                    </a>
                @else
                    <button disabled title="Tidak ada data nilai di kelas ini"
                       class="px-4 py-2 rounded-xl border border-gray-100 bg-gray-50 text-gray-400 text-sm font-bold cursor-not-allowed opacity-60 flex items-center gap-2">
                       <i class="bi bi-slash-circle text-[10px]"></i>
                       Kelas {{ $kf['nama'] }}
                    </button>
                @endif
            @endforeach
        </div>

        @if($activeKelasId != $siswa->kelas_id)
            <div class="ml-auto">
                <span class="text-[10px] px-2 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-md font-bold uppercase">
                    <i class="bi bi-clock-history mr-1"></i> Mode View Historis
                </span>
            </div>
        @endif
    </div>

    {{-- KARTU PROFIL SISWA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-[100px] pointer-events-none"></div>
        
        <div class="relative z-10">
            <h2 class="text-2xl font-bold font-[Poppins-Bold] text-darkblue mb-3 uppercase tracking-wide">
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
                            Kuis
                        </th>
                        
                        {{-- Group Evaluasi (GANTI JADI UNGU AGAR NETRAL) --}}
                        <th colspan="{{ $maxUts }}" class="px-4 py-2 text-center border-r border-gray-100 bg-purple-50/50 text-purple-700">
                            UTS
                        </th>
                        <th colspan="{{ $maxUas }}" class="px-4 py-2 text-center border-r border-gray-100 bg-indigo-50/50 text-indigo-700">
                            UAS
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
                        
                        {{-- Sub-header Evaluasi --}}
                        @for($i = 1; $i <= $maxUts; $i++)
                            <th class="px-4 py-2 text-center bg-purple-50/30 border-r border-gray-100">UTS {{ $i }}</th>
                        @endfor
                        
                        @for($i = 1; $i <= $maxUas; $i++)
                            <th class="px-4 py-2 text-center bg-indigo-50/30 border-r border-gray-100">UAS {{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transkrip as $index => $data)
                        {{-- Helper PHP Kecil untuk Warna Angka --}}
                        @php
                            // Fungsi sederhana untuk menentukan warna teks berdasarkan nilai
                            $getColor = function($val) {
                                $v = floatval($val);
                                if($val === '-' || $val === null) return 'text-gray-300'; // Belum ada nilai
                                if($v >= 85) return 'text-green-600 font-bold';       // Sangat Baik
                                if($v >= 75) return 'text-blue-600 font-medium';      // Baik
                                if($v >= 70) return 'text-yellow-600 font-medium';    // Cukup
                                return 'text-red-500 font-bold';                      // Kurang (termasuk 0)
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

                            {{-- UTS Dinamis --}}
                            @for($i = 0; $i < $maxUts; $i++)
                                <td class="px-4 py-3 text-center border-r border-gray-100">
                                    @if(isset($data->list_uts[$i]))
                                        <span class="{{ $getColor($data->list_uts[$i]) }}">
                                            {{ $data->list_uts[$i] }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endfor

                            {{-- UAS Dinamis --}}
                            @for($i = 0; $i < $maxUas; $i++)
                                <td class="px-4 py-3 text-center border-r border-gray-100">
                                    @if(isset($data->list_uas[$i]))
                                        <span class="{{ $getColor($data->list_uas[$i]) }}">
                                            {{ $data->list_uas[$i] }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endfor

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
                                        '-' => 'bg-gray-100 text-gray-500 border-gray-200',
                                        default => 'bg-red-100 text-red-700 border-red-200',
                                    };
                                    $badgeLabel = match($data->predikat) {
                                        'A' => 'Sangat Baik',
                                        'B' => 'Baik',
                                        'C' => 'Cukup',
                                        '-' => '-',
                                        default => 'Kurang',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ $badgeColor }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $maxKuis + $maxUts + $maxUas }}" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada mata pelajaran yang terdaftar untuk kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection