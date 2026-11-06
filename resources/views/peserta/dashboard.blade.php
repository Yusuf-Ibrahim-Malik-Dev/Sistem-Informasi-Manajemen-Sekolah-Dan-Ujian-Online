@extends('layouts.peserta')

@section('title', 'Dashboard')
@php
    $info = \App\Models\Konfigurasi::get('cbt_informasi', '');
@endphp

@section('content')
    <div class="hero-card mb-4" data-aos="fade-up">
        <div class="position-relative" style="z-index:1">
            <div class="text-uppercase fw-semibold mb-2" style="letter-spacing:.14em;font-size:.72rem;color:#c7d2fe">Selamat datang</div>
            <h2 class="fw-800 mb-2" style="font-size:1.75rem;letter-spacing:-.03em">Halo, {{ $peserta->nama }}</h2>
            <p class="mb-0" style="color:#dbeafe;max-width:680px;font-size:.92rem">
                {{ $peserta->grup?->grup_nama ? 'Kamu terdaftar pada group ' . $peserta->grup->grup_nama . '. ' : '' }}
                Pilih tes yang tersedia di bawah ini dan kerjakan dengan jujur.
            </p>

            @if ($info)
                <div class="mt-3 p-3" style="border-radius:14px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);font-size:.85rem;color:#e0e7ff">
                    {!! nl2br($info) !!}
                </div>
            @endif
        </div>
    </div>

    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-1"><i class="bi bi-journal-text"></i></div>
            <div class="stat-value">{{ $ringkasan['tersedia'] }}</div>
            <div class="stat-label">Tes Tersedia</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-3"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value">{{ $ringkasan['berjalan'] }}</div>
            <div class="stat-label">Sedang Berjalan</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value">{{ $ringkasan['selesai'] }}</div>
            <div class="stat-label">Sudah Selesai</div>
        </div>
    </div>

    @if ($rankingTes->isNotEmpty())
        <div class="glass-card card-pad mb-4" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Ranking Ujian</h3>
                    <p class="section-sub">Ranking sudah dibuka oleh guru untuk ujian yang telah kamu selesaikan.</p>
                </div>
                <a href="{{ route('peserta.ranking.index') }}" class="btn btn-soft btn-sm"><i class="bi bi-trophy me-1"></i> Semua Ranking</a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($rankingTes as $item)
                    <a href="{{ route('peserta.ranking.show', $item) }}" class="btn btn-ghost btn-sm">
                        <i class="bi bi-trophy me-1" style="color:#d97706"></i> {{ $item->tes_nama }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="section-head">
        <div>
            <h3 class="section-title">Daftar Tes</h3>
            <p class="section-sub">Tes yang sesuai dengan group kamu.</p>
        </div>
        <span class="badge-soft"><i class="bi bi-calendar3"></i> {{ now()->translatedFormat('d F Y') }}</span>
    </div>

    <div class="row g-3">
        @forelse ($tes as $item)
            @php
                $attempt = $attempts[$item->tes_id] ?? null;
                $berjalan = $attempt && $attempt->is_berjalan;
                $selesai = $attempt && ! $attempt->is_berjalan;
            @endphp
            <div class="col-md-6 col-xl-4" data-aos="fade-up">
                <div class="tes-card">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <span class="badge-soft {{ $item->status === 'aktif' ? 'success' : 'warning' }}">
                            <i class="bi {{ $item->status === 'aktif' ? 'bi-broadcast' : 'bi-hourglass-split' }}"></i>
                            {{ $item->status === 'aktif' ? 'Dibuka' : 'Belum Dibuka' }}
                        </span>
                        @if ($item->tes_token)
                            <span class="badge-soft warning"><i class="bi bi-key"></i> Token</span>
                        @endif
                    </div>

                    <h5 class="mb-1">{{ $item->tes_nama }}</h5>
                    <p class="text-muted-2 mb-3" style="font-size:.8rem">{{ Str::limit(strip_tags($item->tes_detail), 110) }}</p>

                    <div class="meta-row mb-3">
                        <span><i class="bi bi-clock"></i> {{ $item->tes_duration_time }} menit</span>
                        <span><i class="bi bi-list-ol"></i> {{ $item->jumlah_soal }} soal</span>
                        <span><i class="bi bi-calendar-event"></i> {{ $item->tes_begin_time?->translatedFormat('d M') }} - {{ $item->tes_end_time?->translatedFormat('d M') }}</span>
                    </div>

                    <div class="mt-auto d-flex align-items-center justify-content-between gap-2 pt-2 border-top" style="border-color:var(--line)!important">
                        @if ($selesai)
                            <span class="badge-soft success"><i class="bi bi-check2-circle"></i> Selesai</span>
                            <a href="{{ route('peserta.ujian.hasil', $item) }}" class="btn btn-soft btn-sm">Lihat Hasil <i class="bi bi-arrow-right ms-1"></i></a>
                        @elseif ($berjalan)
                            <span class="badge-soft warning"><i class="bi bi-hourglass-split"></i> Berjalan</span>
                            <a href="{{ route('peserta.ujian.kerjakan', $item) }}" class="btn btn-gradient btn-sm">Lanjutkan <i class="bi bi-arrow-right ms-1"></i></a>
                        @elseif ($item->status === 'aktif')
                            <span class="text-muted-2" style="font-size:.74rem">Belum dikerjakan</span>
                            <a href="{{ route('peserta.tes.show', $item) }}" class="btn btn-gradient btn-sm">Mulai <i class="bi bi-arrow-right ms-1"></i></a>
                        @else
                            <span class="text-muted-2" style="font-size:.74rem">Belum dibuka</span>
                            <button class="btn btn-ghost btn-sm" disabled>Menunggu</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card card-pad">
                    <div class="empty-state">
                        <i class="bi bi-journal-x"></i>
                        Belum ada tes yang tersedia untuk group kamu. Hubungi operator bila ini sebuah kekeliruan.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
