@extends('layouts.admin')

@section('title', $admin->exists ? 'Edit User' : 'Tambah User')
@section('heading', $admin->exists ? 'Edit User Administrator' : 'Tambah User Administrator')
@section('eyebrow', 'Sistem')

@section('content')
    <form method="POST" action="{{ $admin->exists ? route('admin.user.update', $admin) : route('admin.user.store') }}" data-aos="fade-up">
        @csrf
        @if ($admin->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Informasi Akun</h3>
                            <p class="section-sub">Level menentukan menu yang dapat diakses.</p>
                        </div>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $admin->nama) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $admin->username) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-select" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->level }}" @selected(old('level', $admin->level) === $level->level)>{{ $level->keterangan }} ({{ $level->level }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan', $admin->keterangan) }}" placeholder="Opsional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password {{ $admin->exists ? '(kosongkan jika tetap)' : '' }}</label>
                            <input type="password" name="password" class="form-control" @required(! $admin->exists)>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" @required(! $admin->exists)>
                        </div>
                    </div>
                </div>

                <div class="glass-card card-pad mt-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Hak Akses Khusus Akun</h3>
                            <p class="section-sub">Atur menu yang berbeda dari role. Contoh: Guru A dan Guru B bisa memiliki menu berbeda.</p>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="customize_menu_access" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="customize_menu_access"
                               name="customize_menu_access" value="1" @checked(old('customize_menu_access', $customMenuAccess))>
                        <label class="form-check-label fw-semibold" for="customize_menu_access">Aktifkan hak akses khusus untuk akun ini</label>
                    </div>

                    @php
                        $selectedAdd = old('menu_add', collect($menuActions)->filter(fn ($a) => $a['add'])->keys()->all());
                        $selectedEdit = old('menu_edit', collect($menuActions)->filter(fn ($a) => $a['edit'])->keys()->all());
                        $selectedDelete = old('menu_delete', collect($menuActions)->filter(fn ($a) => $a['delete'])->keys()->all());
                        $selectedApprove = old('menu_approve', collect($menuActions)->filter(fn ($a) => $a['approve'])->keys()->all());
                    @endphp
                    <div class="row g-2" id="personalMenuAccess">
                        @foreach ($menus as $menu)
                            <div class="col-md-6 col-xl-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <label class="d-flex align-items-center gap-2 mb-2">
                                        <input type="checkbox" class="form-check-input mt-0" name="menu_access[]"
                                               value="{{ $menu->kode_menu }}"
                                               @checked(in_array($menu->kode_menu, old('menu_access', $selectedMenus), true))>
                                        <span><i class="bi {{ $menu->icon }} me-1"></i>{{ $menu->nama_menu }}</span>
                                    </label>
                                    <div class="d-flex flex-wrap gap-2" style="font-size:.72rem">
                                        <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_add[]" value="{{ $menu->kode_menu }}" @checked(in_array($menu->kode_menu, $selectedAdd, true))> Tambah</label>
                                        <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_edit[]" value="{{ $menu->kode_menu }}" @checked(in_array($menu->kode_menu, $selectedEdit, true))> Ubah</label>
                                        <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_delete[]" value="{{ $menu->kode_menu }}" @checked(in_array($menu->kode_menu, $selectedDelete, true))> Hapus</label>
                                        <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_approve[]" value="{{ $menu->kode_menu }}" @checked(in_array($menu->kode_menu, $selectedApprove, true))> Setujui</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-muted-2 small mt-3">Jika fitur khusus dimatikan, sistem kembali menggunakan hak akses berdasarkan level. Administrator utama tetap memiliki akses penuh.</div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card card-pad">
                    <button class="btn btn-gradient w-100 mb-2"><i class="bi bi-save me-1"></i> {{ $admin->exists ? 'Simpan Perubahan' : 'Simpan User' }}</button>
                    <a href="{{ route('admin.user.index') }}" class="btn btn-ghost w-100">Batal</a>
                </div>
            </div>
        </div>
    </form>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('customize_menu_access');
    const panel = document.getElementById('personalMenuAccess');
    function updateMenuState() {
        panel.style.opacity = toggle.checked ? '1' : '.45';
        panel.querySelectorAll('input').forEach(function (input) {
            input.disabled = !toggle.checked;
        });
    }
    toggle.addEventListener('change', updateMenuState);
    updateMenuState();
});
</script>
@endpush
@endsection
