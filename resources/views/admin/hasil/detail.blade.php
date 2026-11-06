@extends('layouts.admin')

@section('title', 'Detail Hasil')
@section('heading', 'Detail Hasil Ujian')
@section('eyebrow', 'Manajemen Ujian')

@php
    $dijawab = $tesUser->soals->filter(fn ($s) => $s->tessoal_change_time !== null)->count();
    $ragu = $tesUser->soals->where('tessoal_ragu', 1)->count();
    $tidakDijawab = $tesUser->soals->count() - $dijawab;
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" data-aos="fade-up">
        <a href="{{ route('admin.hasil.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hasil.cetak', ['tesUser' => $tesUser, 'auto' => 1]) }}" target="_blank" class="btn btn-soft"><i class="bi bi-printer me-1"></i> Cetak Hasil</a>
            @if ($tesUser->is_berjalan)
                <form method="POST" action="{{ route('admin.hasil.hentikan', $tesUser) }}" onsubmit="return confirm('Hentikan ujian peserta ini?')">
                    @csrf
                    @method('PUT')
                    <button class="btn btn-gradient" style="background:linear-gradient(135deg,#ef4444,#f97316)"><i class="bi bi-stop-circle me-1"></i> Hentikan Ujian</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5" data-aos="fade-up">
            <div class="glass-card card-pad h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar" style="width:56px;height:56px;font-size:1.3rem">{{ strtoupper(substr($tesUser->peserta?->nama ?? '?', 0, 1)) }}</div>
                    <div>
                        <h3 class="section-title mb-1">{{ $tesUser->peserta?->nama ?? '-' }}</h3>
                        <div class="text-muted-2" style="font-size:.8rem">
                            {{ $tesUser->peserta?->user_name }} &middot; {{ $tesUser->peserta?->grup?->grup_nama ?? '-' }}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2" style="font-size:.84rem">
                    <span class="text-muted-2">Tes</span>
                    <span class="fw-semibold text-end">{{ $tesUser->tes?->tes_nama }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.84rem">
                    <span class="text-muted-2">Mulai</span>
                    <span class="fw-semibold">{{ $tesUser->tesuser_creation_time?->translatedFormat('d M Y, H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.84rem">
                    <span class="text-muted-2">Status</span>
                    <span class="badge-soft {{ $tesUser->is_berjalan ? 'warning' : 'success' }}">{{ $tesUser->is_berjalan ? 'Berlangsung' : 'Selesai' }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.84rem">
                    <span class="text-muted-2">Token</span>
                    <span class="fw-semibold">{{ $tesUser->tesuser_token ?: '-' }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-7" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad h-100">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="stat-value" style="color:#4338ca">{{ number_format($nilai, 1) }}</div>
                        <div class="stat-label">Total Nilai</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-value" style="color:#047857">{{ $benar }}</div>
                        <div class="stat-label">Benar</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-value" style="color:#b91c1c">{{ $salah }}</div>
                        <div class="stat-label">Salah</div>
                    </div>
                </div>
                <hr>
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="fw-800">{{ $dijawab }}</div>
                        <div class="stat-label">Dijawab</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-800">{{ $tidakDijawab }}</div>
                        <div class="stat-label">Tidak Dijawab</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-800" style="color:#b45309">{{ $ragu }}</div>
                        <div class="stat-label">Ragu-ragu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Rincian Jawaban</h3>
                <p class="section-sub">Periksa jawaban peserta dan berikan nilai untuk soal essay.</p>
            </div>
            <span class="badge-soft"><i class="bi bi-list-check"></i> {{ $tesUser->soals->count() }} soal</span>
        </div>

        <div class="accordion accordion-modern" id="jawabanAccordion">
            @foreach ($tesUser->soals as $item)
                @php
                    $selected = $item->pilihan->firstWhere('soaljawaban_selected', 1);
                    $selectedId = $selected?->soaljawaban_jawaban_id;
                    $kunciId = $item->soal?->jawabans?->firstWhere('jawaban_benar', 1)?->jawaban_id;
                    $tipe = (int) ($item->soal?->soal_tipe ?? 1);
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#soal{{ $item->tessoal_id }}">
                            <span class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge-soft neutral">Soal {{ $loop->iteration }}</span>
                                <span>{{ Str::limit(strip_tags($item->soal?->soal_detail), 80) }}</span>
                                @if ($item->tessoal_ragu)
                                    <span class="badge-soft warning"><i class="bi bi-question-circle"></i> Ragu</span>
                                @endif
                                <span class="badge-soft {{ $item->tessoal_nilai > 0 ? 'success' : 'neutral' }}">Nilai: {{ number_format((float) $item->tessoal_nilai, 1) }}</span>
                            </span>
                        </button>
                    </h2>
                    <div id="soal{{ $item->tessoal_id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#jawabanAccordion">
                        <div class="accordion-body">
                            <div class="question-text mb-3">{!! $item->soal?->detail_html ?? '-' !!}</div>

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
                                            <span class="d-flex gap-1 align-items-center">
                                                @if ($dipilih)
                                                    <span class="badge-soft info">Dipilih</span>
                                                @endif
                                                @if ($benarOpsi)
                                                    <span class="badge-soft success">Kunci</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($tipe === 3)
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 h-100" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line)">
                                            <div class="form-label mb-1">Jawaban Peserta</div>
                                            <div class="fw-semibold">{{ $item->tessoal_jawaban_text ?: '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 h-100" style="border-radius:12px;background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2)">
                                            <div class="form-label mb-1">Kunci Jawaban</div>
                                            <div class="fw-semibold">{{ $item->soal?->soal_kunci ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 mb-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line)">
                                    <div class="form-label mb-1">Jawaban Peserta</div>
                                    <div style="white-space:pre-line">{{ $item->tessoal_jawaban_text ?: 'Tidak dijawab.' }}</div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.hasil.evaluasi', $item) }}" class="row g-2 align-items-end p-3" style="border-radius:14px;background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.12)">
                                @csrf
                                @method('PUT')
                                <div class="col-md-3">
                                    <label class="form-label">Nilai Soal</label>
                                    <input type="number" step="0.01" min="0" name="tessoal_nilai" class="form-control" value="{{ (float) $item->tessoal_nilai }}">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Catatan Evaluasi</label>
                                    <input type="text" name="tessoal_comment" class="form-control" value="{{ $item->tessoal_comment }}" placeholder="Catatan untuk peserta (opsional)">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-gradient w-100"><i class="bi bi-save me-1"></i> Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
