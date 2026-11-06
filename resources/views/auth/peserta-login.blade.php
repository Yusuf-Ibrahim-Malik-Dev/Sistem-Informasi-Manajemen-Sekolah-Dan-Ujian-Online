@php
    $namaCbt = $namaCbt ?? \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online');
    $keterangan = $keterangan ?? \App\Models\Konfigurasi::get('cbt_keterangan', 'Ujian Online Berbasis Komputer');
    $linkOperator = $linkOperator ?? true;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Peserta &middot; {{ $namaCbt }}</title>
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
                    <div class="brand-sub">Portal Peserta Ujian</div>
                </div>
            </div>

            <h1 class="auth-title">Siap mengerjakan ujian hari ini?</h1>
            <p class="auth-desc">{{ $keterangan }}. Masuk dengan akun peserta Anda, pilih tes yang tersedia, dan kerjakan dengan tenang.</p>

            <div class="auth-feature"><i class="bi bi-clock-history"></i><span>Timer otomatis dan penyimpanan jawaban secara berkala.</span></div>
            <div class="auth-feature"><i class="bi bi-list-check"></i><span>Navigasi soal yang jelas, tandai soal ragu-ragu, dan lanjutkan kapan saja.</span></div>
            <div class="auth-feature"><i class="bi bi-graph-up"></i><span>Lihat nilai dan rincian jawaban setelah ujian selesai.</span></div>
        </div>

        <div class="position-relative" style="z-index:1;font-size:.78rem;color:#94a3b8">
            &copy; {{ now()->year }} {{ $namaCbt }}. Seluruh hak cipta dilindungi.
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card" data-aos="fade-up">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge-soft"><i class="bi bi-person-badge"></i> Portal Peserta</span>
            </div>
            <h2 class="mb-1">Masuk ke Ujian</h2>
            <p class="text-muted-2 mb-4" style="font-size:.86rem">Gunakan username dan password yang diberikan operator.</p>

            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('peserta.login.post') }}" x-data="{ show: false }">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username Peserta</label>
                    <div class="position-relative">
                        <input type="text" name="username" class="form-control ps-5" value="{{ old('username') }}" placeholder="Username peserta" required autofocus>
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
                        <a href="{{ route('orangtua.login') }}" style="font-size:.8rem;color:#4f46e5;font-weight:600">Login orang tua</a>
                        @if ($linkOperator)
                            <span class="text-muted-2" style="font-size:.8rem">&middot;</span>
                            <a href="{{ route('admin.login') }}" style="font-size:.8rem;color:#4f46e5;font-weight:600">Login operator</a>
                        @endif
                    </div>
                </div>

                <button class="btn btn-gradient w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Mulai Masuk</button>
            </form>

            <div class="mt-4">
                <div class="text-muted-2 mb-2" style="font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700">Akun Demo</div>
                <div class="d-flex flex-column gap-2">
                    <div class="demo-chip"><span>Peserta XI MM</span><code>lutfi / lutfi</code></div>
                    <div class="demo-chip"><span>Peserta XI TKJ</span><code>joko / joko</code></div>
                </div>
            </div>
        </div>
    </section>
</div>
@include('partials.url-mask')
</body>
</html>
