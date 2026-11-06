<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use Illuminate\Http\Request;

class ModulController extends Controller
{
    public function index()
    {
        $moduls = Modul::withCount('topiks')->orderBy('modul_nama')->get();

        return view('admin.modul.index', compact('moduls'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'modul_nama' => ['required', 'string', 'max:255', 'unique:cbt_modul,modul_nama'],
            'modul_aktif' => ['nullable', 'boolean'],
        ]);

        Modul::create([
            'modul_nama' => $data['modul_nama'],
            'modul_aktif' => $request->boolean('modul_aktif'),
        ]);

        return back()->with('success', 'Modul berhasil ditambahkan.');
    }

    public function update(Request $request, Modul $modul)
    {
        $data = $request->validate([
            'modul_nama' => ['required', 'string', 'max:255', 'unique:cbt_modul,modul_nama,' . $modul->modul_id . ',modul_id'],
            'modul_aktif' => ['nullable', 'boolean'],
        ]);

        $modul->update([
            'modul_nama' => $data['modul_nama'],
            'modul_aktif' => $request->boolean('modul_aktif'),
        ]);

        return back()->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Modul $modul)
    {
        if ($modul->topiks()->exists()) {
            return back()->with('error', 'Modul tidak dapat dihapus karena masih memiliki topik.');
        }

        $modul->delete();

        return back()->with('success', 'Modul berhasil dihapus.');
    }
}
