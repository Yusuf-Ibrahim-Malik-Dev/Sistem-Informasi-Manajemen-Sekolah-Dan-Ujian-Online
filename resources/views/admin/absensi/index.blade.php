@extends('layouts.admin')

@section('title', 'Absensi')
@section('heading', 'Absensi Peserta')
@section('eyebrow', 'Data Peserta')

@section('content')
    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value">{{ $rekap['hadir'] ?? 0 }}</div>
            <div class="stat-label">Hadir</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-1"><i class="bi bi-envelope-check"></i></div>
            <div class="stat-value">{{ $rekap['izin'] ?? 0 }}</div>
            <div class="stat-label">Izin</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-3"><i class="bi bi-bandaid"></i></div>
            <div class="stat-value">{{ $rekap['sakit'] ?? 0 }}</div>
            <div class="stat-label">Sakit</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-2"><i class="bi bi-person-dash"></i></div>
            <div class="stat-value">{{ $rekap['alpha'] ?? 0 }}</div>
            <div class="stat-label">Alpha</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari Peserta</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama atau username...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Group</label>
                <select name="grup" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua group</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->grup_id }}" @selected((string) request('grup') === (string) $grup->grup_id)>{{ $grup->grup_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Absensi::statusOptions() as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 justify-content-md-end">
                <button class="btn btn-soft"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.absensi.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                <button type="button" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#absensiCreate"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Catatan Absensi</h3>
                <p class="section-sub">{{ $absensis->total() }} data. Absensi otomatis dibuat saat peserta memulai ujian dan dapat dikoreksi admin.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Peserta</th>
                        <th>Ujian</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Sumber</th>
                        <th>Keterangan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensis as $absen)
                        <tr>
                            <td class="fw-semibold text-nowrap">{{ $absen->absensi_tanggal?->translatedFormat('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $absen->peserta?->nama ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $absen->peserta?->grup?->grup_nama ?? '-' }}</div>
                            </td>
                            <td class="text-muted-2" style="font-size:.82rem">{{ $absen->tes?->tes_nama ?? 'Absensi manual' }}</td>
                            <td class="text-center"><span class="badge-soft {{ $absen->status_color }}">{{ $absen->status_label }}</span></td>
                            <td class="text-center">
                                <span class="badge-soft {{ $absen->absensi_sumber === \App\Models\Absensi::SUMBER_UJIAN ? 'info' : 'neutral' }}">
                                    {{ $absen->absensi_sumber === \App\Models\Absensi::SUMBER_UJIAN ? 'Ujian' : 'Manual' }}
                                </span>
                            </td>
                            <td class="text-muted-2" style="font-size:.8rem">{{ $absen->absensi_keterangan ?: '-' }}</td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#absensiEdit{{ $absen->absensi_id }}"><i class="bi bi-pencil-square me-1"></i> Koreksi</button>
                                <form method="POST" action="{{ route('admin.absensi.destroy', $absen) }}" class="d-inline" onsubmit="return confirm('Hapus data absensi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-calendar-x"></i>
                                    Belum ada data absensi sesuai filter.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $absensis->links() }}</div>
    </div>

    <div class="modal fade" id="absensiCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.absensi.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold">Tambah / Koreksi Absensi</h5>
                        <div class="text-muted-2" style="font-size:.78rem">Jika ujian dipilih, data absensi peserta pada ujian tersebut akan diperbarui.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Peserta</label>
                        <select name="absensi_user_id" class="form-select" required>
                            <option value="">Pilih peserta</option>
                            @foreach ($pesertas as $peserta)
                                <option value="{{ $peserta->user_id }}">{{ $peserta->nama }} &middot; {{ $peserta->grup?->grup_nama ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ujian (opsional)</label>
                        <select name="absensi_tes_id" class="form-select">
                            <option value="">Absensi manual (tanpa ujian)</option>
                            @foreach ($tes as $item)
                                <option value="{{ $item->tes_id }}">{{ $item->tes_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="absensi_tanggal" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="absensi_status" class="form-select" required>
                                @foreach (\App\Models\Absensi::statusOptions() as $nilai => $label)
                                    <option value="{{ $nilai }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="absensi_keterangan" class="form-control" rows="2" maxlength="255" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($absensis as $absen)
        <div class="modal fade" id="absensiEdit{{ $absen->absensi_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.absensi.update', $absen) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold">Koreksi Absensi</h5>
                            <div class="text-muted-2" style="font-size:.78rem">{{ $absen->peserta?->nama ?? '-' }} &middot; {{ $absen->tes?->tes_nama ?? 'Manual' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="absensi_tanggal" class="form-control" value="{{ $absen->absensi_tanggal?->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Status</label>
                                <select name="absensi_status" class="form-select" required>
                                    @foreach (\App\Models\Absensi::statusOptions() as $nilai => $label)
                                        <option value="{{ $nilai }}" @selected($absen->absensi_status === $nilai)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="absensi_keterangan" class="form-control" rows="2" maxlength="255">{{ $absen->absensi_keterangan }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan Koreksi</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
