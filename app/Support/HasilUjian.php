<?php

namespace App\Support;

use App\Models\Peserta;
use App\Models\Tes;
use App\Models\TesUser;

class HasilUjian
{
    public static function ringkas(TesUser $attempt): array
    {
        $attempt->loadMissing(['soals.soal.jawabans', 'soals.pilihan.jawaban']);

        $benar = 0;
        $salah = 0;

        foreach ($attempt->soals as $item) {
            $tipe = (int) $item->soal?->soal_tipe;

            if ($tipe === 1) {
                $selected = $item->pilihan->firstWhere('soaljawaban_selected', 1);

                if ($selected && $selected->jawaban?->jawaban_benar) {
                    $benar++;
                } elseif ($selected) {
                    $salah++;
                }

                continue;
            }

            if ($tipe === 3) {
                $jawaban = trim((string) $item->tessoal_jawaban_text);

                if ($jawaban === '') {
                    continue;
                }

                if (strtoupper($jawaban) === strtoupper(trim((string) $item->soal?->soal_kunci))) {
                    $benar++;
                } else {
                    $salah++;
                }
            }
        }

        return [
            'benar' => $benar,
            'salah' => $salah,
            'nilai' => $attempt->nilai,
        ];
    }

    public static function guru(TesUser $attempt): string
    {
        $attempt->loadMissing('soals.soal.creator');

        $nama = $attempt->soals
            ->map(fn ($item) => $item->soal?->creator?->nama)
            ->filter()
            ->unique()
            ->values();

        return $nama->isNotEmpty() ? $nama->implode(', ') : '-';
    }

    public static function guruUntukTes(Tes $tes): string
    {
        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->latest('tesuser_id')
            ->first();

        return $attempt ? self::guru($attempt) : '-';
    }

    public static function semuaPesertaSelesai(Tes $tes): bool
    {
        if (! $tes->is_open) {
            return true;
        }

        $pesertaIds = Peserta::whereIn(
            'user_grup_id',
            $tes->groups()->pluck('cbt_user_grup.grup_id')
        )->pluck('user_id');

        if ($pesertaIds->isEmpty()) {
            return true;
        }

        $adaYangBerjalan = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->whereIn('tesuser_user_id', $pesertaIds)
            ->where('tesuser_status', TesUser::STATUS_BERJALAN)
            ->exists();

        if ($adaYangBerjalan) {
            return false;
        }

        $jumlahSelesai = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->whereIn('tesuser_user_id', $pesertaIds)
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->distinct()
            ->count('tesuser_user_id');

        return $jumlahSelesai >= $pesertaIds->count();
    }

    public static function peringkat(Tes $tes, ?int $pesertaId = null): array
    {
        $daftar = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->with('peserta.grup')
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->get()
            ->sortBy([
                ['total_nilai', 'desc'],
                ['tesuser_creation_time', 'asc'],
                ['tesuser_id', 'asc'],
            ])
            ->values();

        $hasil = [];
        $rank = 0;
        $nilaiSebelumnya = null;

        foreach ($daftar as $index => $attempt) {
            $nilai = (float) ($attempt->total_nilai ?? 0);

            if ($nilaiSebelumnya === null || $nilai < $nilaiSebelumnya) {
                $rank = $index + 1;
                $nilaiSebelumnya = $nilai;
            }

            $hasil[] = [
                'rank' => $rank,
                'nama' => $attempt->peserta?->nama ?? '-',
                'username' => $attempt->peserta?->user_name ?? '-',
                'grup' => $attempt->peserta?->grup?->grup_nama ?? '-',
                'nilai' => $nilai,
                'saya' => $pesertaId !== null && (int) $attempt->tesuser_user_id === $pesertaId,
            ];
        }

        return $hasil;
    }
}
