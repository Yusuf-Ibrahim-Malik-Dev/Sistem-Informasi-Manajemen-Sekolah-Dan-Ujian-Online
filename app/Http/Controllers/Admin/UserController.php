<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Level;
use App\Models\Menu;
use App\Models\UserMenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $admins = Admin::with('level')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->q . '%')
                    ->orWhere('nama', 'like', '%' . $request->q . '%');
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.user.form', [
            'admin' => new Admin(['level' => 'admin']),
            'levels' => Level::orderBy('keterangan')->get(),
            'menus' => Menu::whereNotNull('url')->where('url', '!=', '#')->orderBy('urutan')->get(),
            'customMenuAccess' => false,
            'selectedMenus' => [],
            'menuActions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:user,username'],
            'nama' => ['required', 'string', 'max:150'],
            'level' => ['required', 'exists:user_level,level'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['ts'] = now();
        $data['opsi1'] = '';
        $data['opsi2'] = '';

        $admin = Admin::create($data);
        $this->syncPersonalMenuAccess($request, $admin);

        return redirect()->route('admin.user.index')->with('success', 'User administrator berhasil ditambahkan.');
    }

    public function edit(Admin $admin)
    {
        $personalAccess = $admin->personalMenuAccesses()->get()->keyBy('kode_menu');

        return view('admin.user.form', [
            'admin' => $admin,
            'levels' => Level::orderBy('keterangan')->get(),
            'menus' => Menu::whereNotNull('url')->where('url', '!=', '#')->orderBy('urutan')->get(),
            'customMenuAccess' => $personalAccess->isNotEmpty(),
            'selectedMenus' => $personalAccess->where('is_allowed', true)->keys()->all(),
            'menuActions' => $personalAccess->map(fn (UserMenuAccess $access) => [
                'add' => $access->can_add,
                'edit' => $access->can_edit,
                'delete' => $access->can_delete,
                'approve' => $access->can_approve,
            ])->all(),
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:user,username,' . $admin->id . ',id'],
            'nama' => ['required', 'string', 'max:150'],
            'level' => ['required', 'exists:user_level,level'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);
        $this->syncPersonalMenuAccess($request, $admin);

        return redirect()->route('admin.user.index')->with('success', 'User administrator berhasil diperbarui.');
    }

    public function destroy(Request $request, Admin $admin)
    {
        if ($admin->id === $request->user('web')->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($admin->level === 'admin' && Admin::where('level', 'admin')->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu administrator.');
        }

        ActivityLog::record($request->user('web')->username, 'Menghapus user administrator ' . $admin->username);
        $admin->delete();

        return back()->with('success', 'User administrator berhasil dihapus.');
    }

    /**
     * Simpan override menu per akun. Tanpa override, hak akses tetap mengikuti level.
     */
    private function syncPersonalMenuAccess(Request $request, Admin $admin): void
    {
        if (! $request->boolean('customize_menu_access')) {
            $admin->personalMenuAccesses()->delete();

            return;
        }

        $allowedMenus = $request->input('menu_access', []);
        $addMenus = $request->input('menu_add', []);
        $editMenus = $request->input('menu_edit', []);
        $deleteMenus = $request->input('menu_delete', []);
        $approveMenus = $request->input('menu_approve', []);
        $validCodes = Menu::pluck('kode_menu')->all();

        foreach ($validCodes as $kodeMenu) {
            $allowed = in_array($kodeMenu, $allowedMenus, true);

            UserMenuAccess::updateOrCreate(
                ['user_id' => $admin->id, 'kode_menu' => $kodeMenu],
                [
                    'is_allowed' => $allowed,
                    'can_add' => $allowed && in_array($kodeMenu, $addMenus, true),
                    'can_edit' => $allowed && in_array($kodeMenu, $editMenus, true),
                    'can_delete' => $allowed && in_array($kodeMenu, $deleteMenus, true),
                    'can_approve' => $allowed && in_array($kodeMenu, $approveMenus, true),
                ]
            );
        }
    }
}
