@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('eyebrow', 'Ringkasan Aktivitas')

@section('content')
    <div class="hero-card mb-4" data-aos="fade-up">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index:1">
            <div>
                <div class="text-uppercase fw-semibold mb-2" style="letter-spacing:.14em;font-size:.72rem;color:#c7d2fe">Selamat datang kembali</div>
                <h2 class="fw-800 mb-2" style="font-size:1.7rem;letter-spacing:-.03em">{{ auth('web')->user()->nama }}</h2>
                <p class="mb-0" style="color:#dbeafe;max-width:560px">Pantau pelaksanaan ujian, kelola bank soal, dan analisis hasil peserta dalam satu panel yang terintegrasi.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tes.create') }}" class="btn btn-light fw-semibold"><i class="bi bi-plus-lg me-1"></i> Buat Tes</a>
                <a href="{{ route('admin.soal.create') }}" class="btn btn-outline-light fw-semibold"><i class="bi bi-patch-plus me-1"></i> Tambah Soal</a>
            </div>
        </div>
    </div>

    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-1"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ number_format($stats['peserta']) }}</div>
            <div class="stat-label">Total Peserta</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-2"><i class="bi bi-patch-question"></i></div>
            <div class="stat-value">{{ number_format($stats['soal']) }}</div>
            <div class="stat-label">Bank Soal</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-3"><i class="bi bi-journal-text"></i></div>
            <div class="stat-value">{{ number_format($stats['tes']) }}</div>
            <div class="stat-label">Total Tes</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="160">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value">{{ number_format($stats['selesai']) }}</div>
            <div class="stat-label">Ujian Selesai</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-icon bg-grad-5"><i class="bi bi-graph-up"></i></div>
            <div class="stat-value">{{ number_format($nilaiRata, 1) }}</div>
            <div class="stat-label">Rata-rata Nilai</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up">
            <div class="glass-card card-pad h-100">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Aktivitas Ujian 7 Hari Terakhir</h3>
                        <p class="section-sub">Jumlah percobaan ujian yang dibuat setiap hari.</p>
                    </div>
                    <span class="badge-soft"><i class="bi bi-activity"></i> Live</span>
                </div>
                <div style="height:280px">
                    <canvas id="attemptChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="80">
            <div class="glass-card card-pad h-100">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tes Berlangsung</h3>
                        <p class="section-sub">Tes yang sedang dalam periode pelaksanaan.</p>
                    </div>
                </div>

                @forelse ($tesAktif as $item)
                    <div class="d-flex align-items-start gap-3 py-2 border-bottom" style="border-color:var(--line)!important">
                        <div class="stat-icon bg-grad-6 mb-0" style="width:38px;height:38px;font-size:.95rem"><i class="bi bi-journal-check"></i></div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-truncate" style="font-size:.86rem">{{ $item->tes_nama }}</div>
                            <div class="text-muted-2" style="font-size:.74rem">
                                {{ $item->tes_duration_time }} menit &middot; {{ $item->tes_users_count }} peserta
                            </div>
                        </div>
                        <a href="{{ route('admin.hasil.index', ['tes' => $item->tes_id]) }}" class="btn btn-soft btn-sm">Lihat</a>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        Belum ada tes yang berlangsung.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-12" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Hasil Ujian Terbaru</h3>
                        <p class="section-sub">Peserta yang baru menyelesaikan ujian.</p>
                    </div>
                    <a href="{{ route('admin.hasil.index') }}" class="btn btn-ghost btn-sm">Lihat semua <i class="bi bi-arrow-right ms-1"></i></a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Peserta</th>
                                <th>Tes</th>
                                <th>Group</th>
                                <th>Nilai</th>
                                <th>Waktu</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($terbaru as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar" style="width:34px;height:34px;font-size:.8rem">{{ strtoupper(substr($item->peserta?->nama ?? '?', 0, 1)) }}</div>
                                            <div>
                                                <div class="fw-semibold">{{ $item->peserta?->nama ?? '-' }}</div>
                                                <div class="text-muted-2" style="font-size:.74rem">{{ $item->peserta?->user_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->tes?->tes_nama ?? '-' }}</td>
                                    <td><span class="badge-soft neutral">{{ $item->peserta?->grup?->grup_nama ?? '-' }}</span></td>
                                    <td class="fw-bold">{{ number_format((float) $item->total_nilai, 1) }}</td>
                                    <td class="text-muted-2" style="font-size:.8rem">{{ $item->tesuser_creation_time?->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.hasil.detail', $item) }}" class="btn btn-soft btn-icon"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            Belum ada hasil ujian.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('attemptChart');
        if (!canvas || !window.Chart) return;

        const labels = @json($chart->pluck('label'));
        const values = @json($chart->pluck('total'));
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(99, 102, 241, .35)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Percobaan Ujian',
                    data: values,
                    borderColor: '#6366f1',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: .42,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 10,
                        displayColors: false,
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#94a3b8', font: { size: 11 }, precision: 0 },
                        grid: { color: 'rgba(15, 23, 42, .06)' }
                    }
                }
            }
        });
    });
</script>
@endpush
