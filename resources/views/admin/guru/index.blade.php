@extends('layouts.admin')

@section('title', 'Monitoring Guru')
@section('heading', 'Monitoring Ujian')
@section('eyebrow', 'Guru')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" data-aos="fade-up">
        <div class="section-sub mb-0">Monitor pelaksanaan ujian dan kelola nilai raport peserta.</div>
        <a href="{{ route('admin.guru.raport') }}" class="btn btn-gradient btn-sm"><i class="bi bi-journal-check me-1"></i> Penilaian Raport</a>
    </div>

    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-3"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value">{{ $statistik['berjalan'] }}</div>
            <div class="stat-label">Sedang Mengerjakan</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value">{{ $statistik['selesai'] }}</div>
            <div class="stat-label">Sudah Selesai</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-1"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $statistik['total'] }}</div>
            <div class="stat-label">Total Percobaan</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Kontrol Ranking</h3>
                <p class="section-sub">Aktifkan switch agar ranking ujian muncul di menu peserta. Guru tetap dapat melihat dan mencetak ranking.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tes</th>
                        <th>Peserta Selesai</th>
                        <th>Ranking Peserta</th>
                        <th class="text-end">Ranking</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tesList as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->tes_nama }}</td>
                            <td><span class="badge-soft neutral">{{ $item->peserta_selesai }} peserta</span></td>
                            <td style="width:180px">
                                <form method="POST" action="{{ route('admin.guru.ranking.toggle', $item) }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" @checked($item->tes_ranking_to_users)>
                                    </div>
                                    <span class="badge-soft {{ $item->tes_ranking_to_users ? 'success' : 'neutral' }}">
                                        {{ $item->tes_ranking_to_users ? 'Ditampilkan' : 'Disembunyikan' }}
                                    </span>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.guru.ranking', $item) }}" class="btn btn-soft btn-sm"><i class="bi bi-trophy me-1"></i> Lihat</a>
                                <a href="{{ route('admin.guru.ranking.cetak', ['tes' => $item, 'auto' => 1]) }}" target="_blank" class="btn btn-ghost btn-sm"><i class="bi bi-printer me-1"></i> Cetak</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-trophy"></i>
                                    Belum ada tes yang dapat ditampilkan rankingnya.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Peserta Ujian</h3>
                <p class="section-sub">Pantau peserta yang sedang mengerjakan dan yang sudah menyelesaikan ujian.</p>
            </div>
            <span class="badge-soft info"><i class="bi bi-broadcast"></i> {{ $monitoring->total() }} percobaan</span>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="tes" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua tes</option>
                    @foreach ($tesList as $item)
                        <option value="{{ $item->tes_id }}" @selected(request('tes') == $item->tes_id)>{{ $item->tes_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="grup" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua group</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->grup_id }}" @selected(request('grup') == $grup->grup_id)>{{ $grup->grup_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="1" @selected(request('status') === '1')>Sedang mengerjakan</option>
                    <option value="4" @selected(request('status') === '4')>Selesai</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau username...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-soft flex-grow-1"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.guru.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Tes</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Progres</th>
                        <th>Nilai</th>
                        <th>Mulai</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($monitoring as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar" style="width:34px;height:34px;font-size:.78rem">{{ strtoupper(substr($item->peserta?->nama ?? '?', 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $item->peserta?->nama ?? '-' }}</div>
                                        <div class="text-muted-2" style="font-size:.72rem">{{ $item->peserta?->user_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:.82rem">{{ $item->tes?->tes_nama ?? '-' }}</td>
                            <td><span class="badge-soft neutral">{{ $item->peserta?->grup?->grup_nama ?? '-' }}</span></td>
                            <td>
                                @if ($item->is_berjalan)
                                    @if ($item->is_kedaluwarsa)
                                        <span class="badge-soft danger"><i class="bi bi-clock-history"></i> Waktu Habis</span>
                                    @else
                                        <span class="badge-soft warning"><i class="bi bi-hourglass-split"></i> Mengerjakan</span>
                                        <div class="text-muted-2 mt-1" style="font-size:.7rem">Sisa {{ gmdate('H:i:s', $item->sisa_detik) }}</div>
                                    @endif
                                @else
                                    <span class="badge-soft success"><i class="bi bi-check2-circle"></i> Selesai</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $item->dijawab }}</span>
                                <span class="text-muted-2">/ {{ $item->total_soal }}</span>
                            </td>
                            <td class="fw-800" style="color:#4338ca">{{ number_format((float) $item->total_nilai, 1) }}</td>
                            <td class="text-muted-2" style="font-size:.76rem">{{ $item->tesuser_creation_time?->translatedFormat('d M Y H:i') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.hasil.detail', $item) }}" class="btn btn-soft btn-icon" title="Detail"><i class="bi bi-eye"></i></a>
                                @if (! $item->is_berjalan)
                                    <a href="{{ route('admin.hasil.cetak', ['tesUser' => $item, 'auto' => 1]) }}" target="_blank" class="btn btn-ghost btn-icon" title="Cetak hasil"><i class="bi bi-printer"></i></a>
                                @else
                                    <form method="POST" action="{{ route('admin.hasil.hentikan', $item) }}" class="d-inline" onsubmit="return confirm('Hentikan ujian peserta ini?')">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-ghost btn-icon text-danger" title="Hentikan"><i class="bi bi-stop-circle"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-person-x"></i>
                                    Belum ada peserta yang cocok dengan filter ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $monitoring->links() }}</div>
    </div>
@endsection
