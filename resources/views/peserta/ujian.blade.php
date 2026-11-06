@extends('layouts.exam')

@section('title', 'Mengerjakan ' . $tes->tes_nama)

@php
    $nomor = $daftarSoal->search(fn ($s) => $s->tessoal_id === $current->tessoal_id) + 1;
    $dijawab = $daftarSoal->filter(fn ($s) => $s->tessoal_change_time !== null)->count();
    $tipe = (int) $current->soal->soal_tipe;
    $persen = $daftarSoal->count() > 0 ? round($dijawab / $daftarSoal->count() * 100) : 0;
@endphp

@section('content')
    <div class="exam-layout">
        <div>
            @include('partials.alerts')

            <div class="question-card mb-3" data-aos="fade-up">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-soft"><i class="bi bi-list-ol"></i> Soal {{ $nomor }} / {{ $daftarSoal->count() }}</span>
                        <span class="badge-soft neutral">{{ $current->soal->tipe_label }}</span>
                        @if ($current->tessoal_ragu)
                            <span class="badge-soft warning"><i class="bi bi-question-circle"></i> Ditandai ragu</span>
                        @endif
                    </div>
                    <span class="text-muted-2" style="font-size:.76rem">
                        <i class="bi bi-lightning-charge"></i> Nilai benar: {{ number_format((float) $tes->tes_score_right, 1) }}
                    </span>
                </div>

                <div class="question-text mb-4">{!! $current->soal->detail_html !!}</div>

                @if ($current->soal->audio_url)
                    <div class="mb-4 p-3" style="border-radius:14px;background:#f8fafc;border:1px solid var(--line)">
                        <div class="form-label mb-2"><i class="bi bi-volume-up me-1"></i> Audio Soal</div>
                        <audio controls class="w-100" @if ($current->soal->soal_audio_play) autoplay @endif src="{{ $current->soal->audio_url }}"></audio>
                    </div>
                @endif

                <form method="POST" action="{{ route('peserta.ujian.jawab', $tes) }}">
                    @csrf
                    <input type="hidden" name="tessoal_id" value="{{ $current->tessoal_id }}">

                    @if ($tipe === 1)
                        <div class="d-flex flex-column gap-2">
                            @foreach ($current->pilihan as $pilihan)
                                @php
                                    $jawaban = $pilihan->jawaban;
                                @endphp
                                <label class="answer-option">
                                    <input type="radio" name="jawaban_id" value="{{ $pilihan->soaljawaban_jawaban_id }}" class="form-check-input mt-1" @checked($terpilih && $terpilih->soaljawaban_jawaban_id === $pilihan->soaljawaban_jawaban_id)>
                                    <span class="answer-key">{{ chr(65 + $loop->index) }}</span>
                                    <span class="flex-grow-1 answer-text">{!! $jawaban?->detail_html !!}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($tipe === 3)
                        <div class="mb-3">
                            <label class="form-label">Jawaban Singkat</label>
                            <input type="text" name="jawaban_text" class="form-control" value="{{ $current->tessoal_jawaban_text }}" placeholder="Tulis jawaban singkat..." required>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">Jawaban Essay</label>
                            <textarea name="jawaban_text" class="form-control" rows="7" placeholder="Tulis jawaban lengkap kamu di sini..." required>{{ $current->tessoal_jawaban_text }}</textarea>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top" style="border-color:var(--line)!important">
                        <button type="submit" name="aksi" value="simpan" class="btn btn-soft"><i class="bi bi-save me-1"></i> Simpan Jawaban</button>
                        <button type="submit" name="aksi" value="lanjut" class="btn btn-gradient">Simpan &amp; Lanjut <i class="bi bi-arrow-right ms-1"></i></button>
                        <button type="submit" name="aksi" value="selesai" class="btn btn-ghost ms-auto" onclick="return confirm('Selesaikan ujian sekarang? Pastikan semua jawaban sudah diperiksa.')"><i class="bi bi-flag me-1"></i> Selesai Ujian</button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="exam-nav-card">
            <div class="glass-card card-pad mb-3" data-aos="fade-up">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold" style="font-size:.9rem">Navigasi Soal</div>
                    <span class="badge-soft">{{ $dijawab }}/{{ $daftarSoal->count() }}</span>
                </div>

                <div class="progress mb-3" style="height:6px;border-radius:99px">
                    <div class="progress-bar" role="progressbar" style="width: {{ $persen }}%;background:linear-gradient(90deg,#6366f1,#8b5cf6)"></div>
                </div>

                <div class="nav-grid mb-3">
                    @foreach ($daftarSoal as $item)
                        <a href="{{ route('peserta.ujian.kerjakan', ['tes' => $tes, 'soal' => $item->tessoal_id]) }}"
                           class="nav-num {{ $item->tessoal_id === $current->tessoal_id ? 'aktif' : '' }} {{ $item->tessoal_change_time !== null && $item->tessoal_id !== $current->tessoal_id ? 'dijawab' : '' }} {{ $item->tessoal_ragu && $item->tessoal_id !== $current->tessoal_id ? 'ragu' : '' }}"
                           title="Soal {{ $loop->iteration }}">
                            {{ $loop->iteration }}
                        </a>
                    @endforeach
                </div>

                <div class="d-flex flex-column gap-2" style="font-size:.74rem;color:#64748b">
                    <div class="d-flex align-items-center gap-2"><span class="nav-num dijawab" style="width:18px;height:18px;border-radius:5px"></span> Sudah dijawab</div>
                    <div class="d-flex align-items-center gap-2"><span class="nav-num ragu" style="width:18px;height:18px;border-radius:5px"></span> Ragu-ragu</div>
                    <div class="d-flex align-items-center gap-2"><span class="nav-num" style="width:18px;height:18px;border-radius:5px"></span> Belum dijawab</div>
                </div>
            </div>

            <div class="glass-card card-pad" data-aos="fade-up" data-aos-delay="60">
                <form method="POST" action="{{ route('peserta.ujian.ragu', $tes) }}">
                    @csrf
                    <input type="hidden" name="tessoal_id" value="{{ $current->tessoal_id }}">
                    <button class="btn {{ $current->tessoal_ragu ? 'btn-soft' : 'btn-ghost' }} w-100 mb-2">
                        <i class="bi bi-question-circle me-1"></i> {{ $current->tessoal_ragu ? 'Hapus Tanda Ragu' : 'Tandai Ragu-ragu' }}
                    </button>
                </form>
                <div class="text-muted-2 text-center" style="font-size:.72rem">
                    Jawaban tersimpan otomatis saat menekan tombol simpan.
                </div>
            </div>
        </aside>
    </div>
@endsection
