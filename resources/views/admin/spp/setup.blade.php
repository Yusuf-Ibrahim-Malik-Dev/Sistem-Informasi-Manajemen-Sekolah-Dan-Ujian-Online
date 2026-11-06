@extends('layouts.admin')

@section('title', 'Kategori & Tarif SPP')
@section('heading', 'Kategori & Tarif SPP')
@section('eyebrow', 'Keuangan')

@php
    $lockedSchoolId = $schoolContext?->schoolId();
    $defaultSchoolId = $lockedSchoolId ?? $schools->first()?->id;
    $defaultSchoolName = $schools->firstWhere('id', $defaultSchoolId)?->school_name;
    $showSchoolSelect = $schools->count() > 1 && ! $lockedSchoolId;
@endphp

@section('content')
    <div class="row g-4">
        <div class="col-xl-6" data-aos="fade-up">
            <div class="glass-card card-pad mb-4">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Kategori Biaya</h3>
                        <p class="section-sub">Contoh: SPP, uang gedung, seragam, kegiatan.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.spp.kategori.store') }}" class="row g-2 mb-4">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Sekolah</label>
                        @if ($showSchoolSelect)
                            <select name="school_id" class="form-select" required>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected((int) old('school_id', $defaultSchoolId) === $school->id)>{{ $school->school_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="school_id" value="{{ $defaultSchoolId }}">
                            <input type="text" class="form-control" value="{{ $defaultSchoolName ?? 'Belum ada sekolah' }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="category_code" value="{{ old('category_code') }}" class="form-control" placeholder="SPP" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="category_name" value="{{ old('category_name') }}" class="form-control" placeholder="SPP Bulanan" required>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="catRecurring" @checked(old('is_recurring'))>
                            <label class="form-check-label" for="catRecurring" style="font-size:.82rem">Berulang tiap bulan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActive" @checked(old('is_active', true))>
                            <label class="form-check-label" for="catActive" style="font-size:.82rem">Aktif</label>
                        </div>
                        <button class="btn btn-gradient ms-auto"><i class="bi bi-plus-lg me-1"></i> Simpan Kategori</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td><code>{{ $category->category_code }}</code><div class="text-muted-2" style="font-size:.72rem">{{ $category->school?->school_name }}</div></td>
                                    <td class="fw-semibold" style="font-size:.84rem">{{ $category->category_name }}</td>
                                    <td class="text-center text-muted-2" style="font-size:.78rem">{{ $category->is_recurring ? 'Bulanan' : 'Sekali' }}</td>
                                    <td class="text-center"><span class="badge-soft {{ $category->is_active ? 'success' : 'neutral' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#categoryEdit{{ $category->id }}"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" action="{{ route('admin.spp.kategori.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-tags"></i>Belum ada kategori biaya.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @foreach ($categories as $category)
                <div class="modal fade" id="categoryEdit{{ $category->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST" action="{{ route('admin.spp.kategori.update', $category) }}" class="modal-content">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Kategori</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Kode</label>
                                    <input type="text" name="category_code" value="{{ $category->category_code }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="category_name" value="{{ $category->category_name }}" class="form-control" required>
                                </div>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="catRecurring{{ $category->id }}" @checked($category->is_recurring)>
                                        <label class="form-check-label" for="catRecurring{{ $category->id }}" style="font-size:.82rem">Berulang</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActive{{ $category->id }}" @checked($category->is_active)>
                                        <label class="form-check-label" for="catActive{{ $category->id }}" style="font-size:.82rem">Aktif</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                                <button class="btn btn-gradient">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach

            <div class="glass-card card-pad" data-aos="fade-up">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Metode & Rekening Pembayaran</h3>
                        <p class="section-sub">Tambahkan rekening bank/ATM yang ingin ditampilkan ke orang tua saat membayar SPP atau biaya ujian.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.spp.metode.store') }}" class="row g-2 mb-4">
                    @csrf
                    @if ($showSchoolSelect)
                        <div class="col-md-4">
                            <label class="form-label">Sekolah</label>
                            <select name="school_id" class="form-select" required>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected((int) old('school_id', $defaultSchoolId) === $school->id)>{{ $school->school_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="school_id" value="{{ $defaultSchoolId }}">
                    @endif
                    <div class="col-md-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="method_code" value="{{ old('method_code') }}" class="form-control" placeholder="BSI" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Metode</label>
                        <input type="text" name="method_name" value="{{ old('method_name') }}" class="form-control" placeholder="Transfer Bank BSI" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank / ATM</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control" placeholder="BSI">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" class="form-control" placeholder="0123456789">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Atas Nama</label>
                        <input type="text" name="account_holder" value="{{ old('account_holder') }}" class="form-control" placeholder="Yayasan/Sekolah">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="show_to_guardian" value="1" id="showToGuardianBaru" checked>
                            <label class="form-check-label" for="showToGuardianBaru">Tampilkan ke orang tua</label>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Simpan</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Bank / Rekening</th>
                                <th class="text-center">Tampil</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($methods as $method)
                                <tr>
                                    <td><code>{{ $method->method_code }}</code><div class="text-muted-2" style="font-size:.72rem">{{ $method->school?->school_name }}</div></td>
                                    <td class="fw-semibold" style="font-size:.84rem">{{ $method->method_name }}</td>
                                    <td style="font-size:.8rem">
                                        <div class="fw-semibold">{{ $method->bank_name ?: '-' }}</div>
                                        <div>{{ $method->account_number ?: 'Belum diisi' }}</div>
                                        <div class="text-muted-2" style="font-size:.72rem">a.n. {{ $method->account_holder ?: '-' }}</div>
                                    </td>
                                    <td class="text-center"><span class="badge-soft {{ $method->show_to_guardian ? 'primary' : 'neutral' }}">{{ $method->show_to_guardian ? 'Ya' : 'Tidak' }}</span></td>
                                    <td class="text-center"><span class="badge-soft {{ $method->is_active ? 'success' : 'neutral' }}">{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#methodEdit{{ $method->id }}"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" action="{{ route('admin.spp.metode.destroy', $method) }}" class="d-inline" onsubmit="return confirm('Hapus metode ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-credit-card"></i>Belum ada metode pembayaran.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach ($methods as $method)
                    <div class="modal fade" id="methodEdit{{ $method->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <form method="POST" action="{{ route('admin.spp.metode.update', $method) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Metode & Rekening</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Kode</label>
                                            <input type="text" name="method_code" value="{{ $method->method_code }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Nama Metode</label>
                                            <input type="text" name="method_name" value="{{ $method->method_name }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Bank / ATM</label>
                                            <input type="text" name="bank_name" value="{{ $method->bank_name }}" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nomor Rekening</label>
                                            <input type="text" name="account_number" value="{{ $method->account_number }}" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Atas Nama</label>
                                            <input type="text" name="account_holder" value="{{ $method->account_holder }}" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Urutan Tampil</label>
                                            <input type="number" name="sort_order" value="{{ $method->sort_order }}" min="0" class="form-control">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="methodActive{{ $method->id }}" @checked($method->is_active)>
                                                <label class="form-check-label" for="methodActive{{ $method->id }}">Aktif</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="show_to_guardian" value="1" id="methodShow{{ $method->id }}" @checked($method->show_to_guardian)>
                                                <label class="form-check-label" for="methodShow{{ $method->id }}">Tampilkan ke orang tua</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-xl-6" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tarif Biaya</h3>
                        <p class="section-sub">Tarif default per kategori, tahun ajaran, dan jenjang kelas.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.spp.tarif.store') }}" class="row g-2 mb-4">
                    @csrf
                    @if ($showSchoolSelect)
                        <div class="col-md-6">
                            <label class="form-label">Sekolah</label>
                            <select name="school_id" class="form-select" required>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected((int) old('school_id', $defaultSchoolId) === $school->id)>{{ $school->school_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="school_id" value="{{ $defaultSchoolId }}">
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="fee_category_id" class="form-select" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('fee_category_id') === (string) $category->id)>{{ $category->category_name }} &middot; {{ $category->school?->school_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenjang Kelas</label>
                        <input type="text" name="grade_code" value="{{ old('grade_code') }}" class="form-control" placeholder="X, XI, XII">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nominal</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tarifActive" @checked(old('is_active', true))>
                            <label class="form-check-label" for="tarifActive" style="font-size:.82rem">Aktif</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berlaku Mulai</label>
                        <input type="date" name="effective_from" value="{{ old('effective_from') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berlaku Sampai</label>
                        <input type="date" name="effective_until" value="{{ old('effective_until') }}" class="form-control">
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Simpan Tarif</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jenjang</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($structures as $structure)
                                <tr>
                                    <td>
                                        <div class="fw-semibold" style="font-size:.84rem">{{ $structure->category?->category_name ?? '-' }}</div>
                                        <div class="text-muted-2" style="font-size:.72rem">{{ $structure->school?->school_name }}</div>
                                    </td>
                                    <td class="text-muted-2" style="font-size:.8rem">{{ $structure->grade_code ?: 'Semua' }}</td>
                                    <td class="text-end text-nowrap fw-semibold">Rp {{ number_format((float) $structure->amount, 0, ',', '.') }}</td>
                                    <td class="text-center"><span class="badge-soft {{ $structure->is_active ? 'success' : 'neutral' }}">{{ $structure->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#structureEdit{{ $structure->id }}"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" action="{{ route('admin.spp.tarif.destroy', $structure) }}" class="d-inline" onsubmit="return confirm('Hapus tarif ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-cash-coin"></i>Belum ada tarif biaya.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @foreach ($structures as $structure)
                <div class="modal fade" id="structureEdit{{ $structure->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST" action="{{ route('admin.spp.tarif.update', $structure) }}" class="modal-content">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Tarif</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select name="fee_category_id" class="form-select" required>
                                        @foreach ($categories->where('school_id', $structure->school_id) as $category)
                                            <option value="{{ $category->id }}" @selected($structure->fee_category_id === $category->id)>{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label">Jenjang Kelas</label>
                                        <input type="text" name="grade_code" value="{{ $structure->grade_code }}" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Nominal</label>
                                        <input type="number" name="amount" value="{{ (float) $structure->amount }}" min="0" step="0.01" class="form-control" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Berlaku Mulai</label>
                                        <input type="date" name="effective_from" value="{{ $structure->effective_from?->format('Y-m-d') }}" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Berlaku Sampai</label>
                                        <input type="date" name="effective_until" value="{{ $structure->effective_until?->format('Y-m-d') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tarifActive{{ $structure->id }}" @checked($structure->is_active)>
                                    <label class="form-check-label" for="tarifActive{{ $structure->id }}" style="font-size:.82rem">Aktif</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                                <button class="btn btn-gradient">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
