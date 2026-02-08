@extends('layouts.app')

@section('title', 'Kenaikan Kelas')

@section('sidebar-menu')
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
@endsection

@section('content')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-[Poppins-Bold] text-darkblue">Proses Kenaikan Kelas</h1>
            <p class="text-gray-500 text-sm mt-1">
                Pindahkan siswa ke kelas tingkat selanjutnya secara massal.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- PANEL KIRI: KONTROL --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm h-fit">
            <h3 class="font-[Poppins-Bold] text-darkblue text-lg mb-4">Pengaturan</h3>
            
            <form id="formKenaikan">
                @csrf
                
                {{-- 1. PILIH KELAS ASAL --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Dari Kelas (Asal)</label>
                    <select id="kelasAsal" name="kelas_asal_id" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all" required>
                        <option value="" disabled selected>Pilih Kelas Asal...</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-center my-2 text-gray-400">
                    <i class="bi bi-arrow-down text-2xl"></i>
                </div>

                {{-- 2. PILIH KELAS TUJUAN --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Ke Kelas (Tujuan)</label>
                    <select id="kelasTujuan" name="kelas_tujuan_id" class="w-full p-3 bg-blue-50 border border-blue-200 text-blue-900 font-bold rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all" required>
                        <option value="" disabled selected>Pilih Tujuan...</option>
                        <option value="LULUS" class="bg-green-100 text-green-800 font-bold">🎓 LULUS / ALUMNI</option>
                        <option disabled>----------------</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- TOMBOL PROSES --}}
                <button type="submit" id="btnProses" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Proses Kenaikan
                </button>
            </form>

            <div class="mt-4 p-4 bg-yellow-50 rounded-xl border border-yellow-100 text-xs text-yellow-700 leading-relaxed">
                <i class="bi bi-info-circle-fill mr-1"></i> 
                <strong>Info:</strong> Siswa yang <u>tidak dicentang</u> pada daftar di sebelah kanan akan tetap tinggal di kelas asal.
            </div>
        </div>

        {{-- PANEL KANAN: DAFTAR SISWA --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col min-h-[500px]">
            
            {{-- Header Daftar --}}
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-[Poppins-Bold] text-gray-700 text-sm uppercase">Daftar Siswa Terpilih</h3>
                <div class="text-xs font-bold text-gray-500">
                    Total: <span id="countSelected" class="text-blue-600">0</span> Siswa
                </div>
            </div>

            {{-- Loading State --}}
            <div id="loadingSiswa" class="hidden flex-1 flex flex-col items-center justify-center p-10 text-gray-400">
                <div class="w-8 h-8 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-3"></div>
                Memuat data siswa...
            </div>

            {{-- Empty State --}}
            <div id="emptySiswa" class="flex-1 flex flex-col items-center justify-center p-10 text-gray-400">
                <i class="bi bi-arrow-left-circle text-4xl mb-3 opacity-30"></i>
                <p>Pilih "Kelas Asal" terlebih dahulu</p>
            </div>

            {{-- Tabel Siswa --}}
            <div id="listSiswaContainer" class="hidden overflow-y-auto max-h-[600px]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-100 w-10">
                                <input type="checkbox" id="checkAll" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer" checked>
                            </th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b border-gray-100">Nama Siswa</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b border-gray-100">NISN</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase border-b border-gray-100 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySiswa" class="divide-y divide-gray-50">
                        {{-- Data di-inject via JS --}}
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const kelasAsalSelect = document.getElementById('kelasAsal');
        const loadingSiswa = document.getElementById('loadingSiswa');
        const emptySiswa = document.getElementById('emptySiswa');
        const listContainer = document.getElementById('listSiswaContainer');
        const tbodySiswa = document.getElementById('tbodySiswa');
        const checkAll = document.getElementById('checkAll');
        const countSelected = document.getElementById('countSelected');
        const formKenaikan = document.getElementById('formKenaikan');

        // 1. Load Siswa saat Kelas Asal dipilih
        kelasAsalSelect.addEventListener('change', function() {
            const kelasId = this.value;
            if(!kelasId) return;

            // UI Reset
            loadingSiswa.classList.remove('hidden');
            emptySiswa.classList.add('hidden');
            listContainer.classList.add('hidden');
            tbodySiswa.innerHTML = '';

            // Fetch Data
            fetch(`{{ route('operator.filter_siswa') }}?kelas=${kelasId}`)
                .then(res => res.json())
                .then(data => {
                    loadingSiswa.classList.add('hidden');
                    
                    if(data.length === 0) {
                        emptySiswa.innerHTML = '<p>Tidak ada siswa di kelas ini.</p>';
                        emptySiswa.classList.remove('hidden');
                        return;
                    }

                    listContainer.classList.remove('hidden');
                    
                    // Render Rows
                    let html = '';
                    data.forEach(siswa => {
                        html += `
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-3 text-center">
                                    <input type="checkbox" name="siswa_ids[]" value="${siswa.id}" class="siswa-checkbox w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer" checked>
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-gray-700">${siswa.nama_lengkap}</td>
                                <td class="px-6 py-3 text-sm text-gray-500 font-mono">${siswa.nisn}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="status-badge px-2 py-1 rounded text-[10px] font-bold bg-green-100 text-green-700">Naik</span>
                                </td>
                            </tr>
                        `;
                    });
                    tbodySiswa.innerHTML = html;
                    updateCount();

                    // Re-attach event listeners for individual checkboxes
                    document.querySelectorAll('.siswa-checkbox').forEach(cb => {
                        cb.addEventListener('change', function() {
                            updateStatusBadge(this);
                            updateCount();
                        });
                    });
                });
        });

        // 2. Logic Check All
        checkAll.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.siswa-checkbox').forEach(cb => {
                cb.checked = isChecked;
                updateStatusBadge(cb);
            });
            updateCount();
        });

        // 3. Update UI Badge (Naik vs Tinggal)
        function updateStatusBadge(checkbox) {
            const row = checkbox.closest('tr');
            const badge = row.querySelector('.status-badge');
            
            if (checkbox.checked) {
                badge.className = 'status-badge px-2 py-1 rounded text-[10px] font-bold bg-green-100 text-green-700';
                badge.innerText = 'Naik';
                row.classList.remove('bg-gray-100', 'opacity-50');
            } else {
                badge.className = 'status-badge px-2 py-1 rounded text-[10px] font-bold bg-red-100 text-red-700';
                badge.innerText = 'Tinggal';
                row.classList.add('bg-gray-100', 'opacity-50');
            }
        }

        function updateCount() {
            const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
            countSelected.innerText = checked;
        }

        // 4. Submit Form
        formKenaikan.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validasi manual
            const kelasTujuan = document.getElementById('kelasTujuan').value;
            const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
            
            if(checkedBoxes.length === 0) {
                Swal.fire('Error', 'Pilih minimal satu siswa untuk dinaikkan.', 'error');
                return;
            }

            // Konfirmasi SweetAlert
            Swal.fire({
                title: 'Konfirmasi Kenaikan',
                text: `Anda akan memindahkan ${checkedBoxes.length} siswa ke kelas baru. Lanjutkan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Proses Sekarang!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Collect Data
                    const formData = new FormData(this);
                    
                    // Karena checkbox di luar form asli (diinject JS), kita harus append manual ID-nya
                    // ATAU pastikan form membungkus tabel (tapi layout grid susah).
                    // Solusi: Ambil ID manual dan kirim via fetch JSON
                    
                    const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

                    fetch(`{{ route('operator.proses_kenaikan') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            kelas_asal_id: document.getElementById('kelasAsal').value,
                            kelas_tujuan_id: kelasTujuan,
                            siswa_ids: selectedIds
                        })
                    })
                    .then(res => res.json())
                    .then(response => {
                        if(response.success) {
                            Swal.fire('Berhasil!', response.message, 'success')
                            .then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                        console.error(err);
                    });
                }
            });
        });
    });
</script>
@endsection