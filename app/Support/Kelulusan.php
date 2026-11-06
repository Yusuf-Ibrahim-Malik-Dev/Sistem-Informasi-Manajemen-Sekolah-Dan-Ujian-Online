<?php

namespace App\Support;

use App\Models\Konfigurasi;
use App\Models\Modul;
use App\Models\Peserta;
use App\Models\Raport;
use App\Models\UserGroup;

class Kelulusan
{
    public static function semua(Peserta $peserta): array
    {
        $grup = $peserta->grup;
        $hasil = [];

        foreach (UserGroup::jenjangOptions() as $jenjang => $label) {
            $tingkatAkhir = count(UserGroup::tingkatOptions($jenjang));

            $punyaRaport = Raport::where('raport_user_id', $peserta->user_id)
                ->where('raport_jenjang', $jenjang)
                ->where('raport_tingkat', $tingkatAkhir)
                ->exists();

            $sedangDiKelasAkhir = $grup
                && $grup->grup_jenjang === $jenjang
                && (int) $grup->grup_tingkat === $tingkatAkhir;

            if ($punyaRaport || $sedangDiKelasAkhir) {
                $hasil[] = self::untuk($peserta, $jenjang.'-'.$tingkatAkhir);
            }
        }

        return $hasil;
    }

    public static function untuk(Peserta $peserta, ?string $kode = null): array
    {
        [$jenjang, $tingkat] = self::resolveJenjangTingkat($peserta, $kode);

        $rapors = Raport::with('guru')
            ->where('raport_user_id', $peserta->user_id)
            ->get();

        return self::susun($peserta, $jenjang, $tingkat, self::modulAktif(), $rapors);
    }

    public static function ringkasanKoleksi(iterable $pesertas): array
    {
        $pesertas = collect($pesertas);
        $moduls = self::modulAktif();

        $semuaRaport = Raport::with('guru')
            ->whereIn('raport_user_id', $pesertas->pluck('user_id'))
            ->get()
            ->groupBy('raport_user_id');

        $hasil = [];

        foreach ($pesertas as $peserta) {
            $grup = $peserta->grup;

            if (! $grup) {
                continue;
            }

            $hasil[$peserta->user_id] = self::susun(
                $peserta,
                $grup->grup_jenjang,
                (int) $grup->grup_tingkat,
                $moduls,
                $semuaRaport->get($peserta->user_id, collect())
            );
        }

        return $hasil;
    }

    private static function resolveJenjangTingkat(Peserta $peserta, ?string $kode): array
    {
        $grup = $peserta->grup;
        $jenjang = $grup?->grup_jenjang;
        $tingkat = (int) ($grup?->grup_tingkat ?? 0);

        if ($kode && preg_match('/^([A-Z]+)-(\d+)$/', $kode, $match)) {
            $jenjangKode = $match[1];
            $tingkatKode = (int) $match[2];
            $maksimal = count(UserGroup::tingkatOptions($jenjangKode));

            if (array_key_exists($jenjangKode, UserGroup::jenjangOptions())
                && $tingkatKode >= 1
                && $tingkatKode <= $maksimal) {
                $jenjang = $jenjangKode;
                $tingkat = $tingkatKode;
            }
        }

        return [$jenjang, $tingkat];
    }

    private static function modulAktif()
    {
        return Modul::where('modul_aktif', true)
            ->orderBy('modul_nama')
            ->get();
    }

    private static function susun(Peserta $peserta, ?string $jenjang, int $tingkat, $moduls, iterable $rapors): array
    {
        $kelasAkhir = $jenjang
            && array_key_exists($jenjang, UserGroup::jenjangOptions())
            && $tingkat === count(UserGroup::tingkatOptions($jenjang));

        $modulIds = $moduls->pluck('modul_id');

        $raporsKelas = collect($rapors)
            ->filter(fn ($raport) => $raport->raport_jenjang === $jenjang
                && (int) $raport->raport_tingkat === $tingkat
                && $modulIds->contains($raport->raport_modul_id))
            ->keyBy('raport_modul_id');

        $nilai = $raporsKelas->pluck('raport_nilai');
        $rataRata = $nilai->isNotEmpty() ? round($nilai->avg(), 1) : null;
        $kkm = (float) Konfigurasi::get('cbt_nilai_kelulusan', 70);
        $lengkap = $moduls->isNotEmpty() && $raporsKelas->count() >= $moduls->count();
        $lulus = $kelasAkhir && $lengkap && $rataRata !== null && $rataRata >= $kkm;
        $labelJenjang = UserGroup::jenjangOptions()[$jenjang] ?? $jenjang;

        return [
            'kode' => $jenjang.'-'.$tingkat,
            'kelas_akhir' => $kelasAkhir,
            'jenjang' => $labelJenjang,
            'tingkat' => $tingkat,
            'kelas' => $labelJenjang.' Kelas '.$tingkat,
            'moduls' => $moduls,
            'rapors' => $raporsKelas,
            'rata_rata' => $rataRata,
            'kkm' => $kkm,
            'lengkap' => $lengkap,
            'lulus' => $lulus,
            'predikat' => self::predikat($rataRata),
            'nomor' => 'SKL/'.now()->format('Y').'/'.str_pad((string) $peserta->user_id, 4, '0', STR_PAD_LEFT).'/'.$jenjang.$tingkat,
            'tanggal' => now(),
        ];
    }

    private static function predikat(?float $nilai): string
    {
        if ($nilai === null) {
            return '-';
        }

        return match (true) {
            $nilai >= 90 => 'A',
            $nilai >= 80 => 'B',
            $nilai >= 70 => 'C',
            default => 'D',
        };
    }
}
