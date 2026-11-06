@extends('layouts.admin')

@section('title', 'Soal')
@section('heading', 'Bank Soal')
@section('eyebrow', 'Bank Soal')

@section('content')
    <div class="glass-card card-pad mb-3" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Daftar Grup Soal</h3>
                <p class="section-sub">{{ $totalTopik }} grup &middot; {{ $totalSoal }} soal tersimpan. Kelola soal berdasarkan mata pelajaran dan topik.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.soal.template', ['format' => 'csv']) }}" class="btn btn-ghost"><i class="bi bi-download me-1"></i> Template CSV</a>
                <a href="{{ route('admin.soal.template', ['format' => 'xlsx']) }}" class="btn btn-ghost"><i class="bi bi-file-earmark-excel me-1"></i> Template Excel</a>
                <button class="btn btn-soft" data-bs-toggle="modal" data-bs-target="#importSoalModal"><i class="bi bi-upload me-1"></i> Import Soal</button>
                <button class="btn btn-soft" data-bs-toggle="modal" data-bs-target="#createGrupModal"><i class="bi bi-folder-plus me-1"></i> Tambah Grup</button>
                <a href="{{ route('admin.soal.create') }}" class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Tambah Soal</a>
            </div>
        </div>

        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari topik...">
            </div>
            <div class="col-md-3">
                <select name="modul" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua mata pelajaran</option>
                    @foreach ($moduls as $modul)
                        <option value="{{ $modul->modul_id }}" @selected(request('modul') == $modul->modul_id)>{{ $modul->modul_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="tipe" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua tipe ujian</option>
                    @foreach ($tipeUjian as $value => $label)
                        <option value="{{ $value }}" @selected(request('tipe') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-soft flex-grow-1"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('admin.soal.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 180px;">Mata Pelajaran</th>
                        <th>Topik</th>
                        <th style="width: 160px;">Nama Guru</th>
                        <th style="width: 130px;">Tipe Ujian</th>
                        <th style="width: 130px;">Tanggal</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 110px;" class="text-center">Jumlah Soal</th>
                        <th style="width: 150px;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topiks as $topik)
                        <tr>
                            <td>
                                <span class="badge-soft neutral">{{ $topik->modul?->modul_nama ?? 'Tanpa Mata Pelajaran' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $topik->topik_nama }}</div>
                                @if ($topik->topik_detail)
                                    <div class="text-muted small mt-1">{{ Str::limit(\App\Support\HtmlText::plain($topik->topik_detail), 80) }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $namaGuru = $topik->guru
                                        ? collect([$topik->guru->nama])
                                        : $topik->soals
                                            ->map(fn ($soal) => $soal->creator?->nama)
                                            ->filter()
                                            ->unique()
                                            ->values();
                                @endphp
                                @if ($namaGuru->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($namaGuru as $guru)
                                            <span class="badge-soft info">{{ $guru }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-soft {{ $topik->topik_tipe_ujian === 'uas' ? 'success' : ($topik->topik_tipe_ujian === 'uts' ? 'warning' : 'neutral') }}">
                                    {{ $topik->tipe_ujian_label }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $topik->topik_creation_time?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>
                                @if ($topik->topik_aktif)
                                    <span class="badge-soft success"><i class="bi bi-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge-soft neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge-soft info">{{ $topik->soals_count }} soal</span>
                            </td>
                            <td class="text-end">
                                @php $bolehKelola = $topik->dapatDikelolaOleh(auth('web')->user()); @endphp
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.soal.show', $topik) }}" class="btn btn-soft btn-sm" title="Lihat semua soal"><i class="bi bi-eye"></i></a>
                                    @if ($bolehKelola)
                                        <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#editGrup{{ $topik->topik_id }}" title="Edit grup"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('admin.soal.grup.destroy', $topik) }}" onsubmit="return confirm('Hapus grup {{ $topik->topik_nama }}? Semua soal di dalamnya juga akan dihapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm text-danger" title="Hapus grup"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-folder2-open"></i>
                                    Belum ada grup soal yang cocok dengan filter.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $topiks->links() }}</div>
    </div>

    <div class="modal fade" id="createGrupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.soal.grup.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Grup Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <select name="topik_modul_id" class="form-select" required>
                            <option value="">Pilih mata pelajaran</option>
                            @foreach ($moduls as $modul)
                                <option value="{{ $modul->modul_id }}" @selected(old('topik_modul_id') == $modul->modul_id)>{{ $modul->modul_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Topik</label>
                        <input type="text" name="topik_nama" class="form-control" value="{{ old('topik_nama') }}" placeholder="Contoh: Aljabar" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Guru</label>
                        <select name="topik_guru_id" class="form-select" required>
                            <option value="">Pilih guru</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}" @selected(old('topik_guru_id', auth('web')->id()) == $guru->id)>{{ $guru->nama }} ({{ $guru->username }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Ujian</label>
                        <select name="topik_tipe_ujian" class="form-select" required>
                            @foreach ($tipeUjian as $value => $label)
                                <option value="{{ $value }}" @selected(old('topik_tipe_ujian', 'um') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="topik_detail" class="form-control" rows="3" placeholder="Deskripsi singkat topik">{{ old('topik_detail') }}</textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="topik_aktif" value="1" id="createTopikAktif" @checked(old('topik_aktif', true))>
                        <label class="form-check-label" for="createTopikAktif">Grup aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-gradient">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($topiks as $topik)
        @continue(! $topik->dapatDikelolaOleh(auth('web')->user()))
        <div class="modal fade" id="editGrup{{ $topik->topik_id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.soal.grup.update', $topik) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Grup Soal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran</label>
                            <select name="topik_modul_id" class="form-select" required>
                                @foreach ($moduls as $modul)
                                    <option value="{{ $modul->modul_id }}" @selected($topik->topik_modul_id == $modul->modul_id)>{{ $modul->modul_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Topik</label>
                            <input type="text" name="topik_nama" class="form-control" value="{{ $topik->topik_nama }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Guru</label>
                            <select name="topik_guru_id" class="form-select" required>
                                <option value="">Pilih guru</option>
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}" @selected($topik->topik_guru_id == $guru->id)>{{ $guru->nama }} ({{ $guru->username }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Ujian</label>
                            <select name="topik_tipe_ujian" class="form-select" required>
                                @foreach ($tipeUjian as $value => $label)
                                    <option value="{{ $value }}" @selected($topik->topik_tipe_ujian === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="topik_detail" class="form-control" rows="3">{{ $topik->topik_detail }}</textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="topik_aktif" value="1" id="editTopikAktif{{ $topik->topik_id }}" @checked($topik->topik_aktif)>
                            <label class="form-check-label" for="editTopikAktif{{ $topik->topik_id }}">Grup aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-gradient">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="importSoalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.soal.import') }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Grup Soal Tujuan</label>
                        <select name="topik_id" class="form-select" required>
                            <option value="">Pilih grup soal</option>
                            @foreach ($topikImport as $topik)
                                <option value="{{ $topik->topik_id }}" @selected(old('topik_id') == $topik->topik_id)>
                                    {{ $topik->modul?->modul_nama }} &mdash; {{ $topik->topik_nama }}
                                </option>
                            @endforeach
                        </select>
                        @if ($topikImport->isEmpty())
                            <div class="text-muted small mt-2">Belum ada grup soal yang bisa Anda kelola. Buat grup terlebih dahulu.</div>
                        @endif
                    </div>
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
                    <button class="btn btn-gradient" @disabled($topikImport->isEmpty())><i class="bi bi-upload me-1"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
@endsection
