@extends('layouts.admin')

@section('title', 'Pembayaran SPP')
@section('heading', 'Pembayaran SPP')
@section('eyebrow', 'Keuangan')

@section('content')
    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-1"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-value">{{ number_format($statistik['jumlah'], 0, ',', '.') }}</div>
            <div class="stat-label">Jumlah Transaksi</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-4"><i class="bi bi-wallet2"></i></div>
            <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['total'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Terverifikasi</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-3"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value">{{ number_format($statistik['menunggu'], 0, ',', '.') }}</div>
            <div class="stat-label">Menunggu Verifikasi</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-2"><i class="bi bi-x-circle"></i></div>
            <div class="stat-value">{{ number_format($statistik['ditolak'], 0, ',', '.') }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cari Pembayaran / Peserta</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nomor pembayaran, nama atau username peserta...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-soft flex-grow-1"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.spp.payments') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Riwayat Pembayaran</h3>
                <p class="section-sub">{{ $daftar->total() }} transaksi sesuai filter.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Peserta</th>
                        <th>Tagihan</th>
                        <th>Metode</th>
                        <th>Tanggal</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $payment)
                        <tr>
                            <td class="fw-semibold" style="font-size:.8rem">{{ $payment->payment_number }}</td>
                            <td>
                                <div class="fw-semibold">{{ $payment->student?->nama ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.72rem">{{ $payment->student?->grup?->grup_nama ?? '-' }} &middot; {{ $payment->school?->school_name ?? '-' }}</div>
                            </td>
                            <td>
                                @php $bill = $payment->bill ?? $payment->allocations->first()?->bill; @endphp
                                @if ($bill)
                                    <a href="{{ route('admin.spp.show', $bill) }}" style="font-size:.78rem">{{ $bill->bill_number }}</a>
                                @else
                                    <span class="text-muted-2" style="font-size:.78rem">-</span>
                                @endif
                            </td>
                            <td class="text-muted-2" style="font-size:.8rem">{{ $payment->method?->method_name ?? '-' }}</td>
                            <td class="text-muted-2 text-nowrap" style="font-size:.8rem">{{ ($payment->paid_at ?? $payment->created_at)?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                            <td class="text-end text-nowrap fw-semibold">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                            <td class="text-center"><span class="badge-soft {{ $payment->status_color }}">{{ $payment->status_label }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.spp.payment.show', $payment) }}" class="btn btn-soft btn-sm">Detail</a>
                                @if ($payment->status === \App\Models\SchoolPayment::STATUS_VERIFIED)
                                    <a href="{{ route('admin.spp.payment.receipt', $payment) }}" class="btn btn-ghost btn-sm" title="Kwitansi"><i class="bi bi-printer"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty-state"><i class="bi bi-cash-stack"></i>Belum ada pembayaran SPP.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $daftar->links() }}</div>
    </div>
@endsection
