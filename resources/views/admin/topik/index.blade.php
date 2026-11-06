@extends('layouts.admin')

@section('title', 'Topik')
@section('heading', 'Data Topik')
@section('eyebrow', 'Bank Soal')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tambah Topik</h3>
                        <p class="section-sub">Topik menjadi wadah kumpulan soal.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.topik.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Modul</label>
                        <select name="topik_modul_id" class="form-select" required>
                            <option value="">Pilih modul</option>
                            @foreach ($moduls as $modul)
                                <option value="{{ $modul->modul_id }}" @selected(old('topik_modul_id') == $modul->modul_id)>{{ $modul->modul_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Topik</label>
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
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="topik_aktif" value="1" id="topik_aktif" @checked(old('topik_aktif', true))>
                        <label class="form-check-label" for="topik_aktif" style="font-size:.82rem">Topik aktif</label>
                    </div>
                    <button class="btn btn-gradient w-100"><i class="bi bi-plus-lg me-1"></i> Simpan Topik</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Daftar Topik</h3>
                        <p class="section-sub">{{ $topiks->total() }} topik ditemukan.</p>
                    </div>
                    <form method="GET" class="d-flex gap-2 flex-wrap">
                        <select name="modul" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                            <option value="">Semua modul</option>
                            @foreach ($moduls as $modul)
                                <option value="{{ $modul->modul_id }}" @selected(request('modul') == $modul->modul_id)>{{ $modul->modul_nama }}</option>
                            @endforeach
                        </select>
                        <select name="tipe" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                            <option value="">Semua tipe</option>
                            @foreach ($tipeUjian as $value => $label)
                                <option value="{{ $value }}" @selected(request('tipe') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="width:170px" placeholder="Cari topik...">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Topik</th>
                                <th>Modul</th>
                                <th>Nama Guru</th>
                                <th>Tipe Ujian</th>
                                <th>Soal</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topiks as $topik)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $topik->topik_nama }}</div>
                                        <div class="text-muted-2" style="font-size:.74rem">{{ Str::limit(strip_tags($topik->topik_detail), 60) ?: 'Tanpa deskripsi' }}</div>
                                    </td>
                                    <td><span class="badge-soft neutral">{{ $topik->modul?->modul_nama ?? '-' }}</span></td>
                                    <td>
                                        @if ($topik->guru)
                                            <span class="badge-soft info">{{ $topik->guru->nama }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-soft {{ $topik->topik_tipe_ujian === 'uas' ? 'success' : ($topik->topik_tipe_ujian === 'uts' ? 'warning' : 'neutral') }}">
                                            {{ $topik->tipe_ujian_label }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $topik->soals_count }}</td>
                                    <td>
                                        @if ($topik->topik_aktif)
                                            <span class="badge-soft success"><i class="bi bi-check-circle"></i> Aktif</span>
                                        @else
                                            <span class="badge-soft neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @php $bolehKelola = $topik->dapatDikelolaOleh(auth('web')->user()); @endphp
                                        <a href="{{ route('admin.soal.index', ['topik' => $topik->topik_id]) }}" class="btn btn-ghost btn-icon" title="Lihat soal"><i class="bi bi-patch-question"></i></a>
                                        @if ($bolehKelola)
                                            <button class="btn btn-soft btn-icon" data-bs-toggle="modal" data-bs-target="#editTopik{{ $topik->topik_id }}"><i class="bi bi-pencil"></i></button>
                                            <form method="POST" action="{{ route('admin.topik.destroy', $topik) }}" class="d-inline" onsubmit="return confirm('Hapus topik ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="bi bi-diagram-3"></i>
                                            Belum ada topik yang cocok.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $topiks->links() }}</div>
            </div>
        </div>
    </div>

    @foreach ($topiks as $topik)
        @continue(! $topik->dapatDikelolaOleh(auth('web')->user()))
        <div class="modal fade" id="editTopik{{ $topik->topik_id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.topik.update', $topik) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Topik</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Modul</label>
                                <select name="topik_modul_id" class="form-select" required>
                                    @foreach ($moduls as $modul)
                                        <option value="{{ $modul->modul_id }}" @selected($topik->topik_modul_id == $modul->modul_id)>{{ $modul->modul_nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Topik</label>
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
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="topik_aktif" value="1" id="topikAktif{{ $topik->topik_id }}" @checked($topik->topik_aktif)>
                                <label class="form-check-label" for="topikAktif{{ $topik->topik_id }}" style="font-size:.82rem">Topik aktif</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-gradient">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
