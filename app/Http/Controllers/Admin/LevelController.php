<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Access;
use App\Models\Level;
use App\Models\Menu;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::withCount('admins')->orderBy('id')->get();
        $menus = Menu::orderBy('urutan')->get();
        $access = Access::all()->groupBy('level');

        return view('admin.level.index', compact('levels', 'menus', 'access'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'level' => ['required', 'string', 'max:50', 'unique:user_level,level'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ]);

        Level::create($data);

        return back()->with('success', 'Level berhasil ditambahkan.');
    }

    public function update(Request $request, Level $level)
    {
        $request->validate([
            'menus' => ['nullable', 'array'],
            'menus.*' => ['string', 'exists:user_menu,kode_menu'],
            'menu_add' => ['nullable', 'array'],
            'menu_edit' => ['nullable', 'array'],
            'menu_delete' => ['nullable', 'array'],
            'menu_approve' => ['nullable', 'array'],
        ]);

        Access::where('level', $level->level)->delete();

        $add = collect($request->input('menu_add', []));
        $edit = collect($request->input('menu_edit', []));
        $delete = collect($request->input('menu_delete', []));
        $approve = collect($request->input('menu_approve', []));

        foreach ($request->input('menus', []) as $kode) {
            Access::create([
                'level' => $level->level,
                'kode_menu' => $kode,
                'add' => $add->contains($kode),
                'edit' => $edit->contains($kode),
                'can_delete' => $delete->contains($kode),
                'can_approve' => $approve->contains($kode),
            ]);
        }

        return back()->with('success', 'Hak akses level ' . $level->level . ' berhasil diperbarui.');
    }

    public function destroy(Level $level)
    {
        if ($level->level === 'admin') {
            return back()->with('error', 'Level administrator tidak dapat dihapus.');
        }

        if ($level->admins()->exists()) {
            return back()->with('error', 'Level masih digunakan oleh user administrator.');
        }

        Access::where('level', $level->level)->delete();
        $level->delete();

        return back()->with('success', 'Level berhasil dihapus.');
    }
}
