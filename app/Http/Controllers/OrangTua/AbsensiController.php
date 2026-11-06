<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrangTua\Concerns\ResolvesAnak;
use App\Models\Absensi;

class AbsensiController extends Controller
{
    use ResolvesAnak;

    public function index()
    {
        $orangTua = auth('orangtua')->user();
        [$anak, $daftarAnak] = $this->anakAktif($orangTua);

        if (! $anak) {
            return view('orangtua.absensi', compact('orangTua', 'daftarAnak', 'anak'));
        }

        $query = $anak->absensis()->with('tes')->orderByDesc('absensi_tanggal')->orderByDesc('absensi_id');

        $rekap = collect(Absensi::statusOptions())
            ->mapWithKeys(fn ($label, $status) => [$status => (clone $query)->where('absensi_status', $status)->count()])
            ->all();

        $absensis = $query->paginate(15)->withQueryString();

        return view('orangtua.absensi', compact('orangTua', 'daftarAnak', 'anak', 'absensis', 'rekap'));
    }
}
