@extends('layouts.app')

@section('title', 'Halaman Kepala Sekolah')

@section('sidebar-menu')
    <a href="{{route('daftar_nilai')}}" class="menu-item">Daftar Nilai</a>
    <a href="{{route('daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
    <a href="{{route('daftar_guru')}}" class="menu-item">Daftar Guru</a>
@endsection

@section('content')
     <main class="flex-1 p-10 bg-gray-100 flex flex-col items-center rounded-4xl">
            <!-- Sapaan -->
            <h1 class="text-2xl font-bold mb-20 mt-5 self-start">SELAMAT DATANG IBU LELVARINA S.Pd</h1>

            <!-- Grafik Container -->
            <div class="bg-white rounded-lg shadow p-6 w-full max-w-4xl text-center">
                
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Grafik nilai siswa berdasarkan nilai</h2>
                    <select class="border border-gray-400 px-3 py-1 rounded">
                        <option>UAS</option>
                        <option>UTS</option>
                        <option>KUIS</option>
                    </select>
                </div>
                
                <!-- Placeholder grafik -->
                <div class="w-full flex justify-center items-center bg-gray-200 h-60 rounded">
                    <span class="text-gray-500">[Grafik Placeholder]</span>
                </div>

                <!-- Navigasi Panah dan Bulatan -->
                <div class="flex justify-between items-center mt-4 px-4">
                    <button class="text-2xl">&larr;</button>
                    <div class="flex space-x-2">
                        <div class="w-3 h-3 bg-gray-600 rounded-full"></div>
                        <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                    </div>
                    <button class="text-2xl">&rarr;</button>
                </div>
            </div>
        </main>
    </div>
    
@endsection

