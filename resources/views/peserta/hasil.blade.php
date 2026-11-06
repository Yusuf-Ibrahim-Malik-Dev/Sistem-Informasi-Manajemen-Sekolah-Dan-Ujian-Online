@extends('layouts.peserta')

@section('title', 'Hasil ' . $tes->tes_nama)

@php
    $maks = max(1, (float) $tes->tes_max_score);
    $persen = max(0, min(100, round(((float) $nilai / $maks) * 100)));
    $tidakDijawab = $attempt->soals->filter(fn ($s) => $s->tessoal_change_time === null)->count();
    $ragu = $attempt->soals->where('tessoal_ragu', 1)->count();
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        <div class="d-flex align-items-center gap-2">
            @if ($tes->tes_results_to_users && $bolehLihatRincian)
                <a href="{{ route('peserta.ujian.cetak', ['tes' => $tes, 'auto' => 1]) }}" target="_blank" class="btn btn-soft"><i class="bi bi-printer me-1"></i> Cetak Hasil</a>
            @endif
            <span class="badge-soft neutral"><i class="bi bi-calendar-check"></i> {{ $attempt->tesuser_creation_time?->translatedFormat('d M Y, H:i') }}</span>
        </div>
    </div>

    @if (! $tes->tes_results_to_users)
        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="empty-state">
                <i class="bi bi-eye-slash"></i>
                <h4 class="fw-800 mb-1">Hasil belum dapat ditampilkan</h4>
                <p class="mb-3">Operator belum mengizinkan peserta melihat nilai untuk tes ini. Silakan hubungi operator yang bertugas.</p>
                <a href="{{ route('peserta.dashboard') }}" class="btn btn-gradient">Kembali ke Dashboard</a>
            </div>
        </div>
    @else
        <div class="result-hero mb-4" data-aos="fade-up">
            <div class="score-ring">{{ number_format((float) $nilai, 0) }}</div>
            <h2 class="fw-800 mb-1" style="font-size:1.4rem">{{ $tes->tes_nama }}</h2>
            <p class="mb-1" style="color:#dbeafe">
                Nilai maksimal {{ number_format($maks, 0) }} &middot; Persentase {{ $persen }}%
            </p>
            <p class="mb-0" style="color:#c7d2fe;font-size:.86rem">
                @if ($persen >= 80)
                    Luar biasa! Pertahankan prestasimu.
                @elseif ($persen >= 60)
                    Hasil yang baik. Terus tingkatkan belajarmu.
                @else
                    Jangan menyerah, pelajari kembali materi dan coba lagi di kesempatan berikutnya.
                @endif
            </p>
        </div>

        @if (! $bolehLihatRincian)
            <div class="glass-card card-pad" data-aos="fade-up">
                <div class="d-flex gap-3 align-items-start">
                    <div class="stat-icon bg-grad-2 mb-0"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <h4 class="section-title mb-1">Rincian jawaban belum tersedia</h4>
                        <p class="section-sub mb-0">Status benar/salah dan rincian jawaban akan ditampilkan setelah seluruh peserta menyelesaikan ujian ini.</p>
                    </div>
                </div>
            </div>
        @else
        <div class="stat-grid mb-4">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-value">{{ $benar }}</div>
                <div class="stat-label">Jawaban Benar</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
                <div class="stat-icon bg-grad-3"><i class="bi bi-x-circle"></i></div>
                <div class="stat-value">{{ $salah }}</div>
                <div class="stat-label">Jawaban Salah</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-icon bg-grad-2"><i class="bi bi-dash-circle"></i></div>
                <div class="stat-value">{{ $tidakDijawab }}</div>
                <div class="stat-label">Tidak Dijawab</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
                <div class="stat-icon bg-grad-5"><i class="bi bi-question-circle"></i></div>
                <div class="stat-value">{{ $ragu }}</div>
                <div class="stat-label">Ditandai Ragu</div>
            </div>
        </div>

        @if ($tes->tes_detail_to_users)
            <div class="glass-card card-pad" data-aos="fade-up">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Rincian Jawaban</h3>
                        <p class="section-sub">Pembahasan jawaban dan catatan dari operator.</p>
                    </div>
                    <span class="badge-soft"><i class="bi bi-list-check"></i> {{ $attempt->soals->count() }} soal</span>
                </div>

                <div class="accordion accordion-modern" id="hasilAccordion">
                    @foreach ($attempt->soals as $item)
                        @php
                            $selected = $item->pilihan->firstWhere('soaljawaban_selected', 1);
                            $selectedId = $selected?->soaljawaban_jawaban_id;
                            $kunciId = $item->soal?->jawabans?->firstWhere('jawaban_benar', 1)?->jawaban_id;
                            $tipe = (int) ($item->soal?->soal_tipe ?? 1);
                            $benarSoal = $tipe === 1 ? ($selectedId !== null && $selectedId === $kunciId) : ((float) $item->tessoal_nilai > 0);
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#hasilSoal{{ $item->tessoal_id }}">
                                    <span class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge-soft neutral">Soal {{ $loop->iteration }}</span>
                                        <span>{{ Str::limit(strip_tags($item->soal?->soal_detail), 70) }}</span>
                                        <span class="badge-soft {{ $benarSoal ? 'success' : ($item->tessoal_change_time ? 'danger' : 'neutral') }}">
                                            {{ $benarSoal ? 'Benar' : ($item->tessoal_change_time ? 'Salah' : 'Kosong') }}
                                        </span>
                                        <span class="badge-soft info">Nilai {{ number_format((float) $item->tessoal_nilai, 1) }}</span>
                                    </span>
                                </button>
                            </h2>
                            <div id="hasilSoal{{ $item->tessoal_id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#hasilAccordion">
                                <div class="accordion-body">
                                    <div class="question-text mb-3">{!! $item->soal?->detail_html !!}</div>

                                    @if ($tipe === 1)
                                        <div class="d-flex flex-column gap-2 mb-3">
                                            @foreach ($item->pilihan as $pilihan)
                                                @php
                                                    $jawaban = $pilihan->jawaban;
                                                    $dipilih = (int) $pilihan->soaljawaban_selected === 1;
                                                    $benarOpsi = (bool) $jawaban?->jawaban_benar;
                                                @endphp
                                                <div class="answer-option {{ $dipilih ? 'selected' : '' }}" style="{{ $benarOpsi ? 'border-color:#10b981;background:rgba(16,185,129,.06)' : '' }}">
                                                    <span class="answer-key" style="{{ $benarOpsi ? 'background:#10b981;color:#fff' : '' }}">{{ chr(65 + $loop->index) }}</span>
                                                    <span class="flex-grow-1 answer-text">{!! $jawaban?->detail_html !!}</span>
                                                    <span class="d-flex gap-1">
                                                        @if ($dipilih)
                                                            <span class="badge-soft info">Jawabanmu</span>
                                                        @endif
                                                        @if ($benarOpsi)
                                                            <span class="badge-soft success">Kunci</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 mb-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line)">
                                            <div class="form-label mb-1">Jawabanmu</div>
                                            <div style="white-space:pre-line">{{ $item->tessoal_jawaban_text ?: 'Tidak dijawab.' }}</div>
                                        </div>
                                        @if ($tipe === 3)
                                            <div class="p-3 mb-3" style="border-radius:12px;background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2)">
                                                <div class="form-label mb-1">Kunci Jawaban</div>
                                                <div class="fw-semibold">{{ $item->soal?->soal_kunci ?: '-' }}</div>
                                            </div>
                                        @endif
                                    @endif

                                    @if ($item->tessoal_comment)
                                        <div class="p-3" style="border-radius:12px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.14)">
                                            <div class="form-label mb-1"><i class="bi bi-chat-left-text me-1"></i> Catatan Operator</div>
                                            <div style="font-size:.85rem">{{ $item->tessoal_comment }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="glass-card card-pad" data-aos="fade-up">
                <div class="d-flex gap-3 align-items-start">
                    <div class="stat-icon bg-grad-2 mb-0"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <h4 class="section-title mb-1">Rincian jawaban disembunyikan</h4>
                        <p class="section-sub mb-0">Operator hanya menampilkan nilai total untuk tes ini.</p>
                    </div>
                </div>
            </div>
        @endif
        @endif

        @if (count($ranking))
            <div class="glass-card card-pad mt-4" data-aos="fade-up">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Ranking Ujian</h3>
                        <p class="section-sub">Peringkat berdasarkan nilai akhir peserta yang telah menyelesaikan ujian.</p>
                    </div>
                    <span class="badge-soft info"><i class="bi bi-trophy"></i> {{ count($ranking) }} peserta</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Rank</th>
                                <th>Peserta</th>
                                <th style="width: 110px;" class="text-end">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ranking as $item)
                                <tr class="{{ $item['saya'] ? 'table-active' : '' }}">
                                    <td class="fw-800">#{{ $item['rank'] }}</td>
                                    <td>
                                        {{ $item['nama'] }}
                                        @if ($item['saya'])
                                            <span class="badge-soft info ms-1">Kamu</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($item['nilai'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('peserta.dashboard') }}" class="btn btn-gradient"><i class="bi bi-grid me-1"></i> Kembali ke Dashboard</a>
        </div>
    @endif
@endsection

@if ($tes->tes_results_to_users && $persen >= 70)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.confetti) {
                window.confetti({
                    particleCount: 140,
                    spread: 80,
                    origin: { y: 0.6 },
                    colors: ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b']
                });
            }
        });
    </script>
    @endpush
@endif
