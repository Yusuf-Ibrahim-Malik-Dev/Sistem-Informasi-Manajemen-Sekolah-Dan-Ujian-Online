@extends('layouts.admin')

@section('title', 'Orang Tua')
@section('heading', 'Akun Orang Tua')
@section('eyebrow', 'Data Peserta')

@section('content')
    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari Orang Tua</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama, username, email, atau telepon...">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-soft"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.orangtua.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('admin.orangtua.create') }}" class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Tambah Akun Orang Tua</a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Daftar Akun Orang Tua</h3>
                <p class="section-sub">{{ $orangTua->total() }} akun. Satu akun dapat terhubung ke beberapa anak.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Orang Tua</th>
                        <th>Kontak</th>
                        <th>Anak Terhubung</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orangTua as $ortu)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar" style="width:36px;height:36px;font-size:.82rem">{{ strtoupper(substr($ortu->ortu_nama, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $ortu->ortu_nama }}</div>
                                        <code style="font-size:.74rem;color:#4f46e5">{{ $ortu->ortu_username }}</code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:.82rem">{{ $ortu->ortu_email ?: '-' }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $ortu->ortu_telepon ?: '-' }}</div>
                            </td>
                            <td>
                                @forelse ($ortu->pesertas as $anak)
                                    <span class="badge-soft info mb-1">{{ $anak->nama }} &middot; {{ $anak->grup?->grup_nama ?? '-' }}</span>
                                @empty
                                    <span class="badge-soft neutral">Belum terhubung</span>
                                @endforelse
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.orangtua.edit', $ortu) }}" class="btn btn-soft btn-sm"><i class="bi bi-pencil-square me-1"></i> Edit</a>
                                <form method="POST" action="{{ route('admin.orangtua.destroy', $ortu) }}" class="d-inline" onsubmit="return confirm('Hapus akun orang tua ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash3 me-1"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    Belum ada akun orang tua. Tambahkan akun agar orang tua dapat memantau anak.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $orangTua->links() }}</div>
    </div>
@endsection
