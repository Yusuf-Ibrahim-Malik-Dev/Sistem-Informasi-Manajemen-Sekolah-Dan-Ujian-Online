<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Peserta;
use App\Models\Tes;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with(['peserta.grup', 'tes'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->whereHas('peserta', fn ($p) => $p
                    ->where('user_firstname', 'like', $term)
                    ->orWhere('user_name', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('absensi_status', $request->status))
            ->when($request->filled('grup'), fn ($q) => $q->whereHas('peserta', fn ($p) => $p->where('user_grup_id', $request->grup)))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('absensi_tanggal', $request->tanggal))
            ->orderByDesc('absensi_tanggal')
            ->orderByDesc('absensi_id');

        $rekap = collect(Absensi::statusOptions())
            ->mapWithKeys(fn ($label, $status) => [$status => (clone $query)->where('absensi_status', $status)->count()])
            ->all();

        $absensis = $query->paginate(20)->withQueryString();

        return view('admin.absensi.index', [
            'absensis' => $absensis,
            'rekap' => $rekap,
            'pesertas' => Peserta::with('grup')->orderBy('user_firstname')->orderBy('user_name')->get(),
            'tes' => Tes::orderByDesc('tes_begin_time')->get(),
            'grups' => UserGroup::orderBy('grup_nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'absensi_user_id' => ['required', 'exists:cbt_user,user_id'],
            'absensi_tes_id' => ['nullable', 'exists:cbt_tes,tes_id'],
            'absensi_tanggal' => ['required', 'date'],
            'absensi_status' => ['required', Rule::in(array_keys(Absensi::statusOptions()))],
            'absensi_keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $data['absensi_sumber'] = Absensi::SUMBER_MANUAL;

        if (! empty($data['absensi_tes_id'])) {
            Absensi::updateOrCreate(
                [
                    'absensi_user_id' => $data['absensi_user_id'],
                    'absensi_tes_id' => $data['absensi_tes_id'],
                ],
                [
                    'absensi_tanggal' => $data['absensi_tanggal'],
                    'absensi_status' => $data['absensi_status'],
                    'absensi_keterangan' => $data['absensi_keterangan'] ?? null,
                    'absensi_sumber' => Absensi::SUMBER_MANUAL,
                ]
            );
        } else {
            Absensi::create($data);
        }

        return back()->with('success', 'Data absensi berhasil disimpan.');
    }

    public function update(Request $request, Absensi $absensi)
    {
        $data = $request->validate([
            'absensi_tanggal' => ['required', 'date'],
            'absensi_status' => ['required', Rule::in(array_keys(Absensi::statusOptions()))],
            'absensi_keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $absensi->update([
            'absensi_tanggal' => $data['absensi_tanggal'],
            'absensi_status' => $data['absensi_status'],
            'absensi_keterangan' => $data['absensi_keterangan'] ?? null,
            'absensi_sumber' => Absensi::SUMBER_MANUAL,
        ]);

        return back()->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }
}
