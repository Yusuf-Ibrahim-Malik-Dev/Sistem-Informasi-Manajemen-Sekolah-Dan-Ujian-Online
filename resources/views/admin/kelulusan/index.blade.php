@extends('layouts.admin')

@section('title', 'Kelulusan')
@section('heading', 'Kelulusan')
@section('eyebrow', 'Data Peserta')

@section('content')
    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-1"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $statistik['total'] }}</div>
            <div class="stat-label">Peserta Diproses</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-4"><i class="bi bi-mortarboard"></i></div>
            <div class="stat-value">{{ $statistik['kelas_akhir'] }}</div>
            <div class="stat-label">Peserta Kelas Akhir</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-3"><i class="bi bi-award"></i></div>
            <div class="stat-value">{{ $statistik['lulus'] }}</div>
            <div class="stat-label">Lulus</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-2"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value">{{ $statistik['belum'] }}</div>
            <div class="stat-label">Belum Lulus</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari Peserta</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama atau username...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Group</label>
                <select name="grup" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua group</option>
                    @foreach ($grups as $grup)
                        <option value="{{ $grup->grup_id }}" @selected((string) request('grup') === (string) $grup->grup_id)>{{ $grup->grup_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jenjang</label>
                <select name="jenjang" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua jenjang</option>
                    @foreach (\App\Models\UserGroup::jenjangOptions() as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('jenjang') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kelas</label>
                <select name="tingkat" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua kelas</option>
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected((string) request('tingkat') === (string) $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="lulus" @selected(request('status') === 'lulus')>Lulus</option>
                    <option value="belum" @selected(request('status') === 'belum')>Belum lulus</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-soft"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.kelulusan.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Status Kelulusan Peserta</h3>
                <p class="section-sub">{{ $daftar->total() }} peserta sesuai filter. Surat keterangan lulus dapat dicetak untuk peserta yang sudah lulus.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Kelas</th>
                        <th class="text-center">Mata Pelajaran</th>
                        <th class="text-end">Rata-rata</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $row)
                        @php
                            $peserta = $row['peserta'];
                            $kelulusan = $row['kelulusan'];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar" style="width:36px;height:36px;font-size:.82rem">{{ strtoupper(substr($peserta->nama, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $peserta->nama }}</div>
                                        <code style="font-size:.74rem;color:#4f46e5">{{ $peserta->user_name }}</code>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-soft info">{{ $kelulusan['kelas'] }}</span></td>
                            <td class="text-center text-muted-2">{{ $kelulusan['rapors']->count() }} / {{ $kelulusan['moduls']->count() }}</td>
                            <td class="text-end fw-800" style="color:#4338ca">{{ $kelulusan['rata_rata'] !== null ? number_format($kelulusan['rata_rata'], 1) : '-' }}</td>
                            <td class="text-center">
                                <span class="badge-soft {{ $kelulusan['predikat'] === 'A' ? 'success' : ($kelulusan['predikat'] === 'D' ? 'danger' : 'info') }}">{{ $kelulusan['predikat'] }}</span>
                            </td>
                            <td class="text-center">
                                @if (! $kelulusan['kelas_akhir'])
                                    <span class="badge-soft neutral">Belum kelas akhir</span>
                                @elseif ($kelulusan['lulus'])
                                    <span class="badge-soft success"><i class="bi bi-check2-circle"></i> LULUS</span>
                                @else
                                    <span class="badge-soft danger"><i class="bi bi-x-circle"></i> BELUM LULUS</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#rekap{{ $peserta->user_id }}">
                                    <i class="bi bi-list-check me-1"></i> Rekap
                                </button>
                                @if ($kelulusan['lulus'])
                                    <a href="{{ route('admin.kelulusan.cetak', ['peserta' => $peserta, 'kelas' => $kelulusan['kode']]) }}" target="_blank" class="btn btn-gradient btn-sm">
                                        <i class="bi bi-printer me-1"></i> Cetak
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-award"></i>
                                    Belum ada peserta yang cocok dengan filter.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $daftar->links() }}</div>
    </div>

    @foreach ($daftar as $row)
        @php
            $peserta = $row['peserta'];
            $kelulusan = $row['kelulusan'];
        @endphp
        <div class="modal fade" id="rekap{{ $peserta->user_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold">Rekap Nilai &middot; {{ $peserta->nama }}</h5>
                            <div class="text-muted-2" style="font-size:.78rem">{{ $peserta->user_name }} &middot; {{ $kelulusan['kelas'] }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                            <span class="badge-soft {{ $kelulusan['lengkap'] ? 'success' : 'warning' }}">
                                {{ $kelulusan['rapors']->count() }} / {{ $kelulusan['moduls']->count() }} mata pelajaran
                            </span>
                            <span class="text-muted-2" style="font-size:.82rem">
                                Rata-rata {{ $kelulusan['rata_rata'] !== null ? number_format($kelulusan['rata_rata'], 1) : '-' }}
                                &middot; Nilai minimal {{ number_format($kelulusan['kkm'], 1) }}
                                &middot; Predikat {{ $kelulusan['predikat'] }}
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru Penilai</th>
                                        <th>Catatan</th>
                                        <th class="text-end">Nilai</th>
                                        <th class="text-center">Predikat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kelulusan['moduls'] as $modul)
                                        @php $raport = $kelulusan['rapors']->get($modul->modul_id); @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $modul->modul_nama }}</td>
                                            <td class="text-muted-2" style="font-size:.82rem">{{ $raport?->guru?->nama ?? '-' }}</td>
                                            <td class="text-muted-2" style="font-size:.82rem">{{ $raport?->raport_catatan ?: '-' }}</td>
                                            <td class="text-end fw-800" style="color:#4338ca">{{ $raport ? number_format((float) $raport->raport_nilai, 1) : '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge-soft {{ $raport?->predikat === 'A' ? 'success' : ($raport?->predikat === 'D' ? 'danger' : 'info') }}">
                                                    {{ $raport?->predikat ?? '-' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Tutup</button>
                        @if ($kelulusan['lulus'])
                            <a href="{{ route('admin.kelulusan.cetak', ['peserta' => $peserta, 'kelas' => $kelulusan['kode']]) }}" target="_blank" class="btn btn-gradient">
                                <i class="bi bi-printer me-1"></i> Cetak Surat
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
