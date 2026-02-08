<table>
    <thead>
        {{-- BARIS 1: NAMA MAPEL --}}
        <tr>
            <th rowspan="2" style="font-weight:bold; vertical-align:middle; text-align:center; border:1px solid #000;">NO</th>
            <th rowspan="2" style="font-weight:bold; vertical-align:middle; text-align:center; border:1px solid #000; width:30px;">NAMA SISWA</th>
            
            @foreach($mapels as $mapel)
                {{-- Colspan --}}
                <th colspan="{{ $mapel->jumlah_kuis + 2 }}" 
                    style="font-weight:bold; text-align:center; border:1px solid #000; background-color:#00415a; color:#ffffff;">
                    {{ $mapel->nama_mapel }}
                </th>
            @endforeach

            <th rowspan="2" style="font-weight:bold; vertical-align:middle; text-align:center; border:1px solid #000; background-color:#10B981;">RATA AKHIR</th>
        </tr>

        {{-- BARIS 2: DETAIL KOLOM --}}
        <tr>
            @foreach($mapels as $mapel)
                @for($i = 1; $i <= $mapel->jumlah_kuis; $i++)
                    <th style="text-align:center; border:1px solid #000; background-color:#f3f4f6;">K{{ $i }}</th>
                @endfor
                <th style="text-align:center; border:1px solid #000; background-color:#e0e7ff;">U</th>
                <th style="text-align:center; border:1px solid #000; background-color:#ffedd5;">A</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach ($siswas as $index => $siswa)
            <tr>
                <td style="text-align:center; border:1px solid #000;">{{ $index + 1 }}</td>
                <td style="border:1px solid #000;">{{ $siswa->nama_lengkap }}</td>

                @foreach($mapels as $mapel)
                    @php
                        $data = $rekapNilai[$siswa->id]['mapel'][$mapel->id] ?? [
                            'detail_kuis' => array_fill(0, $mapel->jumlah_kuis, '-'),
                            'uts' => '-',
                            'uas' => '-'
                        ];
                    @endphp
                    
                    {{-- Kuis --}}
                    @foreach($data['detail_kuis'] as $nilaiKuis)
                        <td style="text-align:center; border:1px solid #000;">{{ $nilaiKuis }}</td>
                    @endforeach

                    {{-- Padding Kuis Kosong --}}
                    @for($k = count($data['detail_kuis']); $k < $mapel->jumlah_kuis; $k++)
                        <td style="text-align:center; border:1px solid #000;">-</td>
                    @endfor

                    {{-- UTS & UAS --}}
                    <td style="text-align:center; border:1px solid #000; font-weight:bold;">{{ $data['uts'] }}</td>
                    <td style="text-align:center; border:1px solid #000; font-weight:bold;">{{ $data['uas'] }}</td>
                @endforeach

                {{-- Rata Akhir --}}
                <td style="text-align:center; border:1px solid #000; font-weight:bold; background-color:#ecfdf5;">
                    {{ $rekapNilai[$siswa->id]['rata_akhir'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>