@extends('layouts.admin')

@section('title', 'Rekap Hasil Tes')
@section('heading', 'Rekap Hasil Tes')
@section('eyebrow', 'Laporan')

@section('content')
    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Filter Rekap</h3>
                <p class="section-sub">Ringkasan nilai peserta yang telah menyelesaikan ujian.</p>
            </div>
            <a href="{{ route('admin.laporan.analisis', request()->only('tes')) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pie-chart me-1"></i> Analisis Butir Soal</a>
        </div>

        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="tes" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua tes</option>
                    @foreach ($tesList as $item)
                        <option value="{{ $item->tes_id }}" @selected(request('tes') == $item->tes_id)>{{ $item->tes_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="grup" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua group</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->grup_id }}" @selected(request('grup') == $grup->grup_id)>{{ $grup->grup_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.laporan.rekap') }}" class="btn btn-ghost w-100"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Rekap per Tes</h3>
                        <p class="section-sub">{{ $rekap->count() }} tes memiliki hasil.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Tes</th>
                                <th>Peserta</th>
                                <th>Rata-rata</th>
                                <th>Tertinggi</th>
                                <th>Terendah</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekap as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row['tes']?->tes_nama ?? '-' }}</td>
                                    <td><span class="badge-soft neutral">{{ $row['peserta'] }} peserta</span></td>
                                    <td><span class="fw-800" style="color:#4338ca">{{ number_format($row['rata'], 2) }}</span></td>
                                    <td class="text-success fw-semibold">{{ number_format($row['maks'], 2) }}</td>
                                    <td class="text-danger fw-semibold">{{ number_format($row['min'], 2) }}</td>
                                    <td>{{ number_format($row['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-graph-down"></i>
                                            Belum ada hasil ujian pada filter ini.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad h-100">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Perbandingan Rata-rata</h3>
                        <p class="section-sub">Rata-rata nilai per tes.</p>
                    </div>
                </div>
                @if ($rekap->isEmpty())
                    <div class="empty-state"><i class="bi bi-bar-chart"></i> Belum ada data.</div>
                @else
                    <div style="height:320px"><canvas id="rekapChart"></canvas></div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@if ($rekap->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('rekapChart');
        if (!canvas || !window.Chart) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: @json($rekap->pluck('tes.tes_nama')),
                datasets: [{
                    data: @json($rekap->pluck('rata')),
                    backgroundColor: ['#6366f1', '#06b6d4', '#8b5cf6', '#10b981', '#f59e0b', '#ec4899'],
                    borderRadius: 8,
                    maxBarThickness: 34,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,.06)' }, ticks: { color: '#64748b', font: { size: 10 } } }
                }
            }
        });
    });
</script>
@endif
@endpush
