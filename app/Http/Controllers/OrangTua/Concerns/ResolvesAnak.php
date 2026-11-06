<?php

namespace App\Http\Controllers\OrangTua\Concerns;

use App\Models\OrangTua;

trait ResolvesAnak
{
    protected function anakAktif(OrangTua $orangTua, ?int $preferUserId = null): array
    {
        $daftarAnak = $orangTua->pesertas()
            ->with('grup')
            ->orderBy('user_firstname')
            ->orderBy('user_name')
            ->get();

        $diminta = (int) request()->query('anak', 0);
        $anak = $diminta > 0 ? $daftarAnak->firstWhere('user_id', $diminta) : null;
        $anak ??= $preferUserId ? $daftarAnak->firstWhere('user_id', $preferUserId) : null;

        return [$anak ?: $daftarAnak->first(), $daftarAnak];
    }
}
