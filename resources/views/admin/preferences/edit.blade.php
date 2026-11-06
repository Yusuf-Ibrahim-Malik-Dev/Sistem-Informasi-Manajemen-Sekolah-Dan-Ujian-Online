@extends('layouts.admin')

@section('title', 'Preferensi Tampilan')
@section('eyebrow', 'Personalisasi')
@section('heading', 'Preferensi Tampilan')

@section('content')
<div class="row g-4">
    <div class="col-xl-7">
        <section class="glass-card card-pad">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Tema akun</h2>
                    <p class="section-sub">Pengaturan ini hanya berlaku untuk akun yang sedang login.</p>
                </div>
                <span class="badge-soft success"><i class="bi bi-database-check"></i> Tersimpan di database</span>
            </div>

            <form method="POST" action="{{ route('admin.preferences.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label d-block">Mode tampilan</label>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="theme-choice w-100">
                                <input type="radio" name="theme_mode" value="light" @checked(old('theme_mode', $preference->theme_mode) === 'light')>
                                <span class="theme-preview theme-preview-light"><i class="bi bi-sun"></i><strong>Light mode</strong><small>Tampilan terang dan bersih</small></span>
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <label class="theme-choice w-100">
                                <input type="radio" name="theme_mode" value="dark" @checked(old('theme_mode', $preference->theme_mode) === 'dark')>
                                <span class="theme-preview theme-preview-dark"><i class="bi bi-moon-stars"></i><strong>Dark mode</strong><small>Tampilan gelap yang nyaman</small></span>
                            </label>
                        </div>
                    </div>
                    @error('theme_mode')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="primary_color" class="form-label">Warna utama</label>
                        <div class="d-flex align-items-center gap-3">
                            <input id="primary_color" type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $preference->primary_color) }}">
                            <span class="text-muted-2 small">Tombol, menu aktif, dan identitas aplikasi</span>
                        </div>
                        @error('primary_color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="accent_color" class="form-label">Warna aksen</label>
                        <div class="d-flex align-items-center gap-3">
                            <input id="accent_color" type="color" name="accent_color" class="form-control form-control-color" value="{{ old('accent_color', $preference->accent_color) }}">
                            <span class="text-muted-2 small">Aksen kartu dan elemen pendukung</span>
                        </div>
                        @error('accent_color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="sidebar_collapsed" name="sidebar_collapsed" value="1" @checked(old('sidebar_collapsed', $preference->sidebar_collapsed))>
                    <label class="form-check-label fw-semibold" for="sidebar_collapsed">Gunakan sidebar ringkas</label>
                    <div class="text-muted-2 small">Preferensi disimpan dan digunakan saat membuka halaman berikutnya.</div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-gradient"><i class="bi bi-check2-circle me-1"></i> Simpan tampilan</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Kembali ke dashboard</a>
                </div>
            </form>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="glass-card card-pad h-100">
            <h2 class="section-title mb-1">Pratinjau warna</h2>
            <p class="section-sub mb-4">Contoh kombinasi warna yang sedang dipilih.</p>
            <div class="theme-live-preview">
                <div class="theme-live-sidebar" id="previewSidebar">
                    <div class="theme-live-logo"></div>
                    <div class="theme-live-line"></div>
                    <div class="theme-live-line short"></div>
                    <div class="theme-live-active" id="previewPrimary"></div>
                    <div class="theme-live-line"></div>
                    <div class="theme-live-line short"></div>
                </div>
                <div class="theme-live-content">
                    <div class="theme-live-top"></div>
                    <div class="theme-live-cards">
                        <div></div><div></div><div></div>
                    </div>
                    <div class="theme-live-panel" id="previewAccent"></div>
                </div>
            </div>
            <div class="small text-muted-2 mt-3">Warna akan diterapkan ke seluruh halaman panel setelah disimpan.</div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const primary = document.getElementById('primary_color');
    const accent = document.getElementById('accent_color');
    const primaryPreview = document.getElementById('previewPrimary');
    const accentPreview = document.getElementById('previewAccent');

    function refreshPreview() {
        primaryPreview.style.background = primary.value;
        accentPreview.style.background = accent.value;
    }

    primary.addEventListener('input', refreshPreview);
    accent.addEventListener('input', refreshPreview);
    refreshPreview();
});
</script>
@endpush
