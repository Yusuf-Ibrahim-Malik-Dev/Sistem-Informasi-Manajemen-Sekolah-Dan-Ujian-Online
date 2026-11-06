@extends('layouts.admin')

@section('title', 'Yayasan')
@section('heading', 'Yayasan')
@section('eyebrow', 'Enterprise Core')

@section('content')
    <div class="row g-4">
        @if ($schoolContext->admin()?->isSystemScope())
            <div class="col-lg-4" data-aos="fade-up">
                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Tambah Yayasan</h3>
                            <p class="section-sub">Yayasan menjadi induk seluruh unit sekolah.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.yayasan.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Kode Yayasan</label>
                            <input type="text" name="foundation_code" class="form-control" value="{{ old('foundation_code') }}" placeholder="YPN" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Yayasan</label>
                            <input type="text" name="foundation_name" class="form-control" value="{{ old('foundation_name') }}" placeholder="Yayasan Pendidikan Nusantara" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Legal</label>
                            <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name') }}" placeholder="Opsional">
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
                        <button class="btn btn-gradient w-100"><i class="bi bi-plus-lg me-1"></i> Simpan Yayasan</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="{{ $schoolContext->admin()?->isSystemScope() ? 'col-lg-8' : 'col-12' }}" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Daftar Yayasan</h3>
                        <p class="section-sub">{{ $foundations->count() }} yayasan dalam cakupan akses Anda.</p>
                    </div>
                    <a href="{{ route('admin.sekolah.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-buildings me-1"></i> Kelola Sekolah</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Yayasan</th>
                                <th>Kontak</th>
                                <th>Sekolah</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($foundations as $foundation)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $foundation->foundation_name }}</div>
                                        <div class="text-muted-2" style="font-size:.75rem">{{ $foundation->foundation_code }}{{ $foundation->legal_name ? ' · '.$foundation->legal_name : '' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-size:.8rem">{{ $foundation->email ?: '-' }}</div>
                                        <div class="text-muted-2" style="font-size:.74rem">{{ $foundation->phone ?: '-' }}</div>
                                    </td>
                                    <td><span class="badge-soft info">{{ $foundation->schools_count }} sekolah</span></td>
                                    <td>
                                        <span class="badge-soft {{ $foundation->is_active ? 'success' : 'neutral' }}">{{ $foundation->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-icon" data-bs-toggle="modal" data-bs-target="#editYayasan{{ $foundation->id }}"><i class="bi bi-pencil"></i></button>
                                        @if ($schoolContext->admin()?->isSystemScope())
                                            <form method="POST" action="{{ route('admin.yayasan.destroy', $foundation) }}" class="d-inline" onsubmit="return confirm('Hapus yayasan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state"><i class="bi bi-bank"></i> Belum ada data yayasan.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card card-pad mt-4" data-aos="fade-up">
                <div class="d-flex gap-2">
                    <i class="bi bi-shield-check text-primary"></i>
                    <div style="font-size:.8rem;color:#475569">
                        Data yayasan, sekolah, dan tahun ajaran menjadi fondasi isolasi tenant. Pengguna dengan scope sekolah hanya dapat membuka data sekolah yang ditugaskan.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($foundations as $foundation)
        <div class="modal fade" id="editYayasan{{ $foundation->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.yayasan.update', $foundation) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Yayasan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Kode Yayasan</label>
                                <input type="text" name="foundation_code" class="form-control" value="{{ $foundation->foundation_code }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Yayasan</label>
                                <input type="text" name="foundation_name" class="form-control" value="{{ $foundation->foundation_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Legal</label>
                                <input type="text" name="legal_name" class="form-control" value="{{ $foundation->legal_name }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ $foundation->address }}</textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $foundation->phone }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $foundation->email }}">
                                </div>
                            </div>
                            <input type="hidden" name="is_active" value="0">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($foundation->is_active)>
                                <span class="form-check-label">Yayasan aktif</span>
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
