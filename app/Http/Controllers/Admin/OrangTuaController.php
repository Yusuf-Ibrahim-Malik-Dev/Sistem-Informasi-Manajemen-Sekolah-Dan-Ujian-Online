<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OrangTuaController extends Controller
{
    public function index(Request $request)
    {
        $orangTua = OrangTua::withCount('pesertas')
            ->with('pesertas')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($sub) => $sub
                    ->where('ortu_nama', 'like', $term)
                    ->orWhere('ortu_username', 'like', $term)
                    ->orWhere('ortu_email', 'like', $term)
                    ->orWhere('ortu_telepon', 'like', $term));
            })
            ->orderBy('ortu_nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orangtua.index', compact('orangTua'));
    }

    public function create()
    {
        return view('admin.orangtua.form', [
            'orangTua' => new OrangTua,
            'pesertas' => $this->daftarPeserta(),
            'terpilih' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $orangTua = OrangTua::create([
            'ortu_nama' => $data['ortu_nama'],
            'ortu_username' => $data['ortu_username'],
            'ortu_password' => Hash::make($data['password']),
            'ortu_email' => $data['ortu_email'] ?? null,
            'ortu_telepon' => $data['ortu_telepon'] ?? null,
        ]);

        $orangTua->pesertas()->sync($data['peserta'] ?? []);

        return redirect()->route('admin.orangtua.index')->with('success', 'Akun orang tua berhasil dibuat.');
    }

    public function edit(OrangTua $orangTua)
    {
        return view('admin.orangtua.form', [
            'orangTua' => $orangTua,
            'pesertas' => $this->daftarPeserta(),
            'terpilih' => $orangTua->pesertas()->pluck('cbt_user.user_id')->all(),
        ]);
    }

    public function update(Request $request, OrangTua $orangTua)
    {
        $data = $this->validated($request, $orangTua);

        $orangTua->fill([
            'ortu_nama' => $data['ortu_nama'],
            'ortu_username' => $data['ortu_username'],
            'ortu_email' => $data['ortu_email'] ?? null,
            'ortu_telepon' => $data['ortu_telepon'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $orangTua->ortu_password = Hash::make($data['password']);
        }

        $orangTua->save();
        $orangTua->pesertas()->sync($data['peserta'] ?? []);

        return redirect()->route('admin.orangtua.index')->with('success', 'Akun orang tua berhasil diperbarui.');
    }

    public function destroy(OrangTua $orangTua)
    {
        if ($orangTua->pembayarans()->exists()) {
            return back()->with('error', 'Akun orang tua masih memiliki riwayat pembayaran sehingga tidak dapat dihapus.');
        }

        $orangTua->pesertas()->detach();
        $orangTua->delete();

        return back()->with('success', 'Akun orang tua berhasil dihapus.');
    }

    private function validated(Request $request, ?OrangTua $orangTua = null): array
    {
        return $request->validate([
            'ortu_nama' => ['required', 'string', 'max:255'],
            'ortu_username' => [
                'required', 'string', 'max:100', 'alpha_dash',
                Rule::unique('cbt_orang_tua', 'ortu_username')->ignore($orangTua?->ortu_id, 'ortu_id'),
            ],
            'password' => [$orangTua ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'ortu_email' => ['nullable', 'email', 'max:255'],
            'ortu_telepon' => ['nullable', 'string', 'max:30'],
            'peserta' => ['nullable', 'array'],
            'peserta.*' => ['exists:cbt_user,user_id'],
        ]);
    }

    private function daftarPeserta()
    {
        return Peserta::with('grup')
            ->orderBy('user_firstname')
            ->orderBy('user_name')
            ->get();
    }
}
