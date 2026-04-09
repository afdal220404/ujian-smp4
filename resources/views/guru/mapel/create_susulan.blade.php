@extends('layouts.app')

@section('title', 'Buat Ujian Susulan - ' . $ujian->nama_ujian)

@section('sidebar-menu')
    <div class="mb-4 px-3">
        <a href="{{ route('guru.mapel.ujian.detail', $ujian->id) }}" 
           class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail Ujian
        </a>
    </div>

    <a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link">
        <i class="bi bi-archive-fill"></i> <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.mapel.arsip_soal_siswa.index', $mapel->id) }}" class="nav-link">
        <i class="bi bi-collection"></i> <span>Arsip Soal Siswa</span>
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl shadow-sm">
            <i class="bi bi-calendar-event-fill"></i>
        </div>
        <div>
            <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Buat Ujian Susulan</h1>
            <p class="text-sm text-gray-500">Berdasarkan Ujian Induk: <span class="font-bold text-blue-600">{{ $ujian->nama_ujian }}</span></p>
        </div>
    </div>

    @if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('guru.mapel.ujian.susulan.store', $ujian->id) }}" method="POST">
        @csrf
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 md:p-8 space-y-6">
                {{-- Info Ujian --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Ujian Susulan</label>
                        <input type="text" name="nama_ujian" value="SUSULAN - {{ $ujian->nama_ujian }}" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-gray-700" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal_ujian" value="{{ date('Y-m-d') }}" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none transition-all text-gray-700" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Mulai</label>
                            <input type="time" name="waktu_mulai" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none transition-all text-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Selesai</label>
                            <input type="time" name="waktu_selesai" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none transition-all text-gray-700" required>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Pilih Peserta Susulan</label>
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        @if($siswaBelum->isEmpty())
                            <div class="text-center py-4">
                                <i class="bi bi-people text-2xl text-gray-300"></i>
                                <p class="text-sm text-gray-400 mt-2">Semua siswa sudah mengerjakan ujian ini.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-2">
                                @foreach($siswaBelum as $siswa)
                                <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100 hover:border-blue-300 transition-all cursor-pointer group">
                                    <input type="checkbox" name="peserta_ids[]" value="{{ $siswa->id }}" 
                                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-700 group-hover:text-blue-600 transition-colors truncate">{{ $siswa->nama_lengkap }}</p>
                                        <p class="text-[10px] text-gray-400 font-mono">NISN: {{ $siswa->nisn }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                <a href="{{ route('guru.mapel.ujian.detail', $ujian->id) }}" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-all text-sm">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all text-sm flex items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> Buat Ujian Susulan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
