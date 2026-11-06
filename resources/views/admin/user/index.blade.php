@extends('layouts.admin')

@section('title', 'User Administrator')
@section('heading', 'User Administrator')
@section('eyebrow', 'Sistem')

@section('content')
    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Akun Administrator</h3>
                <p class="section-sub">{{ $admins->total() }} akun pengelola sistem.</p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari user..." style="width:180px">
                    <button class="btn btn-ghost btn-sm"><i class="bi bi-search"></i></button>
                </form>
                <a href="{{ route('admin.user.create') }}" class="btn btn-gradient"><i class="bi bi-person-plus me-1"></i> Tambah User</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>Keterangan</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar" style="width:36px;height:36px;font-size:.82rem">{{ strtoupper(substr($admin->nama, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $admin->nama }}</div>
                                        @if ($admin->id === auth('web')->id())
                                            <div class="badge-soft info" style="font-size:.65rem">Akun Anda</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><code style="color:#4f46e5">{{ $admin->username }}</code></td>
                            <td><span class="badge-soft {{ $admin->level === 'admin' ? 'info' : 'neutral' }}">{{ $admin->level }}</span></td>
                            <td class="text-muted-2" style="font-size:.8rem">{{ $admin->keterangan ?: '-' }}</td>
                            <td class="text-muted-2" style="font-size:.76rem">{{ $admin->ts?->translatedFormat('d M Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.user.edit', $admin) }}" class="btn btn-soft btn-icon"><i class="bi bi-pencil"></i></a>
                                @if ($admin->id !== auth('web')->id())
                                    <form method="POST" action="{{ route('admin.user.destroy', $admin) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-person-x"></i>
                                    Belum ada user administrator.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $admins->links() }}</div>
    </div>
@endsection
