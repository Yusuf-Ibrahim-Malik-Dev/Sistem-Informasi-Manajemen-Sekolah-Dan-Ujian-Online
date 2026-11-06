@extends('layouts.admin')

@section('title', 'Pembayaran')
@section('heading', 'Verifikasi Pembayaran')
@section('eyebrow', 'Manajemen Ujian')

@section('content')
    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-3"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value">{{ $statistik['menunggu'] }}</div>
            <div class="stat-label">Menunggu Verifikasi</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-4"><i class="bi bi-patch-check"></i></div>
            <div class="stat-value">{{ $statistik['diverifikasi'] }}</div>
            <div class="stat-label">Terverifikasi</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-2"><i class="bi bi-x-circle"></i></div>
            <div class="stat-value">{{ $statistik['ditolak'] }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-1"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value" style="font-size:1.25rem">Rp {{ number_format($statistik['nilai_verifikasi'], 0, ',', '.') }}</div>
            <div class="stat-label">Nilai Terverifikasi</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cari Peserta / Orang Tua</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama peserta, orang tua, atau username...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ujian</label>
                <select name="tes" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua ujian</option>
                    @foreach ($tes as $item)
                        <option value="{{ $item->tes_id }}" @selected((string) request('tes') === (string) $item->tes_id)>{{ $item->tes_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Pembayaran::statusOptions() as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-soft"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Daftar Pembayaran</h3>
                <p class="section-sub">{{ $daftar->total() }} data. Periksa bukti transfer sebelum memverifikasi.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Ujian</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Status</th>
                        <th>Bukti</th>
                        <th>Pengunggah</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $bayar)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $bayar->peserta?->nama ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $bayar->peserta?->grup?->grup_nama ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.84rem">{{ $bayar->tes?->tes_nama ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.72rem">{{ $bayar->created_at?->translatedFormat('d M Y H:i') }}</div>
                            </td>
                            <td class="text-end fw-800" style="color:#4338ca">Rp {{ number_format($bayar->pembayaran_nominal, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge-soft {{ $bayar->status_color }}">{{ $bayar->status_label }}</span>
                                @if ($bayar->verifikator)
                                    <div class="text-muted-2 mt-1" style="font-size:.7rem">oleh {{ $bayar->verifikator->nama }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($bayar->pembayaran_bukti)
                                    <a href="{{ route('admin.pembayaran.bukti', $bayar) }}" target="_blank" class="fw-semibold" style="font-size:.8rem;color:#4f46e5">
                                        <i class="bi bi-paperclip"></i> Lihat bukti
                                    </a>
                                @else
                                    <span class="text-muted-2" style="font-size:.78rem">-</span>
                                @endif
                                @if ($bayar->pembayaran_catatan)
                                    <div class="text-muted-2 mt-1" style="font-size:.72rem">Catatan ortu: {{ $bayar->pembayaran_catatan }}</div>
                                @endif
                                @if ($bayar->pembayaran_catatan_admin)
                                    <div class="text-muted-2 mt-1" style="font-size:.72rem">Catatan admin: {{ $bayar->pembayaran_catatan_admin }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.8rem">{{ $bayar->orangTua?->ortu_nama ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.72rem">{{ $bayar->orangTua?->ortu_username ?? '-' }}</div>
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($bayar->pembayaran_status !== \App\Models\Pembayaran::DIVERIFIKASI)
                                    <button class="btn btn-soft btn-sm" data-bs-toggle="modal" data-bs-target="#verifikasi{{ $bayar->pembayaran_id }}">
                                        <i class="bi bi-check2-square me-1"></i> Verifikasi
                                    </button>
                                @else
                                    <span class="badge-soft success">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-wallet2"></i>
                                    Belum ada data pembayaran sesuai filter.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $daftar->links() }}</div>
    </div>

    @foreach ($daftar as $bayar)
        <div class="modal fade" id="verifikasi{{ $bayar->pembayaran_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.pembayaran.verifikasi', $bayar) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold">Verifikasi Pembayaran</h5>
                            <div class="text-muted-2" style="font-size:.78rem">{{ $bayar->peserta?->nama ?? '-' }} &middot; {{ $bayar->tes?->tes_nama ?? '-' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center p-3 mb-3" style="border-radius:14px;background:#eef2ff;border:1px solid #c7d2fe">
                            <span class="text-muted-2" style="font-size:.82rem">Nominal</span>
                            <span class="fw-800" style="font-size:1.1rem;color:#4338ca">Rp {{ number_format($bayar->pembayaran_nominal, 0, ',', '.') }}</span>
                        </div>

                        @if ($bayar->pembayaran_bukti)
                            <a href="{{ route('admin.pembayaran.bukti', $bayar) }}" target="_blank" class="btn btn-soft w-100 mb-3"><i class="bi bi-box-arrow-up-right me-1"></i> Buka Bukti Pembayaran</a>
                        @endif

                        <div class="mb-0">
                            <label class="form-label">Catatan Admin</label>
                            <textarea name="catatan_admin" class="form-control" rows="3" maxlength="500" placeholder="Wajib diisi jika menolak pembayaran...">{{ $bayar->pembayaran_catatan_admin }}</textarea>
                            <div class="text-muted-2 mt-1" style="font-size:.74rem">Catatan penolakan akan terlihat oleh orang tua.</div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="submit" name="aksi" value="tolak" class="btn btn-ghost text-danger"><i class="bi bi-x-circle me-1"></i> Tolak</button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="aksi" value="verifikasi" class="btn btn-gradient"><i class="bi bi-check2-circle me-1"></i> Verifikasi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
