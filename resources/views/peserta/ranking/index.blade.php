@extends('layouts.peserta')

@section('title', 'Ranking Ujian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" data-aos="fade-up">
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        <span class="badge-soft info"><i class="bi bi-trophy"></i> {{ $tes->count() }} ujian</span>
    </div>

    <div class="section-head">
        <div>
            <h3 class="section-title">Ranking Ujian</h3>
            <p class="section-sub">Peringkat peserta pada ujian yang telah kamu selesaikan.</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($tes as $item)
            <div class="col-md-6 col-xl-4" data-aos="fade-up">
                <div class="tes-card h-100">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <span class="badge-soft success"><i class="bi bi-trophy"></i> Ranking Dibuka</span>
                        <span class="badge-soft neutral"><i class="bi bi-people"></i> {{ $item->peserta_selesai }} selesai</span>
                    </div>

                    <h5 class="mb-1">{{ $item->tes_nama }}</h5>
                    <p class="text-muted-2 mb-3" style="font-size:.8rem">{{ Str::limit(strip_tags($item->tes_detail), 110) }}</p>

                    <div class="meta-row mb-3">
                        <span><i class="bi bi-clock"></i> {{ $item->tes_duration_time }} menit</span>
                        <span><i class="bi bi-list-ol"></i> {{ $item->topik_sets_count }} topik</span>
                        <span><i class="bi bi-calendar-event"></i> {{ $item->tes_end_time?->translatedFormat('d M Y') }}</span>
                    </div>

                    <div class="mt-auto d-flex align-items-center gap-2 pt-2 border-top" style="border-color:var(--line)!important">
                        <a href="{{ route('peserta.ranking.show', $item) }}" class="btn btn-gradient btn-sm flex-grow-1">Lihat Ranking</a>
                        <a href="{{ route('peserta.ranking.cetak', ['tes' => $item, 'auto' => 1]) }}" target="_blank" class="btn btn-soft btn-sm"><i class="bi bi-printer"></i></a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card card-pad">
                    <div class="empty-state">
                        <i class="bi bi-trophy"></i>
                        Belum ada ranking yang dibuka. Ranking akan muncul setelah guru menampilkannya dan kamu menyelesaikan ujian.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
