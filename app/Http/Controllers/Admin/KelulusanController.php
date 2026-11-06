<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use App\Models\Peserta;
use App\Models\UserGroup;
use App\Support\Kelulusan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class KelulusanController extends Controller
{
    public function index(Request $request)
    {
        $grups = UserGroup::withCount('pesertas')
            ->orderBy('grup_jenjang')
            ->orderBy('grup_tingkat')
            ->orderBy('grup_nama')
            ->get();

        $pesertas = Peserta::with('grup')
            ->when($request->filled('grup'), fn ($query) => $query->where('user_grup_id', $request->grup))
            ->when($request->filled('jenjang'), fn ($query) => $query->whereHas(
                'grup',
                fn ($grup) => $grup->where('grup_jenjang', $request->jenjang)
            ))
            ->when($request->filled('tingkat'), fn ($query) => $query->whereHas(
                'grup',
                fn ($grup) => $grup->where('grup_tingkat', $request->tingkat)
            ))
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $sub->where('user_name', 'like', '%'.$request->q.'%')
                        ->orWhere('user_firstname', 'like', '%'.$request->q.'%');
                });
            })
            ->orderBy('user_firstname')
            ->get();

        $ringkasan = Kelulusan::ringkasanKoleksi($pesertas);

        $daftar = $pesertas
            ->map(fn ($peserta) => [
                'peserta' => $peserta,
                'kelulusan' => $ringkasan[$peserta->user_id] ?? null,
            ])
            ->filter(fn ($row) => $row['kelulusan'] !== null)
            ->values();

        $statistik = [
            'total' => $daftar->count(),
            'kelas_akhir' => $daftar->filter(fn ($row) => $row['kelulusan']['kelas_akhir'])->count(),
            'lulus' => $daftar->filter(fn ($row) => $row['kelulusan']['lulus'])->count(),
        ];
        $statistik['belum'] = $statistik['total'] - $statistik['lulus'];

        $status = (string) $request->input('status', '');

        if ($status === 'lulus') {
            $daftar = $daftar->filter(fn ($row) => $row['kelulusan']['lulus'])->values();
        } elseif ($status === 'belum') {
            $daftar = $daftar->filter(fn ($row) => ! $row['kelulusan']['lulus'])->values();
        }

        $halaman = LengthAwarePaginator::resolveCurrentPage();
        $perHalaman = 15;

        $daftar = new LengthAwarePaginator(
            $daftar->slice(($halaman - 1) * $perHalaman, $perHalaman)->values(),
            $daftar->count(),
            $perHalaman,
            $halaman,
            [
                'path' => route('admin.kelulusan.index'),
                'query' => $request->query(),
            ]
        );

        return view('admin.kelulusan.index', [
            'daftar' => $daftar,
            'grups' => $grups,
            'statistik' => $statistik,
        ]);
    }

    public function cetak(Request $request, Peserta $peserta)
    {
        $kelulusan = Kelulusan::untuk($peserta, $request->query('kelas'));

        if (! $kelulusan['lulus']) {
            return redirect()
                ->route('admin.kelulusan.index')
                ->with('popup_error', 'Surat kelulusan belum dapat dicetak. Pastikan peserta berada di kelas akhir dan seluruh nilai raport sudah lengkap serta memenuhi nilai minimal kelulusan.');
        }

        return view('kelulusan-cetak', [
            'peserta' => $peserta,
            'kelulusan' => $kelulusan,
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
            'alamat' => Konfigurasi::get('cbt_alamat', ''),
            'kepalaSekolah' => Konfigurasi::get('cbt_kepala_sekolah', 'Kepala Sekolah'),
            'nipKepalaSekolah' => Konfigurasi::get('cbt_nip_kepala_sekolah', '-'),
            'tahunAjaran' => Konfigurasi::get('cbt_tahun_ajaran', ''),
            'kembali' => route('admin.kelulusan.index', ['q' => $peserta->nama]),
        ]);
    }
}
