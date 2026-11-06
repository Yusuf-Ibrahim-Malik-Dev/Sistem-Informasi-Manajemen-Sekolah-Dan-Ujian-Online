<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tes;
use App\Models\TesToken;
use App\Models\TesUser;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = TesToken::with('tes')
            ->when(! $request->user('web')->isAdmin(), fn ($q) => $q->where('token_user_id', $request->user('web')->id))
            ->orderByDesc('token_id')
            ->paginate(15);

        $tes = Tes::orderBy('tes_nama')->get();

        return view('admin.token.index', compact('tokens', 'tes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'token_isi' => ['nullable', 'string', 'max:20'],
            'token_aktif' => ['required', 'integer', 'min:1', 'max:100000'],
            'token_tes_id' => ['required', 'integer', 'min:0'],
        ]);

        $isi = trim((string) ($data['token_isi'] ?? ''));

        if ($isi === '') {
            do {
                $isi = (string) random_int(100000, 999999);
            } while (TesToken::where('token_isi', $isi)->whereDate('token_ts', now()->toDateString())->exists());
        }

        $tesId = (int) $data['token_tes_id'];

        if ($tesId !== 0 && ! Tes::whereKey($tesId)->exists()) {
            return back()->with('error', 'Tes yang dipilih tidak ditemukan.');
        }

        TesToken::generate($isi, $request->user('web')->id, $tesId, (int) $data['token_aktif']);

        return back()->with('success', 'Token ' . $isi . ' berhasil dibuat.');
    }

    public function destroy(TesToken $token)
    {
        $token->delete();

        return back()->with('success', 'Token berhasil dihapus.');
    }
}
