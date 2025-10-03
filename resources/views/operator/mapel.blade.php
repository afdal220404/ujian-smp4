@extends('layouts.app')

@section('title', 'Halaman Operator')

@section('sidebar-menu')
<a href="{{route('operator.daftar_siswa')}}" class="menu-item">Daftar Siswa</a>
<a href="{{route('daftar_guru2')}}" class="menu-item">Daftar Guru</a>
<a href="{{route('mapel')}}" class="menu-item active">Mata Pelajaran</a>
@endsection

@if (session('success'))
<div id="popup-success" class="popup-message success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div id="popup-error" class="popup-message error">
    ❌ Gagal menghapus guru
</div>
@endif

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 w-full mb-5">
    <a class="judul">Mata Pelajaran</a>
</div>

<div class="mapel-container">
    <button type="button" onclick="window.location='{{ route('landingpage3') }}'" class="dark-btn mb-5">
        Beranda <i class="bi bi-house-door-fill"></i>
    </button>
    <div class="filter-section">
        {{-- Ambil daftar kelas dari controller --}}
        <select id="kelasFilter" class="select-box">
            @foreach($kelasList as $kelas)
            <option value="{{ $kelas->id }}">{{ $kelas->kelas }}</option>
            @endforeach
        </select>
        <button id="addMapelBtn" class="dark-btn">Tambah <i class="bi bi-plus-square-fill"></i></button>
        <button id="saveChangesBtn" class="dark-btn disabled mb-5" disabled>Simpan <i class="bi bi-check-square-fill"></i></button>
    </div>

    <div class="overflow-x-auto">
        <div class="form-card">
            <table class="table-container" id="mapelTable">
                <thead>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengampu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan dimuat via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Mata Pelajaran -->
<div id="mapelModal" class="delete-modal2">
    <div class="delete-modal-content2">
        <h3>Tambah Mata Pelajaran</h3>
        <form id="mapelForm" class="form-container2">
            <div class="form-group2">
                <input type="text" id="namaMapel" name="nama_mapel" placeholder="Nama Mata Pelajaran" required>
            </div>
            <div class="form-group2">
                <select id="guruId" name="guru_id" required>
                    <option value="" selected disabled>Pilih Guru Pengampu</option>
                    @foreach ($gurus as $guru)
                    <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-actions2">
                <button type="submit" class="dark-btn">Tambah</button>
                <button type="button" class="btn-cancel2" onclick="closeModal()">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <h3>Konfirmasi Hapus</h3>
        <p id="deleteMessage">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="modal-actions">
            <form id="deleteForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="table-btn-red">Ya, Hapus</button>
            </form>
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    const gurus = @json($gurus ?? []);
    let changesMade = false;

    document.addEventListener('DOMContentLoaded', function() {
        const firstKelasId = document.getElementById('kelasFilter').value;
        loadMapels(firstKelasId);

        document.getElementById('kelasFilter').addEventListener('change', function() {
            loadMapels(this.value);
        });

        document.getElementById('addMapelBtn').addEventListener('click', openAddModal);

        document.getElementById('mapelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveMapel();
        });

        document.getElementById('saveChangesBtn').addEventListener('click', saveChanges);
    });

    function loadMapels(kelasId) {
        let urlTemplate = "{{ route('mapel.getByKelas', ['kelas' => ':kelas']) }}";
        let url = urlTemplate.replace(':kelas', kelasId);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#mapelTable tbody');
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center2">Belum ada data mata pelajaran untuk kelas ini</td></tr>';
                } else {
                    data.forEach(mapel => {
                        const guruOptions = gurus.map(guru =>
                            `<option value="${guru.id}" ${mapel.guru_id == guru.id ? 'selected' : ''}>${guru.nama}</option>`
                        ).join('');
                        const guruOptionsFull = `<option value="" disabled>Pilih Guru</option>` + guruOptions;

                        tbody.innerHTML += `
                            <tr>
                                <td class="editable2" data-id="${mapel.id}" data-field="nama_mapel" ondblclick="enableEdit(this)">${mapel.nama_mapel}</td>
                                <td class="editable2" data-id="${mapel.id}" data-field="guru_id" ondblclick="enableEdit(this)">
                                    <span>${mapel.guru ? mapel.guru.nama : 'Tidak ada guru'}</span>
                                    <select class="guru-select2" style="display:none;">${guruOptionsFull}</select>
                                </td>
                                <td>
                                    <button class="table-btn-red" 
                                            onclick="openDeleteModal(${mapel.id}, '${mapel.nama_mapel.replace(/'/g, "\\'")}')">
                                        Hapus <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                changesMade = false;
                document.getElementById('saveChangesBtn').classList.add('disabled');
                document.getElementById('saveChangesBtn').disabled = true;
            })
            .catch(error => {
                console.error('Error loading mapels:', error);
                const tbody = document.querySelector('#mapelTable tbody');
                tbody.innerHTML = '<tr><td colspan="3" class="text-center2">Error</td></tr>';
            });
    }

    function openAddModal() {
        document.getElementById('namaMapel').value = '';
        document.getElementById('guruId').value = '';
        document.getElementById('mapelModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('mapelModal').style.display = 'none';
    }

    function enableEdit(cell) {
        const id = cell.getAttribute('data-id');
        const field = cell.getAttribute('data-field');

        if (field === 'nama_mapel') {
            const currentText = cell.textContent.trim();
            cell.innerHTML = `<input type="text" value="${currentText}" onblur="saveEdit(${id}, '${field}', this.value)" onkeypress="if(event.keyCode==13) this.blur();">`;
            cell.querySelector('input').focus();
        } else if (field === 'guru_id') {
            const span = cell.querySelector('span');
            const select = cell.querySelector('.guru-select2');
            if (span && select) {
                span.style.display = 'none';
                select.style.display = 'inline';
                select.focus();
                select.addEventListener('change', () => saveEdit(id, field, select.value));
                select.addEventListener('blur', () => {
                    const selectedText = select.options[select.selectedIndex].text;
                    span.textContent = selectedText;
                    span.style.display = 'inline';
                    select.style.display = 'none';
                }, {
                    once: true
                });
            }
        }
        changesMade = true;
        document.getElementById('saveChangesBtn').classList.remove('disabled');
        document.getElementById('saveChangesBtn').disabled = false;
    }

    function saveEdit(id, field, value) {
        fetch(`/mapel/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                [field]: value
            })
        }).then(response => {
            if (response.ok) {
                loadMapels(document.getElementById('kelasFilter').value);
            }
        }).catch(error => console.error('Error saving edit:', error));
    }

    function saveMapel() {
        // ambil ID kelas dari dropdown filter yang ada di halaman utama.
        const kelasId = document.getElementById('kelasFilter').value;

        const namaMapel = document.getElementById('namaMapel').value;
        const guruId = document.getElementById('guruId').value;

        if (!namaMapel || !guruId) {
            alert('Mohon isi semua field!');
            return;
        }

        fetch('{{ route('mapel.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    // Sekarang 'kelas_id' akan berisi nilai yang benar (1, 2, atau 3)
                    kelas_id: kelasId,
                    nama_mapel: namaMapel,
                    guru_id: guruId
                })
            }).then(response => {
            if (response.ok) {
                closeModal();
                loadMapels(kelasId);
            }
        }).catch(error => console.error('Error saving mapel:', error));
    }
    function saveChanges() {
        loadMapels(document.getElementById('kelasFilter').value);
    }
</script>
<script>
    function openDeleteModal(id, nama) {
        const modal = document.getElementById("deleteModal");
        const message = document.getElementById("deleteMessage");
        const form = document.getElementById("deleteForm");

        // Sesuaikan pesan dan URL action untuk mapel
        message.innerText = `Apakah Anda yakin ingin menghapus mata pelajaran "${nama}"?`;
        form.action = `/mapel/${id}`; // Arahkan ke route destroy mapel

        modal.style.display = "flex";
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const popup = document.querySelector('.popup-message');
        if (popup) {
            // Tampilkan
            setTimeout(() => popup.classList.add('show'), 100);

            // Sembunyikan setelah 4 detik
            setTimeout(() => {
                popup.classList.remove('show');
            }, 4000);
        }
    });
</script>
@endsection