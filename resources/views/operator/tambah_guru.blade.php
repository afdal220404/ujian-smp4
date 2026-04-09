@extends('layouts.app')

@section('title', isset($guru) ? 'Edit Guru' : 'Tambah Guru')

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

    {{-- 1. BREADCRUMB --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-0.5">
                <a href="{{ route('operator.landingpage') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i></a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Manajemen Data</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-darkblue font-bold">Data Guru</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ isset($guru) ? 'Edit Data' : 'Tambah Baru' }}</span>
            </div>
            <h1 class="text-xl font-[Poppins-Bold] text-darkblue tracking-tight">
                {{ isset($guru) ? 'Perbarui Profil Guru' : 'Registrasi Guru Baru' }}
            </h1>
        </div>
    </div>

    {{-- 2. KARTU FORMULIR --}}
    <div class="w-full"> 
        <div class="bg-white rounded-xl shadow-lg shadow-blue-900/5 border border-blue-50 overflow-hidden relative">
            
            {{-- Garis Aksen --}}
            <div class="h-1 w-full bg-blue-600"></div>

            {{-- Header Form --}}
            <div class="px-6 py-3 bg-blue-50/50 border-b border-blue-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white text-blue-600 border border-blue-100 shadow-sm flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Formulir Data Guru</h3>
                    <p class="text-xs text-gray-500">
                        Lengkapi profil, peran (role), dan akun login staf pengajar.
                    </p>
                </div>
            </div>

            {{-- Alert Error Global --}}
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

            {{-- FORM BODY --}}
            <form action="@if(isset($guru)) {{ route('guru.update', $guru->id) }} @else {{ route('guru.store') }} @endif" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="p-5">
                
                @csrf
                @if(isset($guru))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">

                    {{-- === GROUP 1: DATA PROFIL === --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-2 text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">
                            <i class="bi bi-person-badge"></i> Data Profil & Jabatan
                        </div>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="col-span-1 md:col-span-2 group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">Nama Lengkap</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400 group-focus-within:text-blue-600">
                                <i class="bi bi-type-h1"></i>
                            </span>
                            <input type="text" name="nama_lengkap" 
                                   value="{{ old('nama_lengkap', $guru->nama_lengkap ?? '') }}" 
                                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all font-semibold text-sm text-gray-700 placeholder-slate-400 shadow-sm"
                                   placeholder="Nama Lengkap beserta gelar..." required>
                        </div>
                        @error('nama_lengkap') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- NIP --}}
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">NIP</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400 group-focus-within:text-blue-600">
                                <i class="bi bi-postcard"></i>
                            </span>
                            <input type="number" name="nip" 
                                   value="{{ old('nip', $guru->nip ?? '') }}" 
                                   class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all font-mono font-medium text-sm text-gray-700 placeholder-slate-400 shadow-sm"
                                   placeholder="Nomor Induk Pegawai..." required>
                        </div>
                        @error('nip') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Role (Jabatan) --}}
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">Role / Jabatan</label>
                        <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400 group-focus-within:text-blue-600 pointer-events-none">
                                <i class="bi bi-shield-check"></i>
                            </span>
                            <select name="role" required 
                                    class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all font-bold text-sm text-gray-700 appearance-none cursor-pointer shadow-sm">
                                <option disabled {{ !isset($guru) ? 'selected' : '' }}>Pilih Role...</option>
                                <option value="Guru" {{ (old('role', $guru->role ?? '') == 'Guru') ? 'selected' : '' }}>Guru</option>
                                <option value="Kepala Sekolah" {{ (old('role', $guru->role ?? '') == 'Kepala Sekolah') ? 'selected' : '' }}>Kepala Sekolah</option>
                                <option value="Operator" {{ (old('role', $guru->role ?? '') == 'Operator') ? 'selected' : '' }}>Operator</option>
                            </select>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 pointer-events-none">
                                <i class="bi bi-chevron-down text-[10px]"></i>
                            </span>
                        </div>
                        @error('role') <p class="text-[10px] text-red-500 mt-0.5 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Upload Foto --}}
                    <div class="col-span-1 md:col-span-2 group">
                        <label class="block text-xs font-bold text-gray-700 mb-1 group-focus-within:text-blue-600 transition-colors">Foto Profil</label>
                        <div class="flex items-start gap-4">
                            {{-- Preview Image --}}
                            <div class="shrink-0">
                                <img id="preview" 
                                     src="@if(isset($guru) && $guru->foto) {{ asset('storage/'.$guru->foto) }} @else {{ asset('image/dummy.jpg') }} @endif" 
                                     alt="Preview" 
                                     class="h-16 w-16 object-cover rounded-xl border border-slate-200 shadow-sm">
                            </div>
                            
                            {{-- Input File --}}
                            <div class="w-full">
                                <input type="file" id="photo" name="foto" accept="image/*" onchange="previewImage()"
                                       class="block w-full text-xs text-slate-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-xs file:font-bold
                                              file:bg-blue-50 file:text-blue-700
                                              hover:file:bg-blue-100
                                              cursor-pointer bg-slate-50 border border-slate-200 rounded-lg">
                                <p class="text-[10px] text-gray-400 mt-1">Format: JPG, JPEG, PNG. Maks: 2MB.</p>
                                @error('foto') <p class="text-[10px] text-red-500 mt-0.5 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>


                    {{-- DIVIDER --}}
                    <div class="col-span-1 md:col-span-2 border-t border-slate-100 my-2"></div>


                    {{-- === GROUP 2: AKUN LOGIN === --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-2 text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-1">
                            <i class="bi bi-lock"></i> Pengaturan Akun
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
                                   value="{{ old('username', $guru->username ?? '') }}" 
                                   class="w-full pl-9 pr-3 py-2 bg-indigo-50/30 border border-indigo-100 rounded-lg focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 outline-none transition-all font-medium text-sm text-gray-700 placeholder-indigo-300/70 shadow-sm"
                                   placeholder="Username login..." required>
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
                                   placeholder="{{ isset($guru) ? '•••••• (Isi jika ingin mengubah)' : 'Min. 6 Karakter (Huruf & Angka)' }}">
                            
                            {{-- Toggle Eye --}}
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-indigo-300 hover:text-indigo-600 cursor-pointer transition-colors focus:outline-none">
                                <i class="bi bi-eye-slash-fill" id="eyeIcon"></i>
                            </button>
                        </div>
                        
                        <div class="flex justify-between items-center mt-0.5 ml-1">
                            @if(isset($guru))
                                <p class="text-[10px] text-gray-400">Kosongkan jika tidak ingin mengubah. (Jika diisi: Min. 6 Karakter & Alfanumerik)</p>
                            @else
                                <p class="text-[10px] text-gray-400">Wajib diisi untuk akun baru. (Min. 6 Karakter & Alfanumerik)</p>
                            @endif
                            @error('password') <p class="text-[10px] text-red-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                </div>

                {{-- ACTION BUTTONS --}}
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('daftar_guru2') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg font-bold hover:bg-gray-50 hover:text-gray-900 transition-all text-xs shadow-sm">
                        Batal
                    </a>
                    
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 transition-all text-xs flex items-center gap-2">
                        <i class="bi bi-save2-fill"></i>
                        <span>{{ isset($guru) ? 'Simpan Data' : 'Simpan' }}</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Fitur Toggle Password
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

    // Fitur Preview Image
    function previewImage() {
        const file = document.getElementById('photo').files[0];
        const preview = document.getElementById('preview');
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
