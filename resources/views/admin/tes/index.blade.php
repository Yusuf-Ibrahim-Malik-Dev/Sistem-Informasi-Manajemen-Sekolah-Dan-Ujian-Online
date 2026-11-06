@extends('layouts.admin')

@section('title', 'Daftar Tes')
@section('heading', 'Manajemen Tes')
@section('eyebrow', 'Manajemen Ujian')

@section('content')
    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Daftar Tes</h3>
                <p class="section-sub">{{ $tes->total() }} tes terdaftar dalam sistem.</p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari tes..." style="width:190px">
                    <button class="btn btn-ghost btn-sm"><i class="bi bi-search"></i></button>
                </form>
                <a href="{{ route('admin.tes.create') }}" class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Buat Tes</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Tes</th>
                        <th>Periode</th>
                        <th>Durasi</th>
                        <th>Biaya</th>
                        <th>Group</th>
                        <th>Soal</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tes as $item)
                        <tr>
                            <td style="min-width:220px">
                                <div class="fw-semibold">{{ $item->tes_nama }}</div>
                                <div class="text-muted-2" style="font-size:.73rem">{{ Str::limit(strip_tags($item->tes_detail), 70) }}</div>
                            </td>
                            <td style="font-size:.8rem">
                                <div>{{ $item->tes_begin_time?->translatedFormat('d M Y H:i') }}</div>
                                <div class="text-muted-2">s.d. {{ $item->tes_end_time?->translatedFormat('d M Y H:i') }}</div>
                            </td>
                            <td><span class="badge-soft neutral"><i class="bi bi-clock"></i> {{ $item->tes_duration_time }} mnt</span></td>
                            <td class="fw-semibold text-nowrap" style="font-size:.8rem">
                                {{ (float) $item->tes_biaya > 0 ? 'Rp ' . number_format($item->tes_biaya, 0, ',', '.') : 'Gratis' }}
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($item->groups as $grup)
                                        <span class="badge-soft info">{{ $grup->grup_nama }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="fw-semibold">{{ $item->topik_sets_count }} set</td>
                            <td class="fw-semibold">{{ $item->tes_users_count }}</td>
                            <td>
                                @if ($item->status === 'aktif')
                                    <span class="badge-soft success"><i class="bi bi-broadcast"></i> Berlangsung</span>
                                @elseif ($item->status === 'belum')
                                    <span class="badge-soft warning"><i class="bi bi-hourglass-split"></i> Belum Mulai</span>
                                @else
                                    <span class="badge-soft neutral"><i class="bi bi-check2-circle"></i> Selesai</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.hasil.index', ['tes' => $item->tes_id]) }}" class="btn btn-ghost btn-icon" title="Hasil"><i class="bi bi-bar-chart"></i></a>
                                <a href="{{ route('admin.tes.edit', $item) }}" class="btn btn-soft btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.tes.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus tes ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-journal-x"></i>
                                    Belum ada tes. Buat tes pertama Anda.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $tes->links() }}</div>
    </div>
@endsection
