@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
    <a href="{{route('daftar_siswa')}}" class="menu-item ">Daftar Siswa</a>
    <a href="{{route('daftar_guru2')}}" class="menu-item active">Daftar Guru</a>
@endsection

@section('content')
 <div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Wali Kelas</a>
</div>

<button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn mb-5">
    Beranda <i class="bi bi-house-door-fill"></i>
</button>

<button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn mb-5">
    Simpan <i class="bi bi-check-square-fill"></i>
</button>

<div class="overflow-x-auto">
    <div class="form-card">
        <table class="table-container">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Guru Wali</th>
                        <th>Set Username</th>
                        <th>Set Password</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>VII</td>
                        <td>
                            <div class="form-group">
                                <select>
                                    <option selected disabled>Pilih Guru</option>
                                    <option>Budi</option>
                                    <option>Bayu</option>
                                </select>
                            </div>
                        </td>
                        <td>
                        <div class="form-group">
                            <input type="text" placeholder="Username">
                         </div>
                        </td>
                        <td>
                            <div class="form-group">
                            <input type="text" placeholder="Password">
                         </div>
                        </td>
                        </td>
                    </tr>
                    <tr>
                        <td>VIII</td>
                        <td>
                            <div class="form-group">
                                <select>
                                    <option selected disabled>Pilih Guru</option>
                                    <option>Budi</option>
                                    <option>Bayu</option>
                                </select>
                            </div>
                        </td>
                        <td>
                        <div class="form-group">
                            <input type="text" placeholder="Username">
                         </div>
                        </td>
                        <td>
                            <div class="form-group">
                            <input type="text" placeholder="Password">
                         </div>
                        </td>
                        </td>
                    </tr>
                    <tr>
                        <td>IX</td>
                        <td>
                            <div class="form-group">
                                <select>
                                    <option selected disabled>Pilih Guru</option>
                                    <option>Budi</option>
                                    <option>Bayu</option>
                                </select>
                            </div>
                        </td>
                        <td>
                        <div class="form-group">
                            <input type="text" placeholder="Username">
                         </div>
                        </td>
                        <td>
                            <div class="form-group">
                            <input type="text" placeholder="Password">
                         </div>
                        </td>
                        </td>
                    </tr>
                </tbody>
            </table>
    </div>
</div>

@endsection