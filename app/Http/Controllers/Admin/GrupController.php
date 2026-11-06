<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupController extends Controller
{
    public function index()
    {
        $grups = UserGroup::withCount(['pesertas', 'tes'])
            ->orderBy('grup_jenjang')
            ->orderBy('grup_tingkat')
            ->orderBy('grup_nama')
            ->get();

        return view('admin.grup.index', compact('grups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grup_nama' => ['required', 'string', 'max:255', 'unique:cbt_user_grup,grup_nama'],
            'grup_jenjang' => ['required', Rule::in(array_keys(UserGroup::jenjangOptions()))],
            'grup_tingkat' => ['required', 'integer', 'min:1', 'max:' . count(UserGroup::tingkatOptions($request->input('grup_jenjang')))],
        ]);

        UserGroup::create($data);

        return back()->with('success', 'Group peserta berhasil ditambahkan.');
    }

    public function update(Request $request, UserGroup $grup)
    {
        $data = $request->validate([
            'grup_nama' => ['required', 'string', 'max:255', 'unique:cbt_user_grup,grup_nama,' . $grup->grup_id . ',grup_id'],
            'grup_jenjang' => ['required', Rule::in(array_keys(UserGroup::jenjangOptions()))],
            'grup_tingkat' => ['required', 'integer', 'min:1', 'max:' . count(UserGroup::tingkatOptions($request->input('grup_jenjang')))],
        ]);

        $grup->update($data);

        return back()->with('success', 'Group peserta berhasil diperbarui.');
    }

    public function destroy(UserGroup $grup)
    {
        if ($grup->pesertas()->exists()) {
            return back()->with('error', 'Group tidak dapat dihapus karena masih memiliki peserta.');
        }

        $grup->tes()->detach();
        $grup->delete();

        return back()->with('success', 'Group peserta berhasil dihapus.');
    }
}
