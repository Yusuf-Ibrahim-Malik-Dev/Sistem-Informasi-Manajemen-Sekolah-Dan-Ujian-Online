<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Konfigurasi;
use App\Models\Modul;
use App\Models\Peserta;
use App\Models\Raport;
use App\Models\Tes;
use App\Models\TesUser;
use App\Models\UserGroup;
use App\Support\HasilUjian;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $tesList = Tes::withCount([
            'tesUsers as peserta_selesai' => fn ($query) => $query->where('tesuser_status', TesUser::STATUS_SELESAI),
        ])->orderBy('tes_nama')->get();

        $grups = UserGroup::orderBy('grup_nama')->get();

        $monitoring = TesUser::with(['tes', 'peserta.grup'])
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->withCount([
                'soals as total_soal',
                'soals as dijawab' => fn ($query) => $query->whereNotNull('tessoal_change_time'),
            ])
            ->when($request->filled('tes'), fn ($query) => $query->where('tesuser_tes_id', $request->tes))
            ->when($request->filled('grup'), function ($query) use ($request) {
                $query->whereHas('peserta', fn ($sub) => $sub->where('user_grup_id', $request->grup));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('tesuser_status', $request->status))
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->whereHas('peserta', function ($sub) use ($request) {
                    $sub->where('user_firstname', 'like', '%' . $request->q . '%')
                        ->orWhere('user_name', 'like', '%' . $request->q . '%');
                });
            })
            ->orderByDesc('tesuser_creation_time')
            ->paginate(20)
            ->withQueryString();

        $statistik = [
            'berjalan' => TesUser::where('tesuser_status', TesUser::STATUS_BERJALAN)->count(),
            'selesai' => TesUser::where('tesuser_status', TesUser::STATUS_SELESAI)->count(),
            'total' => TesUser::count(),
        ];

        return view('admin.guru.index', compact('monitoring', 'tesList', 'grups', 'statistik'));
    }

    public function raport(Request $request)
    {
        $admin = auth('web')->user();
        $moduls = $this->modulUntukGuru($admin);

        $modul = $moduls->firstWhere('modul_id', (int) $request->input('modul')) ?: $moduls->first();

        $grups = UserGroup::withCount('pesertas')
            ->orderBy('grup_jenjang')
            ->orderBy('grup_tingkat')
            ->orderBy('grup_nama')
            ->get();

        $grup = $grups->firstWhere('grup_id', (int) $request->input('grup')) ?: $grups->first();

        $pesertas = collect();
        $nilais = collect();

        if ($modul && $grup) {
            $pesertas = Peserta::where('user_grup_id', $grup->grup_id)
                ->orderBy('user_firstname')
                ->get();

            $nilais = Raport::where('raport_modul_id', $modul->modul_id)
                ->where('raport_jenjang', $grup->grup_jenjang)
                ->where('raport_tingkat', $grup->grup_tingkat)
                ->whereIn('raport_user_id', $pesertas->pluck('user_id'))
                ->get()
                ->keyBy('raport_user_id');
        }

        return view('admin.guru.raport', compact('moduls', 'modul', 'grups', 'grup', 'pesertas', 'nilais'));
    }

    public function simpanRaport(Request $request)
    {
        $data = $request->validate([
            'raport_modul_id' => ['required', 'integer', 'exists:cbt_modul,modul_id'],
            'raport_grup_id' => ['required', 'integer', 'exists:cbt_user_grup,grup_id'],
            'nilai' => ['nullable', 'array'],
            'nilai.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
        ]);

        $admin = auth('web')->user();
        $modul = $this->modulUntukGuru($admin)
            ->firstWhere('modul_id', (int) $data['raport_modul_id']);

        if (! $modul) {
            return redirect()
                ->route('admin.guru.raport')
                ->with('popup_error', 'Anda hanya dapat memberi nilai pada mata pelajaran yang Anda ampu.');
        }

        $grup = UserGroup::findOrFail($data['raport_grup_id']);
        $pesertaIds = Peserta::where('user_grup_id', $grup->grup_id)->pluck('user_id');
        $disimpan = 0;

        foreach ($pesertaIds as $pesertaId) {
            $nilai = $data['nilai'][$pesertaId] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            Raport::updateOrCreate(
                [
                    'raport_user_id' => $pesertaId,
                    'raport_modul_id' => $modul->modul_id,
                    'raport_jenjang' => $grup->grup_jenjang,
                    'raport_tingkat' => $grup->grup_tingkat,
                ],
                [
                    'raport_guru_id' => $admin->id,
                    'raport_nilai' => $nilai,
                    'raport_catatan' => $data['catatan'][$pesertaId] ?? null,
                ]
            );

            $disimpan++;
        }

        if ($disimpan === 0) {
            return back()->with('error', 'Tidak ada nilai yang diisi.');
        }

        return redirect()
            ->route('admin.guru.raport', [
                'modul' => $modul->modul_id,
                'grup' => $data['raport_grup_id'],
            ])
            ->with('success', "Nilai raport berhasil disimpan untuk {$disimpan} peserta.");
    }

    private function modulUntukGuru(Admin $admin)
    {
        return Modul::where('modul_aktif', true)
            ->when(! $admin->isAdmin(), function ($query) use ($admin) {
                $query->whereHas('topiks', fn ($topik) => $topik->where('topik_guru_id', $admin->id));
            })
            ->orderBy('modul_nama')
            ->get();
    }

    public function toggleRanking(Tes $tes)
    {
        $tes->update(['tes_ranking_to_users' => ! $tes->tes_ranking_to_users]);

        return back()->with('success', $tes->tes_ranking_to_users
            ? 'Ranking ujian berhasil ditampilkan ke peserta.'
            : 'Ranking ujian berhasil disembunyikan dari peserta.');
    }

    public function ranking(Tes $tes)
    {
        return view('admin.guru.ranking', [
            'tes' => $tes,
            'ranking' => HasilUjian::peringkat($tes),
            'namaGuru' => HasilUjian::guruUntukTes($tes),
        ]);
    }

    public function cetakRanking(Tes $tes)
    {
        return view('ranking-cetak', [
            'tes' => $tes,
            'ranking' => HasilUjian::peringkat($tes),
            'namaGuru' => HasilUjian::guruUntukTes($tes),
            'kembali' => route('admin.guru.ranking', $tes),
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
        ]);
    }
}
