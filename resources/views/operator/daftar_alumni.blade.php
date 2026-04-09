@extends('layouts.app')

@section('title', 'Data Alumni')

@section('sidebar-menu')
    <a href="{{ route('operator.landingpage') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('operator.daftar_siswa') }}" class="nav-link">
        <i class="bi bi-people"></i> <span>Data Siswa</span>
    </a>
    <a href="{{ route('operator.alumni.index') }}" class="nav-link active">
        <i class="bi bi-mortarboard-fill"></i> <span>Data Alumni</span>
    </a>
    <a href="{{ route('daftar_guru2') }}" class="nav-link">
        <i class="bi bi-person-video3"></i> <span>Data Staff</span>
    </a>
    <a href="{{ route('walikelas.index') }}" class="nav-link">
        <i class="bi bi-award"></i> <span>Set Wali Kelas</span>
    </a>
    <a href="{{ route('mapel') }}" class="nav-link">
        <i class="bi bi-book"></i> <span>Mata Pelajaran</span>
    </a>
@endsection

@section('content')

{{-- 1. HEADER & BREADCRUMB --}}
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
            <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-blue-600 font-bold">Manajemen Data</span>
            <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
            <span class="text-darkblue font-bold">Data Alumni</span>
        </div>
        <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Daftar Lulusan (Alumni)</h1>
    </div>

    <div> 
        @if (session('success'))
        <div id="alert-success" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down mb-2 md:mb-0">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div id="alert-system-error" class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-xl shadow-sm text-sm font-bold animate-fade-in-down">
            <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
        </div>
        @endif
    </div>
</div>

{{-- 2. TOOLBAR (SEARCH) --}}
<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row gap-4 justify-between">
        
        <div class="flex flex-col md:flex-row gap-3 flex-grow">
            <form action="{{ route('operator.alumni.index') }}" method="GET" class="relative w-full md:w-96 flex">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="bi bi-search text-gray-400"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full py-2.5 pl-10 pr-4 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-200 rounded-l-xl focus:border-blue-500 outline-none" placeholder="Cari Nama / NISN Alumni...">
                <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-xl font-bold hover:bg-blue-700 transition">Cari</button>
            </form>
            @if(request('search'))
                <a href="{{ route('operator.alumni.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl flex items-center gap-2 font-bold hover:bg-gray-200 transition">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 3. TABEL DATA --}}
<div class="bg-white border border-gray-200 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] overflow-hidden relative min-h-[300px]">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 sticky top-0 z-0">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Nama Lulusan</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">NISN</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Tahun Lulus</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($siswas as $siswa)
                <tr class="hover:bg-blue-50/20 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-darkblue group-hover:text-blue-600 transition-colors">
                            {{ $siswa->nama_lengkap }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                            {{ $siswa->nisn }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            {{ $siswa->updated_at ? $siswa->updated_at->format('Y') : '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('operator.alumni.detail', $siswa->id) }}" 
                               class="flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm font-bold text-xs">
                                <i class="bi bi-file-earmark-text"></i> Transkrip Detail
                            </a>
                            
                            <button onclick="openDeleteModal({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}')" 
                                    class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm font-bold text-xs" 
                                    title="Hapus Data">
                                <i class="bi bi-trash3"></i> 
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                        <i class="bi bi-inboxes text-4xl mb-3 block opacity-50"></i>
                        Tidak ada data alumni yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="bi bi-exclamation-triangle-fill text-red-600 text-lg"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900" id="modal-title">Hapus Data Alumni</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="deleteMessage">
                                    Apakah Anda yakin ingin menghapus data ini secara permanen?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <form id="deleteForm" method="POST" class="inline-block w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            Ya, Hapus
                        </button>
                    </form>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openDeleteModal(id, nama) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        document.getElementById('deleteMessage').innerHTML = `Apakah Anda yakin ingin menghapus arsip lulusan <b>"${nama}"</b>?`;
        form.action = `{{ url('/operator/siswa') }}/${id}`;
        modal.classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    
    setTimeout(() => {
        document.querySelectorAll('[id^="alert-"]').forEach(el => el.remove());
    }, 4000);
</script>
@endsection
