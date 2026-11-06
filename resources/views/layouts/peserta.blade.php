@php
    $pesertaAktif = auth('peserta')->user();
    $namaCbt = \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online');
    $adaRanking = \App\Models\Tes::where('tes_ranking_to_users', true)
        ->whereHas('groups', fn ($q) => $q->where('grup_id', $pesertaAktif->user_grup_id))
        ->whereHas('tesUsers', fn ($q) => $q
            ->where('tesuser_user_id', $pesertaAktif->user_id)
            ->where('tesuser_status', \App\Models\TesUser::STATUS_SELESAI))
        ->exists();
    $kelasAkhir = $pesertaAktif->grup
        && (int) $pesertaAktif->grup->grup_tingkat === count(\App\Models\UserGroup::tingkatOptions($pesertaAktif->grup->grup_jenjang));
    $menuAktif = [
        'dashboard' => request()->routeIs('peserta.dashboard'),
        'absensi' => request()->routeIs('peserta.absensi.*'),
        'prestasi' => request()->routeIs('peserta.prestasi.*'),
        'raport' => request()->routeIs('peserta.raport.*'),
        'kelulusan' => request()->routeIs('peserta.kelulusan.*'),
        'ranking' => request()->routeIs('peserta.ranking.*'),
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ $namaCbt }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
<div class="app-shell" x-data="{ sidebarOpen: false }">
    <aside class="sidebar" :class="sidebarOpen ? 'sidebar-open' : ''">
        <div class="brand">
            <div class="brand-mark"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <div class="brand-title">{{ $namaCbt }}</div>
                <div class="brand-sub">Portal Peserta</div>
            </div>
        </div>

        <div class="sidebar-scroll">
            <div class="sidebar-label">Menu Peserta</div>
            <nav class="d-flex flex-column gap-1">
                <a href="{{ route('peserta.dashboard') }}" class="side-link {{ $menuAktif['dashboard'] ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('peserta.absensi.index') }}" class="side-link {{ $menuAktif['absensi'] ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i>
                    <span>Absensi</span>
                </a>
                <a href="{{ route('peserta.prestasi.index') }}" class="side-link {{ $menuAktif['prestasi'] ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Prestasi</span>
                </a>
                <a href="{{ route('peserta.raport.index') }}" class="side-link {{ $menuAktif['raport'] ? 'active' : '' }}">
                    <i class="bi bi-journal-check"></i>
                    <span>Raport</span>
                </a>
                @if ($kelasAkhir)
                    <a href="{{ route('peserta.kelulusan.index') }}" class="side-link {{ $menuAktif['kelulusan'] ? 'active' : '' }}">
                        <i class="bi bi-award"></i>
                        <span>Kelulusan</span>
                    </a>
                @endif
                @if ($adaRanking)
                    <a href="{{ route('peserta.ranking.index') }}" class="side-link {{ $menuAktif['ranking'] ? 'active' : '' }}">
                        <i class="bi bi-trophy"></i>
                        <span>Ranking</span>
                    </a>
                @endif
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar">{{ strtoupper(substr($pesertaAktif->nama ?? 'P', 0, 1)) }}</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.85rem">{{ $pesertaAktif->nama }}</div>
                    <div class="text-truncate" style="font-size:.72rem;color:#94a3b8">{{ $pesertaAktif->grup?->grup_nama ?? '-' }}</div>
                </div>
                <form method="POST" action="{{ route('peserta.logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.1);color:#e2e8f0" title="Keluar">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="sidebar-backdrop" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

    <main class="main-content">
        <header class="topbar">
            <button type="button" class="icon-btn d-lg-none" @click="sidebarOpen = true" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>

            <div class="flex-grow-1 min-w-0">
                <div class="page-eyebrow">Portal Peserta</div>
                <h1 class="page-title text-truncate">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2 text-muted-2" style="font-size:.8rem">
                <i class="bi bi-calendar3"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>

            <div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="icon-btn" @click="open = !open" aria-label="Menu peserta">
                    <i class="bi bi-person-circle"></i>
                </button>
                <div class="glass-card position-absolute end-0 mt-2 p-2" style="width:230px;z-index:1050" x-show="open" x-cloak x-transition>
                    <div class="px-2 py-1">
                        <div class="fw-bold" style="font-size:.86rem">{{ $pesertaAktif->nama }}</div>
                        <div class="text-muted-2" style="font-size:.74rem">{{ $pesertaAktif->user_name }} &middot; {{ $pesertaAktif->grup?->grup_nama ?? '-' }}</div>
                    </div>
                    <hr class="my-1">
                    <a href="{{ route('peserta.dashboard') }}" class="side-link" style="color:#334155">
                        <i class="bi bi-grid" style="color:#6366f1"></i> Dashboard
                    </a>
                    <a href="{{ route('peserta.absensi.index') }}" class="side-link" style="color:#334155">
                        <i class="bi bi-geo-alt" style="color:#0ea5e9"></i> Absensi
                    </a>
                    <a href="{{ route('peserta.prestasi.index') }}" class="side-link" style="color:#334155">
                        <i class="bi bi-graph-up-arrow" style="color:#0ea5e9"></i> Prestasi
                    </a>
                    <a href="{{ route('peserta.raport.index') }}" class="side-link" style="color:#334155">
                        <i class="bi bi-journal-check" style="color:#10b981"></i> Raport
                    </a>
                    @if ($kelasAkhir)
                        <a href="{{ route('peserta.kelulusan.index') }}" class="side-link" style="color:#334155">
                            <i class="bi bi-award" style="color:#d97706"></i> Kelulusan
                        </a>
                    @endif
                    @if ($adaRanking)
                        <a href="{{ route('peserta.ranking.index') }}" class="side-link" style="color:#334155">
                            <i class="bi bi-trophy" style="color:#d97706"></i> Ranking
                        </a>
                    @endif
                    <form method="POST" action="{{ route('peserta.logout') }}">
                        @csrf
                        <button type="submit" class="side-link w-100 border-0 bg-transparent text-start" style="color:#b91c1c">
                            <i class="bi bi-box-arrow-right" style="color:#ef4444"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="content-wrap">
            @include('partials.alerts')
            @yield('content')
        </div>
    </main>
</div>
@include('partials.url-mask')
@stack('scripts')
</body>
</html>
