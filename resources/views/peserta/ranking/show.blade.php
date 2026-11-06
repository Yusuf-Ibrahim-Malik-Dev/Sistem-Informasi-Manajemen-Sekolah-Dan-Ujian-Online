@extends('layouts.peserta')

@section('title', 'Ranking ' . $tes->tes_nama)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" data-aos="fade-up">
        <a href="{{ route('peserta.ranking.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Ranking</a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('peserta.ranking.cetak', ['tes' => $tes, 'auto' => 1]) }}" target="_blank" class="btn btn-soft"><i class="bi bi-printer me-1"></i> Cetak Ranking</a>
            <span class="badge-soft neutral"><i class="bi bi-person-badge"></i> {{ $namaGuru }}</span>
        </div>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">{{ $tes->tes_nama }}</h3>
                <p class="section-sub">Ranking Ujian · Peringkat berdasarkan nilai akhir peserta yang telah menyelesaikan ujian.</p>
            </div>
            <span class="badge-soft info"><i class="bi bi-trophy"></i> {{ count($ranking) }} peserta</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">Rank</th>
                        <th>Peserta</th>
                        <th>Group</th>
                        <th class="text-end" style="width: 120px;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ranking as $item)
                        <tr class="{{ $item['saya'] ? 'table-active' : '' }}">
                            <td class="fw-800">#{{ $item['rank'] }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item['nama'] }}</div>
                                <div class="text-muted-2" style="font-size:.72rem">{{ $item['username'] }}</div>
                                @if ($item['saya'])
                                    <span class="badge-soft info mt-1">Kamu</span>
                                @endif
                            </td>
                            <td><span class="badge-soft neutral">{{ $item['grup'] }}</span></td>
                            <td class="text-end fw-800" style="color:#4338ca">{{ number_format($item['nilai'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-trophy"></i>
                                    Belum ada peserta yang menyelesaikan ujian ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('peserta.ranking.index') }}" class="btn btn-gradient"><i class="bi bi-trophy me-1"></i> Semua Ranking</a>
    </div>
@endsection
