@extends('layouts.admin')

@section('title', 'Analisis Butir Soal')
@section('heading', 'Analisis Butir Soal')
@section('eyebrow', 'Laporan')

@section('content')
    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Pilih Tes</h3>
                <p class="section-sub">Analisis tingkat kesulitan dan daya pembeda butir soal.</p>
            </div>
            <a href="{{ route('admin.laporan.rekap', request()->only('tes')) }}" class="btn btn-ghost btn-sm"><i class="bi bi-table me-1"></i> Rekap Nilai</a>
        </div>

        <form method="GET" class="row g-2">
            <div class="col-md-5">
                <select name="tes" class="form-select" onchange="this.form.submit()">
                    @foreach ($tesList as $item)
                        <option value="{{ $item->tes_id }}" @selected($tes?->tes_id === $item->tes_id)>{{ $item->tes_nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if (! $tes)
        <div class="glass-card card-pad">
            <div class="empty-state"><i class="bi bi-journal-x"></i> Belum ada tes untuk dianalisis.</div>
        </div>
    @else
        <div class="stat-grid mb-4">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon bg-grad-1"><i class="bi bi-people"></i></div>
                <div class="stat-value">{{ $ringkasan['peserta'] }}</div>
                <div class="stat-label">Peserta Selesai</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
                <div class="stat-icon bg-grad-2"><i class="bi bi-patch-question"></i></div>
                <div class="stat-value">{{ $ringkasan['soal'] }}</div>
                <div class="stat-label">Butir Soal Dianalisis</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-icon bg-grad-4"><i class="bi bi-graph-up"></i></div>
                <div class="stat-value">{{ number_format($ringkasan['rata'], 1) }}</div>
                <div class="stat-label">Rata-rata Nilai</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
                <div class="stat-icon bg-grad-5"><i class="bi bi-trophy"></i></div>
                <div class="stat-value">{{ number_format($ringkasan['tertinggi'], 1) }}</div>
                <div class="stat-label">Nilai Tertinggi</div>
            </div>
        </div>

        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Butir Soal</h3>
                    <p class="section-sub">Tingkat kesulitan = proporsi jawaban benar. Daya pembeda membandingkan kelompok atas dan bawah (27%).</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Soal</th>
                            <th>Tipe</th>
                            <th>Dijawab</th>
                            <th>Benar</th>
                            <th>Tingkat Kesulitan</th>
                            <th>Daya Pembeda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($analisis as $row)
                            <tr>
                                <td class="text-muted-2">{{ $loop->iteration }}</td>
                                <td style="min-width:260px">
                                    <div class="fw-semibold">{{ Str::limit(strip_tags($row['soal']?->soal_detail), 90) }}</div>
                                    <div class="text-muted-2" style="font-size:.72rem">{{ $row['soal']?->topik?->topik_nama }}</div>
                                </td>
                                <td><span class="badge-soft neutral">{{ $row['soal']?->tipe_label }}</span></td>
                                <td>{{ $row['dijawab'] }} / {{ $row['peserta'] }}</td>
                                <td class="fw-semibold text-success">{{ $row['benar'] }}</td>
                                <td>
                                    <span class="badge-soft {{ $row['kategori_tingkat'] === 'Mudah' ? 'success' : ($row['kategori_tingkat'] === 'Sedang' ? 'warning' : 'danger') }}">
                                        {{ $row['kategori_tingkat'] }} ({{ number_format($row['tingkat'], 2) }})
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-soft {{ $row['kategori_daya'] === 'Baik' ? 'success' : ($row['kategori_daya'] === 'Cukup' ? 'warning' : 'danger') }}">
                                        {{ $row['kategori_daya'] }} ({{ number_format($row['daya'], 2) }})
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-pie-chart"></i>
                                        Belum ada jawaban peserta yang dapat dianalisis pada tes ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
