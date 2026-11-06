<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AttendanceLocation;
use App\Models\Soal;
use App\Models\Tes;
use App\Models\TesToken;
use App\Models\Topik;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TesController extends Controller
{
    public function index(Request $request)
    {
        $tes = Tes::with('groups')
            ->withCount(['topikSets', 'tesUsers'])
            ->when($request->filled('q'), fn ($q) => $q->where('tes_nama', 'like', '%'.$request->q.'%'))
            ->orderByDesc('tes_id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.tes.index', compact('tes'));
    }

    public function create()
    {
        return view('admin.tes.form', [
            'tes' => new Tes([
                'tes_duration_time' => 60,
                'tes_score_right' => 1,
                'tes_score_wrong' => 0,
                'tes_score_unanswered' => 0,
                'tes_max_score' => 100,
                'tes_biaya' => 0,
                'tes_absensi_wajib' => false,
                'tes_token' => false,
                'tes_results_to_users' => true,
                'tes_detail_to_users' => false,
                'tes_ranking_to_users' => false,
            ]),
            'topiks' => Topik::with('modul')->withCount('soals')->orderBy('topik_nama')->get(),
            'grups' => UserGroup::orderBy('grup_nama')->get(),
            'sets' => collect(),
            'locations' => $this->attendanceLocations(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $tes = Tes::create($data['tes']);
            $tes->groups()->sync($data['groups']);

            foreach ($data['sets'] as $set) {
                $tes->topikSets()->create($set);
            }
        });

        return redirect()->route('admin.tes.index')->with('success', 'Tes berhasil dibuat.');
    }

    public function edit(Tes $tes)
    {
        return view('admin.tes.form', [
            'tes' => $tes->load(['groups', 'topikSets']),
            'topiks' => Topik::with('modul')->withCount('soals')->orderBy('topik_nama')->get(),
            'grups' => UserGroup::orderBy('grup_nama')->get(),
            'sets' => $tes->topikSets,
            'locations' => $this->attendanceLocations(),
        ]);
    }

    public function update(Request $request, Tes $tes)
    {
        $data = $this->validated($request, $tes);

        DB::transaction(function () use ($data, $tes) {
            $tes->update($data['tes']);
            $tes->groups()->sync($data['groups']);
            $tes->topikSets()->delete();

            foreach ($data['sets'] as $set) {
                $tes->topikSets()->create($set);
            }
        });

        return redirect()->route('admin.tes.index')->with('success', 'Tes berhasil diperbarui.');
    }

    public function destroy(Tes $tes)
    {
        if ($tes->tesUsers()->exists()) {
            return back()->with('error', 'Tes sudah memiliki peserta sehingga tidak dapat dihapus.');
        }

        if ($tes->pembayarans()->exists()) {
            return back()->with('error', 'Tes sudah memiliki data pembayaran sehingga tidak dapat dihapus.');
        }

        DB::transaction(function () use ($tes) {
            $tes->topikSets()->delete();
            $tes->groups()->detach();
            Absensi::where('absensi_tes_id', $tes->tes_id)->delete();
            TesToken::where('token_tes_id', $tes->tes_id)->delete();
            $tes->delete();
        });

        return back()->with('success', 'Tes berhasil dihapus.');
    }

    private function validated(Request $request, ?Tes $tes = null): array
    {
        $validated = $request->validate([
            'tes_nama' => ['required', 'string', 'max:255', 'unique:cbt_tes,tes_nama'.($tes ? ','.$tes->tes_id.',tes_id' : '')],
            'tes_detail' => ['required', 'string'],
            'tes_begin_time' => ['required', 'date'],
            'tes_end_time' => ['required', 'date', 'after:tes_begin_time'],
            'tes_duration_time' => ['required', 'integer', 'min:1', 'max:1000'],
            'tes_ip_range' => ['nullable', 'string', 'max:255'],
            'tes_results_to_users' => ['nullable', 'boolean'],
            'tes_detail_to_users' => ['nullable', 'boolean'],
            'tes_ranking_to_users' => ['nullable', 'boolean'],
            'tes_score_right' => ['required', 'numeric', 'min:0'],
            'tes_score_wrong' => ['required', 'numeric'],
            'tes_score_unanswered' => ['required', 'numeric'],
            'tes_max_score' => ['required', 'numeric', 'min:1'],
            'tes_biaya' => ['nullable', 'numeric', 'min:0'],
            'tes_absensi_wajib' => ['nullable', 'boolean'],
            'tes_absensi_location_id' => ['nullable', 'integer', 'exists:att_attendance_locations,id'],
            'tes_checkin_mulai' => ['nullable', 'date'],
            'tes_checkin_selesai' => ['nullable', 'date', 'after_or_equal:tes_checkin_mulai'],
            'tes_token' => ['nullable', 'boolean'],
            'groups' => ['required', 'array', 'min:1'],
            'groups.*' => ['exists:cbt_user_grup,grup_id'],
            'sets' => ['required', 'array', 'min:1'],
            'sets.*.topik_id' => ['required', 'exists:cbt_topik,topik_id'],
            'sets.*.tipe' => ['required', 'in:0,1,2,3'],
            'sets.*.difficulty' => ['required', 'integer', 'min:1', 'max:5'],
            'sets.*.jumlah' => ['required', 'integer', 'min:1', 'max:500'],
            'sets.*.jawaban' => ['nullable', 'integer', 'min:0', 'max:10'],
            'sets.*.acak_jawaban' => ['nullable', 'boolean'],
            'sets.*.acak_soal' => ['nullable', 'boolean'],
        ]);

        $sets = collect($request->input('sets', []))->map(function ($set) {
            return [
                'tset_topik_id' => (int) $set['topik_id'],
                'tset_tipe' => (int) $set['tipe'],
                'tset_difficulty' => (int) $set['difficulty'],
                'tset_jumlah' => (int) $set['jumlah'],
                'tset_jawaban' => (int) ($set['jawaban'] ?? 0),
                'tset_acak_jawaban' => ! empty($set['acak_jawaban']) ? 1 : 0,
                'tset_acak_soal' => ! empty($set['acak_soal']) ? 1 : 0,
            ];
        })->values()->all();

        $kebutuhan = collect($sets)
            ->groupBy(fn ($set) => $set['tset_topik_id'].'|'.$set['tset_tipe'].'|'.$set['tset_difficulty'])
            ->map(fn ($items) => $items->sum('tset_jumlah'));

        foreach ($kebutuhan as $key => $jumlah) {
            [$topikId, $tipe, $difficulty] = array_map('intval', explode('|', $key));

            $tersedia = Soal::where('soal_topik_id', $topikId)
                ->where('soal_difficulty', $difficulty)
                ->when($tipe !== 0, fn ($q) => $q->where('soal_tipe', $tipe))
                ->where('soal_aktif', 1)
                ->count();

            if ($tersedia < $jumlah) {
                throw ValidationException::withMessages([
                    'sets' => 'Jumlah soal yang diminta melebihi soal aktif yang tersedia pada salah satu topik (tersedia '.$tersedia.' soal).',
                ]);
            }
        }

        return [
            'tes' => [
                'tes_nama' => $validated['tes_nama'],
                'tes_detail' => $validated['tes_detail'],
                'tes_begin_time' => $validated['tes_begin_time'],
                'tes_end_time' => $validated['tes_end_time'],
                'tes_duration_time' => $validated['tes_duration_time'],
                'tes_ip_range' => $validated['tes_ip_range'] ?? '*.*.*.*',
                'tes_results_to_users' => $request->boolean('tes_results_to_users'),
                'tes_detail_to_users' => $request->boolean('tes_detail_to_users'),
                'tes_ranking_to_users' => $request->boolean('tes_ranking_to_users'),
                'tes_score_right' => $validated['tes_score_right'],
                'tes_score_wrong' => $validated['tes_score_wrong'],
                'tes_score_unanswered' => $validated['tes_score_unanswered'],
                'tes_max_score' => $validated['tes_max_score'],
                'tes_biaya' => $validated['tes_biaya'] ?? 0,
                'tes_absensi_wajib' => $request->boolean('tes_absensi_wajib'),
                'tes_absensi_location_id' => $request->boolean('tes_absensi_wajib')
                    ? ($validated['tes_absensi_location_id'] ?? null)
                    : null,
                'tes_checkin_mulai' => $request->boolean('tes_absensi_wajib')
                    ? ($validated['tes_checkin_mulai'] ?? null)
                    : null,
                'tes_checkin_selesai' => $request->boolean('tes_absensi_wajib')
                    ? ($validated['tes_checkin_selesai'] ?? null)
                    : null,
                'tes_token' => $request->boolean('tes_token'),
            ],
            'groups' => $validated['groups'],
            'sets' => $sets,
        ];
    }

    private function attendanceLocations()
    {
        return AttendanceLocation::with('school')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
