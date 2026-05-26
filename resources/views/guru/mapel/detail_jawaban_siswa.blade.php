@extends('layouts.app')

@section('title', 'Jawaban Siswa - ' . $siswa->nama_lengkap)

@section('sidebar-menu')
{{-- Tombol Kembali --}}
<div class="mb-4 px-3">
    <a href="{{ route('guru.index') }}"
        class="flex items-center gap-2 text-cyan-100/70 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
        <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
    </a>
</div>

<a href="{{ route('guru.mapel.dashboard', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
</a>
<a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link">
    <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
</a>
<a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link">
    <i class="bi bi-collection"></i> <span>Bank Soal</span>
</a>
@endsection

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
    <div>
        <div>
            <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-gray-500">Kelas VII</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Detail Ujian</span>
                <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                <span class="text-blue-600 font-bold">Detail Jawaban</span>
            </div>
        </div>
        <h1 class="text-3xl font-[Poppins-Bold] text-darkblue"></h1>
    </div>

    <a href="{{ route('guru.mapel.ujian.detail', $ujian->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
{{-- 2. INFO SISWA & SKOR --}}
<div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex-grow text-center md:text-left">
            <h1 class="text-2xl font-bold text-darkblue">{{ $siswa->nama_lengkap }}</h1>
            <div class="flex flex-wrap justify-center md:justify-start items-center gap-3 mt-2">
                <span class="px-2.5 py-1 bg-gray-100 rounded text-xs text-gray-600 font-medium">NISN: {{ $siswa->nisn }}</span>
                <span class="px-2.5 py-1 bg-gray-100 rounded text-xs text-gray-600 font-medium">Kelas: {{ $siswa->kelas->kelas ?? '-' }}</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold">{{ $ujian->nama_ujian }}</span>
            </div>
        </div>

        {{-- Skor Circle --}}
        <div class="flex items-center gap-4 border-l-0 md:border-l border-gray-100 md:pl-6">
            {{-- Letakkan di dalam kartu Info Siswa --}}
            <div class="flex flex-wrap justify-center md:justify-start items-center gap-6 mt-4 pt-4 border-t border-gray-50">

                {{-- Waktu Mulai --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <i class="bi bi-play-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none">Mulai</p>
                        <p class="text-xs font-semibold text-gray-700">
                            {{ $hasilUjian ? \Carbon\Carbon::parse($hasilUjian->waktu_mulai)->format('H:i') : '-' }} WIB
                        </p>
                    </div>
                </div>

                {{-- Waktu Selesai --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                        <i class="bi bi-stop-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none">Selesai</p>
                        <p class="text-xs font-semibold text-gray-700">
                            {{ $hasilUjian ? \Carbon\Carbon::parse($hasilUjian->waktu_selesai)->format('H:i') : '-' }} WIB
                        </p>
                    </div>
                </div>

                {{-- Jumlah Benar --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none">Benar</p>
                        <p class="text-xs font-semibold text-green-600">{{ $jumlahBenar }} <span class="text-gray-400 font-normal">/ {{ $jumlahTotalSoal }}</span></p>
                    </div>
                </div>

                {{-- Jumlah Salah --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none">Salah</p>
                        <p class="text-xs font-semibold text-red-500">{{ $jumlahTotalSoal - $jumlahBenar }} <span class="text-gray-400 font-normal">/ {{ $jumlahTotalSoal }}</span></p>
                    </div>
                </div>
            </div>
            <div class="text-right hidden md:block">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Nilai Akhir</div>
                <div class="text-xs text-gray-400">Skor Total</div>
            </div>
            <div class="relative w-16 h-16 flex items-center justify-center rounded-full border-4 {{ $nilai >= 75 ? 'border-green-100 bg-green-50 text-green-600' : 'border-red-100 bg-red-50 text-red-600' }}">
                <span class="text-xl font-bold">{{ round($nilai) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- 3. DAFTAR SOAL (GRID 2 KOLOM) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($daftarSoal as $soal)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative group hover:shadow-md transition-all flex flex-col h-full">

        {{-- Header Kartu: Nomor & Status --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-500 font-bold text-xs flex items-center justify-center shadow-sm">
                    {{ $loop->iteration }}
                </span>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Soal</span>
            </div>

            {{-- Status Badge --}}
            @if($soal->status_jawaban)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-green-100 text-green-700 text-[10px] font-bold">
                <i class="bi bi-check-lg"></i> Benar
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-red-100 text-red-700 text-[10px] font-bold">
                <i class="bi bi-x-lg"></i> Salah
            </span>
            @endif
        </div>

        <div class="p-5 flex-grow flex flex-col">
            {{-- Pertanyaan & Gambar --}}
            <div class="mb-5 flex-grow">

                {{-- PERBAIKAN TAMPILAN GAMBAR --}}
                @if(isset($soal->gambar) && !empty($soal->gambar))
                <div class="mb-4 w-full bg-gray-50 rounded-lg border border-gray-100 p-2 flex justify-center items-center">
                    {{-- Gunakan max-h agar tidak terlalu tinggi, dan w-auto agar rasio terjaga --}}
                    <img src="{{ asset('storage/' . $soal->gambar) }}"
                        alt="Soal"
                        class="max-h-60 max-w-full h-auto object-contain rounded shadow-sm hover:scale-105 transition-transform cursor-pointer"
                        onclick="window.open(this.src, '_blank')">
                </div>
                @endif

                {{-- Teks Pertanyaan --}}
                <div class="text-gray-800 text-sm font-medium leading-relaxed">
                    {!! nl2br(e($soal->pertanyaan)) !!}
                </div>
            </div>

            {{-- OPTION JAWABAN (MENDUKUNG SEMUA TIPE SOAL) --}}
            <div class="grid grid-cols-1 gap-2 mt-auto">
                
                {{-- 1. PILIHAN GANDA --}}
                @if($soal->tipe == 'pilihan_ganda' || empty($soal->tipe))
                    @foreach(['A', 'B', 'C', 'D'] as $opt)
                        @php
                            $propKey = 'opsi_' . strtolower($opt);
                            $imgKey  = 'gambar_' . strtolower($opt);
                            $textOpsi = $soal->$propKey ?? null;
                            $imgOpsi  = $soal->$imgKey ?? null;

                            $wrapperClass = "border-gray-100 bg-white text-gray-500 hover:bg-gray-50";
                            $badgeClass = "bg-gray-100 text-gray-400";
                            $icon = "";

                            $isDijawab = (strtoupper($soal->jawaban_siswa ?? '') == $opt);
                            // Ambil kunci, pastikan aman dari "opsi_" format
                            $kunciBersih = str_ireplace('opsi_', '', $soal->kunci_jawaban ?? '');
                            $isKunci = (strtoupper($kunciBersih) == $opt);

                            if ($isDijawab) {
                                if ($isKunci) {
                                    $wrapperClass = "border-green-500 bg-green-50 text-green-900 ring-1 ring-green-500 shadow-sm";
                                    $badgeClass = "bg-green-200 text-green-800";
                                    $icon = "<i class='bi bi-check-circle-fill text-green-600 text-sm ml-auto'></i>";
                                } else {
                                    $wrapperClass = "border-red-500 bg-red-50 text-red-900 ring-1 ring-red-500 shadow-sm";
                                    $badgeClass = "bg-red-200 text-red-800";
                                    $icon = "<i class='bi bi-x-circle-fill text-red-500 text-sm ml-auto'></i>";
                                }
                            } elseif ($isKunci) {
                                $wrapperClass = "border-green-400 bg-white text-green-700 border-dashed border-2";
                                $badgeClass = "bg-green-100 text-green-600";
                                $icon = "<span class='text-[10px] font-bold text-green-600 bg-green-100 px-1.5 py-0.5 rounded ml-auto'>Kunci</span>";
                            }
                        @endphp
                        <div class="flex items-center gap-3 p-2.5 rounded-lg border text-xs transition-all {{ $wrapperClass }}">
                            <div class="w-6 h-6 rounded flex items-center justify-center font-bold flex-shrink-0 {{ $badgeClass }}">
                                {{ $opt }}
                            </div>
                            <div class="font-medium flex-grow break-words leading-snug flex flex-col gap-1">
                                @if(!empty($imgOpsi)) 
                                    <img src="{{ asset('storage/' . $imgOpsi) }}" class="max-h-16 w-auto rounded object-contain bg-white border border-gray-100"> 
                                @endif
                                @if(!empty($textOpsi)) <span>{{ $textOpsi }}</span> @else @if(empty($imgOpsi)) <span>-</span> @endif @endif
                            </div>
                            {!! $icon !!}
                        </div>
                    @endforeach

                {{-- 2. JAWABAN GANDA (Pilih Banyak) --}}
                @elseif($soal->tipe == 'jawaban_ganda')
                    @php
                        // Cek apakah data_soal perlu di-decode manual
                        $dataSoal = is_string($soal->data_soal) ? json_decode($soal->data_soal, true) : $soal->data_soal;
                        $options = $dataSoal['options'] ?? [];
                        
                        $kunciRaw = $soal->kunci_jawaban ?? '';
                        $kunciList = array_filter(array_map('trim', array_map('strtoupper', explode(',', str_ireplace('opsi_', '', $kunciRaw)))));
                        
                        $jwbRaw = $soal->jawaban_siswa ?? '';
                        // Jawaban siswa bisa berupa array JSON ["A","C"] atau string "A,C"
                        $jwbDecoded = json_decode($jwbRaw, true);
                        $jwbList = is_array($jwbDecoded) ? $jwbDecoded : array_filter(array_map('trim', array_map('strtoupper', explode(',', $jwbRaw))));
                    @endphp
                    @foreach($options as $idx => $opt)
                        @php
                            $huruf = $abjad[$idx] ?? '';
                            $optId = isset($opt['id']) ? strtoupper(trim((string)$opt['id'])) : '';
                            
                            $isKunci = in_array($huruf, $kunciList) || ($optId !== '' && in_array($optId, $kunciList)) || (!empty($opt['correct']) && (bool)$opt['correct'] === true);
                            $isDijawab = in_array($huruf, $jwbList) || ($optId !== '' && in_array($optId, $jwbList));

                            $wrapperClass = "border-gray-100 bg-white text-gray-500 hover:bg-gray-50";
                            $badgeClass = "bg-gray-100 text-gray-400";
                            $icon = "";

                            if ($isDijawab) {
                                if ($isKunci) {
                                    $wrapperClass = "border-green-500 bg-green-50 text-green-900 ring-1 ring-green-500 shadow-sm";
                                    $badgeClass = "bg-green-200 text-green-800";
                                    $icon = "<i class='bi bi-check-circle-fill text-green-600 text-sm ml-auto'></i>";
                                } else {
                                    $wrapperClass = "border-red-500 bg-red-50 text-red-900 ring-1 ring-red-500 shadow-sm";
                                    $badgeClass = "bg-red-200 text-red-800";
                                    $icon = "<i class='bi bi-x-circle-fill text-red-500 text-sm ml-auto'></i>";
                                }
                            } elseif ($isKunci) {
                                $wrapperClass = "border-green-400 bg-white text-green-700 border-dashed border-2";
                                $badgeClass = "bg-green-100 text-green-600";
                                $icon = "<span class='text-[10px] font-bold text-green-600 bg-green-100 px-1.5 py-0.5 rounded ml-auto'>Kunci</span>";
                            }
                        @endphp
                        <div class="flex items-center gap-3 p-2.5 rounded-lg border text-xs transition-all {{ $wrapperClass }}">
                            <div class="w-6 h-6 rounded flex items-center justify-center font-bold flex-shrink-0 {{ $badgeClass }}">
                                <i class="bi {{ $isDijawab ? 'bi-check-square-fill' : 'bi-square' }}"></i>
                            </div>
                            <div class="font-medium flex-grow break-words leading-snug flex flex-col gap-1">
                                @if(!empty($opt['gambar'])) 
                                    <img src="{{ asset('storage/' . $opt['gambar']) }}" class="max-h-16 w-auto rounded object-contain bg-white border border-gray-100"> 
                                @endif
                                @if(!empty($opt['text'])) <span>{{ $opt['text'] }}</span> @else @if(empty($opt['gambar'])) <span>-</span> @endif @endif
                            </div>
                            {!! $icon !!}
                        </div>
                    @endforeach

                {{-- 3. BENAR / SALAH --}}
                @elseif($soal->tipe == 'benar_salah')
                    @php
                        $dataSoal = is_string($soal->data_soal) ? json_decode($soal->data_soal, true) : $soal->data_soal;
                        $pernyataan = $dataSoal['pernyataan'] ?? [];
                        
                        $jwbRaw = $soal->jawaban_siswa ?? '';
                        $jwbArr = json_decode($jwbRaw, true);
                        if(!is_array($jwbArr)) $jwbArr = [];
                    @endphp
                    <div class="bg-gray-50 rounded-lg p-3 text-xs border border-gray-200">
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($pernyataan as $idx => $stmt)
                                @php
                                    $kunci = strtoupper($stmt['correct'] ?? '');
                                    
                                    // Ambil jawaban siswa berdasarkan index/ID
                                    $idStmt = $stmt['id'] ?? $idx;
                                    $jwbSiswa = isset($jwbArr[$idStmt]) ? strtoupper($jwbArr[$idStmt]) : (isset($jwbArr[$idx]) ? strtoupper($jwbArr[$idx]) : '');
                                    
                                    $isBenar = ($kunci === $jwbSiswa);
                                @endphp
                                <div class="flex flex-col gap-2 p-2.5 bg-white border {{ $jwbSiswa ? ($isBenar ? 'border-green-300 shadow-sm ring-1 ring-green-200' : 'border-red-300 shadow-sm ring-1 ring-red-200') : 'border-gray-200' }} rounded">
                                    <div class="flex flex-col flex-1 leading-snug text-gray-700">
                                        @if(!empty($stmt['gambar'])) 
                                            <img src="{{ asset('storage/' . $stmt['gambar']) }}" class="max-h-12 w-auto rounded object-contain mb-1 border border-gray-100"> 
                                        @endif
                                        <span>{{ $stmt['text'] ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 pt-2 border-t border-gray-100">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Jwb Siswa:</span>
                                            @if($jwbSiswa === 'TRUE')
                                                <span class="bg-blue-100 text-blue-700 border border-blue-200 px-2 py-0.5 rounded text-[10px] font-black">BENAR</span>
                                            @elseif($jwbSiswa === 'FALSE')
                                                <span class="bg-blue-100 text-blue-700 border border-blue-200 px-2 py-0.5 rounded text-[10px] font-black">SALAH</span>
                                            @else
                                                <span class="bg-gray-100 text-gray-500 border border-gray-200 px-2 py-0.5 rounded text-[10px] font-black">KOSONG</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Kunci:</span>
                                            <span class="bg-green-100 text-green-700 border border-green-200 px-2 py-0.5 rounded text-[10px] font-black">{{ $kunci === 'TRUE' ? 'BENAR' : 'SALAH' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                {{-- 4. MENJODOHKAN --}}
                @elseif($soal->tipe == 'menjodohkan')
                    @php
                        $dataSoal = is_string($soal->data_soal) ? json_decode($soal->data_soal, true) : $soal->data_soal;
                        $matches = $dataSoal['matches'] ?? [];
                        
                        $jwbRaw = $soal->jawaban_siswa ?? '';
                        $jwbArr = json_decode($jwbRaw, true);
                        if(!is_array($jwbArr)) $jwbArr = [];
                    @endphp
                    <div class="bg-gray-50 rounded-lg p-3 text-xs border border-gray-200">
                        <div class="font-bold border-b border-gray-200 pb-2 mb-3 text-gray-600 flex items-center gap-2 uppercase tracking-wider text-[10px]">
                            <i class="bi bi-link"></i> Pemetaan Jawaban Siswa
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($matches as $lIdx => $matchL)
                                @php
                                    $leftKey = "L" . $lIdx;
                                    $rightKeySiswa = $jwbArr[$leftKey] ?? null;
                                    
                                    $studentRightText = 'Tidak dijawab';
                                    $studentRightImg = null;
                                    $isBenar = false;

                                    if ($rightKeySiswa !== null) {
                                        $rIdx = str_replace('R', '', $rightKeySiswa);
                                        if (isset($matches[$rIdx])) {
                                            $studentRightText = $matches[$rIdx]['right'] ?? '-';
                                            $studentRightImg = $matches[$rIdx]['gambar_right'] ?? null;
                                        }
                                        // Cek apakah benar (di sistem basic, L0 berpasangan dgn R0)
                                        if ($rIdx == $lIdx) {
                                            $isBenar = true;
                                        }
                                    }
                                @endphp
                                <div class="flex flex-col gap-1.5 relative">
                                    <div class="flex items-stretch gap-2">
                                        {{-- Kotak Kiri (Pertanyaan) --}}
                                        <div class="bg-white border border-gray-200 rounded px-2 py-2 flex-1 flex flex-col shadow-sm">
                                            @if(!empty($matchL['gambar_left'])) <img src="{{ asset('storage/' . $matchL['gambar_left']) }}" class="max-h-12 w-auto mb-1 object-contain border border-gray-100"> @endif
                                            <span class="font-medium text-gray-700">{{ $matchL['left'] ?? '-' }}</span>
                                        </div>
                                        
                                        <div class="flex flex-col items-center justify-center px-1 shrink-0">
                                            <i class="bi bi-arrow-right text-gray-400"></i>
                                        </div>
                                        
                                        {{-- Kotak Kanan (Pilihan Siswa) --}}
                                        <div class="bg-white border {{ $rightKeySiswa ? ($isBenar ? 'border-green-400 bg-green-50 ring-1 ring-green-300' : 'border-red-400 bg-red-50 ring-1 ring-red-300') : 'border-gray-200' }} rounded px-2 py-2 flex-1 flex flex-col shadow-sm relative">
                                            @if(!empty($studentRightImg)) <img src="{{ asset('storage/' . $studentRightImg) }}" class="max-h-12 w-auto mb-1 object-contain ml-auto border border-gray-100"> @endif
                                            <span class="text-right font-medium {{ !$rightKeySiswa ? 'text-gray-400 italic' : 'text-gray-700' }}">{{ $studentRightText }}</span>
                                            
                                            {{-- Icon Bulat Benar/Salah --}}
                                            @if($rightKeySiswa)
                                                <div class="absolute -top-2.5 -right-2.5">
                                                    @if($isBenar)
                                                        <i class="bi bi-check-circle-fill text-green-500 bg-white rounded-full text-lg shadow-sm"></i>
                                                    @else
                                                        <i class="bi bi-x-circle-fill text-red-500 bg-white rounded-full text-lg shadow-sm"></i>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tampilkan Kunci Jika Siswa Salah/Kosong --}}
                                    @if(!$isBenar)
                                        <div class="text-[10px] text-green-600 text-right pr-2 mt-0.5 flex items-center justify-end gap-1">
                                            <i class="bi bi-info-circle"></i> Kunci Benar: <strong>{{ $matchL['right'] ?? '-' }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection