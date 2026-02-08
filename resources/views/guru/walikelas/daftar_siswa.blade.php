@extends('layouts.app')

@section('title', 'Siswa Kelas ' . $kelas->kelas)

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

    {{-- HEADER & SEARCH --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Data Siswa</h1>
            <p class="text-gray-500 mt-1">
                Daftar siswa kelas <span class="font-bold text-darkblue">{{ $kelas->kelas }}</span> beserta rekapitulasi nilai Ujian Sementara.
            </p>
        </div>
        <div class="relative w-full md:w-64">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="bi bi-search text-gray-400"></i>
            </span>
            <input type="text" id="searchInput" 
                   class="w-full py-2.5 pl-10 pr-4 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 shadow-sm" 
                   placeholder="Cari nama siswa...">
        </div>
    </div>

    {{-- TABEL SISWA --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-xs uppercase text-gray-500 font-bold tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4">Identitas Siswa</th>
                        <th class="px-6 py-4 text-center text-blue-600">Rata Kuis</th>
                        <th class="px-6 py-4 text-center text-yellow-600">Rata UTS</th>
                        <th class="px-6 py-4 text-center text-red-600">Rata UAS</th>
                        <th class="px-6 py-4 text-center text-darkblue font-extrabold bg-blue-50/20">Nilai Akhir</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="siswaTableBody" class="divide-y divide-gray-100">
                    @forelse ($siswas as $index => $siswa)
                        <tr class="hover:bg-blue-50/10 transition-colors group">
                            
                            {{-- No --}}
                            <td class="px-6 py-4 text-center font-medium text-gray-400">{{ $index + 1 }}</td>
                            
                            {{-- Nama & NISN --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                        {{ substr($siswa->nama_lengkap, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-[Poppins-Bold] text-darkblue text-sm group-hover:text-primary transition-colors">
                                            {{ $siswa->nama_lengkap }}
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $siswa->nisn }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Nilai Rata-rata --}}
                            <td class="px-6 py-4 text-center font-bold text-gray-600">{{ $siswa->rata_kuis }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-600">{{ $siswa->rata_uts }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-600">{{ $siswa->rata_uas }}</td>

                            {{-- Nilai Akhir (Badge Warna) --}}
                            <td class="px-6 py-4 text-center bg-gray-50/30">
                                @php
                                    $val = floatval($siswa->grade_raw);
                                    $bg = $val >= 85 ? 'bg-green-100 text-green-700' : ($val >= 70 ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700');
                                @endphp
                                <span class="px-3 py-1 rounded-lg text-sm font-bold {{ $bg }}">
                                    {{ $siswa->nilai_akhir }}
                                </span>
                            </td>

                            {{-- Tombol Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('guru.walikelas.siswa.detail', $siswa->id) }}" 
                                   class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:border-primary hover:text-primary transition-all shadow-sm hover:shadow-md">
                                    <i class="bi bi-journal-text"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada siswa yang terdaftar di kelas ini.
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
    // Simple Client-side Search
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