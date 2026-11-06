@extends('layouts.admin')

@section('title', 'Modul')
@section('heading', 'Data Modul')
@section('eyebrow', 'Bank Soal')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tambah Modul</h3>
                        <p class="section-sub">Kelompokkan topik berdasarkan mata pelajaran.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.modul.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Modul</label>
                        <input type="text" name="modul_nama" class="form-control" value="{{ old('modul_nama') }}" placeholder="Contoh: Matematika" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="modul_aktif" value="1" id="modul_aktif" @checked(old('modul_aktif', true))>
                        <label class="form-check-label" for="modul_aktif" style="font-size:.82rem">Modul aktif</label>
                    </div>
                    <button class="btn btn-gradient w-100"><i class="bi bi-plus-lg me-1"></i> Simpan Modul</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Daftar Modul</h3>
                        <p class="section-sub">{{ $moduls->count() }} modul terdaftar.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Modul</th>
                                <th>Topik</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($moduls as $modul)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="stat-icon bg-grad-1 mb-0" style="width:36px;height:36px;font-size:.9rem"><i class="bi bi-folder2-open"></i></div>
                                            <span class="fw-semibold">{{ $modul->modul_nama }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge-soft neutral">{{ $modul->topiks_count }} topik</span></td>
                                    <td>
                                        @if ($modul->modul_aktif)
                                            <span class="badge-soft success"><i class="bi bi-check-circle"></i> Aktif</span>
                                        @else
                                            <span class="badge-soft neutral"><i class="bi bi-pause-circle"></i> Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-soft btn-icon" data-bs-toggle="modal" data-bs-target="#editModul{{ $modul->modul_id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('admin.modul.destroy', $modul) }}" class="d-inline" onsubmit="return confirm('Hapus modul ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>


                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="bi bi-folder-x"></i>
                                            Belum ada modul. Tambahkan modul pertama Anda.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach ($moduls as $modul)
                    <div class="modal fade" id="editModul{{ $modul->modul_id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.modul.update', $modul) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Modul</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Modul</label>
                                            <input type="text" name="modul_nama" class="form-control" value="{{ $modul->modul_nama }}" required>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modul_aktif" value="1" id="aktif{{ $modul->modul_id }}" @checked($modul->modul_aktif)>
                                            <label class="form-check-label" for="aktif{{ $modul->modul_id }}" style="font-size:.82rem">Modul aktif</label>
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
            </div>
        </div>
    </div>
@endsection
