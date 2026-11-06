@extends('layouts.admin')

@section('title', 'Hasil & Evaluasi')
@section('heading', 'Hasil Ujian')
@section('eyebrow', 'Manajemen Ujian')

@section('content')
    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Hasil Peserta</h3>
                <p class="section-sub">{{ $hasil->total() }} percobaan ujian tercatat.</p>
            </div>
            <a href="{{ route('admin.laporan.rekap') }}" class="btn btn-ghost btn-sm"><i class="bi bi-graph-up me-1"></i> Rekap Nilai</a>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="tes" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua tes</option>
                    @foreach ($tes as $item)
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
                    <option value="1" @selected(request('status') === '1')>Berlangsung</option>
                    <option value="4" @selected(request('status') === '4')>Selesai</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari peserta...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-soft flex-grow-1"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.hasil.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Tes</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Soal</th>
                        <th>Nilai</th>
                        <th>Waktu</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hasil as $item)
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
                                    <span class="badge-soft warning"><i class="bi bi-hourglass-split"></i> Berlangsung</span>
                                @else
                                    <span class="badge-soft success"><i class="bi bi-check2-circle"></i> Selesai</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $item->soals_count }}</td>
                            <td>
                                <span class="fw-800" style="font-size:1rem;color:#4338ca">{{ number_format((float) $item->total_nilai, 1) }}</span>
                            </td>
                            <td class="text-muted-2" style="font-size:.76rem">{{ $item->tesuser_creation_time?->translatedFormat('d M Y H:i') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.hasil.detail', $item) }}" class="btn btn-soft btn-icon" title="Detail"><i class="bi bi-eye"></i></a>
                                @if (! $item->is_berjalan)
                                    <a href="{{ route('admin.hasil.cetak', ['tesUser' => $item, 'auto' => 1]) }}" target="_blank" class="btn btn-ghost btn-icon" title="Cetak hasil"><i class="bi bi-printer"></i></a>
                                @endif
                                @if ($item->is_berjalan)
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
                                    <i class="bi bi-bar-chart"></i>
                                    Belum ada hasil ujian yang cocok.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $hasil->links() }}</div>
    </div>
@endsection
