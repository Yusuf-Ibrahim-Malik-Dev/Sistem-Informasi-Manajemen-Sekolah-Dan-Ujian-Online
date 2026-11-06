<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use App\Models\Raport;
use App\Models\UserGroup;

class RaportController extends Controller
{
    public function index()
    {
        $peserta = auth('peserta')->user();

        $moduls = Modul::where('modul_aktif', true)
            ->orderBy('modul_nama')
            ->get();

        $semuaRaport = Raport::with(['guru', 'modul'])
            ->where('raport_user_id', $peserta->user_id)
            ->get();

        $kelasSekarang = $peserta->grup
            ? $peserta->grup->grup_jenjang . '-' . $peserta->grup->grup_tingkat
            : null;

        $levels = collect();

        foreach (UserGroup::jenjangOptions() as $jenjang => $labelJenjang) {
            foreach (UserGroup::tingkatOptions($jenjang) as $tingkat => $labelKelas) {
                $key = $jenjang . '-' . $tingkat;
                $rapors = $semuaRaport
                    ->filter(fn ($raport) => $raport->raport_jenjang === $jenjang
                        && (int) $raport->raport_tingkat === $tingkat)
                    ->keyBy('raport_modul_id');

                if ($rapors->isEmpty() && $key !== $kelasSekarang) {
                    continue;
                }

                $nilai = $rapors->pluck('raport_nilai');

                $levels->push([
                    'key' => $key,
                    'jenjang' => $jenjang,
                    'tingkat' => $tingkat,
                    'label' => $labelJenjang . ' ' . $labelKelas,
                    'rapors' => $rapors,
                    'rata_rata' => $nilai->isNotEmpty() ? round($nilai->avg(), 1) : null,
                    'jumlah' => $rapors->count(),
                    'aktif' => $key === $kelasSekarang,
                ]);
            }
        }

        $diminta = (string) request('kelas', '');
        $aktif = $levels->firstWhere('key', $diminta)
            ?: $levels->firstWhere('key', $kelasSekarang)
            ?: $levels->first();

        return view('peserta.raport.index', [
            'peserta' => $peserta,
            'moduls' => $moduls,
            'levels' => $levels,
            'aktif' => $aktif,
            'rapors' => $aktif['rapors'] ?? collect(),
            'rataRata' => $aktif['rata_rata'] ?? null,
        ]);
    }
}
