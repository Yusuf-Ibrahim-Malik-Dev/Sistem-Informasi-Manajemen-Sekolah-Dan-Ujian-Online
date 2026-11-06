@extends('layouts.admin')

@section('title', 'Level & Hak Akses')
@section('heading', 'Level & Hak Akses')
@section('eyebrow', 'Sistem')

@php
    $parents = $menus->whereNull('parent')->sortBy('urutan');
    $children = $menus->whereNotNull('parent');
@endphp

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad mb-4">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tambah Level</h3>
                        <p class="section-sub">Buat level akses baru untuk operator.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.level.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kode Level</label>
                        <input type="text" name="level" class="form-control" value="{{ old('level') }}" placeholder="Contoh: pengawas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="Nama tampilan level">
                    </div>
                    <button class="btn btn-gradient w-100"><i class="bi bi-plus-lg me-1"></i> Simpan Level</button>
                </form>
            </div>

            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Daftar Level</h3>
                        <p class="section-sub">{{ $levels->count() }} level terdaftar.</p>
                    </div>
                </div>

                @foreach ($levels as $level)
                    <div class="d-flex align-items-center justify-content-between gap-2 py-2 border-bottom" style="border-color:var(--line)!important">
                        <div>
                            <div class="fw-semibold" style="font-size:.86rem">{{ $level->keterangan ?: $level->level }}</div>
                            <div class="text-muted-2" style="font-size:.73rem"><code>{{ $level->level }}</code> &middot; {{ $level->admins_count }} user</div>
                        </div>
                        @if ($level->level !== 'admin')
                            <form method="POST" action="{{ route('admin.level.destroy', $level) }}" onsubmit="return confirm('Hapus level ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            @foreach ($levels as $level)
                @php
                    $accessMap = collect($access[$level->level] ?? [])->keyBy('kode_menu');
                    $checked = $accessMap->keys()->all();
                @endphp
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">{{ $level->keterangan ?: $level->level }}</h3>
                            <p class="section-sub">Atur menu yang dapat diakses level <code>{{ $level->level }}</code>.</p>
                        </div>
                        @if ($level->level === 'admin')
                            <span class="badge-soft info"><i class="bi bi-shield-check"></i> Akses penuh</span>
                        @else
                            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleAll(this)">Pilih semua</button>
                        @endif
                    </div>

                    @if ($level->level === 'admin')
                        <div class="p-3" style="border-radius:14px;background:rgba(6,182,212,.07);border:1px solid rgba(6,182,212,.16);font-size:.82rem;color:#155e75">
                            <i class="bi bi-info-circle me-1"></i> Level administrator otomatis memiliki akses ke seluruh menu.
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.level.update', $level) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                @foreach ($parents as $parent)
                                    @php
                                        $items = $children->where('parent', $parent->kode_menu)->sortBy('urutan');
                                        $direct = $items->isEmpty() && $parent->url !== '#';
                                    @endphp

                                    @continue($parent->kode_menu === 'dashboard')

                                    @if ($items->isNotEmpty() || $direct)
                                        <div class="col-md-6">
                                            <div class="p-3 h-100" style="border-radius:14px;border:1px solid var(--line);background:#f8fafc">
                                                <div class="fw-bold mb-2" style="font-size:.8rem"><i class="bi {{ $parent->icon }} me-1 text-primary"></i> {{ $parent->nama_menu }}</div>

                                                @if ($direct)
                                                    @php $row = $accessMap->get($parent->kode_menu); @endphp
                                                    <div class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="menus[]" value="{{ $parent->kode_menu }}" id="menu-{{ $level->level }}-{{ $parent->kode_menu }}" @checked(in_array($parent->kode_menu, $checked))>
                                                            <label class="form-check-label" for="menu-{{ $level->level }}-{{ $parent->kode_menu }}" style="font-size:.82rem">{{ $parent->nama_menu }}</label>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2 ps-4" style="font-size:.7rem">
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_add[]" value="{{ $parent->kode_menu }}" @checked($row?->add)> Tambah</label>
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_edit[]" value="{{ $parent->kode_menu }}" @checked($row?->edit)> Ubah</label>
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_delete[]" value="{{ $parent->kode_menu }}" @checked($row?->can_delete)> Hapus</label>
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_approve[]" value="{{ $parent->kode_menu }}" @checked($row?->can_approve)> Setujui</label>
                                                        </div>
                                                    </div>
                                                @endif

                                                @foreach ($items as $item)
                                                    @php $row = $accessMap->get($item->kode_menu); @endphp
                                                    <div class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="menus[]" value="{{ $item->kode_menu }}" id="menu-{{ $level->level }}-{{ $item->kode_menu }}" @checked(in_array($item->kode_menu, $checked))>
                                                            <label class="form-check-label" for="menu-{{ $level->level }}-{{ $item->kode_menu }}" style="font-size:.82rem">{{ $item->nama_menu }}</label>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2 ps-4" style="font-size:.7rem">
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_add[]" value="{{ $item->kode_menu }}" @checked($row?->add)> Tambah</label>
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_edit[]" value="{{ $item->kode_menu }}" @checked($row?->edit)> Ubah</label>
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_delete[]" value="{{ $item->kode_menu }}" @checked($row?->can_delete)> Hapus</label>
                                                            <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="menu_approve[]" value="{{ $item->kode_menu }}" @checked($row?->can_approve)> Setujui</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <button class="btn btn-gradient mt-4"><i class="bi bi-save me-1"></i> Simpan Hak Akses</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleAll(button) {
        const form = button.closest('.glass-card').querySelector('form');
        if (!form) return;
        const boxes = form.querySelectorAll('input[name="menus[]"]');
        const allChecked = Array.from(boxes).every((box) => box.checked);
        boxes.forEach((box) => { box.checked = !allChecked; });
        button.textContent = allChecked ? 'Pilih semua' : 'Kosongkan';
    }
</script>
@endpush
