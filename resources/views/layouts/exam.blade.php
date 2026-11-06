@php
    $pesertaAktif = auth('peserta')->user();
    $namaCbt = \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ujian') &middot; {{ $namaCbt }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="exam-body">
<header class="exam-topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <div class="brand-mark" style="width:38px;height:38px;font-size:1.05rem"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="min-w-0">
            <div class="fw-800 text-truncate" style="font-size:.92rem">{{ $tes->tes_nama }}</div>
            <div class="text-muted-2 text-truncate" style="font-size:.72rem">{{ $pesertaAktif->nama }} &middot; {{ $pesertaAktif->grup?->grup_nama ?? '-' }}</div>
        </div>
    </div>

    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="badge-soft neutral d-none d-md-inline-flex"><i class="bi bi-list-ol"></i> {{ $attempt->jumlah_soal }} soal</span>
        <div class="timer-pill" id="examTimer" data-seconds="{{ $sisaDetik }}">
            <i class="bi bi-stopwatch"></i>
            <span id="examTimerText">--:--</span>
        </div>
    </div>
</header>

@yield('content')

<form method="POST" action="{{ route('peserta.ujian.selesai', $tes) }}" id="finishExamForm" class="d-none">
    @csrf
</form>

<script>
    (function () {
        const timer = document.getElementById('examTimer');
        if (!timer) return;

        let seconds = parseInt(timer.dataset.seconds || '0', 10);
        const text = document.getElementById('examTimerText');
        let submitted = false;

        function render() {
            if (seconds < 0) seconds = 0;
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            text.textContent = (h > 0 ? String(h).padStart(2, '0') + ':' : '') + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            timer.classList.toggle('danger', seconds <= 300);
        }

        render();

        const interval = window.setInterval(function () {
            seconds -= 1;
            render();

            if (seconds <= 0 && !submitted) {
                submitted = true;
                window.clearInterval(interval);
                document.getElementById('finishExamForm').submit();
            }
        }, 1000);
    })();
</script>
@include('partials.url-mask')
@stack('scripts')
</body>
</html>
