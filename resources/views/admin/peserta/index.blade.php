@extends('layouts.admin')

@section('title', 'Peserta')
@section('heading', 'Daftar Peserta')
@section('eyebrow', 'Data Peserta')

@section('content')
    @include('partials.grafik-prestasi', [
        'chartPrestasi' => $chartPrestasi,
        'chartId' => 'grafikPrestasiPeserta',
        'chartTitle' => 'Grafik Prestasi Peserta',
        'chartDescription' => 'Rata-rata nilai raport per jenjang dan kelas. Gunakan tombol jenjang untuk melihat kelas 1 sampai 6 (SD) atau 1 sampai 3 (SMP/SMA/SMK).',
    ])

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Data Peserta</h3>
                <p class="section-sub">{{ $pesertas->total() }} peserta terdaftar.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-ghost" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload me-1"></i> Import CSV</button>
                <a href="{{ route('admin.peserta.create') }}" class="btn btn-gradient"><i class="bi bi-person-plus me-1"></i> Tambah Peserta</a>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, username, atau email...">
            </div>
            <div class="col-md-3">
                <select name="grup" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua group</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->grup_id }}" @selected(request('grup') == $grup->grup_id)>{{ $grup->grup_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="jenjang" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua jenjang</option>
                    @foreach (\App\Models\UserGroup::jenjangOptions() as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('jenjang') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="tingkat" class="form-select" onchange="this.form.submit()">
                    <option value="">Kelas</option>
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected((string) request('tingkat') === (string) $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-soft flex-grow-1"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.peserta.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Username</th>
                        <th>Group</th>
                        <th>Kontak</th>
                        <th>Detail</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesertas as $peserta)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar" style="width:36px;height:36px;font-size:.82rem">{{ strtoupper(substr($peserta->nama, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $peserta->nama }}</div>
                                        <div class="text-muted-2" style="font-size:.73rem">{{ $peserta->user_email ?: 'Tanpa email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><code style="color:#4f46e5">{{ $peserta->user_name }}</code></td>
                            <td><span class="badge-soft neutral">{{ $peserta->grup?->grup_nama ?? '-' }}</span></td>
                            <td class="text-muted-2" style="font-size:.8rem">{{ $peserta->user_email ?: '-' }}</td>
                            <td class="text-muted-2" style="font-size:.8rem">{{ $peserta->user_detail ?: '-' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.peserta.edit', $peserta) }}" class="btn btn-soft btn-icon"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.peserta.destroy', $peserta) }}" class="d-inline" onsubmit="return confirm('Hapus peserta ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    Belum ada peserta yang cocok.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $pesertas->links() }}</div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.peserta.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Import Peserta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:.84rem;color:#475569">Unggah file CSV dengan kolom: <code>user_name</code>, <code>user_password</code>, <code>user_firstname</code>, <code>grup_nama</code>, <code>user_email</code> (opsional), <code>user_detail</code> (opsional).</p>
                        <a href="{{ route('admin.peserta.template') }}" class="btn btn-soft btn-sm mb-3"><i class="bi bi-download me-1"></i> Unduh Template CSV</a>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-gradient"><i class="bi bi-upload me-1"></i> Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
