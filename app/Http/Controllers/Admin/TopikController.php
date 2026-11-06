<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Modul;
use App\Models\TesTopikSet;
use App\Models\Topik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TopikController extends Controller
{
    public function index(Request $request)
    {
        $moduls = Modul::orderBy('modul_nama')->get();
        $gurus = Admin::orderBy('nama')->get();
        $tipeUjian = Topik::tipeUjianOptions();

        $topiks = Topik::with(['modul', 'guru'])
            ->withCount('soals')
            ->when($request->filled('modul'), fn ($q) => $q->where('topik_modul_id', $request->modul))
            ->when($request->filled('tipe'), fn ($q) => $q->where('topik_tipe_ujian', $request->tipe))
            ->when($request->filled('q'), fn ($q) => $q->where('topik_nama', 'like', '%' . $request->q . '%'))
            ->orderBy('topik_nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.topik.index', compact('moduls', 'gurus', 'tipeUjian', 'topiks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'topik_modul_id' => ['required', 'exists:cbt_modul,modul_id'],
            'topik_nama' => ['required', 'string', 'max:255'],
            'topik_guru_id' => ['required', 'exists:user,id'],
            'topik_tipe_ujian' => ['required', Rule::in(array_keys(Topik::tipeUjianOptions()))],
            'topik_detail' => ['nullable', 'string'],
            'topik_aktif' => ['nullable', 'boolean'],
        ]);

        Topik::create([
            'topik_modul_id' => $data['topik_modul_id'],
            'topik_nama' => $data['topik_nama'],
            'topik_tipe_ujian' => $data['topik_tipe_ujian'],
            'topik_guru_id' => $data['topik_guru_id'],
            'topik_detail' => $data['topik_detail'] ?? null,
            'topik_aktif' => $request->boolean('topik_aktif'),
        ]);

        return back()->with('success', 'Topik berhasil ditambahkan.');
    }

    public function update(Request $request, Topik $topik)
    {
        if ($redirect = $this->pastikanBolehKelola($topik)) {
            return $redirect;
        }

        $data = $request->validate([
            'topik_modul_id' => ['required', 'exists:cbt_modul,modul_id'],
            'topik_nama' => ['required', 'string', 'max:255'],
            'topik_guru_id' => ['required', 'exists:user,id'],
            'topik_tipe_ujian' => ['required', Rule::in(array_keys(Topik::tipeUjianOptions()))],
            'topik_detail' => ['nullable', 'string'],
            'topik_aktif' => ['nullable', 'boolean'],
        ]);

        $topik->update([
            'topik_modul_id' => $data['topik_modul_id'],
            'topik_nama' => $data['topik_nama'],
            'topik_tipe_ujian' => $data['topik_tipe_ujian'],
            'topik_guru_id' => $data['topik_guru_id'],
            'topik_detail' => $data['topik_detail'] ?? null,
            'topik_aktif' => $request->boolean('topik_aktif'),
        ]);

        return back()->with('success', 'Topik berhasil diperbarui.');
    }

    public function destroy(Topik $topik)
    {
        if ($redirect = $this->pastikanBolehKelola($topik)) {
            return $redirect;
        }

        if ($topik->soals()->exists()) {
            return back()->with('error', 'Topik tidak dapat dihapus karena masih memiliki soal.');
        }

        if (TesTopikSet::where('tset_topik_id', $topik->topik_id)->exists()) {
            return back()->with('error', 'Topik tidak dapat dihapus karena masih dipakai pada komposisi tes.');
        }

        $topik->delete();

        return back()->with('success', 'Topik berhasil dihapus.');
    }

    private function pastikanBolehKelola(?Topik $topik): ?RedirectResponse
    {
        if ($topik && $topik->dapatDikelolaOleh(auth('web')->user())) {
            return null;
        }

        return redirect()->route('admin.topik.index')->with(
            'popup_error',
            'Topik ini dikelola oleh guru lain. Anda hanya bisa melihat datanya, tidak bisa mengubah atau menghapusnya.'
        );
    }
}
