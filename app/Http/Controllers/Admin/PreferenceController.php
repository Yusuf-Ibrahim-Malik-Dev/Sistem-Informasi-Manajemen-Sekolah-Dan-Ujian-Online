<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    public function edit(Request $request)
    {
        $preference = UserPreference::firstOrCreate(
            ['user_id' => $request->user('web')->id],
            [
                'theme_mode' => 'light',
                'primary_color' => '#6366f1',
                'accent_color' => '#06b6d4',
                'sidebar_collapsed' => false,
            ]
        );

        return view('admin.preferences.edit', compact('preference'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme_mode' => ['required', Rule::in(['light', 'dark'])],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_collapsed' => ['nullable', 'boolean'],
        ]);

        UserPreference::updateOrCreate(
            ['user_id' => $request->user('web')->id],
            [
                'theme_mode' => $data['theme_mode'],
                'primary_color' => $data['primary_color'],
                'accent_color' => $data['accent_color'],
                'sidebar_collapsed' => $request->boolean('sidebar_collapsed'),
            ]
        );

        return redirect()->route('admin.preferences.edit')
            ->with('success', 'Preferensi tampilan berhasil disimpan untuk akun Anda.');
    }
}
