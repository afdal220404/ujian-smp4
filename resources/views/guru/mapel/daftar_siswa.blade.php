@extends('layouts.app')

@section('title', 'Data Siswa - ' . $mapel->nama_mapel)

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
        <i class="bi bi-collection"></i> <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.mapel.arsip_soal_siswa.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.arsip_soal_siswa.*') ? 'active' : '' }}">
        <i class="bi bi-folder2-open"></i> <span>Arsip Soal Siswa</span>
    </a>
@endsection

@section('content')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-gray-500">Kelas VII</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Daftar Siswa</span>
            </div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Data Siswa & Nilai</h1>
            <p class="text-gray-500 mt-1">
                Kelas <span class="font-bold text-darkblue">{{ $mapel->kelas->kelas }}</span> 
                • {{ $siswas->count() }} Siswa
            </p>
        </div>
        
        {{-- TOMBOL KEMBALI --}}
        <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">
            <i class="bi bi-arrow-left mr-1"></i> Kembali ke Menu Mapel
        </a>
    </div>

    {{-- TABEL NILAI --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        
        {{-- Toolbar Pencarian --}}
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
            <h3 class="font-[Poppins-Bold] text-gray-700 text-sm uppercase tracking-wider">Rekapitulasi Nilai</h3>
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="bi bi-search text-gray-400"></i>
                </span>
                <input type="text" id="searchInput" 
                       class="w-full py-2 pl-9 pr-4 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg focus:border-primary focus:outline-none" 
                       placeholder="Cari siswa...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-xs uppercase text-gray-500 font-bold tracking-wider border-b-2 border-gray-100">
                        <th rowspan="2" class="px-6 py-4 w-12 text-center border-r border-gray-100">No</th>
                        <th rowspan="2" class="px-6 py-4 border-r border-gray-100 min-w-[250px]">Nama Siswa</th>
                        
                        {{-- KUIS DINAMIS --}}
                        <th colspan="{{ $maxKuis + 1 }}" class="px-4 py-2 text-center border-r border-gray-100 bg-blue-50/30 text-blue-700">Kuis / Harian</th>
                        
                        {{-- UTS DINAMIS (Rata-rata Dihapus, Colspan Disesuaikan) --}}
                        <th colspan="{{ $maxUts }}" class="px-4 py-2 text-center border-r border-gray-100 bg-orange-50/30 text-orange-700">UTS</th>
                        
                        {{-- UAS DINAMIS (Rata-rata Dihapus, Colspan Disesuaikan) --}}
                        <th colspan="{{ $maxUas }}" class="px-4 py-2 text-center border-r border-gray-100 bg-red-50/30 text-red-700">UAS</th>
                        
                        <th rowspan="2" class="px-6 py-4 text-center text-darkblue bg-gray-50">Nilai Akhir</th>
                    </tr>
                    <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                        {{-- Sub-header Kuis --}}
                        @for($i = 1; $i <= $maxKuis; $i++)
                            <th class="px-3 py-2 text-center bg-blue-50/30 w-12 border-r border-white">K{{ $i }}</th>
                        @endfor
                        <th class="px-2 py-2 text-center bg-blue-100/50 text-blue-800 border-r border-gray-100 w-16">Rata</th>
                        
                        {{-- Sub-header UTS --}}
                        @for($i = 1; $i <= $maxUts; $i++)
                            <th class="px-3 py-2 text-center bg-orange-50/30 w-16 border-r border-white">UTS {{ $i }}</th>
                        @endfor

                        {{-- Sub-header UAS --}}
                        @for($i = 1; $i <= $maxUas; $i++)
                            <th class="px-3 py-2 text-center bg-red-50/30 w-16 border-r border-white">UAS {{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody id="siswaTableBody" class="divide-y divide-gray-100">
                    @forelse ($siswas as $index => $siswa)
                        {{-- Helper Warna --}}
                        @php
                            $getColor = function($val) {
                                $v = floatval($val);
                                if($val === '-' || $val == 0) return 'text-gray-300';
                                if($v >= 90) return 'text-green-600 font-bold';
                                if($v >= 80) return 'text-blue-600 font-medium';
                                if($v >= 70) return 'text-yellow-600 font-medium';
                                return 'text-red-500 font-bold';
                            };
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-center font-medium text-gray-400 border-r border-gray-50">{{ $index + 1 }}</td>
                            
                            <td class="px-6 py-3 border-r border-gray-50">
                                <div class="font-[Poppins-Bold] text-darkblue text-sm">{{ $siswa->nama_lengkap }}</div>
                                <div class="text-[11px] text-gray-400 font-mono mt-0.5">NISN: {{ $siswa->nisn }}</div>
                            </td>

                            {{-- ================= KUIS ================= --}}
                            @for($i = 0; $i < $maxKuis; $i++)
                                <td class="px-3 py-3 text-center text-sm border-r border-gray-50">
                                    @if(isset($siswa->list_kuis[$i]))
                                        <span class="{{ $getColor($siswa->list_kuis[$i]) }}">{{ $siswa->list_kuis[$i] }}</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endfor
                            {{-- Rata Kuis --}}
                            <td class="px-2 py-3 text-center border-r border-gray-100 bg-blue-50/10">
                                <span class="{{ $getColor($siswa->rata_kuis) }}">{{ $siswa->rata_kuis }}</span>
                            </td>

                            {{-- ================= UTS ================= --}}
                            @for($i = 0; $i < $maxUts; $i++)
                                <td class="px-3 py-3 text-center text-sm border-r border-gray-50">
                                    @if(isset($siswa->list_uts[$i]))
                                        <span class="{{ $getColor($siswa->list_uts[$i]) }}">{{ $siswa->list_uts[$i] }}</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endfor

                            {{-- ================= UAS ================= --}}
                            @for($i = 0; $i < $maxUas; $i++)
                                <td class="px-3 py-3 text-center text-sm border-r border-gray-50">
                                    @if(isset($siswa->list_uas[$i]))
                                        <span class="{{ $getColor($siswa->list_uas[$i]) }}">{{ $siswa->list_uas[$i] }}</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            @endfor

                            {{-- ================= NILAI AKHIR ================= --}}
                            <td class="px-6 py-3 text-center bg-gray-50">
                                @php
                                    $val = floatval($siswa->grade_raw);
                                    $bg = $val >= 90 ? 'bg-green-100 text-green-700 border-green-200' : 
                                         ($val >= 80 ? 'bg-blue-100 text-blue-700 border-blue-200' : 
                                         ($val >= 70 ? 'bg-yellow-100 text-yellow-700 border-yellow-200' : 'bg-red-100 text-red-700 border-red-200'));
                                @endphp
                                <span class="px-3 py-1 rounded-lg text-sm font-bold border {{ $bg }}">
                                    {{ $siswa->nilai_akhir }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            {{-- Colspan disesuaikan: 4 (No, Nama, Rata Kuis, Nilai Akhir) + Kuis + UTS + UAS --}}
                            <td colspan="{{ 4 + $maxKuis + $maxUts + $maxUas }}" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada siswa di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#siswaTableBody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>
@endsection