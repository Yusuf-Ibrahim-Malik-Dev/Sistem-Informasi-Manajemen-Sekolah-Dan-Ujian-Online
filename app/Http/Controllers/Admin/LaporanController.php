<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesUser;
use App\Models\UserGroup;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function rekap(Request $request)
    {
        $tesList = Tes::orderBy('tes_nama')->get();
        $grups = UserGroup::orderBy('grup_nama')->get();

        $attempts = TesUser::with('tes')
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->when($request->filled('tes'), fn ($q) => $q->where('tesuser_tes_id', $request->tes))
            ->when($request->filled('grup'), function ($q) use ($request) {
                $q->whereHas('peserta', fn ($sub) => $sub->where('user_grup_id', $request->grup));
            })
            ->get();

        $rekap = $attempts->groupBy('tesuser_tes_id')->map(function ($items) {
            $nilai = $items->pluck('total_nilai')->map(fn ($v) => (float) $v);

            return [
                'tes' => $items->first()->tes,
                'peserta' => $items->count(),
                'rata' => round($nilai->avg() ?? 0, 2),
                'maks' => round($nilai->max() ?? 0, 2),
                'min' => round($nilai->min() ?? 0, 2),
                'total' => round($nilai->sum(), 2),
            ];
        })->sortByDesc('rata')->values();

        return view('admin.laporan.rekap', compact('rekap', 'tesList', 'grups'));
    }

    public function analisis(Request $request)
    {
        $tesList = Tes::orderBy('tes_nama')->get();
        $tes = $request->filled('tes')
            ? $tesList->firstWhere('tes_id', (int) $request->tes)
            : $tesList->first();

        $analisis = collect();
        $ringkasan = null;

        if ($tes) {
            $attempts = TesUser::with('soals')
                ->where('tesuser_tes_id', $tes->tes_id)
                ->where('tesuser_status', TesUser::STATUS_SELESAI)
                ->get();

            $attemptIds = $attempts->pluck('tesuser_id');

            $skor = $attempts->map(fn ($attempt) => [
                'id' => $attempt->tesuser_id,
                'nilai' => (float) $attempt->soals->sum('tessoal_nilai'),
            ])->sortByDesc('nilai')->values();

            $n = max(1, (int) ceil($skor->count() * 0.27));
            $upper = $skor->take($n)->pluck('id')->all();
            $lower = $skor->reverse()->take($n)->pluck('id')->all();

            $items = TesSoal::with(['soal', 'pilihan.jawaban'])
                ->whereIn('tessoal_tesuser_id', $attemptIds)
                ->get()
                ->groupBy('tessoal_soal_id');

            $analisis = $items->map(function ($group) use ($upper, $lower) {
                $soal = $group->first()->soal;
                $dijawab = $group->filter(fn ($item) => $item->tessoal_change_time !== null)->count();

                $benar = $group->filter(fn ($item) => $this->isCorrect($item, $soal))->count();

                $upperBenar = $group->whereIn('tessoal_tesuser_id', $upper)
                    ->filter(fn ($item) => $this->isCorrect($item, $soal))->count();
                $lowerBenar = $group->whereIn('tessoal_tesuser_id', $lower)
                    ->filter(fn ($item) => $this->isCorrect($item, $soal))->count();

                $upperTotal = max(1, count($upper));
                $lowerTotal = max(1, count($lower));

                $tingkat = $dijawab > 0 ? $benar / $dijawab : 0;
                $daya = ($upperBenar / $upperTotal) - ($lowerBenar / $lowerTotal);

                return [
                    'soal' => $soal,
                    'peserta' => $group->count(),
                    'dijawab' => $dijawab,
                    'benar' => $benar,
                    'salah' => max(0, $dijawab - $benar),
                    'tingkat' => round($tingkat, 2),
                    'kategori_tingkat' => $this->kategoriTingkat($tingkat),
                    'daya' => round($daya, 2),
                    'kategori_daya' => $this->kategoriDaya($daya),
                ];
            })->sortBy('soal.soal_id')->values();

            $ringkasan = [
                'peserta' => $attempts->count(),
                'soal' => $analisis->count(),
                'rata' => round($skor->avg('nilai') ?? 0, 2),
                'tertinggi' => round($skor->max('nilai') ?? 0, 2),
                'terendah' => round($skor->min('nilai') ?? 0, 2),
            ];
        }

        return view('admin.laporan.analisis', compact('tesList', 'tes', 'analisis', 'ringkasan'));
    }

    private function isCorrect(TesSoal $item, $soal): bool
    {
        if ((int) $soal->soal_tipe === 1) {
            $selected = $item->pilihan->firstWhere('soaljawaban_selected', 1);

            return (bool) ($selected?->jawaban?->jawaban_benar);
        }

        if ((int) $soal->soal_tipe === 3) {
            $jawaban = trim((string) $item->tessoal_jawaban_text);

            return $jawaban !== '' && strtoupper($jawaban) === strtoupper(trim((string) $soal->soal_kunci));
        }

        if ((int) $soal->soal_tipe === 2) {
            return $item->tessoal_change_time !== null && (float) $item->tessoal_nilai > 0;
        }

        return false;
    }

    private function kategoriTingkat(float $nilai): string
    {
        if ($nilai >= 0.7) {
            return 'Mudah';
        }

        if ($nilai >= 0.3) {
            return 'Sedang';
        }

        return 'Sukar';
    }

    private function kategoriDaya(float $nilai): string
    {
        if ($nilai >= 0.4) {
            return 'Baik';
        }

        if ($nilai >= 0.2) {
            return 'Cukup';
        }

        return 'Perlu perbaikan';
    }
}
