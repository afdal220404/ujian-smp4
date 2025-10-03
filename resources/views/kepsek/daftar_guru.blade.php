@extends('layouts.app')

@section('title', 'Halaman Guru Mapel')

@section('sidebar-menu')
    <a href="" class="menu-item ">Daftar Nilai</a>
    <a href="" class="menu-item">Daftar Siswa</a>
    <a href="" class="menu-item active">Daftar Guru</a>
@endsection

@section('content')
 <div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Daftar Guru</a>
</div>
    <div class="overflow-x-auto">
              <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
                    <thead>
                        <tr class="bg-gray-700 text-white text-center">
                            <th class="px-4 py-3 border border-gray-600">Nama Guru</th>
                            <th class="px-4 py-3 border border-gray-600">NIP</th>
                            <th class="px-4 py-3 border border-gray-600">Status</th>
                            <th class="px-4 py-3 border border-gray-600">Mata Pelajaran</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-50 text-center">
                        <tr class="hover:bg-gray-100 transition">
                            <td class="px-4 py-3 border border-gray-300">Budi Santoso</td>
                            <td class="px-4 py-3 border border-gray-300"></td>
                            <td class="px-4 py-3 border border-gray-300"></td>
                            <td class="px-4 py-3 border border-gray-300"></td>
                            
                        </tr>
                        <tr class="hover:bg-gray-100 transition">
                            <td class="px-4 py-3 border border-gray-300">Siti Aminah</td>
                            <td class="px-4 py-3 border border-gray-300"></td>
                            <td class="px-4 py-3 border border-gray-300"></td>
                            <td class="px-4 py-3 border border-gray-300"></td>
                           
                        </tr>
                    </tbody>
                </table>
        </div>
    </div>

@endsection
