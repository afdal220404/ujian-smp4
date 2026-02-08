@extends('layouts.app')

@section('title', 'Nilai Siswa')

@section('sidebar-menu')
    <div class="px-3 mb-4">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Menu Siswa</div>
        <a href="{{ route('siswa.dashboard') }}" class="nav-link rounded-xl">
            <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('siswa.nilai') }}" class="nav-link active rounded-xl">
            <i class="bi bi-award"></i> <span>Nilai</span>
        </a>
        <a href="{{ route('siswa.bank_soal') }}" class="nav-link rounded-xl">
             <i class="bi bi-file-earmark-text"></i> <span>Bank Soal</span>
        </a>
    </div>
@endsection

@section('content')
<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-gray-400 text-xs mb-6">
        <a href="{{ route('siswa.dashboard') }}" class="hover:text-blue-600"><i class="bi bi-house-door"></i> Home</a>
        <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
        <span class="text-blue-600 font-bold">Nilai</span>
    </div>

    <div class="space-y-8">
    
    {{-- Header & Stats Area --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Title & Search --}}
        <div class="lg:col-span-2 flex flex-col justify-center space-y-6">
            <div>
                <h1 class="text-3xl font-[Poppins-Bold] text-darkblue tracking-tight">
                    Riwayat Nilai <span class="text-blue-600">Akademik</span>
                </h1>
                <p class="text-gray-500 mt-2 text-base leading-relaxed">
                    Pantau pencapaian belajarmu di sini. Tingkatkan terus nilaimu untuk hasil yang memuaskan! 🚀
                </p>
            </div>

            <form action="{{ route('siswa.nilai') }}" method="GET" class="relative group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400 group-focus-within:text-blue-500 transition-colors text-lg"></i>
                </div>
                <input type="text" name="search" value="{{ $keyword }}" placeholder="Cari mata pelajaran atau ujian..." 
                       class="block w-full pl-14 pr-12 py-4 rounded-2xl bg-white border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] text-gray-700 placeholder:text-gray-400 focus:ring-4 focus:ring-blue-100 focus:border-blue-300 focus:outline-none transition-all duration-300">
                @if($keyword)
                    <a href="{{ route('siswa.nilai') }}" class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                        <i class="bi bi-x-circle-fill text-lg"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- Right Column: Overall Stats Card --}}
        <div class="relative bg-white rounded-3xl p-6 border border-blue-100 shadow-[0_10px_40px_rgba(37,99,235,0.08)] overflow-hidden group">
            {{-- Decorative Blobs --}}
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-50 rounded-full blur-2xl opacity-60"></div>
            
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-2">Rata-Rata Total</p>
                        <h2 class="text-5xl font-[Poppins-Bold] text-black bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 tracking-tight">
                            {{ number_format($rataRataKeseluruhan, 1) }}
                        </h2>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50/50 shadow-sm flex items-center justify-center text-blue-600 text-xl border border-blue-100">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center gap-3">
                    <div class="px-3 py-1.5 rounded-lg bg-white border border-gray-100 shadow-sm flex items-center gap-2 text-xs font-bold text-gray-600 text-nowrap">
                    </div>
                     <p class="text-xs text-gray-400 font-medium text-right flex-1">
                        Dari {{ $mapels->count() }} Mata Pelajaran
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($mapels->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            @if($keyword)
                <div class="relative mb-6">
                    <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center text-red-300">
                        <i class="bi bi-search text-4xl"></i>
                    </div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm border border-red-100 text-red-500">
                        <i class="bi bi-x-lg text-xs"></i>
                    </div>
                </div>
                <h3 class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Pencarian Tidak Ditemukan</h3>
                <p class="text-gray-500 max-w-md mx-auto">Kami tidak dapat menemukan mata pelajaran atau ujian yang cocok dengan kata kunci "<span class="font-bold text-darkblue">{{ $keyword }}</span>".</p>
                <a href="{{ route('siswa.nilai') }}" class="mt-6 px-6 py-2.5 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200 transition-colors">
                   Reset Pencarian
                </a>
            @else
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center text-blue-300 mb-6">
                    <i class="bi bi-journal-album text-5xl"></i>
                </div>
                <h3 class="text-xl font-[Poppins-Bold] text-gray-800 mb-2">Belum Ada Data Akademik</h3>
                <p class="text-gray-500 max-w-md mx-auto">Anda belum terdaftar pada mata pelajaran apapun saat ini.</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-5">
            @foreach($mapels as $mapel)
            <div class="group bg-white rounded-3xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] hover:shadow-[0_15px_40px_rgba(0,0,0,0.06)] hover:border-blue-100 transition-all duration-300 overflow-hidden relative">
                
                {{-- Side Accent Bar --}}
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-blue-400 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                {{-- Card Header (Clickable) --}}
                <div class="p-6 cursor-pointer flex flex-col md:flex-row gap-6 relative z-10" onclick="toggleMapelAccord(this)">
                    
                    {{-- Teacher & Subject Icon Area --}}
                    <div class="flex items-center gap-5">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 p-1 shadow-inner">
                                @if($mapel->guru && $mapel->guru->foto)
                                    <img src="{{ asset('storage/' . $mapel->guru->foto) }}" alt="Foto Guru" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <div class="w-full h-full rounded-xl bg-white flex items-center justify-center border border-gray-100">
                                        <i class="bi bi-person-fill text-2xl text-gray-300"></i>
                                    </div>
                                @endif
                            </div>
                            <!-- Icon subject indicator -->
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md border border-gray-100 text-blue-600">
                                <i class="bi bi-journal-text text-sm"></i>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-[Poppins-Bold] text-lg text-darkblue group-hover:text-blue-600 transition-colors mb-1.5">
                                {{ $mapel->nama_mapel }}
                            </h3>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <span class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-xs">
                                    <i class="bi bi-person font-bold text-gray-400"></i>
                                </span>
                                {{ $mapel->guru->nama_lengkap ?? 'Guru Belum Ditentukan' }}
                            </div>
                        </div>
                    </div>

                    {{-- Stats & Toggle Area --}}
                    <div class="flex-1 flex items-center justify-between md:justify-end gap-4 md:gap-8 border-t md:border-t-0 border-gray-50 pt-4 md:pt-0 mt-2 md:mt-0">
                        
                        <div class="grid grid-cols-2 gap-8 text-right">
                             {{-- Rata-Rata Stat --}}
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-0.5">Rata-Rata</span>
                                @if($mapel->rata_rata > 0)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-lg font-[Poppins-Bold] {{ $mapel->rata_rata >= 75 ? 'text-emerald-600' : 'text-amber-500' }}">
                                            {{ number_format($mapel->rata_rata, 1) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm font-bold text-gray-300">-</span>
                                @endif
                            </div>

                             {{-- Total Ujian Stat --}}
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-0.5">Total Ujian</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-lg font-[Poppins-Bold] text-gray-700">
                                        {{ $mapel->ujian_selesai->count() }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-400">Selesai</span>
                                </div>
                            </div>
                        </div>

                        {{-- Layout Arrow --}}
                         <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-600 group-hover:text-white transition-all transform rotate-0 arrow-icon shadow-sm group-hover:shadow-md">
                            <i class="bi bi-chevron-down text-sm"></i>
                        </div>

                    </div>
                </div>

                {{-- Accordion Content --}}
                <div class="mapel-content hidden border-t border-gray-100 bg-gray-50/50">
                    <div class="p-6">
                         @if($mapel->ujian_selesai->isEmpty())
                            <div class="bg-white rounded-2xl p-8 border border-dashed border-gray-300 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 text-gray-400 mb-3">
                                    <i class="bi bi-clock-history text-xl"></i>
                                </div>
                                <p class="text-gray-500 font-medium text-sm">Belum ada riwayat ujian untuk mata pelajaran ini.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($mapel->ujian_selesai as $index => $ujian)
                                    @php $hasil = $ujian->hasilUjians->first(); @endphp
                                    @if($hasil)
                                        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 transition-all group/item relative overflow-hidden">
                                            <div class="flex justify-between items-start mb-3">
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $ujian->jenis_ujian == 'UAS' ? 'bg-red-50 text-red-600 border-red-100' : ($ujian->jenis_ujian == 'UTS' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-blue-50 text-blue-600 border-blue-100') }}">
                                                            {{ $ujian->jenis_ujian }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-400 font-medium">
                                                            <i class="bi bi-calendar4 me-1"></i> {{ $ujian->created_at->format('d M') }}
                                                        </span>
                                                    </div>
                                                    <h4 class="font-bold text-gray-800 text-sm group-hover/item:text-blue-600 transition-colors line-clamp-1" title="{{ $ujian->nama_ujian }}">
                                                        {{ $ujian->nama_ujian }}
                                                    </h4>
                                                </div>
                                                <div class="flex flex-col items-end">
                                                    <span class="text-[9px] font-bold text-gray-400 uppercase mb-0.5 tracking-wider">Nilai Akhir</span>
                                                    <span class="text-2xl font-[Poppins-Bold] {{ $hasil->nilai >= 75 ? 'text-emerald-600' : 'text-red-500' }}">
                                                        {{ $hasil->nilai }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-t border-gray-50 flex justify-end items-center"> 
                                                <a href="{{ route('siswa.ujian.detail', $ujian->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors">
                                                    Lihat Detail <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                
            </div>
            @endforeach
        </div>
    @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleMapelAccord(header) {
        const content = header.nextElementSibling;
        const arrow = header.querySelector('.arrow-icon');
        
        // Toggle Hidden
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }
</script>
@endsection
