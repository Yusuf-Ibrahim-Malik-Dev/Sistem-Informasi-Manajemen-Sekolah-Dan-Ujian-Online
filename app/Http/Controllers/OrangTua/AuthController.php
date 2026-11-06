<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use App\Models\OrangTua;
use App\Support\LegacyPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('orangtua')->check()) {
            return redirect()->route('orangtua.dashboard');
        }

        return view('auth.orangtua-login', [
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
            'keterangan' => Konfigurasi::get('cbt_keterangan', 'Ujian Online Berbasis Komputer'),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $orangTua = OrangTua::where('ortu_username', $credentials['username'])->first();

        if (! $orangTua || ! LegacyPassword::verify($credentials['password'], $orangTua->ortu_password)) {
            return back()
                ->withErrors(['username' => 'Username atau password orang tua salah.'])
                ->onlyInput('username');
        }

        LegacyPassword::upgrade($orangTua, $credentials['password'], 'ortu_password');

        Auth::guard('orangtua')->login($orangTua, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('orangtua.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('orangtua')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('orangtua.login');
    }
}
