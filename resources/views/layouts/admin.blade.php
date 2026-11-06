@php
    $currentAdmin = auth('web')->user();
    $allMenus = \App\Models\Menu::orderBy('urutan')->get();
    $parents = $allMenus->whereNull('parent')->sortBy('urutan');
    $namaCbt = \App\Models\Konfigurasi::get('cbt_nama', 'Sistem Informasi Sekolah');
    $currentPreference = $currentAdmin?->preference;
    $themeMode = $currentPreference?->theme_mode ?? 'light';
    $primaryColor = $currentPreference?->primary_color ?? '#6366f1';
    $accentColor = $currentPreference?->accent_color ?? '#06b6d4';
    $sidebarCollapsed = $currentPreference?->sidebar_collapsed ?? false;
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
<body data-theme="{{ $themeMode }}" style="--brand-1: {{ $primaryColor }}; --brand-3: {{ $accentColor }};">
<div class="app-shell {{ $sidebarCollapsed ? 'sidebar-collapsed' : '' }}" x-data="{ sidebarOpen: false }">
    <aside class="sidebar" :class="sidebarOpen ? 'sidebar-open' : ''">
        <div class="brand">
            <div class="brand-mark"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <div class="brand-title">{{ $namaCbt }}</div>
                <div class="brand-sub">School Management</div>
            </div>
        </div>

        <div class="sidebar-scroll">
            <div class="sidebar-label">Menu Utama</div>
            <nav class="d-flex flex-column gap-1">
                @foreach ($parents as $parent)
                    @php
                        $children = $allMenus->where('parent', $parent->kode_menu)->sortBy('urutan');
                        $visibleChildren = $children->filter(fn ($child) => $currentAdmin->hasAccess($child->kode_menu));
                    @endphp

                    @if ($children->isNotEmpty())
                        @continue($visibleChildren->isEmpty())
                        @php
                            $groupActive = $visibleChildren->contains(fn ($child) => request()->routeIs($child->url));
                        @endphp
                        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
                            <button type="button" class="side-link side-group-toggle" @click="open = !open" :aria-expanded="open.toString()">
                                <i class="bi {{ $parent->icon }}"></i>
                                <span>{{ $parent->nama_menu }}</span>
                                <i class="bi bi-chevron-right chev"></i>
                            </button>
                            <div class="side-sub" x-show="open" x-transition.opacity.duration.200ms>
                                @foreach ($visibleChildren as $child)
                                    <a href="{{ route($child->url) }}" class="side-link {{ request()->routeIs($child->url) ? 'active' : '' }}">
                                        <i class="bi {{ $child->icon }}"></i>
                                        <span>{{ $child->nama_menu }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php
                            $allowed = $parent->kode_menu === 'dashboard' || $currentAdmin->hasAccess($parent->kode_menu);
                            $isActive = match ($parent->kode_menu) {
                                'laporan' => request()->routeIs('admin.laporan.*'),
                                'guru' => request()->routeIs('admin.guru.*'),
                                default => request()->routeIs($parent->url),
                            };
                        @endphp
                        @if ($allowed && $parent->url !== '#')
                            <a href="{{ route($parent->url) }}" class="side-link {{ $isActive ? 'active' : '' }}">
                                <i class="bi {{ $parent->icon }}"></i>
                                <span>{{ $parent->nama_menu }}</span>
                            </a>
                        @endif
                    @endif
                @endforeach
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar">{{ strtoupper(substr($currentAdmin->nama ?? 'A', 0, 1)) }}</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.85rem">{{ $currentAdmin->nama }}</div>
                    <div class="text-truncate" style="font-size:.72rem;color:#94a3b8">{{ $currentAdmin->level }}</div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
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
                <div class="page-eyebrow">@yield('eyebrow', 'Panel Sekolah')</div>
                <h1 class="page-title text-truncate">@yield('heading', 'Dashboard')</h1>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2 text-muted-2" style="font-size:.8rem">
                <i class="bi bi-calendar3"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>

            @if (isset($schoolOptions) && $schoolOptions->isNotEmpty())
                <form method="POST" action="{{ route('admin.sekolah.switch') }}" class="d-none d-md-block">
                    @csrf
                    <select name="school_id" class="form-select form-select-sm" style="min-width:220px" onchange="this.form.submit()" aria-label="Pilih konteks sekolah">
                        @unless ($schoolContext->admin()?->isSchoolScope())
                            <option value="0" @selected(! $activeSchool)>Semua sekolah</option>
                        @endunless
                        @foreach ($schoolOptions as $option)
                            <option value="{{ $option->id }}" @selected($activeSchool?->id === $option->id)>{{ $option->school_name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <div class="position-relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="icon-btn" @click="open = !open" aria-label="Menu pengguna">
                    <i class="bi bi-person-circle"></i>
                </button>
                <div class="glass-card position-absolute end-0 mt-2 p-2" style="width:220px;z-index:1050" x-show="open" x-cloak x-transition>
                    <div class="px-2 py-1">
                        <div class="fw-bold" style="font-size:.86rem">{{ $currentAdmin->nama }}</div>
                        <div class="text-muted-2" style="font-size:.74rem">{{ $currentAdmin->username }}</div>
                    </div>
                    <hr class="my-1">
                    <a href="{{ route('admin.dashboard') }}" class="side-link" style="color:#334155">
                        <i class="bi bi-speedometer2" style="color:#6366f1"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.preferences.edit') }}" class="side-link" style="color:#334155">
                        <i class="bi bi-palette" style="color:var(--brand-1)"></i> Preferensi Tampilan
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
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
