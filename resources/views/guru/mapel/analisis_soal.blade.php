@extends('layouts.app')

@section('title', 'Analisis Soal - ' . $ujian->nama_ujian)

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
    <a href="{{ route('guru.mapel.siswa', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.siswa') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i> <span>Daftar Nilai Siswa</span>
    </a>
    <a href="{{ route('guru.mapel.bank_soal.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.bank_soal.*') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.mapel.arsip_soal_siswa.index', $mapel->id) }}" class="nav-link {{ Route::is('guru.mapel.arsip_soal_siswa.*') ? 'active' : '' }}">
        <i class="bi bi-folder2-open"></i> <span>Arsip Soal Siswa</span>
    </a>
@endsection

@section('content')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
        <div>
            <div>
                <div class="flex items-center gap-2 text-gray-400 text-xs mb-1">
                    <a href="{{ route('guru.index') }}" class="hover:text-blue-600 transition-colors"><i class="bi bi-house-door"></i> Home</a>
                    <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                    <span class="text-gray-500">Kelas {{ $kelas->kelas ?? 'VII' }}</span>
                    <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                    <span class="text-blue-600 font-bold">{{ $mapel->nama_mapel }}</span>
                    <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                    <a href="{{ route('guru.mapel.ujian.detail', $ujian->id) }}" class="hover:text-blue-600 transition-colors text-blue-600 font-bold">Detail Ujian</a>
                    <i class="bi bi-chevron-right text-[8px] opacity-50"></i>
                    <span class="text-blue-600 font-bold">Analisis Soal</span>
                </div>
            </div>
            <h1 class="text-3xl font-[Poppins-Bold] text-darkblue">Analisis Soal: {{ $ujian->nama_ujian }}</h1>
            <p class="text-gray-500 mt-1">
                Laporan untuk kelas <span class="font-bold text-darkblue">{{ $kelas->kelas ?? 'VII' }}</span>.
            </p>
        </div>

        <a href="{{ route('guru.mapel.ujian.detail', $ujian->id) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- KONTEN ANALISIS SOAL --}}
    <div class="space-y-6">
        @foreach($analisis as $index => $item)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        
                        {{-- Bagian Soal --}}
                        <div class="w-full md:w-1/2 flex flex-col border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0 md:pr-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 font-bold flex items-center justify-center border border-blue-100">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider leading-none mb-1">Pertanyaan</span>
                                        <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest leading-none bg-blue-50 px-2 py-1 rounded">
                                            @switch($item['soal']->tipe)
                                                @case('pilihan_ganda') Pilihan Ganda @break
                                                @case('jawaban_ganda') Jawaban Ganda @break
                                                @case('benar_salah') Benar / Salah @break
                                                @case('menjodohkan') Menjodohkan @break
                                                @default {{ str_replace('_', ' ', $item['soal']->tipe) }}
                                            @endswitch
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if(isset($item['soal']->gambar) && !empty($item['soal']->gambar))
                                <div class="mb-4 w-full bg-gray-50 rounded-lg border border-gray-100 p-2 flex justify-center items-center">
                                    <img src="{{ asset('storage/' . $item['soal']->gambar) }}" alt="Soal" class="max-h-40 max-w-full h-auto object-contain rounded shadow-sm hover:scale-105 transition-transform cursor-pointer" onclick="window.open(this.src, '_blank')">
                                </div>
                            @endif

                            <div class="text-gray-800 text-sm font-medium leading-relaxed mb-4">
                                {!! nl2br(e($item['soal']->pertanyaan)) !!}
                            </div>
                            
                            {{-- Tampilkan Semua Opsi & Highlight Kunci --}}
                            {{-- Tampilkan Semua Opsi & Highlight Kunci --}}
                            <div class="grid grid-cols-1 gap-2 mt-auto">
                                
                               {{-- 1. PILIHAN GANDA --}}
                                @if($item['soal']->tipe == 'pilihan_ganda')
                                    @php
                                        // 1. Ambil Kunci Jawaban. Jika kosong/dihidden oleh Model, PAKSA ambil langsung dari Database!
                                        $kunciRaw = $item['soal']->kunci_jawaban ?? null;
                                        if ($kunciRaw === null || $kunciRaw === '') {
                                            // PERBAIKAN: Gunakan tabel bank_soal_items dan bank_soal_id
                                            $kunciRaw = \Illuminate\Support\Facades\DB::table('bank_soal_items')->where('id', $item['soal']->bank_soal_id)->value('kunci_jawaban') ?? '';
                                        }
                                        
                                        // 2. Bersihkan format (Antisipasi jika di database tersimpan "opsi_b", maka ubah jadi "B")
                                        $kunciRaw = str_ireplace('opsi_', '', $kunciRaw);
                                        
                                        // 3. Pecah jadi array dan jadikan huruf kapital
                                        $kunciList = array_filter(array_map('trim', array_map('strtoupper', explode(',', $kunciRaw))));
                                    @endphp
                                    @foreach(['A', 'B', 'C', 'D'] as $opt)
                                        @php
                                            $textKey = 'opsi_' . strtolower($opt);
                                            $imgKey  = 'gambar_' . strtolower($opt);
                                            $textOpsi = $item['soal']->$textKey;
                                            $imgOpsi  = $item['soal']->$imgKey;
                                            
                                            // Pencocokan Kunci
                                            $isKunci = in_array(strtoupper($opt), $kunciList);
                                            
                                            $wrapperClass = $isKunci ? "bg-green-50 border-green-200 text-green-900 shadow-sm ring-1 ring-green-100" : "bg-gray-50 border-gray-100 text-gray-500";
                                        @endphp
                                        <div class="flex items-center gap-3 p-2 rounded-lg text-xs transition-all border {{ $wrapperClass }}">
                                            <span class="w-6 h-6 rounded flex items-center justify-center font-bold flex-shrink-0 {{ $isKunci ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-500' }}">
                                                @if($isKunci) <i class="bi bi-check2-square text-sm"></i> @else {{ $opt }} @endif
                                            </span>
                                            <div class="flex flex-col leading-snug">
                                                @if(!empty($imgOpsi)) 
                                                    <img src="{{ asset('storage/' . $imgOpsi) }}" alt="Opsi {{ $opt }}" class="max-h-16 rounded object-contain mb-1 bg-white border border-gray-100"> 
                                                @endif
                                                @if(!empty($textOpsi)) <span>{{ $textOpsi }}</span> @else @if(empty($imgOpsi)) <span>-</span> @endif @endif
                                            </div>
                                            @if($isKunci)
                                                <i class="bi bi-check-circle-fill text-green-600 text-sm ml-auto"></i>
                                            @endif
                                        </div>
                                    @endforeach

                                {{-- 2. JAWABAN GANDA (PILIH BANYAK) --}}
                                @elseif($item['soal']->tipe == 'jawaban_ganda')
                                    @php
                                        $dataSoal = is_string($item['soal']->data_soal) ? json_decode($item['soal']->data_soal, true) : $item['soal']->data_soal;
                                        $options = $dataSoal['options'] ?? [];
                                        
                                        // 1. Ambil Kunci Jawaban. Jika kosong/dihidden, PAKSA ambil langsung dari Database!
                                        $kunciRaw = $item['soal']->kunci_jawaban ?? null;
                                        if ($kunciRaw === null || $kunciRaw === '') {
                                            // PERBAIKAN: Gunakan tabel bank_soal_items dan bank_soal_id
                                            $kunciRaw = \Illuminate\Support\Facades\DB::table('bank_soal_items')->where('id', $item['soal']->bank_soal_id)->value('kunci_jawaban') ?? '';
                                        }
                                        
                                        // 2. Bersihkan format (Antisipasi format "opsi_a, opsi_c")
                                        $kunciRaw = str_ireplace('opsi_', '', $kunciRaw);
                                        
                                        $kunciList = [];
                                        if (!empty($kunciRaw)) {
                                            $kunciArray = explode(',', $kunciRaw);
                                            $kunciList = array_filter(array_map('trim', array_map('strtoupper', $kunciArray)));
                                        }

                                        $abjad = range('A', 'Z');

                                        // Fallback untuk format lama
                                        if (empty($options)) {
                                            foreach (['A', 'B', 'C', 'D'] as $huruf) {
                                                $propText = 'opsi_' . strtolower($huruf);
                                                $propImg = 'gambar_' . strtolower($huruf);
                                                if (!empty($item['soal']->$propText) || !empty($item['soal']->$propImg)) {
                                                    $options[] = [
                                                        'id' => $huruf,
                                                        'text' => $item['soal']->$propText ?? '',
                                                        'gambar' => $item['soal']->$propImg ?? '',
                                                    ];
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @foreach($options as $idx => $opt)
                                        @php
                                            $huruf = $abjad[$idx] ?? '';
                                            $optId = isset($opt['id']) ? strtoupper(trim((string)$opt['id'])) : '';
                                            
                                            // Pencocokan
                                            $isKunci = in_array($huruf, $kunciList) || 
                                                       ($optId !== '' && in_array($optId, $kunciList)) || 
                                                       (!empty($opt['correct']) && (bool)$opt['correct'] === true);
                                                       
                                            $wrapperClass = $isKunci ? "bg-green-50 border-green-200 text-green-900 shadow-sm ring-1 ring-green-100" : "bg-gray-50 border-gray-100 text-gray-500";
                                        @endphp
                                        <div class="flex items-center gap-3 p-2 rounded-lg text-xs transition-all border {{ $wrapperClass }}">
                                            <span class="w-6 h-6 rounded flex items-center justify-center font-bold flex-shrink-0 {{ $isKunci ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-500' }}">
                                                @if($isKunci)
                                                    <i class="bi bi-check2-square text-sm"></i>
                                                @else
                                                    {{ $optId !== '' ? $optId : $huruf }}
                                                @endif
                                            </span>
                                            <div class="flex flex-col leading-snug">
                                                @if(!empty($opt['gambar'])) 
                                                    <img src="{{ asset('storage/' . $opt['gambar']) }}" alt="Opsi" class="max-h-16 rounded object-contain mb-1 bg-white border border-gray-100"> 
                                                @endif
                                                @if(!empty($opt['text'])) 
                                                    <span>{{ $opt['text'] }}</span> 
                                                @else 
                                                    @if(empty($opt['gambar'])) <span>-</span> @endif 
                                                @endif
                                            </div>
                                            @if($isKunci)
                                                <i class="bi bi-check-circle-fill text-green-600 text-sm ml-auto"></i>
                                            @endif
                                        </div>
                                    @endforeach

                                {{-- 3. BENAR / SALAH (COMPLEX) --}}
                                @elseif($item['soal']->tipe == 'benar_salah')
                                    @php
                                        $dataSoal = is_string($item['soal']->data_soal) ? json_decode($item['soal']->data_soal, true) : $item['soal']->data_soal;
                                        $pernyataan = $dataSoal['pernyataan'] ?? [];
                                    @endphp
                                    @if(!empty($pernyataan))
                                        <div class="bg-gray-50 rounded-lg p-3 text-xs border border-gray-200">
                                            <div class="font-bold border-b border-gray-200 pb-2 mb-2 text-gray-600 flex items-center gap-2">
                                                <i class="bi bi-list-check"></i> Daftar Pernyataan & Kunci
                                            </div>
                                            <div class="grid grid-cols-1 gap-2">
                                                @foreach($pernyataan as $stmt)
                                                    @php
                                                        $isBenar = strtoupper($stmt['correct'] ?? '') === 'TRUE';
                                                    @endphp
                                                    <div class="flex items-center justify-between gap-3 p-2 bg-white border border-gray-100 rounded shadow-sm">
                                                        <div class="flex flex-col flex-1 leading-snug">
                                                            @if(!empty($stmt['gambar'])) 
                                                                <img src="{{ asset('storage/' . $stmt['gambar']) }}" class="max-h-12 w-auto rounded object-contain mb-1"> 
                                                            @endif
                                                            <span class="text-gray-700">{{ $stmt['text'] ?? '-' }}</span>
                                                        </div>
                                                        <div class="shrink-0">
                                                            @if($isBenar)
                                                                <span class="bg-green-100 text-green-700 border border-green-200 px-2.5 py-1 rounded text-[10px] font-black">BENAR</span>
                                                            @else
                                                                <span class="bg-red-100 text-red-700 border border-red-200 px-2.5 py-1 rounded text-[10px] font-black">SALAH</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                {{-- 4. MENJODOHKAN --}}
                                @elseif($item['soal']->tipe == 'menjodohkan')
                                    @php
                                        $dataSoal = is_string($item['soal']->data_soal) ? json_decode($item['soal']->data_soal, true) : $item['soal']->data_soal;
                                        $matches = $dataSoal['matches'] ?? [];
                                    @endphp
                                    @if(!empty($matches))
                                        <div class="bg-green-50 border border-green-200 text-green-900 shadow-sm ring-1 ring-green-100 rounded-lg text-xs p-3">
                                            <div class="font-bold border-b border-green-200 pb-2 mb-3 text-green-800 flex items-center gap-1.5">
                                                <i class="bi bi-check-circle-fill"></i> Pasangan Benar (Kunci)
                                            </div>
                                            <div class="grid grid-cols-1 gap-2">
                                                @foreach($matches as $match)
                                                    <div class="flex items-center gap-2">
                                                        {{-- Kotak Kiri --}}
                                                        <div class="bg-white border border-green-200 rounded px-2 py-1.5 flex-1 flex flex-col shadow-sm">
                                                            @if(!empty($match['gambar_left'])) <img src="{{ asset('storage/' . $match['gambar_left']) }}" class="max-h-10 mb-1 object-contain"> @endif
                                                            @if(!empty($match['left'])) <span>{{ $match['left'] }}</span> @endif
                                                        </div>
                                                        
                                                        <i class="bi bi-link text-green-600 font-bold text-lg px-1"></i>
                                                        
                                                        {{-- Kotak Kanan --}}
                                                        <div class="bg-white border border-green-200 rounded px-2 py-1.5 flex-1 flex flex-col shadow-sm">
                                                            @if(!empty($match['gambar_right'])) <img src="{{ asset('storage/' . $match['gambar_right']) }}" class="max-h-10 mb-1 object-contain ml-auto"> @endif
                                                            @if(!empty($match['right'])) <span class="text-right">{{ $match['right'] }}</span> @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif

                            </div>
                        </div>

                        {{-- Bagian Statistik --}}
                        <div class="w-full md:w-1/2 flex flex-col">
                            
                            {{-- Chart / Angka --}}
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-green-50 rounded-xl p-4 border border-green-100 flex flex-col items-center justify-center text-center">
                                    <div class="text-3xl font-[Poppins-Bold] text-green-600 mb-1">{{ $item['jumlah_benar'] }}</div>
                                    <div class="text-[11px] font-bold text-green-800 uppercase tracking-widest text-center">Siswa<br>Benar</div>
                                </div>
                                <div class="bg-red-50 rounded-xl p-4 border border-red-100 flex flex-col items-center justify-center text-center">
                                    <div class="text-3xl font-[Poppins-Bold] text-red-600 mb-1">{{ $item['jumlah_salah'] }}</div>
                                    <div class="text-[11px] font-bold text-red-800 uppercase tracking-widest text-center">Siswa<br>Salah</div>
                                </div>
                            </div>

                            {{-- Daftar Siswa --}}
                            <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Daftar Siswa Benar --}}
                                <div>
                                    <div class="text-[11px] font-bold text-green-600 mb-2 flex items-center gap-1.5 border-b border-green-100 pb-1">
                                        <i class="bi bi-check-circle-fill"></i> Siswa Menjawab Benar
                                    </div>
                                    <div class="flex flex-col gap-1.5 max-h-[150px] overflow-y-auto pr-2 custom-scrollbar">
                                        @forelse($item['siswa_benar'] as $siswa)
                                            <div class="flex items-center gap-2 px-2 py-1 bg-green-100/30 border border-green-100 rounded">
                                                <div class="font-medium text-green-800 text-[11px]">{{ $siswa->nama_lengkap }}</div>
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Tidak ada siswa</span>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Daftar Siswa Salah --}}
                                <div>
                                    <div class="text-[11px] font-bold text-red-600 mb-2 flex items-center gap-1.5 border-b border-red-100 pb-1">
                                        <i class="bi bi-x-circle-fill"></i> Siswa Menjawab Salah
                                    </div>
                                    <div class="flex flex-col gap-1.5 max-h-[150px] overflow-y-auto pr-2 custom-scrollbar">
                                        @forelse($item['siswa_salah'] as $siswa)
                                            <div class="flex items-center gap-2 px-2 py-1 bg-red-100/30 border border-red-100 rounded">
                                                <div class="font-medium text-red-800 text-[11px]">{{ $siswa->nama_lengkap }}</div>
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Tidak ada siswa</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(241, 241, 241, 0.5); 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
@endsection
