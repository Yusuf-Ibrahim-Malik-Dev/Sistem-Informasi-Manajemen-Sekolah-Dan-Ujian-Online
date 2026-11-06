<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\TesUser;
use App\Support\PrestasiKelas;

class PrestasiController extends Controller
{
    public function index()
    {
        $peserta = auth('peserta')->user();

        $riwayat = TesUser::with('tes')
            ->where('tesuser_user_id', $peserta->user_id)
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->orderByDesc('tesuser_creation_time')
            ->get();

        $nilai = $riwayat->pluck('total_nilai')->map(fn ($item) => (float) $item);

        $jenjang = $peserta->jenjangSekolah();
        $tingkat = null;

        if ($jenjang !== null) {
            $tingkat = $peserta->rapors()
                ->where('raport_jenjang', $jenjang)
                ->pluck('raport_tingkat')
                ->map(fn ($item) => (int) $item)
                ->filter()
                ->unique();

            if ($peserta->grup && strtoupper((string) $peserta->grup->grup_jenjang) === $jenjang) {
                $tingkat->push((int) $peserta->grup->grup_tingkat);
            }

            $tingkat = $tingkat->unique()->sort()->values()->all() ?: null;
        }

        return view('peserta.prestasi.index', [
            'peserta' => $peserta,
            'riwayat' => $riwayat,
            'chartPrestasi' => PrestasiKelas::perKelas($jenjang, $tingkat),
            'statistik' => [
                'ujian' => $riwayat->count(),
                'rata_rata' => $nilai->isNotEmpty() ? round($nilai->avg(), 1) : null,
                'tertinggi' => $nilai->isNotEmpty() ? round($nilai->max(), 1) : null,
            ],
        ]);
    }
}
