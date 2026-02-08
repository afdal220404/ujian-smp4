@extends('layouts.app')

@section('title', 'Set Wali Kelas')

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Utama</div>
    <a href="{{ route('operator.landingpage') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>

    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Manajemen Data</div>
    <a href="{{ route('operator.daftar_siswa') }}" class="nav-link">
        <i class="bi bi-people"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('daftar_guru2') }}" class="nav-link">
        <i class="bi bi-person-video3"></i> <span>Data Guru</span>
    </a>

    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Akademik</div>
    <a href="{{ route('walikelas.index') }}" class="nav-link">
        <i class="bi bi-award"></i> <span>Set Wali Kelas</span>
    </a>
    <a href="{{ route('mapel') }}" class="nav-link">
        <i class="bi bi-book"></i> <span>Mata Pelajaran</span>
    </a>
@endsection

@section('content')

    {{-- 1. BREADCRUMB --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
                <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Akademik</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-darkblue font-bold">Wali Kelas</span>
            </div>
            <h1 class="text-xl font-[Poppins-Bold] text-darkblue tracking-tight">
                Pengaturan Wali Kelas
            </h1>
        </div>
    </div>

    {{-- 2. ALERT NOTIFIKASI --}}
    <div class="mb-4">
        @if (session('success'))
        <div id="alert-success" class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
            <i class="bi bi-check-circle-fill text-lg"></i> {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div id="alert-error" class="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-100 text-red-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
            <i class="bi bi-exclamation-triangle-fill text-lg"></i> {{ session('error') }}
        </div>
        @endif
    </div>

    {{-- 3. KARTU FORMULIR --}}
    <div class="w-full"> 
        <div class="bg-white rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden relative">
            
            {{-- Header Card --}}
            <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                        <i class="bi bi-person-video2"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Daftar Kelas & Wali</h3>
                        <p class="text-xs text-gray-500">Pasangkan kelas dengan guru penanggung jawab.</p>
                    </div>
                </div>
            </div>

            {{-- Form Table --}}
            <form action="{{ route('walikelas.store') }}" method="POST">
                @csrf
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50 sticky top-0 z-0">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 w-1/4">Nama Kelas</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Guru Wali Kelas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($kelasList as $kelas)
                            <tr class="hover:bg-blue-50/10 transition-colors">
                                {{-- Kolom Nama Kelas --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 font-bold text-xs flex items-center justify-center">
                                            {{ substr($kelas->kelas, 0, 2) }}
                                        </div>
                                        <span class="text-sm font-bold text-darkblue">{{ $kelas->kelas }}</span>
                                    </div>
                                </td>

                                {{-- Kolom Pilih Guru --}}
                                <td class="px-6 py-4">
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="bi bi-person text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                        </div>
                                        
                                        <select name="wali_kelas[{{ $kelas->id }}]" 
                                                class="w-full md:w-3/4 pl-9 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm font-medium text-gray-700 appearance-none cursor-pointer shadow-sm hover:border-blue-300">
                                            <option value="">-- Pilih Guru Wali --</option>
                                            @foreach($gurus as $guru)
                                                <option value="{{ $guru->id }}" {{ ($waliKelasData[$kelas->id] ?? null) == $guru->id ? 'selected' : '' }}>
                                                    {{ $guru->nama_lengkap }} ({{ $guru->nip ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none md:right-1/4 mr-2">
                                            <i class="bi bi-chevron-down text-[10px] text-gray-400"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-6 py-8 text-center text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <i class="bi bi-hdd-stack text-3xl mb-2 opacity-50"></i>
                                        <span class="text-sm">Belum ada data kelas.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" onclick="window.location.reload()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold hover:bg-gray-100 transition-all text-sm shadow-sm">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                    
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm flex items-center gap-2 shadow-blue-200 shadow-md">
                        <i class="bi bi-check-circle-fill"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Auto Close Alerts
        const alerts = document.querySelectorAll('[id^="alert-"]');
        if (alerts.length > 0) {
            setTimeout(() => {
                alerts.forEach(el => {
                    el.style.transition = "opacity 0.5s ease";
                    el.style.opacity = "0";
                    setTimeout(() => el.remove(), 500);
                });
            }, 4000);
        }
    });
</script>
@endsection