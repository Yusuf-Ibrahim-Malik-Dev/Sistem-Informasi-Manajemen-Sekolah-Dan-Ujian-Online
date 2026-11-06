@extends('layouts.admin')

@section('title', 'Sekolah & Tahun Ajaran')
@section('heading', 'Sekolah & Tahun Ajaran')
@section('eyebrow', 'Enterprise Core')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tambah Sekolah</h3>
                        <p class="section-sub">Unit sekolah berada di bawah yayasan.</p>
                    </div>
                </div>

                @if ($foundations->isEmpty())
                    <div class="empty-state"><i class="bi bi-bank"></i> Tambahkan yayasan terlebih dahulu.</div>
                @else
                    <form method="POST" action="{{ route('admin.sekolah.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Yayasan</label>
                            <select name="foundation_id" class="form-select" required>
                                @foreach ($foundations as $foundation)
                                    <option value="{{ $foundation->id }}" @selected((int) old('foundation_id', $schoolContext->foundationId()) === $foundation->id)>{{ $foundation->foundation_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label">Nama Sekolah</label>
                                <input type="text" name="school_name" class="form-control" value="{{ old('school_name') }}" placeholder="SMA Nusantara 2" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label">Jenjang</label>
                                <select name="school_level_id" class="form-select" required>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}" @selected((int) old('school_level_id') === $level->id)>{{ $level->level_code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Kode Sekolah</label>
                                <input type="text" name="school_code" class="form-control" value="{{ old('school_code') }}" placeholder="SMA-N2" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">NPSN</label>
                                <input type="text" name="npsn" class="form-control" value="{{ old('npsn') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                        <input type="hidden" name="is_active" value="1">
                        <button class="btn btn-gradient w-100"><i class="bi bi-plus-lg me-1"></i> Simpan Sekolah</button>
                    </form>
                @endif
            </div>

            <div class="glass-card card-pad mt-4" data-aos="fade-up" data-aos-delay="40">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tambah Tahun Ajaran</h3>
                        <p class="section-sub">Semester 1 dan 2 dibuat otomatis.</p>
                    </div>
                </div>

                @if ($schools->isEmpty())
                    <div class="empty-state"><i class="bi bi-calendar-plus"></i> Tambahkan sekolah terlebih dahulu.</div>
                @else
                    <form method="POST" action="{{ route('admin.sekolah.tahun.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Sekolah</label>
                            <select name="school_id" class="form-select" required>
                                @foreach ($schools as $sekolah)
                                    <option value="{{ $sekolah->id }}" @selected((int) old('school_id', $schoolContext->schoolId()) === $sekolah->id)>{{ $sekolah->school_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Tahun Ajaran</label>
                            <input type="text" name="year_name" class="form-control" value="{{ old('year_name') }}" placeholder="2026/2027" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Mulai</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Selesai</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                            </div>
                        </div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <span class="form-check-label">Jadikan tahun ajaran aktif</span>
                        </label>
                        <button class="btn btn-gradient w-100"><i class="bi bi-calendar-plus me-1"></i> Simpan Tahun Ajaran</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Daftar Sekolah</h3>
                        <p class="section-sub">{{ $schools->count() }} sekolah dalam cakupan akses Anda.</p>
                    </div>
                    <a href="{{ route('admin.yayasan.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-bank me-1"></i> Kelola Yayasan</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Sekolah</th>
                                <th>Yayasan</th>
                                <th>Data</th>
                                <th>Tahun Aktif</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schools as $sekolah)
                                @php($tahunAktif = $sekolah->academicYears->firstWhere('is_active', true))
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $sekolah->school_name }}</div>
                                        <div class="text-muted-2" style="font-size:.74rem">{{ $sekolah->school_code }} · {{ $sekolah->level?->level_code ?? '-' }} · NPSN {{ $sekolah->npsn ?: '-' }}</div>
                                    </td>
                                    <td style="font-size:.8rem">{{ $sekolah->foundation?->foundation_name ?? '-' }}</td>
                                    <td>
                                        <span class="badge-soft neutral">{{ $sekolah->students_count }} siswa</span>
                                        <span class="badge-soft info">{{ $sekolah->groups_count }} kelas</span>
                                        <span class="badge-soft info">{{ $sekolah->tests_count }} tes</span>
                                    </td>
                                    <td>
                                        @if ($tahunAktif)
                                            <span class="badge-soft success">{{ $tahunAktif->year_name }}</span>
                                        @else
                                            <span class="badge-soft neutral">Belum diatur</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-icon" data-bs-toggle="modal" data-bs-target="#editSekolah{{ $sekolah->id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('admin.sekolah.destroy', $sekolah) }}" class="d-inline" onsubmit="return confirm('Hapus sekolah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"><div class="empty-state"><i class="bi bi-buildings"></i> Belum ada sekolah.</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card card-pad mt-4" data-aos="fade-up" data-aos-delay="100">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tahun Ajaran</h3>
                        <p class="section-sub">Riwayat periode akademik tersimpan per sekolah.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Sekolah</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($academicYears as $tahun)
                                <tr>
                                    <td class="fw-semibold">{{ $tahun->year_name }}</td>
                                    <td style="font-size:.8rem">{{ $tahun->school?->school_name ?? '-' }}</td>
                                    <td class="text-muted-2" style="font-size:.78rem">{{ $tahun->start_date?->format('d M Y') }} – {{ $tahun->end_date?->format('d M Y') }}</td>
                                    <td><span class="badge-soft {{ $tahun->is_active ? 'success' : 'neutral' }}">{{ $tahun->is_active ? 'Aktif' : 'Arsip' }}</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-icon" data-bs-toggle="modal" data-bs-target="#editTahun{{ $tahun->id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('admin.sekolah.tahun.destroy', $tahun) }}" class="d-inline" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"><div class="empty-state"><i class="bi bi-calendar-x"></i> Belum ada tahun ajaran.</div></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($schools as $sekolah)
        <div class="modal fade" id="editSekolah{{ $sekolah->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.sekolah.update', $sekolah) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Sekolah</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Yayasan</label>
                                <select name="foundation_id" class="form-select" required>
                                    @foreach ($foundations as $foundation)
                                        <option value="{{ $foundation->id }}" @selected($sekolah->foundation_id === $foundation->id)>{{ $foundation->foundation_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-7">
                                    <label class="form-label">Nama Sekolah</label>
                                    <input type="text" name="school_name" class="form-control" value="{{ $sekolah->school_name }}" required>
                                </div>
                                <div class="col-5">
                                    <label class="form-label">Jenjang</label>
                                    <select name="school_level_id" class="form-select" required>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->id }}" @selected($sekolah->school_level_id === $level->id)>{{ $level->level_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Kode Sekolah</label>
                                    <input type="text" name="school_code" class="form-control" value="{{ $sekolah->school_code }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">NPSN</label>
                                    <input type="text" name="npsn" class="form-control" value="{{ $sekolah->npsn }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ $sekolah->address }}</textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $sekolah->phone }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $sekolah->email }}">
                                </div>
                            </div>
                            <input type="hidden" name="is_active" value="0">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($sekolah->is_active)>
                                <span class="form-check-label">Sekolah aktif</span>
                            </label>
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

    @foreach ($academicYears as $tahun)
        <div class="modal fade" id="editTahun{{ $tahun->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.sekolah.tahun.update', $tahun) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Tahun Ajaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Tahun Ajaran</label>
                                <input type="text" name="year_name" class="form-control" value="{{ $tahun->year_name }}" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Mulai</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $tahun->start_date?->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Selesai</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $tahun->end_date?->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <input type="hidden" name="is_active" value="0">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($tahun->is_active)>
                                <span class="form-check-label">Jadikan tahun ajaran aktif</span>
                            </label>
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
