@php
    $orangTuaAktif = auth('orangtua')->user();
    $namaCbt = \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online');
    $daftarAnak = $daftarAnak ?? $orangTuaAktif->pesertas()->with('grup')->orderBy('user_firstname')->get();
    $anakAktif = $anak ?? $daftarAnak->first();
    $anakQuery = $anakAktif ? ['anak' => $anakAktif->user_id] : [];
    $menuAktif = [
        'dashboard' => request()->routeIs('orangtua.dashboard'),
        'absensi' => request()->routeIs('orangtua.absensi.*'),
        'pembayaran' => request()->routeIs('orangtua.pembayaran.*'),
        'spp' => request()->routeIs('orangtua.spp.*'),
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
            <div class="brand-mark"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="brand-title">{{ $namaCbt }}</div>
                <div class="brand-sub">Portal Orang Tua</div>
            </div>
        </div>

        <div class="sidebar-scroll">
            <div class="sidebar-label">Menu Orang Tua</div>
            <nav class="d-flex flex-column gap-1">
                <a href="{{ route('orangtua.dashboard', $anakQuery) }}" class="side-link {{ $menuAktif['dashboard'] ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('orangtua.absensi.index', $anakQuery) }}" class="side-link {{ $menuAktif['absensi'] ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Absensi Anak</span>
                </a>
                <a href="{{ route('orangtua.pembayaran.index', $anakQuery) }}" class="side-link {{ $menuAktif['pembayaran'] ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Pembayaran Ujian</span>
                </a>
                <a href="{{ route('orangtua.spp.index', $anakQuery) }}" class="side-link {{ $menuAktif['spp'] ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Tagihan SPP</span>
                </a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar">{{ strtoupper(substr($orangTuaAktif->ortu_nama ?? 'O', 0, 1)) }}</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.85rem">{{ $orangTuaAktif->ortu_nama }}</div>
                    <div class="text-truncate" style="font-size:.72rem;color:#94a3b8">{{ $orangTuaAktif->ortu_username }}</div>
                </div>
                <form method="POST" action="{{ route('orangtua.logout') }}">
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
                <div class="page-eyebrow">Portal Orang Tua</div>
                <h1 class="page-title text-truncate">@yield('title', 'Dashboard')</h1>
            </div>

            @if ($daftarAnak->count() > 1)
                <div class="d-none d-md-block">
                    <select class="form-select form-select-sm" style="min-width:190px" onchange="const url = new URL(window.location.href); url.searchParams.set('anak', this.value); window.location = url.toString();">
                        @foreach ($daftarAnak as $pilihan)
                            <option value="{{ $pilihan->user_id }}" @selected($anakAktif && (int) $pilihan->user_id === (int) $anakAktif->user_id)>
                                {{ $pilihan->nama }} &middot; {{ $pilihan->grup?->grup_nama ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="d-none d-lg-flex align-items-center gap-2 text-muted-2" style="font-size:.8rem">
                <i class="bi bi-calendar3"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>

            <div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="icon-btn" @click="open = !open" aria-label="Menu orang tua">
                    <i class="bi bi-person-circle"></i>
                </button>
                <div class="glass-card position-absolute end-0 mt-2 p-2" style="width:230px;z-index:1050" x-show="open" x-cloak x-transition>
                    <div class="px-2 py-1">
                        <div class="fw-bold" style="font-size:.86rem">{{ $orangTuaAktif->ortu_nama }}</div>
                        <div class="text-muted-2" style="font-size:.74rem">{{ $orangTuaAktif->ortu_username }}@if ($anakAktif) &middot; {{ $anakAktif->nama }}@endif</div>
                    </div>
                    <hr class="my-1">
                    <a href="{{ route('orangtua.dashboard', $anakQuery) }}" class="side-link" style="color:#334155">
                        <i class="bi bi-grid" style="color:#6366f1"></i> Dashboard
                    </a>
                    <a href="{{ route('orangtua.absensi.index', $anakQuery) }}" class="side-link" style="color:#334155">
                        <i class="bi bi-calendar-check" style="color:#0ea5e9"></i> Absensi
                    </a>
                    <a href="{{ route('orangtua.pembayaran.index', $anakQuery) }}" class="side-link" style="color:#334155">
                        <i class="bi bi-wallet2" style="color:#10b981"></i> Pembayaran
                    </a>
                    <a href="{{ route('orangtua.spp.index', $anakQuery) }}" class="side-link" style="color:#334155">
                        <i class="bi bi-receipt" style="color:#f59e0b"></i> Tagihan SPP
                    </a>
                    <form method="POST" action="{{ route('orangtua.logout') }}">
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
