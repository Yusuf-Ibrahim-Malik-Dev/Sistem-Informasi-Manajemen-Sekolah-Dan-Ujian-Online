@php
    $namaCbt = \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online');
    $keterangan = \App\Models\Konfigurasi::get('cbt_keterangan', 'Ujian Online Berbasis Komputer');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Operator &middot; {{ $namaCbt }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="auth-wrap">
    <section class="auth-hero">
        <div class="auth-hero-content">
            <div class="brand" style="padding:0">
                <div class="brand-mark"><i class="bi bi-mortarboard-fill"></i></div>
                <div>
                    <div class="brand-title">{{ $namaCbt }}</div>
                    <div class="brand-sub">Panel Administrator</div>
                </div>
            </div>

            <h1 class="auth-title">Kelola ujian online dengan lebih mudah.</h1>
            <p class="auth-desc">{{ $keterangan }}. Satu panel untuk bank soal, jadwal tes, token, evaluasi, dan analisis hasil peserta.</p>

            <div class="auth-feature"><i class="bi bi-shield-check"></i><span>Keamanan berlapis dengan token ujian dan kontrol akses level.</span></div>
            <div class="auth-feature"><i class="bi bi-graph-up-arrow"></i><span>Analisis butir soal otomatis: tingkat kesulitan dan daya pembeda.</span></div>
            <div class="auth-feature"><i class="bi bi-lightning-charge"></i><span>Antarmuka modern, cepat, dan responsif di semua perangkat.</span></div>
        </div>

        <div class="position-relative" style="z-index:1;font-size:.78rem;color:#94a3b8">
            &copy; {{ now()->year }} {{ $namaCbt }}. Seluruh hak cipta dilindungi.
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card" data-aos="fade-up">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge-soft"><i class="bi bi-shield-lock"></i> Area Terbatas</span>
            </div>
            <h2 class="mb-1">Masuk sebagai Operator</h2>
            <p class="text-muted-2 mb-4" style="font-size:.86rem">Gunakan akun administrator yang telah diberikan.</p>

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" x-data="{ show: false }">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="position-relative">
                        <input type="text" name="username" class="form-control ps-5" value="{{ old('username') }}" placeholder="Username operator" required autofocus>
                        <i class="bi bi-person position-absolute" style="left:16px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="position-relative">
                        <input :type="show ? 'text' : 'password'" name="password" class="form-control ps-5 pe-5" placeholder="Password" required>
                        <i class="bi bi-lock position-absolute" style="left:16px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
                        <button type="button" class="btn position-absolute" style="right:4px;top:50%;transform:translateY(-50%);color:#64748b" @click="show = !show" tabindex="-1">
                            <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:.8rem">Ingat saya</label>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('peserta.login') }}" style="font-size:.8rem;color:#4f46e5;font-weight:600">Login peserta</a>
                        <span class="text-muted-2" style="font-size:.8rem">&middot;</span>
                        <a href="{{ route('orangtua.login') }}" style="font-size:.8rem;color:#4f46e5;font-weight:600">Login orang tua</a>
                    </div>
                </div>

                <button class="btn btn-gradient w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk Panel</button>
            </form>

            <div class="mt-4">
                <div class="text-muted-2 mb-2" style="font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700">Akun Demo</div>
                <div class="d-flex flex-column gap-2">
                    <div class="demo-chip"><span>Administrator</span><code>admin / admin</code></div>
                    <div class="demo-chip"><span>Operator Tes</span><code>operator / operator</code></div>
                </div>
            </div>
        </div>
    </section>
</div>
@include('partials.url-mask')
</body>
</html>
