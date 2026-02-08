@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('sidebar-menu')
    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3 mt-4">Utama</div>
    <a href="{{ route('operator.landingpage') }}" class="nav-link">
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
    <a href="{{ route('mapel') }}" class="nav-link active">
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
                <span class="text-darkblue font-bold">Mata Pelajaran</span>
            </div>
            <h1 class="text-xl font-[Poppins-Bold] text-darkblue tracking-tight">
                Manajemen Mata Pelajaran
            </h1>
        </div>
    </div>

    {{-- 2. NOTIFIKASI --}}
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

    {{-- 3. TOOLBAR (Filter & Action) --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            
            {{-- KIRI: Filter Kelas --}}
            <div class="relative w-full md:w-64 group">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-500 pointer-events-none group-focus-within:text-blue-700">
                    <i class="bi bi-bookmark-fill"></i>
                </span>
                <select id="kelasFilter" 
                        class="w-full pl-10 pr-8 py-2.5 bg-blue-50/50 border border-blue-100 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm font-bold text-blue-900 appearance-none cursor-pointer shadow-sm hover:border-blue-300">
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
                    @endforeach
                </select>
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-blue-400">
                    <i class="bi bi-chevron-down text-[10px]"></i>
                </span>
            </div>

            {{-- KANAN: Tombol Aksi --}}
            <div class="flex gap-2 w-full md:w-auto">
                {{-- Tombol Refresh --}}
                <button onclick="loadMapels(document.getElementById('kelasFilter').value)" 
                        class="px-3 py-2.5 bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 hover:text-gray-900 transition-all border border-gray-200 shadow-sm"
                        title="Muat Ulang Data">
                    <i class="bi bi-arrow-clockwise text-lg"></i>
                </button>

                {{-- Tombol Tambah --}}
                <button onclick="openAddModal()" 
                        class="w-full md:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 hover:shadow-lg transition-all flex items-center justify-center gap-2 shadow-blue-200 shadow-md">
                    <i class="bi bi-plus-lg"></i> Tambah Mapel
                </button>
            </div>
        </div>
    </div>

    {{-- 4. TABEL DATA --}}
    <div class="w-full"> 
        <div class="bg-white rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden relative min-h-[300px]">
            
            {{-- Loading Overlay --}}
            <div id="loadingOverlay" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center backdrop-blur-sm hidden">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-sm text-gray-500 font-bold mt-3">Memuat mata pelajaran...</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="mapelTable">
                    <thead class="bg-gray-50 sticky top-0 z-0">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 w-1/3">Mata Pelajaran</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Guru Pengampu</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        {{-- Data di-load via JS --}}
                    </tbody>
                </table>
            </div>
            
            {{-- Info Edit --}}
            <div class="px-6 py-3 bg-blue-50/30 border-t border-gray-100 text-xs text-blue-600 flex items-center gap-2">
                <i class="bi bi-info-circle-fill"></i>
                <span>Tips: Klik dua kali (double click) pada nama mapel atau guru untuk mengedit data secara langsung.</span>
            </div>
        </div>
    </div>

    {{-- 5. MODAL TAMBAH MAPEL --}}
    <div id="mapelModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-100">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-book-fill text-blue-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-bold leading-6 text-gray-900">Tambah Mata Pelajaran</h3>
                                <div class="mt-4 space-y-4">
                                    
                                    {{-- Input Nama Mapel --}}
                                    <div class="group text-left">
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Mata Pelajaran</label>
                                        <input type="text" id="namaMapelInput" 
                                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm font-medium placeholder-gray-400"
                                               placeholder="Contoh: Matematika, Bahasa Inggris...">
                                    </div>

                                    {{-- Input Guru --}}
                                    <div class="group text-left">
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Guru Pengampu</label>
                                        <div class="relative">
                                            <select id="guruIdInput" 
                                                    class="w-full pl-3 pr-8 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all text-sm font-medium appearance-none cursor-pointer">
                                                <option value="" selected disabled>-- Pilih Guru --</option>
                                                @foreach ($gurus as $guru)
                                                    <option value="{{ $guru->id }}">{{ $guru->nama_lengkap }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                                <i class="bi bi-chevron-down text-[10px]"></i>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button onclick="saveMapel()" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 sm:w-auto transition-colors">
                            Simpan
                        </button>
                        <button onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 6. MODAL HAPUS --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="bi bi-trash3-fill text-red-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-[Poppins-Bold] leading-6 text-gray-900">Hapus Mata Pelajaran</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" id="deleteMessage">
                                        Apakah Anda yakin? Data ini tidak bisa dikembalikan.
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
    const gurus = @json($gurus ?? []);
    const loadingOverlay = document.getElementById("loadingOverlay");

    document.addEventListener('DOMContentLoaded', function() {
        const kelasFilter = document.getElementById('kelasFilter');
        
        // Load data awal
        loadMapels(kelasFilter.value);

        // Event saat filter ganti
        kelasFilter.addEventListener('change', function() {
            loadMapels(this.value);
        });
    });

    // 1. FUNGSI LOAD DATA
    function loadMapels(kelasId) {
        loadingOverlay.classList.remove('hidden'); // Tampilkan loading
        
        let urlTemplate = "{{ route('mapel.getByKelas', ['kelas' => ':kelas']) }}";
        let url = urlTemplate.replace(':kelas', kelasId);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#mapelTable tbody');
                tbody.innerHTML = '';
                
                if (data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="bi bi-journal-x text-4xl mb-2 opacity-50"></i>
                                    <span class="text-sm font-medium">Belum ada mata pelajaran di kelas ini.</span>
                                </div>
                            </td>
                        </tr>`;
                } else {
                    // Siapkan opsi guru untuk dropdown edit inline
                    const guruOptions = gurus.map(guru =>
                        `<option value="${guru.id}">${guru.nama_lengkap}</option>`
                    ).join('');
                    
                    data.forEach(mapel => {
                        // Cek apakah guru ada, jika tidak tampilkan '-'
                        const namaGuru = mapel.guru ? mapel.guru.nama_lengkap : '<span class="text-red-400 italic">Belum ada guru</span>';
                        
                        tbody.innerHTML += `
                            <tr class="hover:bg-blue-50/20 transition-colors group border-b border-gray-50 last:border-none">
                                {{-- KOLOM NAMA MAPEL (Editable) --}}
                                <td class="px-6 py-4 whitespace-nowrap cursor-pointer" 
                                    ondblclick="enableEdit(this, ${mapel.id}, 'nama_mapel')">
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-journal-text text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                                        <span class="text-sm font-bold text-darkblue display-value">${mapel.nama_mapel}</span>
                                        <input type="text" class="edit-input hidden w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-200" value="${mapel.nama_mapel}">
                                    </div>
                                </td>

                                {{-- KOLOM GURU (Editable) --}}
                                <td class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                    ondblclick="enableEdit(this, ${mapel.id}, 'guru_id')">
                                    
                                    <div class="display-value text-sm font-medium text-gray-600">
                                        ${namaGuru}
                                    </div>
                                    
                                    <select class="edit-input hidden w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-200">
                                        <option value="" disabled>Pilih Guru</option>
                                        ${gurus.map(g => `<option value="${g.id}" ${mapel.guru_id == g.id ? 'selected' : ''}>${g.nama_lengkap}</option>`).join('')}
                                    </select>
                                </td>

                                {{-- KOLOM AKSI --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button onclick="openDeleteModal(${mapel.id}, '${mapel.nama_mapel.replace(/'/g, "\\'")}')" 
                                            class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all shadow-sm"
                                            title="Hapus Mapel">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                loadingOverlay.classList.add('hidden'); // Sembunyikan loading
            });
    }

    // 2. FITUR EDIT INLINE (Double Click)
    function enableEdit(cell, id, field) {
        const displayEl = cell.querySelector('.display-value');
        const inputEl = cell.querySelector('.edit-input');
        
        // Sembunyikan teks, tampilkan input
        displayEl.classList.add('hidden');
        inputEl.classList.remove('hidden');
        inputEl.focus();

        // Event saat selesai edit (Blur / Enter)
        const saveHandler = () => {
            const newValue = inputEl.value;
            
            // Simpan ke server
            saveEdit(id, field, newValue, cell);
            
            // Kembalikan tampilan
            // (Untuk sementara update text dulu biar responsif, nanti di-refresh loadMapels)
            if(field === 'nama_mapel') {
                displayEl.textContent = newValue;
            } else {
                 // Khusus dropdown guru, ambil text option yang dipilih
                 const selectedText = inputEl.options[inputEl.selectedIndex].text;
                 displayEl.textContent = selectedText;
            }
            
            displayEl.classList.remove('hidden');
            inputEl.classList.add('hidden');
        };

        // Simpan saat klik luar (blur)
        inputEl.addEventListener('blur', saveHandler, { once: true });
        
        // Simpan saat tekan Enter (khusus text input)
        if(inputEl.tagName === 'INPUT') {
            inputEl.addEventListener('keypress', function(e) {
                if(e.key === 'Enter') inputEl.blur();
            });
        }
    }

    // 3. SIMPAN PERUBAHAN EDIT
    function saveEdit(id, field, value, cell) {
        fetch(`/mapel/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ [field]: value })
        })
        .then(response => {
            if (response.ok) {
                // Beri efek visual sukses (border hijau sesaat)
                cell.classList.add('bg-green-50');
                setTimeout(() => cell.classList.remove('bg-green-50'), 500);
            } else {
                alert('Gagal menyimpan perubahan.');
            }
        })
        .catch(error => console.error('Error saving edit:', error));
    }

    // 4. SIMPAN MAPEL BARU
    function saveMapel() {
        const kelasId = document.getElementById('kelasFilter').value;
        const namaMapel = document.getElementById('namaMapelInput').value;
        const guruId = document.getElementById('guruIdInput').value;

        if (!namaMapel || !guruId) {
            alert('Mohon isi nama mata pelajaran dan pilih guru pengampu!');
            return;
        }

        fetch('{{ route('mapel.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                kelas_id: kelasId,
                nama_mapel: namaMapel,
                guru_id: guruId
            })
        })
        .then(response => {
            if (response.ok) {
                closeModal();
                // Reset form
                document.getElementById('namaMapelInput').value = '';
                document.getElementById('guruIdInput').value = '';
                // Reload data
                loadMapels(kelasId);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // 5. MODAL CONTROL
    function openAddModal() {
        document.getElementById('mapelModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('mapelModal').classList.add('hidden');
    }

    function openDeleteModal(id, nama) {
        const modal = document.getElementById("deleteModal");
        const message = document.getElementById("deleteMessage");
        const form = document.getElementById("deleteForm");

        message.innerText = `Apakah Anda yakin ingin menghapus mapel "${nama}"?`;
        form.action = `/mapel/${id}`;
        modal.classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.add('hidden');
    }

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
</script>
@endsection