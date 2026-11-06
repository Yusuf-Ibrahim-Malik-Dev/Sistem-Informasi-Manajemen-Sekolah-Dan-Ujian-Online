<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrangTua\Concerns\ResolvesAnak;
use App\Models\Absensi;
use App\Models\Pembayaran;
use App\Models\Tes;

class DashboardController extends Controller
{
    use ResolvesAnak;

    public function index()
    {
        $orangTua = auth('orangtua')->user();
        [$anak, $daftarAnak] = $this->anakAktif($orangTua);

        if (! $anak) {
            return view('orangtua.dashboard', compact('orangTua', 'daftarAnak', 'anak'));
        }

        $absensis = $anak->absensis()
            ->with('tes')
            ->orderByDesc('absensi_tanggal')
            ->orderByDesc('absensi_id')
            ->get();

        $rekapAbsensi = collect(Absensi::statusOptions())
            ->mapWithKeys(fn ($label, $status) => [$status => $absensis->where('absensi_status', $status)->count()])
            ->all();

        $pembayarans = $anak->pembayarans()->get()->keyBy('pembayaran_tes_id');

        $tes = Tes::whereHas('groups', fn ($q) => $q->where('grup_id', $anak->user_grup_id))
            ->orderByDesc('tes_begin_time')
            ->get();

        $totalTagihan = (float) $tes->where('tes_biaya', '>', 0)->sum('tes_biaya');
        $terbayar = (float) $pembayarans->where('pembayaran_status', Pembayaran::DIVERIFIKASI)->sum('pembayaran_nominal');
        $menunggu = $pembayarans->where('pembayaran_status', Pembayaran::MENUNGGU)->count();
        $ditolak = $pembayarans->where('pembayaran_status', Pembayaran::DITOLAK)->count();

        $ringkasanPembayaran = [
            'total_tagihan' => $totalTagihan,
            'terbayar' => $terbayar,
            'sisa' => max(0, $totalTagihan - $terbayar),
            'menunggu' => $menunggu,
            'ditolak' => $ditolak,
        ];

        return view('orangtua.dashboard', compact(
            'orangTua',
            'daftarAnak',
            'anak',
            'absensis',
            'rekapAbsensi',
            'tes',
            'pembayarans',
            'ringkasanPembayaran'
        ));
    }
}
