<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use App\Models\Tes;
use App\Models\TesUser;
use App\Support\HasilUjian;

class RankingController extends Controller
{
    public function index()
    {
        $peserta = auth('peserta')->user();

        $tes = Tes::withCount([
            'tesUsers as peserta_selesai' => fn ($query) => $query->where('tesuser_status', TesUser::STATUS_SELESAI),
            'topikSets',
        ])
            ->where('tes_ranking_to_users', true)
            ->whereHas('groups', fn ($query) => $query->where('grup_id', $peserta->user_grup_id))
            ->whereHas('tesUsers', fn ($query) => $query
                ->where('tesuser_user_id', $peserta->user_id)
                ->where('tesuser_status', TesUser::STATUS_SELESAI))
            ->orderByDesc('tes_end_time')
            ->get();

        return view('peserta.ranking.index', compact('tes'));
    }

    public function show(Tes $tes)
    {
        $peserta = auth('peserta')->user();
        $this->pastikanAkses($tes, $peserta);

        return view('peserta.ranking.show', [
            'tes' => $tes,
            'ranking' => HasilUjian::peringkat($tes, $peserta->user_id),
            'namaGuru' => HasilUjian::guruUntukTes($tes),
        ]);
    }

    public function cetak(Tes $tes)
    {
        $peserta = auth('peserta')->user();
        $this->pastikanAkses($tes, $peserta);

        return view('ranking-cetak', [
            'tes' => $tes,
            'ranking' => HasilUjian::peringkat($tes, $peserta->user_id),
            'namaGuru' => HasilUjian::guruUntukTes($tes),
            'kembali' => route('peserta.ranking.show', $tes),
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
        ]);
    }

    private function pastikanAkses(Tes $tes, $peserta): void
    {
        abort_unless($tes->tes_ranking_to_users, 404);
        abort_unless($tes->groups()->where('grup_id', $peserta->user_grup_id)->exists(), 403, 'Anda tidak terdaftar pada tes ini.');

        $selesai = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->exists();

        abort_unless($selesai, 403, 'Ranking dapat dilihat setelah Anda menyelesaikan ujian.');
    }
}
