@extends('layouts.admin')

@section('title', 'Soal ' . $topik->topik_nama)
@section('heading', 'Bank Soal')
@section('eyebrow', 'Bank Soal')

@php $bolehKelola = $topik->dapatDikelolaOleh(auth('web')->user()); @endphp

@section('content')
    <div class="glass-card card-pad mb-3" data-aos="fade-up">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <a href="{{ route('admin.soal.index') }}" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Kembali ke daftar grup</a>
                <h3 class="section-title mt-2 mb-1">{{ $topik->topik_nama }}</h3>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-soft neutral"><i class="bi bi-folder2-open"></i> {{ $topik->modul?->modul_nama ?? 'Tanpa Mata Pelajaran' }}</span>
                    <span class="badge-soft info"><i class="bi bi-person-badge"></i> {{ $topik->guru?->nama ?? 'Tanpa Guru' }}</span>
                    <span class="badge-soft {{ $topik->topik_tipe_ujian === 'uas' ? 'success' : ($topik->topik_tipe_ujian === 'uts' ? 'warning' : 'neutral') }}">{{ $topik->tipe_ujian_label }}</span>
                    @if ($topik->topik_aktif)
                        <span class="badge-soft success"><i class="bi bi-check-circle"></i> Aktif</span>
                    @else
                        <span class="badge-soft neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>
                    @endif
                    <span class="badge-soft info">{{ $soals->count() }} soal</span>
                    @if ($topik->topik_creation_time)
                        <span class="text-muted small">Dibuat {{ $topik->topik_creation_time->translatedFormat('d M Y') }}</span>
                    @endif
                </div>
                @if ($topik->topik_detail)
                    <p class="section-sub mt-2 mb-0">{{ \App\Support\HtmlText::plain($topik->topik_detail) }}</p>
                @endif
            </div>
            @if ($bolehKelola)
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.soal.template', ['format' => 'csv']) }}" class="btn btn-ghost"><i class="bi bi-download me-1"></i> Template CSV</a>
                    <a href="{{ route('admin.soal.template', ['format' => 'xlsx']) }}" class="btn btn-ghost"><i class="bi bi-file-earmark-excel me-1"></i> Template Excel</a>
                    <button class="btn btn-soft" data-bs-toggle="modal" data-bs-target="#importSoalModal"><i class="bi bi-upload me-1"></i> Import Soal</button>
                    <a href="{{ route('admin.soal.create', ['topik' => $topik->topik_id]) }}" class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Tambah Soal</a>
                </div>
            @endif
        </div>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Soal</th>
                        <th style="width: 130px;">Tipe</th>
                        <th style="width: 90px;">Level</th>
                        <th style="width: 140px;">Nama Guru</th>
                        <th style="width: 120px;">Tipe Ujian</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 130px;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($soals as $soal)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="soal-text-table">{{ Str::limit(\App\Support\HtmlText::plain($soal->soal_detail), 180) }}</div>
                                <div class="text-muted small mt-1">{{ $soal->jawabans_count }} pilihan jawaban</div>
                            </td>
                            <td>
                                <span class="badge-soft {{ (int) $soal->soal_tipe === 1 ? 'info' : ((int) $soal->soal_tipe === 2 ? 'warning' : 'neutral') }}">{{ $soal->tipe_label }}</span>
                            </td>
                            <td>
                                <span class="badge-soft {{ $soal->soal_difficulty >= 3 ? 'danger' : ($soal->soal_difficulty === 2 ? 'warning' : 'success') }}">Level {{ $soal->soal_difficulty }}</span>
                            </td>
                            <td>
                                @if ($soal->creator)
                                    <div class="fw-semibold" style="font-size:.8rem">{{ $soal->creator->nama }}</div>
                                    <div class="text-muted small">{{ $soal->creator->username }}</div>
                                @else
                                    <span class="text-muted small">Tidak diketahui</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-soft {{ $topik->topik_tipe_ujian === 'uas' ? 'success' : ($topik->topik_tipe_ujian === 'uts' ? 'warning' : 'neutral') }}">
                                    {{ $topik->tipe_ujian_label }}
                                </span>
                            </td>
                            <td>
                                @if ($soal->soal_aktif)
                                    <span class="badge-soft success"><i class="bi bi-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge-soft neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($bolehKelola)
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.soal.edit', $soal) }}" class="btn btn-soft btn-sm" title="Edit soal"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.soal.destroy', $soal) }}" onsubmit="return confirm('Hapus soal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm text-danger" title="Hapus soal"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-patch-question"></i>
                                    Belum ada soal pada grup ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($bolehKelola)
        <div class="modal fade" id="importSoalModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.soal.import') }}" enctype="multipart/form-data" class="modal-content">
                    @csrf
                    <input type="hidden" name="topik_id" value="{{ $topik->topik_id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Soal ke {{ $topik->topik_nama }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">File CSV / Excel</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        </div>
                        <div class="text-muted small">
                            Gunakan <a href="{{ route('admin.soal.template', ['format' => 'csv']) }}">template CSV</a>
                            atau <a href="{{ route('admin.soal.template', ['format' => 'xlsx']) }}">template Excel</a> agar format kolom sesuai.
                            Tipe soal: <code>pg</code>, <code>essay</code>, atau <code>singkat</code>. Kunci pilihan ganda diisi huruf A-E.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-gradient"><i class="bi bi-upload me-1"></i> Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
