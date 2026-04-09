@extends('layouts.app')

@section('title', isset($siswa) ? 'Edit Siswa' : 'Tambah Siswa')

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

    {{-- 1. BREADCRUMB (Margin bawah dikurangi mb-6 -> mb-4) --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
                <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Manajemen Data</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-darkblue font-bold">Data Siswa</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ isset($siswa) ? 'Edit Data' : 'Tambah Baru' }}</span>
            </div>
            <h1 class="text-xl font-[Poppins-Bold] text-darkblue tracking-tight">
                {{ isset($siswa) ? 'Perbarui Informasi Siswa' : 'Registrasi Siswa Baru' }}
            </h1>
        </div>
    </div>

    {{-- 2. KARTU FORMULIR --}}
    <div class="w-full"> 
        <div class="bg-white rounded-xl shadow-lg shadow-blue-900/5 border border-blue-50 overflow-hidden relative">
            
            <div class="h-1 w-full bg-blue-600"></div>

            {{-- Header Form (Padding dikurangi py-6 -> py-3) --}}
            <div class="px-6 py-3 bg-blue-50/50 border-b border-blue-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white text-blue-600 border border-blue-100 shadow-sm flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Formulir Data Siswa</h3>
                    <p class="text-xs text-gray-500">
                        Lengkapi data akademik dan akun login siswa.
                    </p>
                </div>
            </div>

            {{-- Alert Error --}}
            @if ($errors->any())
                <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-100 rounded-lg flex items-start gap-3 animate-fade-in-down">
                    <i class="bi bi-x-circle-fill text-red-500 mt-0.5"></i>
                    <div>
                        <h4 class="text-xs font-bold text-red-800">Gagal Menyimpan</h4>
                        <ul class="text-[10px] text-red-600 mt-0.5 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Form Body (Padding p-8 -> p-5, Gap gap-y-6 -> gap-y-3) --}}
            <form action="@if(isset($siswa)) {{ route('siswa.update', $siswa->id) }} @else {{ route('siswa.store') }} @endif" method="POST" class="p-5">
                @csrf
                @if(isset($siswa))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">

                    {{-- GROUP 1: DATA UTAMA --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-2 text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">
                            <i class="bi bi-person-badge"></i> Data Akademik
                        </div>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="col-span-1 md:col-span-2 group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">Nama Lengkap</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400 group-focus-within:text-blue-600">
                                <i class="bi bi-type-h1"></i>
                            </span>
                            {{-- Input Padding dikurangi (py-3 -> py-2) --}}
                            <input type="text" name="nama_lengkap" 
                                   value="{{ old('nama_lengkap', $siswa->nama_lengkap ?? '') }}" 
                                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all font-semibold text-sm text-gray-700 placeholder-slate-400 shadow-sm"
                                   placeholder="Masukkan nama lengkap siswa..." required>
                        </div>
                        @error('nama_lengkap') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- NISN --}}
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">NISN</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400 group-focus-within:text-blue-600">
                                <i class="bi bi-postcard"></i>
                            </span>
                            <input type="text" name="nisn" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '')"
                                   value="{{ old('nisn', $siswa->nisn ?? '') }}" 
                                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all font-mono font-medium text-sm text-gray-700 placeholder-slate-400 shadow-sm"
                                   placeholder="Masukkan 10 digit NISN..." required>
                        </div>
                        @error('nisn') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Kelas --}}
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">Kelas</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400 group-focus-within:text-blue-600 pointer-events-none">
                                <i class="bi bi-bookmark-fill"></i>
                            </span>
                            <select name="kelas_id" required 
                                    class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all font-bold text-sm text-gray-700 appearance-none cursor-pointer shadow-sm">
                                <option value="" disabled selected>Pilih Kelas...</option>
                                @foreach ($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}" {{ (old('kelas_id', $siswa->kelas_id ?? '') == $kelas->id) ? 'selected' : '' }}>
                                        {{ $kelas->kelas }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 pointer-events-none">
                                <i class="bi bi-chevron-down text-[10px]"></i>
                            </span>
                        </div>
                        @error('kelas_id') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- DIVIDER (Margin dikurangi) --}}
                    <div class="col-span-1 md:col-span-2 border-t border-slate-100 my-2"></div>

                    {{-- GROUP 2: AKUN LOGIN --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-2 text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-1">
                            <i class="bi bi-shield-lock"></i> Pengaturan Akun
                        </div>
                    </div>

                    {{-- Username --}}
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-indigo-600 transition-colors">Username</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-400 group-focus-within:text-indigo-600">
                                <i class="bi bi-person-circle"></i>
                            </span>
                            <input type="text" name="username" 
                                   value="{{ old('username', $siswa->username ?? '') }}" 
                                   class="w-full pl-9 pr-3 py-2 bg-indigo-50/30 border border-indigo-100 rounded-lg focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none transition-all font-medium text-sm text-gray-700 placeholder-indigo-300/70 shadow-sm"
                                   placeholder="Buat username..." required>
                        </div>
                        @error('username') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-indigo-600 transition-colors">Password</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-400 group-focus-within:text-indigo-600">
                                <i class="bi bi-key-fill"></i>
                            </span>
                            <input type="password" name="password" id="passwordInput"
                                   class="w-full pl-9 pr-10 py-2 bg-indigo-50/30 border border-indigo-100 rounded-lg focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none transition-all font-medium text-sm text-gray-700 placeholder-indigo-300/70 shadow-sm"
                                   placeholder="{{ isset($siswa) ? '••••••' : 'Min. 6 Karakter' }}">
                            
                            {{-- Toggle Eye --}}
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-indigo-300 hover:text-indigo-600 cursor-pointer transition-colors focus:outline-none">
                                <i class="bi bi-eye-slash-fill" id="eyeIcon"></i>
                            </button>
                        </div>
                        
                        <div class="flex justify-between items-center mt-0.5 ml-1">
                            @if(isset($siswa))
                                <p class="text-[10px] text-gray-400">Kosongkan jika tetap.</p>
                            @else
                                <p class="text-[10px] text-gray-400">kombinasi huruf dan angka</p>
                            @endif
                            @error('password') <p class="text-[10px] text-red-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                </div>

                {{-- ACTION BUTTONS (Margin top dikurangi mt-10 -> mt-4) --}}
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('operator.daftar_siswa') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg font-bold hover:bg-gray-50 hover:text-gray-900 transition-all text-xs shadow-sm">
                        Batal
                    </a>
                    
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 transition-all text-xs flex items-center gap-2">
                        <i class="bi bi-save2-fill"></i>
                        <span>{{ isset($siswa) ? 'Simpan Data' : 'Simpan' }}</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        } else {
            input.type = "password";
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        }
    }
</script>
@endsection
