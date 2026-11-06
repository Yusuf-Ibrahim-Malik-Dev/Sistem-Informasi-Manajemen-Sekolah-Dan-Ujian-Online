@extends('layouts.admin')

@section('title', 'Group Peserta')
@section('heading', 'Group Peserta')
@section('eyebrow', 'Data Peserta')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tambah Group</h3>
                        <p class="section-sub">Group digunakan untuk menentukan akses tes.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.grup.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Group</label>
                        <input type="text" name="grup_nama" class="form-control" value="{{ old('grup_nama') }}" placeholder="Contoh: XI MM" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label">Jenjang</label>
                            <select name="grup_jenjang" class="form-select" data-jenjang required>
                                @foreach (\App\Models\UserGroup::jenjangOptions() as $kode => $label)
                                    <option value="{{ $kode }}" @selected(old('grup_jenjang', 'SMA') === $kode)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label">Kelas</label>
                            <select name="grup_tingkat" class="form-select" data-tingkat data-selected="{{ old('grup_tingkat', 1) }}" required></select>
                        </div>
                    </div>
                    <button class="btn btn-gradient w-100"><i class="bi bi-plus-lg me-1"></i> Simpan Group</button>
                </form>

                <div class="mt-4 p-3" style="border-radius:14px;background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.14)">
                    <div class="d-flex gap-2">
                        <i class="bi bi-info-circle text-primary"></i>
                        <div style="font-size:.78rem;color:#475569">Peserta hanya dapat mengikuti tes yang group-nya terdaftar pada tes tersebut.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Daftar Group</h3>
                        <p class="section-sub">{{ $grups->count() }} group terdaftar.</p>
                    </div>
                    <a href="{{ route('admin.peserta.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-people me-1"></i> Daftar Peserta</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Jenjang / Kelas</th>
                                <th>Peserta</th>
                                <th>Tes</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grups as $grup)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="stat-icon bg-grad-5 mb-0" style="width:36px;height:36px;font-size:.9rem"><i class="bi bi-collection"></i></div>
                                            <span class="fw-semibold">{{ $grup->grup_nama }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge-soft info">{{ $grup->jenjang_label }} Kelas {{ (int) $grup->grup_tingkat }}</span></td>
                                    <td><span class="badge-soft neutral">{{ $grup->pesertas_count }} peserta</span></td>
                                    <td><span class="badge-soft info">{{ $grup->tes_count }} tes</span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-soft btn-icon" data-bs-toggle="modal" data-bs-target="#editGrup{{ $grup->grup_id }}"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('admin.grup.destroy', $grup) }}" class="d-inline" onsubmit="return confirm('Hapus group ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="bi bi-collection"></i>
                                            Belum ada group peserta.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($grups as $grup)
        <div class="modal fade" id="editGrup{{ $grup->grup_id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.grup.update', $grup) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Group</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Nama Group</label>
                            <input type="text" name="grup_nama" class="form-control" value="{{ $grup->grup_nama }}" required>
                            <div class="row g-2 mt-1">
                                <div class="col-7">
                                    <label class="form-label">Jenjang</label>
                                    <select name="grup_jenjang" class="form-select" data-jenjang required>
                                        @foreach (\App\Models\UserGroup::jenjangOptions() as $kode => $label)
                                            <option value="{{ $kode }}" @selected($grup->grup_jenjang === $kode)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label">Kelas</label>
                                    <select name="grup_tingkat" class="form-select" data-tingkat data-selected="{{ $grup->grup_tingkat }}" required></select>
                                </div>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-jenjang]').forEach(function (selectJenjang) {
                const form = selectJenjang.closest('form');
                const selectTingkat = form.querySelector('[data-tingkat]');
                const selected = selectTingkat.dataset.selected || 1;

                function renderTingkat() {
                    const maks = selectJenjang.value === 'SD' ? 6 : 3;
                    const nilai = Math.min(parseInt(selectTingkat.value || selected, 10), maks);

                    selectTingkat.innerHTML = '';
                    for (let i = 1; i <= maks; i++) {
                        const opsi = document.createElement('option');
                        opsi.value = i;
                        opsi.textContent = 'Kelas ' + i;
                        opsi.selected = i === nilai;
                        selectTingkat.appendChild(opsi);
                    }
                }

                selectJenjang.addEventListener('change', renderTingkat);
                renderTingkat();
            });
        });
    </script>
@endpush
