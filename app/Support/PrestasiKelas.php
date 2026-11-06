<?php

namespace App\Support;

use App\Models\Raport;
use App\Models\UserGroup;

class PrestasiKelas
{
    public static function perKelas(?string $jenjang = null, ?array $tingkat = null): array
    {
        $rapors = Raport::query()
            ->when($jenjang !== null, fn ($query) => $query->where('raport_jenjang', $jenjang))
            ->when($tingkat !== null, fn ($query) => $query->whereIn('raport_tingkat', $tingkat))
            ->get();

        $statistik = [];

        foreach ($rapors as $raport) {
            $raportJenjang = $raport->raport_jenjang;
            $raportTingkat = (int) $raport->raport_tingkat;

            if (! $raportJenjang || ! $raportTingkat) {
                continue;
            }

            $kunci = $raportJenjang . '-' . $raportTingkat;
            $statistik[$kunci] ??= ['total' => 0.0, 'jumlah' => 0];
            $statistik[$kunci]['total'] += (float) $raport->raport_nilai;
            $statistik[$kunci]['jumlah']++;
        }

        $hasil = [];
        $opsiJenjang = UserGroup::jenjangOptions();

        if ($jenjang !== null && isset($opsiJenjang[$jenjang])) {
            $opsiJenjang = [$jenjang => $opsiJenjang[$jenjang]];
        }

        foreach ($opsiJenjang as $kode => $label) {
            $labels = [];
            $nilai = [];
            $jumlah = [];
            $opsiTingkat = UserGroup::tingkatOptions($kode);

            if ($tingkat !== null) {
                $opsiTingkat = array_intersect_key($opsiTingkat, array_flip($tingkat));
            }

            if ($opsiTingkat === []) {
                $opsiTingkat = UserGroup::tingkatOptions($kode);
            }

            foreach ($opsiTingkat as $tingkatKode => $labelKelas) {
                $data = $statistik[$kode . '-' . $tingkatKode] ?? null;
                $labels[] = $labelKelas;
                $nilai[] = $data && $data['jumlah'] > 0
                    ? round($data['total'] / $data['jumlah'], 1)
                    : 0;
                $jumlah[] = $data['jumlah'] ?? 0;
            }

            $hasil[$kode] = [
                'label' => $label,
                'labels' => $labels,
                'nilai' => $nilai,
                'jumlah' => $jumlah,
            ];
        }

        return $hasil;
    }
}
