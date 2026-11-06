<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Support\LegacyPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('username', $credentials['username'])->first();

        if (! $admin || ! LegacyPassword::verify($credentials['password'], $admin->password)) {
            return back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->onlyInput('username');
        }

        LegacyPassword::upgrade($admin, $credentials['password'], 'password');

        Auth::guard('web')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLog::record($admin->username, 'Login ke panel administrator');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('web')->user();

        if ($admin) {
            ActivityLog::record($admin->username, 'Logout dari panel administrator');
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
