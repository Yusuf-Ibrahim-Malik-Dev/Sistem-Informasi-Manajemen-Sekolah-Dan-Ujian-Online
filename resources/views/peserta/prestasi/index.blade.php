@extends('layouts.peserta')

@section('title', 'Prestasi')

@section('content')
    <div class="section-head mb-3">
        <div>
            <h3 class="section-title">Prestasi Saya</h3>
            <p class="section-sub">Ringkasan nilai ujianmu dan perbandingan rata-rata per kelas.</p>
        </div>
        <span class="badge-soft info"><i class="bi bi-person-badge"></i> {{ $peserta->grup?->kelas_label ?? 'Belum ada kelas' }}</span>
    </div>

    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-1"><i class="bi bi-journal-check"></i></div>
            <div class="stat-value">{{ $statistik['ujian'] }}</div>
            <div class="stat-label">Ujian Selesai</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-4"><i class="bi bi-graph-up"></i></div>
            <div class="stat-value">{{ $statistik['rata_rata'] !== null ? number_format($statistik['rata_rata'], 1) : '-' }}</div>
            <div class="stat-label">Rata-rata Nilai</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-3"><i class="bi bi-trophy"></i></div>
            <div class="stat-value">{{ $statistik['tertinggi'] !== null ? number_format($statistik['tertinggi'], 1) : '-' }}</div>
            <div class="stat-label">Nilai Tertinggi</div>
        </div>
    </div>

    @include('partials.grafik-prestasi', [
        'chartPrestasi' => $chartPrestasi,
        'chartId' => 'grafikPrestasiPesertaPortal',
        'chartTitle' => 'Prestasi per Jenjang dan Kelas',
        'chartDescription' => 'Rata-rata nilai raport seluruh peserta pada setiap kelas.',
    ])

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Riwayat Nilai Ujian</h3>
                <p class="section-sub">Nilai dari ujian yang sudah kamu selesaikan.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Tes</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-end">Nilai</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->tes?->tes_nama ?? '-' }}</td>
                            <td class="text-muted-2" style="font-size:.8rem">{{ $item->tesuser_creation_time?->translatedFormat('d M Y H:i') }}</td>
                            <td><span class="badge-soft success"><i class="bi bi-check2-circle"></i> Selesai</span></td>
                            <td class="text-end fw-800" style="color:#4338ca">{{ number_format((float) $item->total_nilai, 1) }}</td>
                            <td class="text-end">
                                @if ($item->tes)
                                    <a href="{{ route('peserta.ujian.hasil', $item->tes) }}" class="btn btn-soft btn-sm">Lihat Hasil</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-bar-chart"></i>
                                    Belum ada nilai ujian yang tercatat.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
