@extends('layouts.app')

@section('title', 'Leger Nilai Kelas ' . $kelas->kelas)

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

    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
    <div>
        {{-- BREADCRUMB --}}
        <div class="flex items-center gap-2 text-gray-400 text-xs mb-2">
            {{-- Level 1: Home --}}
            <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors">
                <i class="bi bi-house-door"></i> Home
            </a>

            {{-- Separator --}}
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>

            {{-- Level 2: Dashboard Kelas (Link aktif untuk kembali) --}}
            <span class="text-gray-500">Wali Kelas</span>

            {{-- Separator --}}
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>

            {{-- Level 3: Halaman Aktif --}}
            <span class="text-blue-600 font-bold">Leger Nilai</span>
        </div>

        <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Leger Nilai Siswa</h1>
        <p class="text-gray-500 mt-1">
            Rincian nilai Kuis, UTS, dan UAS seluruh siswa kelas <span class="font-bold text-darkblue">{{ $kelas->kelas }}</span>.
        </p>
    </div>

    <div class="flex gap-2">
        {{-- Tombol Export Excel --}}
        <a href="{{ route('guru.walikelas.rekap_nilai.export', $kelas->id) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 transition-colors shadow-sm flex items-center gap-2 print:hidden">
            <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
        </a>
    </div>
</div>

    {{-- TABEL LEGER --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        
        {{-- Toolbar --}}
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/30 print:hidden">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                Total: {{ $siswas->count() }} Siswa • {{ $mapels->count() }} Mata Pelajaran
            </div>
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="bi bi-search text-gray-400"></i>
                </span>
                <input type="text" id="searchInput" 
                       class="w-full py-2 pl-9 pr-4 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg focus:border-primary focus:outline-none" 
                       placeholder="Cari nama siswa...">
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar pb-4">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    {{-- BARIS 1: NAMA MAPEL --}}
                    <tr class="bg-[#00415a] text-white">
                        <th rowspan="2" class="px-4 py-2 w-12 text-center border-r border-white/10 sticky left-0 bg-[#00415a] z-30 text-xs font-bold">NO</th>
                        <th rowspan="2" class="px-6 py-2 border-r border-white/10 sticky left-[48px] bg-[#00415a] z-30 min-w-[250px] text-xs font-bold text-left">NAMA SISWA</th>
                        
                        @foreach($mapels as $mapel)
                            {{-- Colspan menyesuaikan jumlah kuis + uts + uas --}}
                            <th colspan="{{ $mapel->jumlah_kuis + $mapel->jumlah_uts + $mapel->jumlah_uas }}" 
                                class="px-2 py-2 text-center border-r border-white/10 text-[10px] font-bold tracking-wider uppercase bg-[#00415a]">
                                {{ $mapel->nama_mapel }}
                            </th>
                        @endforeach

                        <th rowspan="2" class="px-4 py-2 w-20 text-center bg-emerald-700 border-l border-white/10 text-xs font-bold">NILAI AKHIR</th>
                    </tr>

                    {{-- BARIS 2: DETAIL KOLOM (K1..Kn, U, A) --}}
                    <tr class="bg-[#005a7d] text-white">
                        @foreach($mapels as $mapel)
                            {{-- Loop Header Kuis --}}
                            @for($i = 1; $i <= $mapel->jumlah_kuis; $i++)
                                <th class="px-2 py-1 text-center border-r border-white/10 text-[9px] w-10 bg-blue-900/30 font-normal">
                                    K{{ $i }}
                                </th>
                            @endfor
                            
                            {{-- Header UTS & UAS --}}
                            @for($i = 1; $i <= $mapel->jumlah_uts; $i++)
                                <th class="px-2 py-1 text-center border-r border-white/10 text-[8px] w-8 bg-purple-900/30 font-bold" title="Nilai UTS">UTS{{ $i }}</th>
                            @endfor
                            @for($i = 1; $i <= $mapel->jumlah_uas; $i++)
                                <th class="px-2 py-1 text-center border-r border-white/10 text-[8px] w-8 bg-orange-900/30 font-bold" title="Nilai UAS">UAS{{ $i }}</th>
                            @endfor
                        @endforeach
                    </tr>
                </thead>

                <tbody id="legerTableBody" class="divide-y divide-gray-100 text-xs">
                    @forelse ($siswas as $index => $siswa)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            
                            {{-- No --}}
                            <td class="px-4 py-2 text-center font-medium text-gray-500 border-r border-gray-100 bg-white sticky left-0 z-20 group-hover:bg-blue-50/30">
                                {{ $index + 1 }}
                            </td>
                            
                            {{-- Nama Siswa --}}
                            <td class="px-6 py-2 border-r border-gray-100 bg-white sticky left-[48px] z-20 group-hover:bg-blue-50/30">
                                <div class="font-bold text-[#00415a] uppercase truncate w-60">{{ $siswa->nama_lengkap }}</div>
                                <div class="text-[9px] text-gray-400 font-mono">{{ $siswa->nisn }}</div>
                            </td>

                            {{-- Nilai Per Mapel --}}
                            @foreach($mapels as $mapel)
                                @php
                                    // Ambil Data Mapel
                                    $data = $rekapNilai[$siswa->id]['mapel'][$mapel->id] ?? [
                                        'detail_kuis' => array_fill(0, $mapel->jumlah_kuis, '-'),
                                        'uts' => '-',
                                        'uas' => '-'
                                    ];
                                @endphp
                                
                                {{-- 1. Loop Nilai Kuis --}}
                                @foreach($data['detail_kuis'] as $nilaiKuis)
                                    <td class="px-2 py-2 text-center border-r border-gray-50 text-gray-500">
                                        {{ $nilaiKuis }}
                                    </td>
                                @endforeach

                                {{-- Jika kuis kurang dari jumlah kolom (padding) --}}
                                @for($k = count($data['detail_kuis']); $k < $mapel->jumlah_kuis; $k++)
                                    <td class="px-2 py-2 text-center border-r border-gray-50 text-gray-300">-</td>
                                @endfor

                                {{-- 2. Loop Nilai UTS --}}
                                @foreach($data['detail_uts'] as $nilaiUts)
                                    <td class="px-2 py-2 text-center border-r border-gray-50 text-[#00415a] bg-purple-50/10 font-medium">
                                        {{ $nilaiUts }}
                                    </td>
                                @endforeach
                                @for($k = count($data['detail_uts']); $k < $mapel->jumlah_uts; $k++)
                                    <td class="px-2 py-2 text-center border-r border-gray-50 text-[#00415a] bg-purple-50/10 font-medium">-</td>
                                @endfor

                                {{-- 3. Loop Nilai UAS --}}
                                @foreach($data['detail_uas'] as $nilaiUas)
                                    <td class="px-2 py-2 text-center border-r border-gray-200 text-[#00415a] bg-orange-50/10 font-medium">
                                        {{ $nilaiUas }}
                                    </td>
                                @endforeach
                                @for($k = count($data['detail_uas']); $k < $mapel->jumlah_uas; $k++)
                                    <td class="px-2 py-2 text-center border-r border-gray-200 text-[#00415a] bg-orange-50/10 font-medium">-</td>
                                @endfor
                            @endforeach

                            {{-- Rata-Rata Akhir Siswa --}}
                            @php
                                $rataAkhir = $rekapNilai[$siswa->id]['rata_akhir'] ?? 0;
                                $grade = $rekapNilai[$siswa->id]['grade_akhir'] ?? 0;
                                $color = $grade >= 75 ? 'text-emerald-700 bg-emerald-50' : ($grade > 0 ? 'text-red-600 bg-red-50' : 'text-gray-400');
                            @endphp
                            <td class="px-4 py-2 text-center font-bold border-l border-gray-200 {{ $color }}">
                                {{ $grade > 0 ? $rataAkhir : '-' }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada data siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Keterangan --}}
    <div class="mt-4 text-[10px] text-gray-400 flex gap-4 justify-end print:hidden">
        <span><b>Kn</b> = Nilai Kuis ke-n</span>
        <span><b>U</b> = UTS</span>
        <span><b>A</b> = UAS</span>
        <span><b>Nilai Akhir</b> = Rata-rata Gabungan Seluruh Mapel</span>
    </div>

@endsection

@section('scripts')
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#legerTableBody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar { height: 12px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 6px; border: 3px solid #f8fafc; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
    
    @media print {
        .print\:hidden { display: none !important; }
        .sidebar { display: none; }
        body { background: white; }
        .overflow-x-auto { overflow: visible !important; }
    }
</style>
@endsection