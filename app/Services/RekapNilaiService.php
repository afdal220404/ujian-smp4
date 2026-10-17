<?php

namespace App\Services;

use App\Models\HasilUjian;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\Ujian;

/**
 * Satu-satunya tempat perhitungan Leger / Rekap Nilai wali kelas.
 *
 * Sebelumnya logika ini ditulis dua kali: sekali di
 * GuruWaliKelasController::indexRekapNilai() untuk tampilan HTML, dan sekali
 * lagi di App\Exports\RekapNilaiExport::view() untuk file Excel. Keduanya
 * menghasilkan struktur data yang berbeda sehingga ekspor Excel selalu
 * gagal (Undefined array key "detail_uts") dan angkanya pun bisa berbeda
 * dengan yang tampil di layar.
 *
 * Sekarang tampilan dan ekspor memakai service ini, jadi struktur kolom,
 * jumlah kuis/UTS/UAS dan nilai akhirnya dijamin sama.
 */
class RekapNilaiService
{
    /**
     * Susun matriks nilai satu kelas.
     *
     * @return array{kelas: Kelas, mapels: \Illuminate\Support\Collection, siswas: \Illuminate\Support\Collection, rekapNilai: array}
     */
    public function build(Kelas $kelas): array
    {
        $mapels = Mapel::where('kelas_id', $kelas->id)->orderBy('nama_mapel')->get();

        // --- Header kolom dinamis: hitung jumlah kuis/UTS/UAS per mapel ---
        // Hanya ujian induk yang dihitung (ujian susulan mengikuti induknya).
        foreach ($mapels as $mapel) {
            $ujianKuis = $this->ujianInduk($mapel->id, 'Kuis');
            $mapel->jumlah_kuis = $ujianKuis->count() > 0 ? $ujianKuis->count() : 1;
            $mapel->ujian_kuis_ids = $ujianKuis->pluck('id')->toArray();

            $ujianUts = $this->ujianInduk($mapel->id, 'UTS');
            $mapel->jumlah_uts = $ujianUts->count() > 0 ? $ujianUts->count() : 1;
            $mapel->ujian_uts_ids = $ujianUts->pluck('id')->toArray();

            $ujianUas = $this->ujianInduk($mapel->id, 'UAS');
            $mapel->jumlah_uas = $ujianUas->count() > 0 ? $ujianUas->count() : 1;
            $mapel->ujian_uas_ids = $ujianUas->pluck('id')->toArray();
        }

        $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();

        $allHasil = HasilUjian::whereIn('siswa_id', $siswas->pluck('id'))
            ->where('kelas_id', $kelas->id)
            ->with('ujian')
            ->get();

        $rekapNilai = [];

        foreach ($siswas as $siswa) {
            $hasilSiswa = $allHasil->where('siswa_id', $siswa->id);

            $totalNilaiAkhirSemuaMapel = 0;
            $jumlahMapelDiambil = 0;

            foreach ($mapels as $mapel) {
                // Nilai milik mapel ini. Relasi ujian dicek null agar data
                // hasil ujian yang ujiannya sudah terhapus tidak membuat error.
                $hasilMapel = $hasilSiswa->filter(
                    fn ($h) => $h->ujian && $h->ujian->mapel_id == $mapel->id
                );

                // A. KUIS (nilai diambil dari ujian induk atau susulannya)
                $nilaiKuisArr = $this->kumpulkanNilai($hasilMapel, $mapel->ujian_kuis_ids);
                $nilaiKuisValid = array_filter($nilaiKuisArr, fn ($v) => is_numeric($v));
                $rataKuis = count($nilaiKuisValid) > 0
                    ? array_sum($nilaiKuisValid) / count($nilaiKuisValid)
                    : null;

                // B. UTS
                $nilaiUtsArr = $this->kumpulkanNilai($hasilMapel, $mapel->ujian_uts_ids);
                $nilaiUtsValid = array_filter($nilaiUtsArr, fn ($v) => is_numeric($v));
                $utsVal = count($nilaiUtsValid) > 0
                    ? array_sum($nilaiUtsValid) / count($nilaiUtsValid)
                    : null;

                // C. UAS
                $nilaiUasArr = $this->kumpulkanNilai($hasilMapel, $mapel->ujian_uas_ids);
                $nilaiUasValid = array_filter($nilaiUasArr, fn ($v) => is_numeric($v));
                $uasVal = count($nilaiUasValid) > 0
                    ? array_sum($nilaiUasValid) / count($nilaiUasValid)
                    : null;

                // D. Nilai akhir mapel: rata-rata dari komponen yang sudah ada
                $komponen = array_filter([$rataKuis, $utsVal, $uasVal], fn ($v) => $v !== null);
                $akhirMapel = count($komponen) > 0 ? array_sum($komponen) / count($komponen) : null;

                if ($akhirMapel !== null) {
                    $totalNilaiAkhirSemuaMapel += $akhirMapel;
                    $jumlahMapelDiambil++;
                }

                $rekapNilai[$siswa->id]['mapel'][$mapel->id] = [
                    'detail_kuis' => $nilaiKuisArr,
                    'detail_uts'  => $nilaiUtsArr,
                    'detail_uas'  => $nilaiUasArr,
                ];
            }

            $rataRataSiswa = $jumlahMapelDiambil > 0
                ? ($totalNilaiAkhirSemuaMapel / $jumlahMapelDiambil)
                : 0;

            $rekapNilai[$siswa->id]['rata_akhir'] = number_format($rataRataSiswa, 1);
            $rekapNilai[$siswa->id]['grade_akhir'] = $rataRataSiswa;
        }

        return compact('kelas', 'mapels', 'siswas', 'rekapNilai');
    }

    /**
     * Daftar ujian induk (bukan susulan) untuk satu mapel & satu jenis ujian.
     */
    private function ujianInduk(int $mapelId, string $jenis)
    {
        return Ujian::where('mapel_id', $mapelId)
            ->where('jenis_ujian', $jenis)
            ->where('is_susulan', false)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Ambil nilai siswa untuk daftar ujian; "-" bila belum dikerjakan.
     * Bila mapel belum punya ujian, kembalikan satu placeholder "-" supaya
     * jumlah kolom pada tabel tetap sama dengan jumlah_kuis/uts/uas.
     */
    private function kumpulkanNilai($hasilMapel, array $ujianIds): array
    {
        if (empty($ujianIds)) {
            return ['-'];
        }

        $nilai = [];
        foreach ($ujianIds as $ujianId) {
            $score = $hasilMapel
                ->filter(fn ($h) => $h->ujian_id == $ujianId || $h->ujian->ujian_induk_id == $ujianId)
                ->first();

            $nilai[] = $score ? $score->nilai : '-';
        }

        return $nilai;
    }
}
