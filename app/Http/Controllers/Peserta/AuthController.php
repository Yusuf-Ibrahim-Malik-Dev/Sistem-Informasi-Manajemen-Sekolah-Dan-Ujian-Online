<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use App\Models\Peserta;
use App\Support\LegacyPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('peserta')->check()) {
            return redirect()->route('peserta.dashboard');
        }

        return view('auth.peserta-login', [
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
            'keterangan' => Konfigurasi::get('cbt_keterangan', 'Ujian Online Berbasis Komputer'),
            'linkOperator' => Konfigurasi::get('link_login_operator', 'ya') === 'ya',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $peserta = Peserta::with('grup')->where('user_name', $credentials['username'])->first();

        if (! $peserta || ! LegacyPassword::verify($credentials['password'], $peserta->user_password)) {
            return back()
                ->withErrors(['username' => 'Username atau password peserta salah.'])
                ->onlyInput('username');
        }

        LegacyPassword::upgrade($peserta, $credentials['password'], 'user_password');

        Auth::guard('peserta')->login($peserta, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('peserta.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('peserta')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('peserta.login');
    }
}
